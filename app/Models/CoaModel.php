<?php

namespace App\Models;

use App\Services\SaldoService;
use CodeIgniter\Model;

class CoaModel extends Model
{
    protected $table         = 'coa';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'kode_coa',
        'nama_coa',
        'saldo_normal',
        'is_header',
        'tipe',
        'saldo_awal',
        'is_off',
    ];

    protected $useTimestamps = false;

    /**
     * Subquery mutasi BKK (belum di-soft-delete) per akun.
     * id_coa = debit, id_coa_kb = kredit.
     */
    private function selectSaldo(): string
    {
        $debit = "(SELECT COALESCE(SUM(d.nilai), 0) FROM tbbkk_d d
                    JOIN tbbkk b ON b.id = d.id_bkk AND b.is_deleted = 0
                    WHERE d.id_coa = coa.id)";
        $kredit = "(SELECT COALESCE(SUM(d.nilai), 0) FROM tbbkk_d d
                    JOIN tbbkk b ON b.id = d.id_bkk AND b.is_deleted = 0
                    WHERE d.id_coa_kb = coa.id)";

        return "coa.id, coa.kode_coa, coa.nama_coa, coa.saldo_normal, coa.is_header,
                coa.tipe, coa.is_off, coa.saldo_awal,
                {$debit} AS mutasi_debit,
                {$kredit} AS mutasi_kredit,
                (coa.saldo_awal + CASE WHEN coa.saldo_normal = 'Debit'
                    THEN {$debit} - {$kredit}
                    ELSE {$kredit} - {$debit} END) AS saldo_akhir";
    }

    /**
     * Ambil semua COA beserta saldo awal, mutasi, dan saldo akhir,
     * diurutkan berdasarkan kode_coa
     */
    public function getAll_l1H(): array
    {
        return $this->db->table($this->table)
            ->select($this->selectSaldo(), false)
            ->orderBy('kode_coa', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu COA beserta saldo awal, mutasi, dan saldo akhir
     */
    public function getWithSaldo_l1H(int $id): ?array
    {
        $row = $this->db->table($this->table)
            ->select($this->selectSaldo(), false)
            ->where('coa.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Ambil COA berdasarkan tipe (kolom `tipe`)
     */
    public function getByTipe_l1H(string $tipe): array
    {
        return $this->select('id, kode_coa, nama_coa, saldo_normal, is_header, tipe, is_off')
            ->where('tipe', $tipe)
            ->where('is_off', 0)
            ->orderBy('kode_coa', 'ASC')
            ->findAll();
    }

    /**
     * Ambil daftar tipe yang unik (untuk filter)
     */
    public function getTipeList_l1H(): array
    {
        return $this->db->table($this->table)
            ->select('DISTINCT tipe')
            ->where('tipe IS NOT NULL')
            ->orderBy('tipe', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Validasi saldo akun kas/bank sebelum BKK disimpan.
     *
     * Setiap baris BKK mengkredit akun kas (id_coa_kb). Untuk akun bersaldo normal
     * Debit, saldo akhir tidak boleh menjadi negatif setelah BKK ini diposting.
     *
     * @param array    $baris     [['id_coa_kb' => int, 'nilai' => float], ...]
     * @param int|null $exceptBkk ID BKK yang sedang diedit (mutasi lamanya diabaikan)
     * @return string|null        Pesan error, atau null jika saldo mencukupi
     */
    public function cekSaldoKas_l1H(array $baris, ?int $exceptBkk = null): ?string
    {
        $perAkun = [];
        foreach ($baris as $b) {
            $perAkun[$b['id_coa_kb']] = ($perAkun[$b['id_coa_kb']] ?? 0) + (float) $b['nilai'];
        }

        foreach ($perAkun as $idAkun => $total) {
            $akun = $this->getWithSaldo_l1H((int) $idAkun);

            if (!$akun || $akun['saldo_normal'] !== 'Debit') {
                continue;
            }

            $saldo = (float) $akun['saldo_akhir'];

            // Saat edit: kembalikan efek BKK lama agar tidak terhitung dua kali
            if ($exceptBkk !== null) {
                $lama = $this->db->table('tbbkk_d')
                    ->select("COALESCE(SUM(CASE WHEN id_coa_kb = {$idAkun} THEN nilai ELSE 0 END), 0) AS kredit,
                              COALESCE(SUM(CASE WHEN id_coa = {$idAkun} THEN nilai ELSE 0 END), 0) AS debit", false)
                    ->where('id_bkk', $exceptBkk)
                    ->get()
                    ->getRowArray();
                $saldo += (float) $lama['kredit'] - (float) $lama['debit'];
            }

            if (!(new SaldoService())->cekSaldoCukup($akun['saldo_normal'], $saldo, $total)) {
                return sprintf(
                    'Saldo akun %s - %s tidak mencukupi. Saldo tersedia Rp %s, dibutuhkan Rp %s.',
                    $akun['kode_coa'],
                    $akun['nama_coa'],
                    number_format($saldo, 2, ',', '.'),
                    number_format($total, 2, ',', '.')
                );
            }
        }

        return null;
    }
    /**
     * Cek apakah kode_coa sudah digunakan (exclude id tertentu saat edit)
     */
    public function cekKode_l1H(string $kodeCoa, ?int $id = null): bool
    {
        $builder = $this->where('kode_coa', $kodeCoa);

        if ($id !== null) {
            $builder->where('id !=', $id);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Nonaktifkan: set is_off = 1
     */
    public function nonaktifkan_l1H(int $id): bool
    {
        return $this->update($id, ['is_off' => 1]);
    }

    /**
     * Aktifkan: set is_off = 0
     */
    public function aktifkan_l1H(int $id): bool
    {
        return $this->update($id, ['is_off' => 0]);
    }

    /**
     * Statistik total akun per tipe (untuk stat cards di halaman COA)
     */
    public function getTotalPerTipe_l1H(): array
    {
        return $this->db->table($this->table)
            ->select(
                "tipe,
                 COUNT(*) AS total,
                 SUM(CASE WHEN is_off = 0 THEN 1 ELSE 0 END) AS aktif,
                 SUM(CASE WHEN is_off = 1 THEN 1 ELSE 0 END) AS tidak_aktif",
                false
            )
            ->where('tipe IS NOT NULL')
            ->groupBy('tipe')
            ->orderBy('tipe', 'ASC')
            ->get()
            ->getResultArray();
    }
}
