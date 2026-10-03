<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\HargaService;

/**
 * ═══════════════════════════════════════════════════════════════════
 *  HargaServiceTest — Unit Tests untuk Business Rule: Kalkulasi Pajak & Diskon
 * ═══════════════════════════════════════════════════════════════════
 *
 * Memastikan kalkulasi pajak dan diskon tidak membocorkan nilai transaksi:
 *   ✅ Diskon bertingkat dihitung berdasarkan tier yang tepat
 *   ✅ PPN 11% dihitung atas DPP (setelah diskon)
 *   ✅ Total akhir = DPP + PPN
 *   ✅ Kalkulasi multi-item akurat
 *   ✅ Tidak ada nilai negatif yang bocor
 *
 * Run: vendor/bin/phpunit tests/unit/HargaServiceTest.php
 *
 * @internal
 */
final class HargaServiceTest extends CIUnitTestCase
{
    private HargaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HargaService();
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 1: hitungPersenDiskon() — Tiered Discount
    // ───────────────────────────────────────────

    /**
     * @test
     * Subtotal 400rb (< 500rb) → 0% diskon
     */
    public function testDiskonNolPersenUntukSubtotalDiBawah500rb(): void
    {
        $persen = $this->service->hitungPersenDiskon(400_000);
        $this->assertSame(0.0, $persen, 'Subtotal < 500rb harus mendapat diskon 0%');
    }

    /**
     * @test
     * Subtotal tepat 500rb → 5% diskon (masuk tier pertama)
     */
    public function testDiskon5PersenUntukSubtotalTepat500rb(): void
    {
        $persen = $this->service->hitungPersenDiskon(500_000);
        $this->assertSame(0.05, $persen, 'Subtotal tepat 500rb harus mendapat diskon 5%');
    }

    /**
     * @test
     * Subtotal 999.999 (< 1jt) → masih 5% diskon
     */
    public function testDiskon5PersenUntukSubtotalDiBawah1jt(): void
    {
        $persen = $this->service->hitungPersenDiskon(999_999);
        $this->assertSame(0.05, $persen, 'Subtotal 999.999 harus diskon 5% (belum masuk tier 1jt)');
    }

    /**
     * @test
     * Subtotal tepat 1jt → 10% diskon (tier kedua)
     */
    public function testDiskon10PersenUntukSubtotalTepat1jt(): void
    {
        $persen = $this->service->hitungPersenDiskon(1_000_000);
        $this->assertSame(0.10, $persen, 'Subtotal tepat 1jt harus mendapat diskon 10%');
    }

    /**
     * @test
     * Subtotal 3jt (1jt–5jt) → 10% diskon
     */
    public function testDiskon10PersenUntukSubtotal3jt(): void
    {
        $persen = $this->service->hitungPersenDiskon(3_000_000);
        $this->assertSame(0.10, $persen);
    }

    /**
     * @test
     * Subtotal tepat 5jt → 15% diskon (tier ketiga)
     */
    public function testDiskon15PersenUntukSubtotalTepat5jt(): void
    {
        $persen = $this->service->hitungPersenDiskon(5_000_000);
        $this->assertSame(0.15, $persen, 'Subtotal tepat 5jt harus mendapat diskon 15%');
    }

    /**
     * @test
     * Subtotal tepat 20jt → 20% diskon (tier tertinggi)
     */
    public function testDiskon20PersenUntukSubtotalTepat20jt(): void
    {
        $persen = $this->service->hitungPersenDiskon(20_000_000);
        $this->assertSame(0.20, $persen, 'Subtotal >= 20jt harus mendapat diskon maksimal 20%');
    }

    /**
     * @test
     * Subtotal sangat besar (100jt) → tetap 20% diskon maksimal
     */
    public function testDiskonTidakMelebihi20PersenMeskiSubtotalSangatBesar(): void
    {
        $persen = $this->service->hitungPersenDiskon(100_000_000);
        $this->assertSame(0.20, $persen, 'Diskon maksimal 20%, tidak boleh lebih');
    }

    /**
     * @test
     * Subtotal negatif → Exception (tidak valid)
     */
    public function testHitungPersenDiskonThrowsExceptionWhenSubtotalNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak boleh negatif/i');

        $this->service->hitungPersenDiskon(-100_000);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 2: hitungNilaiDiskon()
    // ───────────────────────────────────────────

