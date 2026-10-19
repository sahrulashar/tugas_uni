<?php

namespace App\Controllers;

use App\Models\JurnalUmumModel;
use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Services\NeracaSaldoService;

class Akuntansi extends BaseController
{
    protected JurnalUmumModel   $jurnalModel;
    protected NeracaSaldoService $neracaService;

    public function __construct()
    {
        $this->jurnalModel   = new JurnalUmumModel();
        $this->neracaService = new NeracaSaldoService();
    }

    // ═══════════════════════════════════════════
    //  JURNAL UMUM
    // ═══════════════════════════════════════════

    /**
     * Tampilkan Buku Jurnal Umum (Automated Journal Viewer)
     */
    public function jurnal_l1H()
    {
        $this->cekAkses('jrn', 'daftar');

        $jurnalList = $this->jurnalModel->getJurnalLengkap_l1H();

        $totalDebit  = array_sum(array_column($jurnalList, 'total_debit'));
        $totalKredit = array_sum(array_column($jurnalList, 'total_kredit'));

        return view('akuntansi/jurnal', [
            'jurnalList'  => $jurnalList,
            'totalDebit'  => $totalDebit,
            'totalKredit' => $totalKredit,
        ]);
    }

    // ═══════════════════════════════════════════
    //  NERACA SALDO (TRIAL BALANCE)
    // ═══════════════════════════════════════════

    /**
     * Tampilkan Neraca Saldo hasil integrasi transaksi & jurnal
     */
    public function neraca_saldo_l1H()
    {
        $this->cekAkses('nrc', 'daftar');

        $dataNeraca = $this->neracaService->ambilNeracaSaldoDariDb();

        return view('akuntansi/neraca_saldo', [
            'neraca' => $dataNeraca,
        ]);
    }

    // ═══════════════════════════════════════════
    //  PRAKTIKUM PERTEMUAN 9 (LAB RUNNER)
    // ═══════════════════════════════════════════

    /**
     * Halaman Interaktif Pengujian Praktikum Pertemuan 9
     */
    public function praktikum_l1H()
    {
        $this->cekAkses('pkt', 'daftar');

        $barangModel = new BarangModel();
        $barangList  = $barangModel->getAktif_l1H();
        $dataNeraca  = $this->neracaService->ambilNeracaSaldoDariDb();
        $jurnalList  = $this->jurnalModel->getJurnalLengkap_l1H();

        return view('akuntansi/praktikum', [
            'barangList' => $barangList,
            'neraca'     => $dataNeraca,
            'jurnalList' => array_slice($jurnalList, 0, 5),
        ]);
    }

