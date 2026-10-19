<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Services\StokService;
use App\Services\JurnalService;
use App\Libraries\AuditLogger;

class PenjualanModel extends Model
{
    protected $DBGroup       = 'default';
    protected $table         = 'penjualan';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'no_faktur',
        'tgl_jual',
        'customer',
        'total_bruto',
        'diskon',
        'total_netto',
        'total_hpp',
        'metode_bayar',
        'id_user',
        'is_deleted',
        'created_at',
    ];
    protected $useTimestamps = false;

    /**
     * Simpan transaksi penjualan kasir secara atomik:
     * Validasi Stok -> Potong Stok -> Simpan Penjualan -> Jurnal Otomatis -> Audit Trail.
     *
     * @param array $header
     * @param array $items
     * @param int   $userId
     * @return array ['success' => bool, 'no_faktur' => string, 'id_penjualan' => int, 'id_jurnal' => int, 'message' => string]
     *
     * @throws \Throwable Jika gagal (akan di-rollback otomatis)
     */
    public function simpanPenjualanAtomik_l1H(array $header, array $items, int $userId = 1, $db = null): array
    {
        $db = $db ?? \Config\Database::connect('default');
        $db->transBegin();

        $stokService   = new StokService();
        $jurnalService = new JurnalService();

        try {
            if (empty($items)) {
                throw new \InvalidArgumentException('Daftar barang belanja tidak boleh kosong.');
            }

            $totalBruto = 0.0;
            $totalHpp   = 0.0;
            $detailRows = [];

            // 1. Validasi stok & kalkulasi HPP + Penjualan
            foreach ($items as $item) {
                $kodeBarang = trim($item['kode_barang'] ?? '');
                $qty        = (float) ($item['qty'] ?? 0);

                if ($qty <= 0) {
                    throw new \InvalidArgumentException('Qty barang harus lebih dari 0.');
                }

                // Lock barang row for update (mencegah race condition)
                $barang = $db->table('barang')
                    ->where('kode_barang', $kodeBarang)
                    ->where('is_deleted', 0)
                    ->get()
                    ->getRowArray();

                if (!$barang) {
                    throw new \RuntimeException("Barang dengan kode {$kodeBarang} tidak ditemukan.");
                }

                $stokTersedia = (float) $barang['stok'];
                $hargaBeli    = (float) $barang['harga_beli'];
                $hargaJual    = (float) ($item['harga_satuan'] ?? $barang['harga_jual']);

                // Validasi aturan bisnis StokService
                if (!$stokService->cekStokCukup($stokTersedia, $qty)) {
                    throw new \RuntimeException(
                        "Stok barang '{$barang['nama_barang']}' tidak mencukupi (Tersedia: {$stokTersedia}, Diminta: {$qty})."
                    );
                }

                // Kurangi stok
                $sisaStok = $stokService->kurangiStok($stokTersedia, $qty);
                $db->table('barang')
                    ->where('id', $barang['id'])
                    ->update([
                        'stok'       => $sisaStok,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                $subtotal   = round($qty * $hargaJual, 2);
                $subtotalHpp= round($qty * $hargaBeli, 2);

                $totalBruto += $subtotal;
                $totalHpp   += $subtotalHpp;

                $detailRows[] = [
                    'kode_barang'  => $kodeBarang,
                    'nama_barang'  => $barang['nama_barang'],
                    'qty'          => $qty,
                    'harga_satuan' => $hargaJual,
                    'hpp_satuan'   => $hargaBeli,
                    'subtotal'     => $subtotal,
                ];
            }

            $diskon     = (float) ($header['diskon'] ?? 0);
            $totalNetto = max(0.0, round($totalBruto - $diskon, 2));
            $noFaktur   = $header['no_faktur'] ?? 'FK-' . date('Ymd-His');
            $tglJual    = $header['tgl_jual'] ?? date('Y-m-d');
            $customer   = $header['customer'] ?? 'Pelanggan Umum';
            $metode     = $header['metode_bayar'] ?? 'Tunai';

            // 2. Simpan Header Penjualan
            $penjualanData = [
                'no_faktur'    => $noFaktur,
                'tgl_jual'     => $tglJual,
                'customer'     => $customer,
                'total_bruto'  => $totalBruto,
                'diskon'       => $diskon,
                'total_netto'  => $totalNetto,
                'total_hpp'    => $totalHpp,
                'metode_bayar' => $metode,
                'id_user'      => $userId,
                'is_deleted'   => 0,
                'created_at'   => date('Y-m-d H:i:s'),
            ];

            $db->table('penjualan')->insert($penjualanData);
            $idPenjualan = $db->insertID();

            // 3. Simpan Detail Penjualan
            foreach ($detailRows as &$d) {
                $d['id_penjualan'] = $idPenjualan;
                $db->table('penjualan_d')->insert($d);
            }

            // 4. Trigger Automated Journaling Engine
            $idJurnal = $jurnalService->generateDanSimpanJurnal(
                $penjualanData,
                $detailRows,
                $db
            );

            // 5. Catat Audit Log
            AuditLogger::catat(
                'TAMBAH',
                'penjualan',
                $idPenjualan,
                ['after' => $penjualanData],
                'Transaksi',
                "Penjualan Kasir Faktur #{$noFaktur} (Netto: Rp " . number_format($totalNetto, 0, ',', '.') . ")"
            );

            AuditLogger::catat(
                'TAMBAH',
                'jurnal_umum',
                $idJurnal,
                ['after' => ['sumber' => 'PENJUALAN', 'no_referensi' => $noFaktur]],
                'Akuntansi',
                "Jurnal otomatis penjualan faktur #{$noFaktur}"
            );

            // Commit jika semua tahap sukses
            $db->transCommit();

            return [
                'success'      => true,
                'no_faktur'    => $noFaktur,
                'id_penjualan' => $idPenjualan,
                'id_jurnal'    => $idJurnal,
                'total_netto'  => $totalNetto,
                'total_hpp'    => $totalHpp,
                'message'      => "Transaksi {$noFaktur} berhasil diproses, stok berkurang, dan jurnal otomatis terbentuk.",
            ];

        } catch (\Throwable $e) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
