<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\StokService;

/**
 * ═══════════════════════════════════════════════════════════════════
 *  StokServiceTest — Unit Tests untuk Business Rule: Cek Stok Otomatis
 * ═══════════════════════════════════════════════════════════════════
 *
 * Memastikan logika stok tidak membocorkan nilai transaksi:
 *   ✅ Stok tidak pernah negatif
 *   ✅ Kurangi stok gagal jika tidak cukup
 *   ✅ Tambah stok valid
 *   ✅ Validasi multi-item order
 *
 * Run: vendor/bin/phpunit tests/unit/StokServiceTest.php
 *
 * @internal
 */
final class StokServiceTest extends CIUnitTestCase
{
    private StokService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StokService();
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 1: cekStokCukup()
    // ───────────────────────────────────────────

    /**
     * @test
     * Stok 100, diminta 50 → CUKUP (normal case)
     */
    public function testCekStokCukupReturnsTrueWhenStokMelebihi(): void
    {
        $result = $this->service->cekStokCukup(100.0, 50.0);
        $this->assertTrue($result, 'Stok 100 harus mencukupi untuk permintaan 50');
    }

    /**
     * @test
     * Stok 50, diminta 50 → CUKUP (tepat habis — masih valid)
     */
    public function testCekStokCukupReturnsTrueWhenStokSamaDenganQty(): void
    {
        $result = $this->service->cekStokCukup(50.0, 50.0);
        $this->assertTrue($result, 'Stok tepat sama dengan qty harus diterima (tidak kurang)');
    }

    /**
     * @test
     * Stok 20, diminta 50 → TIDAK CUKUP → return false
     * Aturan: sistem harus menolak order yang melebihi stok
     */
    public function testCekStokCukupReturnsFalseWhenStokKurang(): void
    {
        $result = $this->service->cekStokCukup(20.0, 50.0);
        $this->assertFalse($result, 'Stok 20 tidak cukup untuk permintaan 50 — harus return false');
    }

    /**
     * @test
     * Stok 0 (habis), diminta berapa pun → TIDAK CUKUP
     */
    public function testCekStokCukupReturnsFalseWhenStokKosong(): void
    {
        $result = $this->service->cekStokCukup(0.0, 1.0);
        $this->assertFalse($result, 'Stok kosong harus selalu return false');
    }

    /**
     * @test
     * Qty negatif → Exception (qty tidak boleh negatif/nol)
     */
    public function testCekStokCukupThrowsExceptionWhenQtyNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/harus lebih dari 0/i');

        $this->service->cekStokCukup(100.0, -5.0);
    }

    /**
     * @test
     * Stok negatif di database → Exception (data korup/anomali)
     */
    public function testCekStokCukupThrowsExceptionWhenStokNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak boleh negatif/i');

        $this->service->cekStokCukup(-10.0, 5.0);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 2: kurangiStok()
    // ───────────────────────────────────────────

    /**
     * @test
     * Kurangi stok normal: 100 - 30 = 70
     */
    public function testKurangiStokBerhasilReturnSisaStok(): void
    {
        $result = $this->service->kurangiStok(100.0, 30.0);
        $this->assertSame(70.0, $result, 'Sisa stok harus 70 setelah dikurangi 30 dari 100');
    }

    /**
     * @test
     * Kurangi stok hingga 0 → valid (stok habis bukan error)
     */
    public function testKurangiStokHingnaHabisReturnNol(): void
    {
        $result = $this->service->kurangiStok(50.0, 50.0);
        $this->assertSame(0.0, $result, 'Kurangi hingga habis harus return 0.0');
    }

    /**
     * @test
     * Kurangi stok melebihi tersedia → Exception
     * Ini adalah aturan bisnis KRITIS: stok TIDAK BOLEH negatif
     */
    public function testKurangiStokThrowsExceptionWhenMelebihiStok(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/stok tidak mencukupi/i');

        $this->service->kurangiStok(20.0, 50.0);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 3: tambahStok()
    // ───────────────────────────────────────────

    /**
     * @test
     * Penerimaan barang: stok bertambah dengan benar
     */
    public function testTambahStokBerhasilReturnStokBaru(): void
    {
        $result = $this->service->tambahStok(50.0, 100.0);
        $this->assertSame(150.0, $result, 'Stok harus menjadi 150 setelah ditambah 100');
    }

    /**
     * @test
     * Tambah stok ke stok kosong → valid
     */
    public function testTambahStokDariKosongBerhasil(): void
    {
        $result = $this->service->tambahStok(0.0, 25.0);
        $this->assertSame(25.0, $result, 'Stok 0 + 25 harus menjadi 25');
    }

    /**
     * @test
     * Tambah qty 0 → Exception (tidak ada artinya)
     */
    public function testTambahStokThrowsExceptionWhenQtyNol(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->tambahStok(50.0, 0.0);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 4: hitungNilaiStok()
    // ───────────────────────────────────────────

    /**
     * @test
     * Valuasi stok: 100 unit × Rp 50.000 = Rp 5.000.000
     */
    public function testHitungNilaiStokBerhasil(): void
    {
        $result = $this->service->hitungNilaiStok(100.0, 50_000.0);
        $this->assertSame(5_000_000.0, $result);
    }

    /**
     * @test
     * Stok 0 → nilai 0 (tidak error)
     */
    public function testHitungNilaiStokKosongReturnNol(): void
    {
        $result = $this->service->hitungNilaiStok(0.0, 50_000.0);
        $this->assertSame(0.0, $result);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 5: validateOrderItems()
    // ───────────────────────────────────────────

    /**
     * @test
     * Semua item cukup stok → valid = true, errors kosong
     */
    public function testValidateOrderItemsAllValidReturnsTrueNoErrors(): void
    {
        $items = [
            ['nama_barang' => 'Beras 5kg', 'stok' => 100.0, 'qty' => 50.0],
            ['nama_barang' => 'Minyak 2L', 'stok' => 80.0,  'qty' => 20.0],
            ['nama_barang' => 'Gula 1kg',  'stok' => 60.0,  'qty' => 30.0],
        ];

        $result = $this->service->validateOrderItems($items);

        $this->assertTrue($result['valid'], 'Semua item stok cukup — harus valid');
        $this->assertEmpty($result['errors'], 'Tidak ada error jika semua stok cukup');
    }

    /**
     * @test
     * Ada satu item yang stok kurang → valid = false, errors berisi item tersebut
     */
    public function testValidateOrderItemsOneItemKurangStokReturnsFalse(): void
    {
        $items = [
            ['nama_barang' => 'Beras 5kg', 'stok' => 100.0, 'qty' => 50.0],
            ['nama_barang' => 'Gula 1kg',  'stok' => 10.0,  'qty' => 50.0],  // stok kurang!
        ];

        $result = $this->service->validateOrderItems($items);

        $this->assertFalse($result['valid'], 'Ada item yang stok kurang — harus invalid');
        $this->assertNotEmpty($result['errors'], 'Harus ada pesan error');
        $this->assertStringContainsString('Gula 1kg', $result['errors'][0]);
    }

    /**
     * @test
     * Semua item stok kurang → semua masuk errors
     */
    public function testValidateOrderItemsAllKurangReturnMultipleErrors(): void
    {
        $items = [
            ['nama_barang' => 'Item A', 'stok' => 5.0,  'qty' => 10.0],
            ['nama_barang' => 'Item B', 'stok' => 3.0,  'qty' => 20.0],
        ];

        $result = $this->service->validateOrderItems($items);

        $this->assertFalse($result['valid']);
        $this->assertCount(2, $result['errors'], 'Kedua item harus masuk daftar error');
    }
}
