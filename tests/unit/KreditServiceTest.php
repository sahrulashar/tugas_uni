<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\KreditService;

/**
 * ═══════════════════════════════════════════════════════════════════
 *  KreditServiceTest — Unit Tests untuk Business Rule: Limit Kredit
 * ═══════════════════════════════════════════════════════════════════
 *
 * Memastikan logika kredit tidak membocorkan nilai transaksi:
 *   ✅ Order dalam limit → disetujui
 *   ✅ Order melebihi limit → ditolak
 *   ✅ Sisa kredit dihitung benar
 *   ✅ Utilisasi dihitung akurat
 *   ✅ Klasifikasi risiko akurat
 *   ✅ Pelunasan piutang valid
 *
 * Run: vendor/bin/phpunit tests/unit/KreditServiceTest.php
 *
 * @internal
 */
final class KreditServiceTest extends CIUnitTestCase
{
    private KreditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KreditService();
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 1: cekLimitKredit()
    // ───────────────────────────────────────────

    /**
     * @test
     * Order dalam batas limit → disetujui
     * Limit 10jt, piutang 3jt, order 5jt → total 8jt < 10jt → OK
     */
    public function testCekLimitKreditDalamBatasDiizinkan(): void
    {
        $result = $this->service->cekLimitKredit(10_000_000, 3_000_000, 5_000_000);
        $this->assertTrue($result, 'Order 5jt dengan piutang 3jt dari limit 10jt harus diizinkan');
    }

    /**
     * @test
     * Order tepat di batas limit → diizinkan (batas inklusif)
     * Limit 10jt, piutang 5jt, order 5jt → total 10jt = limit → OK
     */
    public function testCekLimitKreditTepat_AtasBatasDisetujui(): void
    {
        $result = $this->service->cekLimitKredit(10_000_000, 5_000_000, 5_000_000);
        $this->assertTrue($result, 'Total tepat sama dengan limit harus diizinkan');
    }

    /**
     * @test
     * Order melebihi limit → DITOLAK
     * Limit 10jt, piutang 8jt, order 5jt → total 13jt > 10jt → TOLAK
     */
    public function testCekLimitKreditMelebihiBatasDitolak(): void
    {
        $result = $this->service->cekLimitKredit(10_000_000, 8_000_000, 5_000_000);
        $this->assertFalse($result, 'Total 13jt melebihi limit 10jt harus ditolak');
    }

    /**
     * @test
     * Piutang sudah penuh (= limit), order 1 rupiah pun → DITOLAK
     */
    public function testCekLimitKreditPiutangSudahPenuhDitolak(): void
    {
        $result = $this->service->cekLimitKredit(10_000_000, 10_000_000, 1);
        $this->assertFalse($result, 'Piutang sudah di limit, order berapapun harus ditolak');
    }

    /**
     * @test
     * Nilai transaksi 0 atau negatif → Exception (bukan transaksi valid)
     */
    public function testCekLimitKreditThrowsExceptionWhenNilaiTransaksiNol(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/harus lebih dari 0/i');

        $this->service->cekLimitKredit(10_000_000, 0, 0);
    }

    /**
     * @test
     * Limit kredit negatif → Exception (data tidak valid)
     */
    public function testCekLimitKreditThrowsExceptionWhenLimitNegatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->cekLimitKredit(-1_000_000, 0, 500_000);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 2: hitungSisaKredit()
    // ───────────────────────────────────────────

    /**
     * @test
     * Sisa kredit = limit - piutang = 10jt - 3jt = 7jt
     */
    public function testHitungSisaKreditReturnSisaBenar(): void
    {
        $result = $this->service->hitungSisaKredit(10_000_000, 3_000_000);
        $this->assertSame(7_000_000.0, $result);
    }

    /**
     * @test
     * Piutang melebihi limit (anomali) → sisa = 0 (bukan negatif)
     */
    public function testHitungSisaKreditReturnNolWhenOverLimit(): void
    {
        $result = $this->service->hitungSisaKredit(5_000_000, 8_000_000);
        $this->assertSame(0.0, $result, 'Sisa kredit tidak boleh negatif, minimum 0');
    }

