<?php

namespace App\Models;

use CodeIgniter\Model;

class TbBeliModel extends Model
{
    protected $table         = 'tbbeli';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'no_beli',
        'tgl',
        'toko',
        'is_deleted',
    ];

    protected $useTimestamps = false;

    // ═══════════════════════════════════════════
    //  METHOD 1: simpanTransaksiPembelian
    //  Database Transaction + Rollback
    // ═══════════════════════════════════════════

    /**
     * Simpan transaksi pembelian (header + detail) secara atomik.
     *
     * Alur:
     *  1. Mulai transaksi   → $db->transStart()
     *  2. Insert ke tbbeli  (master)
     *  3. Loop insert ke tbbeli_d (detail)
     *     → jika harga item < 0, lempar Exception → rollback otomatis
     *  4. Tutup transaksi   → $db->transComplete()
     *  5. Return true jika sukses, false jika gagal / rollback
     *
     * @param array $master  ['no_beli' => 'BL-001', 'tgl' => '2026-09-16', 'toko' => 'Toko Maju']
     * @param array $detail  [['barng' => 'Beras 5kg', 'harga' => 75000], ...]
     * @return bool
     *
     * ──────────────────────────────────────────
     * UJI COBA 1 — Harga Negatif (Validasi Rollback)
     * ──────────────────────────────────────────
     * Kirim detail dengan salah satu item harga = -10000:
     *
     *   $master = ['no_beli' => 'BL-TEST', 'tgl' => '2026-09-16', 'toko' => 'Toko ABC'];
     *   $detail = [
     *       ['barng' => 'Gula 1kg',  'harga' => 15000],
     *       ['barng' => 'Minyak 2L', 'harga' => -10000],  // <- harga negatif
     *       ['barng' => 'Tepung 1kg','harga' => 12000],
     *   ];
     *   $hasil = $beliModel->simpanTransaksiPembelian($master, $detail);
     *
     * Hasil yang diharapkan:
     *  - $hasil === false
     *  - Tabel tbbeli   : TIDAK ada baris 'BL-TEST'
     *  - Tabel tbbeli_d : TIDAK ada baris apapun dari transaksi ini
     *  Seluruh transaksi ter-ROLLBACK karena Exception dilempar sebelum transComplete()
     *
     * ──────────────────────────────────────────
     * UJI COBA 2 — Simulasi Server Crash (die di tengah foreach)
     * ──────────────────────────────────────────
     * Tambahkan die() di dalam foreach detail untuk simulasi crash server:
     *
     *   foreach ($detail as $item) {
     *       // ... validasi harga ...
     *       $detailModel->insert([...]);
     *
     *       die("Koneksi Terputus!");  // <- uncomment ini untuk simulasi crash
     *   }
     *
     * Hasil yang diharapkan:
     *  - Script berhenti mendadak → transComplete() TIDAK pernah dipanggil
     *  - MySQL auto-rollback transaksi yang belum di-COMMIT
     *  - Tabel tbbeli   : TIDAK ada data tersimpan
     *  - Tabel tbbeli_d : TIDAK ada data tersimpan
     *  → Ini membuktikan bahwa transaction bersifat ATOMIK (all-or-nothing)
     * ──────────────────────────────────────────
     */
    public function simpanTransaksiPembelian(array $master, array $detail): bool
    {
        $db          = $this->db;
        $detailModel = new TbBeliDModel();

        // Mulai transaksi — semua operasi db setelah ini masuk satu unit atomik
        $db->transStart();

        try {
            // [STEP 1] Insert header ke tabel tbbeli
            $idBeli = $this->insert([
                'no_beli' => $master['no_beli'],
                'tgl'     => $master['tgl'],
                'toko'    => $master['toko'],
            ]);

            // [STEP 2] Loop dan insert setiap item detail ke tbbeli_d
            foreach ($detail as $item) {
                $harga = (float) ($item['harga'] ?? 0);

                // Validasi bisnis: harga tidak boleh negatif
                if ($harga < 0) {
                    // Lempar Exception → memaksa rollback
                    throw new \Exception(
                        "Harga item '{$item['barng']}' tidak boleh negatif (nilai: {$harga})."
                    );
                }

                // [UJI COBA 2] Uncomment baris berikut untuk simulasi server crash:
                // die("Koneksi Terputus!");

                $detailModel->insert([
                    'id_beli' => $idBeli,
                    'barng'   => $item['barng'],
                    'harga'   => $harga,
                ]);
            }

        } catch (\Exception $e) {
            // Rollback — batalkan semua operasi dalam blok ini
            $db->transRollback();

            // Catat error ke log CI4 untuk keperluan debugging
            log_message('error', '[TbBeliModel::simpanTransaksiPembelian] ' . $e->getMessage());

            return false;
        }

        // Tutup transaksi — COMMIT jika tidak ada error, ROLLBACK jika ada
        $db->transComplete();

        // transStatus() → true = COMMIT berhasil, false = ada rollback
        return $db->transStatus();
    }

    // ═══════════════════════════════════════════
    //  METHOD 2: softDeleteBeli
    //  Soft Delete (is_deleted = 1)
    // ═══════════════════════════════════════════

    /**
     * Soft delete transaksi beli berdasarkan ID.
     *
     * TIDAK menggunakan DELETE FROM — hanya update kolom is_deleted = 1
     * sehingga data tetap ada di database untuk audit / history.
     *
     * @param int $id  Primary key baris di tabel tbbeli
     * @return bool    true jika berhasil, false jika record tidak ditemukan
     */
    public function softDeleteBeli_l1H(int $id): bool
    {
        $record = $this->find($id);

        if (!$record) {
            return false;
        }

        $this->db->table('tbbeli')
            ->where('id', $id)
            ->update(['is_deleted' => 1]);

        return $this->db->affectedRows() > 0;
    }

    // ═══════════════════════════════════════════
    //  METHOD 3: catatAuditLog
    //  Audit Trail
    // ═══════════════════════════════════════════

    /**
     * Catat jejak aktivitas pengguna ke tabel audit_log.
     *
     * Dipanggil setiap kali ada operasi penting:
     *  - 'TAMBAH'      → setelah simpanTransaksiPembelian() berhasil
     *  - 'EDIT'        → setelah data berhasil diperbarui
     *  - 'SOFT_DELETE' → setelah softDeleteBeli_l1H() berhasil
     *
     * @param int    $userId    ID user yang melakukan aksi (dari session)
     * @param string $aksi      'TAMBAH' | 'EDIT' | 'SOFT_DELETE'
     * @param string $tabel     Nama tabel yang terdampak, misal 'tbbeli'
     * @param int    $recordId  ID record yang terdampak
     * @return bool
     *
     * Contoh pemakaian di Controller:
     *
     *   $userId = session()->get('user_id') ?? 0;
     *
     *   // Setelah simpan berhasil:
     *   $beliModel->catatAuditLog_l1H($userId, 'TAMBAH', 'tbbeli', $idBeli);
     *
     *   // Setelah soft delete:
     *   $beliModel->catatAuditLog_l1H($userId, 'SOFT_DELETE', 'tbbeli', $id);
     */
    public function catatAuditLog_l1H(int $userId, string $aksi, string $tabel, int $recordId, array $detail = []): bool
    {
        // Delegasi ke AuditLogger terpusat (user_id diambil otomatis dari session)
        // Parameter $userId dipertahankan untuk kompatibilitas pemanggil lama.
        return \App\Libraries\AuditLogger::catat($aksi, $tabel, $recordId, $detail);
    }

    // ═══════════════════════════════════════════
    //  HELPER — Query Umum
    // ═══════════════════════════════════════════

    /**
     * Ambil semua transaksi beli yang belum di-soft-delete
     */
    public function getAll_l1H(): array
    {
        return $this->db->table('tbbeli')
            ->where('is_deleted', 0)
            ->orderBy('tgl', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu transaksi beli berdasarkan ID (hanya yang aktif)
     */
    public function getById_l1H(int $id): ?array
    {
        $row = $this->db->table('tbbeli')
            ->where('id', $id)
            ->where('is_deleted', 0)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Cek apakah nomor beli sudah digunakan
     */
    public function cekNoBeli_l1H(string $noBeli, ?int $id = null): bool
    {
        $builder = $this->where('no_beli', $noBeli)
                        ->where('is_deleted', 0);
        if ($id !== null) {
            $builder->where('id !=', $id);
        }
        return $builder->countAllResults() > 0;
    }
}