    /**
     * @test
     * Subtotal 2jt → diskon 10% = 200rb
     */
    public function testHitungNilaiDiskonReturnRupiahBenar(): void
    {
        $nilai = $this->service->hitungNilaiDiskon(2_000_000);
        $this->assertSame(200_000.0, $nilai, 'Nilai diskon 10% dari 2jt harus 200rb');
    }

    /**
     * @test
     * Subtotal 400rb (< 500rb) → diskon 0 rupiah
     */
    public function testHitungNilaiDiskonNolUntukSubtotalKecil(): void
    {
        $nilai = $this->service->hitungNilaiDiskon(400_000);
        $this->assertSame(0.0, $nilai, 'Diskon 0% berarti nilai diskon = 0');
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 3: hitungPpn()
    // ───────────────────────────────────────────

    /**
     * @test
     * DPP 1.000.000 → PPN 11% = 110.000
     */
    public function testHitungPpnReturnNilaiBenar(): void
    {
        $ppn = $this->service->hitungPpn(1_000_000);
        $this->assertSame(110_000.0, $ppn, 'PPN 11% dari 1jt harus 110rb');
    }

    /**
     * @test
     * DPP 0 → PPN 0 (tidak ada transaksi)
     */
    public function testHitungPpnNolReturnNol(): void
    {
        $ppn = $this->service->hitungPpn(0);
        $this->assertSame(0.0, $ppn);
    }

    /**
     * @test
     * DPP negatif → Exception (tidak valid)
     */
    public function testHitungPpnThrowsExceptionWhenDppNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->hitungPpn(-500_000);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 4: kalkulasiHargaFinal() — End-to-End
    // ───────────────────────────────────────────

    /**
     * @test
     * Kalkulasi lengkap subtotal 400rb (diskon 0%):
     *   diskon = 0
     *   dpp    = 400.000
     *   ppn    = 44.000
     *   total  = 444.000
     */
    public function testKalkulasiHargaFinalTanpaDiskon(): void
    {
        $result = $this->service->kalkulasiHargaFinal(400_000);

        $this->assertSame(400_000.0, $result['subtotal']);
        $this->assertSame(0.0,       $result['persen_diskon']);
        $this->assertSame(0.0,       $result['nilai_diskon']);
        $this->assertSame(400_000.0, $result['dpp']);
        $this->assertSame(44_000.0,  $result['ppn']);
        $this->assertSame(444_000.0, $result['total_akhir']);
    }

    /**
     * @test
     * Kalkulasi lengkap subtotal 2jt (diskon 10%):
     *   diskon = 200.000
     *   dpp    = 1.800.000
     *   ppn    = 198.000
     *   total  = 1.998.000
     */
    public function testKalkulasiHargaFinalDenganDiskon10Persen(): void
    {
        $result = $this->service->kalkulasiHargaFinal(2_000_000);

        $this->assertSame(2_000_000.0, $result['subtotal']);
        $this->assertSame(0.10,        $result['persen_diskon']);
        $this->assertSame(200_000.0,   $result['nilai_diskon']);
        $this->assertSame(1_800_000.0, $result['dpp']);
        $this->assertSame(198_000.0,   $result['ppn']);
        $this->assertSame(1_998_000.0, $result['total_akhir']);
    }

    /**
     * @test
     * Kalkulasi subtotal 20jt (diskon 20%):
     *   diskon = 4.000.000
     *   dpp    = 16.000.000
     *   ppn    = 1.760.000
     *   total  = 17.760.000
     */
    public function testKalkulasiHargaFinalDenganDiskonMaksimal20Persen(): void
    {
        $result = $this->service->kalkulasiHargaFinal(20_000_000);

        $this->assertSame(20_000_000.0, $result['subtotal']);
        $this->assertSame(0.20,         $result['persen_diskon']);
        $this->assertSame(4_000_000.0,  $result['nilai_diskon']);
        $this->assertSame(16_000_000.0, $result['dpp']);
        $this->assertSame(1_760_000.0,  $result['ppn']);
        $this->assertSame(17_760_000.0, $result['total_akhir']);
    }

    /**
     * @test
     * Subtotal 0 → semua komponen 0 (transaksi kosong bukan error)
     */
    public function testKalkulasiHargaFinalSubtotalNolReturnSemuaNol(): void
    {
        $result = $this->service->kalkulasiHargaFinal(0);

        $this->assertSame(0.0, $result['total_akhir']);
        $this->assertSame(0.0, $result['ppn']);
        $this->assertSame(0.0, $result['nilai_diskon']);
    }

    /**
     * @test
     * Pastikan PPN dihitung dari DPP (setelah diskon), BUKAN dari subtotal
     * Ini adalah aturan bisnis kritis — PPN atas gross amount adalah SALAH
     *
     * Subtotal 1jt, diskon 10% → DPP 900rb → PPN dari 900rb = 99rb
     * PPN yang SALAH jika dihitung dari subtotal = 1jt × 11% = 110rb
     */
    public function testPpnDihitungDariDppBukanSubtotal(): void
    {
        $result = $this->service->kalkulasiHargaFinal(1_000_000);

        // DPP = 1jt - (1jt × 10%) = 900rb
        $this->assertSame(900_000.0, $result['dpp'], 'DPP harus 900rb setelah diskon 10%');

        // PPN = 900rb × 11% = 99rb (bukan 110rb!)
        $this->assertSame(99_000.0, $result['ppn'], 'PPN harus dihitung dari DPP (900rb), bukan subtotal (1jt)');

        // Pastikan PPN BUKAN dari subtotal asli
        $ppnSalah = 1_000_000 * 0.11;
        $this->assertNotEquals($ppnSalah, $result['ppn'], 'PPN tidak boleh dihitung dari subtotal sebelum diskon');
    }

    /**
     * @test
     * Semua key wajib ada di array hasil kalkulasi
     */
    public function testKalkulasiHargaFinalReturnArrayDenganSemuaKey(): void
    {
        $result = $this->service->kalkulasiHargaFinal(1_000_000);

        $this->assertArrayHasKey('subtotal',      $result);
        $this->assertArrayHasKey('persen_diskon', $result);
        $this->assertArrayHasKey('nilai_diskon',  $result);
        $this->assertArrayHasKey('dpp',           $result);
        $this->assertArrayHasKey('ppn',           $result);
        $this->assertArrayHasKey('total_akhir',   $result);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 5: hitungSubtotalItems()
    // ───────────────────────────────────────────

    /**
     * @test
     * 3 item → subtotal = sum(qty × harga)
     */
    public function testHitungSubtotalItemsReturnJumlahBenar(): void
    {
        $items = [
            ['qty' => 2.0, 'harga_satuan' => 50_000],   // 100.000
            ['qty' => 5.0, 'harga_satuan' => 30_000],   // 150.000
            ['qty' => 1.0, 'harga_satuan' => 75_000],   // 75.000
        ];

        $subtotal = $this->service->hitungSubtotalItems($items);
        $this->assertSame(325_000.0, $subtotal, 'Subtotal harus 325rb (100rb+150rb+75rb)');
    }

    /**
     * @test
     * Array kosong → subtotal 0 (tidak error)
     */
    public function testHitungSubtotalItemsArrayKosongReturnNol(): void
    {
        $subtotal = $this->service->hitungSubtotalItems([]);
        $this->assertSame(0.0, $subtotal);
    }

    /**
     * @test
     * Item dengan harga negatif → Exception
     */
    public function testHitungSubtotalItemsThrowsExceptionWhenHargaNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/harga satuan tidak boleh negatif/i');

        $this->service->hitungSubtotalItems([
            ['qty' => 2.0, 'harga_satuan' => -10_000],
        ]);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 6: kalkulasiInvoice() — Full Pipeline
    // ───────────────────────────────────────────

    /**
     * @test
     * End-to-end: dari array item hingga total invoice final
     * Subtotal = 2jt → diskon 10% → DPP 1.8jt → PPN 198rb → Total 1.998jt
     */
    public function testKalkulasiInvoiceEndToEndBenar(): void
    {
        $items = [
            ['qty' => 2.0, 'harga_satuan' => 500_000],  // 1.000.000
            ['qty' => 4.0, 'harga_satuan' => 250_000],  // 1.000.000
        ];                                               // total = 2.000.000

        $result = $this->service->kalkulasiInvoice($items);

        $this->assertSame(2_000_000.0, $result['subtotal']);
        $this->assertSame(0.10,        $result['persen_diskon']);
        $this->assertSame(200_000.0,   $result['nilai_diskon']);
        $this->assertSame(1_800_000.0, $result['dpp']);
        $this->assertSame(198_000.0,   $result['ppn']);
        $this->assertSame(1_998_000.0, $result['total_akhir']);
    }
}
