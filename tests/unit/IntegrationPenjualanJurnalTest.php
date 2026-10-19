<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\JurnalService;
use App\Services\NeracaSaldoService;
use App\Services\StokService;
use App\Models\BarangModel;
use App\Models\PenjualanModel;

/**
 * IntegrationPenjualanJurnalTest
 *
 * Menguji aliran data lintas modul Pertemuan 9:
 *   1. JurnalService: Pengujian aturan bisnis double-entry & balance
 *   2. NeracaSaldoService: Pengujian rekapitulasi Neraca Saldo seimbang
 *   3. End-to-End Atomik: Kasir Penjualan → Potong Stok → Jurnal Otomatis → Neraca Saldo
 *   4. Validasi Rollback: Pembelian over-stock digagalkan secara atomik (ACID)
 */
final class IntegrationPenjualanJurnalTest extends CIUnitTestCase
{
    private JurnalService $jurnalService;
    private NeracaSaldoService $neracaService;
    private StokService $stokService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jurnalService = new JurnalService();
        $this->neracaService = new NeracaSaldoService();
        $this->stokService   = new StokService();
    }

    // ───────────────────────────────────────────
    //  1. PENGUJIAN JURNALSERVICE (PURE LOGIC)
    // ───────────────────────────────────────────

    public function testJurnalPenjualanBalanceAntaraDebitDanKredit(): void
    {
        $netto = 150000.0;
        $hpp   = 90000.0;

        $jurnal = $this->jurnalService->buatEntriJurnalPenjualan($netto, $hpp, '1111', 'Penjualan Kopi');

        $this->assertTrue($this->jurnalService->cekBalance($jurnal['entries']));
        $this->assertSame(240000.0, $jurnal['total_debit']);
        $this->assertSame(240000.0, $jurnal['total_kredit']);
        $this->assertSame($jurnal['total_debit'], $jurnal['total_kredit']);
        $this->assertCount(4, $jurnal['entries']);
    }

    public function testJurnalPenjualanTanpaHppHanyaDuaAyat(): void
    {
        $netto = 75000.0;
        $hpp   = 0.0;

        $jurnal = $this->jurnalService->buatEntriJurnalPenjualan($netto, $hpp, '1111', 'Penjualan Jasa/Produk 0 HPP');

        $this->assertTrue($this->jurnalService->cekBalance($jurnal['entries']));
        $this->assertSame(75000.0, $jurnal['total_debit']);
        $this->assertSame(75000.0, $jurnal['total_kredit']);
        $this->assertCount(2, $jurnal['entries']);
    }

    public function testJurnalPenjualanNilaiNolAtauNegatifDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->jurnalService->buatEntriJurnalPenjualan(0.0, 5000.0);
    }

    public function testJurnalPenjualanHppNegatifDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->jurnalService->buatEntriJurnalPenjualan(100000.0, -100.0);
    }

    // ───────────────────────────────────────────
    //  2. PENGUJIAN NERACA SALDO SERVICE
    // ───────────────────────────────────────────

    public function testHitungNeracaSaldoSeimbang(): void
    {
        $coaList = [
            ['kode_coa' => '1111', 'nama_coa' => 'Kas', 'saldo_normal' => 'Debit', 'saldo_awal' => 5000000.0, 'is_header' => 'D'],
            ['kode_coa' => '1131', 'nama_coa' => 'Persediaan', 'saldo_normal' => 'Debit', 'saldo_awal' => 2000000.0, 'is_header' => 'D'],
            ['kode_coa' => '3111', 'nama_coa' => 'Modal', 'saldo_normal' => 'Kredit', 'saldo_awal' => 7000000.0, 'is_header' => 'D'],
            ['kode_coa' => '4110', 'nama_coa' => 'Penjualan', 'saldo_normal' => 'Kredit', 'saldo_awal' => 0.0, 'is_header' => 'D'],
            ['kode_coa' => '5120', 'nama_coa' => 'HPP', 'saldo_normal' => 'Debit', 'saldo_awal' => 0.0, 'is_header' => 'D'],
        ];

        // Simulasi mutasi penjualan 100rb (HPP 60rb):
        // Kas: +100rb (D) -> 5.100.000
        // Persediaan: -60rb (K) -> 1.940.000
        // HPP: +60rb (D) -> 60.000
        // Total Debit: 5.100.000 + 1.940.000 + 60.000 = 7.100.000
        // Modal: 7.000.000 (K)
        // Penjualan: +100rb (K) -> 100.000
        // Total Kredit: 7.000.000 + 100.000 = 7.100.000
        $mutasi = [
            '1111' => ['debit' => 100000.0, 'kredit' => 0.0],
            '4110' => ['debit' => 0.0, 'kredit' => 100000.0],
            '5120' => ['debit' => 60000.0, 'kredit' => 0.0],
            '1131' => ['debit' => 0.0, 'kredit' => 60000.0],
        ];

        $hasil = $this->neracaService->hitungNeracaSaldo($coaList, $mutasi);

        $this->assertTrue($hasil['is_balance']);
        $this->assertSame(0.0, $hasil['selisih']);
        $this->assertSame(7100000.0, $hasil['tot_akhir_debit']);
        $this->assertSame(7100000.0, $hasil['tot_akhir_kredit']);
    }

    // ───────────────────────────────────────────
    //  3. PENGUJIAN INTEGRASI DATABASE (END-TO-END)
    // ───────────────────────────────────────────

    public function testAliranDataEndToEndPenjualanHinggaNeracaSaldo(): void
    {
        $db = \Config\Database::connect('default');

        // 1. Ambil barang uji coba
        $barang = $db->table('barang')->where('is_deleted', 0)->where('stok >=', 1)->get()->getRowArray();
        $this->assertNotNull($barang, 'Master barang harus memiliki data aktif');

        $stokAwal = (float) $barang['stok'];
        $qtyBeli  = 1.0;
        $noFaktur = 'TEST-UNIT-' . date('His') . '-' . rand(100, 999);

        $header = [
            'no_faktur'    => $noFaktur,
            'tgl_jual'     => date('Y-m-d'),
            'customer'     => 'Tester Unit P9',
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

        $penjualanModel = new PenjualanModel();

        // 2. Eksekusi alur penjualan atomik
        $res = $penjualanModel->simpanPenjualanAtomik_l1H($header, $items, 1);
        $this->assertTrue($res['success'], 'Penjualan atomik harus sukses: ' . ($res['message'] ?? ''));

        // 3. Verifikasi stok berkurang
        $barangTerbaru = $db->table('barang')->where('id', $barang['id'])->get()->getRowArray();
        $this->assertSame(round($stokAwal - $qtyBeli, 2), (float) $barangTerbaru['stok']);

        // 4. Verifikasi jurnal umum tercipta
        $jurnal = $db->table('jurnal_umum')->where('no_referensi', $noFaktur)->get()->getRowArray();
        $this->assertNotNull($jurnal, 'Jurnal umum harus otomatis terbentuk untuk faktur ' . $noFaktur);
        $this->assertSame('PENJUALAN', $jurnal['sumber']);
        $this->assertSame((float) $jurnal['total_debit'], (float) $jurnal['total_kredit']);

        // 5. Verifikasi ayat jurnal detail
        $jurnalDetails = $db->table('jurnal_umum_d')->where('id_jurnal', $jurnal['id'])->get()->getResultArray();
        $this->assertCount(4, $jurnalDetails);

        // 6. Verifikasi Neraca Saldo tetap 100% Balanced
        $neraca = $this->neracaService->ambilNeracaSaldoDariDb($db);
        $this->assertTrue($neraca['is_balance'], 'Neraca saldo harus tetap seimbang setelah transaksi');
        $this->assertLessThan(0.01, $neraca['selisih']);
    }

    // ───────────────────────────────────────────
    //  4. PENGUJIAN VALIDASI ROLLBACK (OVER-STOCK)
    // ───────────────────────────────────────────

    public function testRollbackSaatKuantitasMelebihiStok(): void
    {
        $db = \Config\Database::connect('default');

        $barang = $db->table('barang')->where('is_deleted', 0)->get()->getRowArray();
        $this->assertNotNull($barang);

        $stokTersedia = (float) $barang['stok'];
        $qtyKelebihan = $stokTersedia + 50000.0; // Melebihi stok
        $noFakturFail = 'TEST-FAIL-' . date('His') . '-' . rand(100, 999);

        $header = [
            'no_faktur'    => $noFakturFail,
            'tgl_jual'     => date('Y-m-d'),
            'customer'     => 'Tester Overstock',
            'diskon'       => 0,
            'metode_bayar' => 'Tunai',
        ];

        $items = [
            [
                'kode_barang'  => $barang['kode_barang'],
                'qty'          => $qtyKelebihan,
                'harga_satuan' => (float) $barang['harga_jual'],
            ],
        ];

        $penjualanModel = new PenjualanModel();
        $res = $penjualanModel->simpanPenjualanAtomik_l1H($header, $items, 1);
        $this->assertFalse($res['success'], 'Order melebihi stok harus ditolak');

        // Verifikasi tidak ada faktur tersimpan
        $fakturCek = $db->table('penjualan')->where('no_faktur', $noFakturFail)->get()->getRowArray();
        $this->assertNull($fakturCek, 'Faktur yang gagal tidak boleh ada di database (Rollback)');

        // Verifikasi tidak ada jurnal umum tersimpan
        $jurnalCek = $db->table('jurnal_umum')->where('no_referensi', $noFakturFail)->get()->getRowArray();
        $this->assertNull($jurnalCek, 'Jurnal umum untuk transaksi gagal tidak boleh ada di database (Rollback)');

        // Verifikasi stok tidak berubah sama sekali
        $barangSetelah = $db->table('barang')->where('id', $barang['id'])->get()->getRowArray();
        $this->assertSame($stokTersedia, (float) $barangSetelah['stok'], 'Stok barang harus utuh tidak berkurang');
    }
}