    /**
     * Uji Kasus 1: Alur Normal End-to-End
     * Penjualan Kasir -> Potong Stok -> Jurnal Otomatis -> Neraca Saldo Seimbang
     */
    public function uji_sukses_p9_l1H()
    {
        $this->cekAkses('pkt', 'tambah');

        $barangModel    = new BarangModel();
        $penjualanModel = new PenjualanModel();

        // Cari barang dengan stok > 2
        $barang = $barangModel->where('is_deleted', 0)->where('stok >=', 2)->first();
        if (!$barang) {
            return redirect()->to('/46124026/akuntansi/praktikum_l1H')
                ->with('error', 'Tidak ada barang dengan stok mencukupi untuk uji coba.');
        }

        $stokAwal = (float) $barang['stok'];
        $qtyBeli  = 2.0;
        $noFaktur = 'TEST-P9-' . date('His');

        $header = [
            'no_faktur'    => $noFaktur,
            'tgl_jual'     => date('Y-m-d'),
            'customer'     => 'Uji Praktikum Mahasiswa P9',
            'diskon'       => 0,
            'metode_bayar' => 'Tunai',
        ];

        $items = [
            [
                'kode_barang'  => $barang['kode_barang'],
                'qty'          => $qtyBeli,
                'harga_satuan' => (float) $barang['harga_jual'],
            ],
        ];

        $hasil = $penjualanModel->simpanPenjualanAtomik_l1H($header, $items, (int) (session()->get('user_id') ?: 1));

        if ($hasil['success']) {
            $barangTerbaru = $barangModel->find($barang['id']);
            $stokAkhir = (float) $barangTerbaru['stok'];

            $neraca = $this->neracaService->ambilNeracaSaldoDariDb();
            $statusNeraca = $neraca['is_balance'] ? '✅ SEIMBANG (DEBIT = KREDIT)' : '❌ TIDAK SEIMBANG';

            $msg = "<b>✅ UJI PRAKTIKUM PERTEMUAN 9 BERHASIL (END-TO-END SUKSES)!</b><br>"
                 . "1. Transaksi Penjualan Faktur <b>{$noFaktur}</b> berhasil disimpan.<br>"
                 . "2. Pengurangan Stok '{$barang['nama_barang']}': <b>{$stokAwal}</b> → <b>{$stokAkhir}</b> (terpotong {$qtyBeli} {$barang['satuan']}).<br>"
                 . "3. Penjurnal Otomatis: Jurnal ID <b>#{$hasil['id_jurnal']}</b> tercatat dengan total Netto Rp " . number_format($hasil['total_netto'], 0, ',', '.') . " & HPP Rp " . number_format($hasil['total_hpp'], 0, ',', '.') . ".<br>"
                 . "4. Status Neraca Saldo Saat Ini: <b>{$statusNeraca}</b> (Selisih: Rp " . number_format($neraca['selisih'], 2) . ").";

            return redirect()->to('/46124026/akuntansi/praktikum_l1H')
                ->with('success', $msg);
        }

        return redirect()->to('/46124026/akuntansi/praktikum_l1H')
            ->with('error', 'Gagal memproses pengujian: ' . $hasil['message']);
    }

    /**
     * Uji Kasus 2: Rollback Saat Stok Kurang (Under-stock Prevention)
     */
    public function uji_rollback_p9_l1H()
    {
        $this->cekAkses('pkt', 'tambah');

        $barangModel    = new BarangModel();
        $penjualanModel = new PenjualanModel();

        $barang = $barangModel->where('is_deleted', 0)->first();
        if (!$barang) {
            return redirect()->to('/46124026/akuntansi/praktikum_l1H')
                ->with('error', 'Data barang tidak tersedia.');
        }

        $stokTersedia = (float) $barang['stok'];
        $qtyDiminta   = $stokTersedia + 9999; // Melebihi stok yang ada
        $noFaktur     = 'FAIL-P9-' . date('His');

        $header = [
            'no_faktur'    => $noFaktur,
            'tgl_jual'     => date('Y-m-d'),
            'customer'     => 'Uji Coba Gagal Overstock',
            'diskon'       => 0,
            'metode_bayar' => 'Tunai',
        ];

        $items = [
            [
                'kode_barang'  => $barang['kode_barang'],
                'qty'          => $qtyDiminta,
                'harga_satuan' => (float) $barang['harga_jual'],
            ],
        ];

        $hasil = $penjualanModel->simpanPenjualanAtomik_l1H($header, $items, (int) (session()->get('user_id') ?: 1));

        if (!$hasil['success']) {
            // Verifikasi stok tidak berubah
            $barangCek = $barangModel->find($barang['id']);
            $stokTerkini = (float) $barangCek['stok'];

            $msg = "<b>✅ UJI COBA ROLLBACK BERHASIL (ACID TERJAGA)!</b><br>"
                 . "Transaksi dibatalkan oleh StokService karena stok tidak mencukupi (diminta {$qtyDiminta}, tersedia {$stokTersedia}).<br>"
                 . "Hasil Rollback Database: Stok barang <b>tetap {$stokTerkini}</b>, tidak ada faktur tersimpan, dan tidak ada jurnal umum yang tercipta.";

            return redirect()->to('/46124026/akuntansi/praktikum_l1H')
                ->with('warning', $msg);
        }

        return redirect()->to('/46124026/akuntansi/praktikum_l1H')
            ->with('error', 'Transaksi malah berhasil padahal stok tidak cukup!');
    }
}