    /**
     * @test
     * Piutang = 0 (baru, belum ada transaksi) → sisa = limit penuh
     */
    public function testHitungSisaKreditTanpaPiutangReturnLimitPenuh(): void
    {
        $result = $this->service->hitungSisaKredit(10_000_000, 0);
        $this->assertSame(10_000_000.0, $result);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 3: hitungUtilisasi()
    // ───────────────────────────────────────────

    /**
     * @test
     * Piutang 5jt dari limit 10jt → utilisasi 50% = 0.5
     */
    public function testHitungUtilisasiReturnPersenBenar(): void
    {
        $result = $this->service->hitungUtilisasi(10_000_000, 5_000_000);
        $this->assertSame(0.5, $result);
    }

    /**
     * @test
     * Piutang = limit → utilisasi 100% = 1.0
     */
    public function testHitungUtilisasiPenuhReturnSatu(): void
    {
        $result = $this->service->hitungUtilisasi(10_000_000, 10_000_000);
        $this->assertSame(1.0, $result);
    }

    /**
     * @test
     * Limit 0 → Exception (pembagian nol tidak valid)
     */
    public function testHitungUtilisasiThrowsExceptionWhenLimitNol(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->hitungUtilisasi(0, 5_000_000);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 4: klasifikasiRisiko()
    // ───────────────────────────────────────────

    /**
     * @test
     * Utilisasi 50% → AMAN
     */
    public function testKlasifikasiRisikoAmanPada50Persen(): void
    {
        $this->assertSame('AMAN', $this->service->klasifikasiRisiko(0.5));
    }

    /**
     * @test
     * Utilisasi tepat 70% → AMAN (batas bawah PERHATIAN belum terlewat)
     */
    public function testKlasifikasiRisikoAmanPada70Persen(): void
    {
        $this->assertSame('AMAN', $this->service->klasifikasiRisiko(0.70));
    }

    /**
     * @test
     * Utilisasi 80% → PERHATIAN (70%–90%)
     */
    public function testKlasifikasiRisikoPerhatianPada80Persen(): void
    {
        $this->assertSame('PERHATIAN', $this->service->klasifikasiRisiko(0.80));
    }

    /**
     * @test
     * Utilisasi 95% → KRITIS (90%–100%)
     */
    public function testKlasifikasiRisikoKritisPada95Persen(): void
    {
        $this->assertSame('KRITIS', $this->service->klasifikasiRisiko(0.95));
    }

    /**
     * @test
     * Utilisasi > 100% → OVER_LIMIT (harus ditolak sistem)
     */
    public function testKlasifikasiRisikoOverLimitPadaDiAtas100Persen(): void
    {
        $this->assertSame('OVER_LIMIT', $this->service->klasifikasiRisiko(1.05));
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 5: prosesPelunasan()
    // ───────────────────────────────────────────

    /**
     * @test
     * Bayar sebagian: piutang 5jt, bayar 2jt → sisa 3jt
     */
    public function testProsesPelunasanSebagianReturnSisaBenar(): void
    {
        $result = $this->service->prosesPelunasan(5_000_000, 2_000_000);
        $this->assertSame(3_000_000.0, $result);
    }

    /**
     * @test
     * Bayar penuh: piutang 5jt, bayar 5jt → sisa 0 (lunas)
     */
    public function testProsesPelunasanPenuhReturnNol(): void
    {
        $result = $this->service->prosesPelunasan(5_000_000, 5_000_000);
        $this->assertSame(0.0, $result);
    }

    /**
     * @test
     * Over-payment: bayar lebih dari piutang → Exception
     * Aturan bisnis: over-payment TIDAK diizinkan (mencegah manipulasi neraca)
     */
    public function testProsesPelunasanOverPaymentThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/over-payment tidak diizinkan/i');

        $this->service->prosesPelunasan(1_000_000, 2_000_000);
    }

    // ───────────────────────────────────────────
    //  TEST GROUP 6: evaluasiOrderKredit()
    // ───────────────────────────────────────────

    /**
     * @test
     * Order valid dalam limit → evaluasi menyetujui
     */
    public function testEvaluasiOrderKreditDisetujui(): void
    {
        $result = $this->service->evaluasiOrderKredit(10_000_000, 2_000_000, 3_000_000);

        $this->assertTrue($result['disetujui']);
        $this->assertArrayHasKey('risiko', $result);
        $this->assertArrayHasKey('utilisasi', $result);
        $this->assertArrayHasKey('sisa_kredit', $result);
        $this->assertArrayHasKey('pesan', $result);
    }

    /**
     * @test
     * Order melebihi limit → evaluasi menolak
     */
    public function testEvaluasiOrderKreditDitolak(): void
    {
        $result = $this->service->evaluasiOrderKredit(5_000_000, 4_000_000, 2_000_000);

        $this->assertFalse($result['disetujui']);
        $this->assertStringContainsStringIgnoringCase('ditolak', $result['pesan']);
    }
}
