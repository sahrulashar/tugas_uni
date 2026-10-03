<?php

use App\Services\HargaService;
use App\Services\KreditService;
use App\Services\SaldoService;
use App\Services\StokService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * BusinessRulesTest — pengujian lintas service ERP
 *
 *  1. SaldoService : saldo akhir COA & kecukupan kas untuk BKK
 *  2. Kebocoran    : pesan exception tidak boleh memuat nilai transaksi
 *  3. Alur gabungan: invoice → limit kredit → stok
 *
 * Run: vendor/bin/phpunit tests/unit/BusinessRulesTest.php
 *
 * @internal
 */
final class BusinessRulesTest extends CIUnitTestCase
{
    private SaldoService $saldo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saldo = new SaldoService();
    }

    // ───────────── SaldoService: saldo akhir ─────────────

    public function testSaldoAkhirNormalDebit(): void
    {
        // 1jt + debit 200rb - kredit 300rb = 900rb
        $this->assertSame(900000.0, $this->saldo->hitungSaldoAkhir('Debit', 1_000_000, 200_000, 300_000));
    }

    public function testSaldoAkhirNormalKredit(): void
    {
        // 5jt + kredit 1jt - debit 500rb = 5,5jt
        $this->assertSame(5500000.0, $this->saldo->hitungSaldoAkhir('Kredit', 5_000_000, 500_000, 1_000_000));
    }

    public function testSaldoAkhirTanpaMutasiSamaDenganSaldoAwal(): void
    {
        $this->assertSame(250000.0, $this->saldo->hitungSaldoAkhir('Debit', 250_000, 0, 0));
    }

    public function testSaldoAkhirSatuBkkMengurangiKasDanMenambahAkunTujuan(): void
    {
        $nilai = 500_000;

        $kas    = $this->saldo->hitungSaldoAkhir('Debit', 25_000_000, 0, $nilai);   // dikredit
        $beban  = $this->saldo->hitungSaldoAkhir('Debit', 0, $nilai, 0);            // didebit

        $this->assertSame(24500000.0, $kas);
        $this->assertSame(500000.0, $beban);
    }

    public function testSaldoAkhirMutasiNegatifDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->saldo->hitungSaldoAkhir('Debit', 0, -1, 0);
    }

    public function testSaldoAkhirSaldoNormalTidakDikenalDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->saldo->hitungSaldoAkhir('Netral', 0, 0, 0);
    }

    // ───────────── SaldoService: kecukupan kas ─────────────

    public function testKasCukupDiperbolehkan(): void
    {
        $this->assertTrue($this->saldo->cekSaldoCukup('Debit', 1_000_000, 400_000));
    }

    public function testKasTepatHabisDiperbolehkan(): void
    {
        $this->assertTrue($this->saldo->cekSaldoCukup('Debit', 1_000_000, 1_000_000));
    }

    public function testKasKurangSatuRupiahDitolak(): void
    {
        $this->assertFalse($this->saldo->cekSaldoCukup('Debit', 1_000_000, 1_000_000.01));
    }

    public function testKasKosongDitolak(): void
    {
        $this->assertFalse($this->saldo->cekSaldoCukup('Debit', 0, 1));
    }

    public function testKasSudahNegatifDitolak(): void
    {
        $this->assertFalse($this->saldo->cekSaldoCukup('Debit', -100, 1));
    }

    public function testAkunSaldoNormalKreditTidakDibatasi(): void
    {
        $this->assertTrue($this->saldo->cekSaldoCukup('Kredit', 0, 9_999_999));
    }

    public function testNilaiNolAtauNegatifDitolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->saldo->cekSaldoCukup('Debit', 1_000_000, 0);
    }

    // ───────────── Tidak membocorkan nilai transaksi ─────────────

    /**
     * Setiap aturan bisnis yang melempar exception dijalankan dengan nilai
     * "rahasia" yang unik; pesan exception tidak boleh memuatnya.
     *
     * @return array<string, array{callable, string}>
     */
    public static function skenarioKegagalan(): array
    {
        return [
            'harga: subtotal negatif' => [
                static fn () => (new HargaService())->hitungPersenDiskon(-7_654_321), '7654321',
            ],
            'harga: dpp negatif' => [
                static fn () => (new HargaService())->hitungPpn(-7_654_321), '7654321',
            ],
            'stok: qty negatif' => [
                static fn () => (new StokService())->cekStokCukup(100, -7654321), '7654321',
            ],
            'stok: stok negatif' => [
                static fn () => (new StokService())->cekStokCukup(-7654321, 5), '7654321',
            ],
            'stok: melebihi stok' => [
                static fn () => (new StokService())->kurangiStok(7654321, 8654321), '7654321',
            ],
            'stok: qty masuk nol' => [
                static fn () => (new StokService())->tambahStok(7654321, 0), '7654321',
            ],
            'kredit: over-payment' => [
                static fn () => (new KreditService())->prosesPelunasan(7_654_321, 8_654_321), '7654321',
            ],
            'kredit: piutang negatif' => [
                static fn () => (new KreditService())->cekLimitKredit(10_000_000, -7_654_321, 1), '7654321',
            ],
            'saldo: mutasi negatif' => [
                static fn () => (new SaldoService())->hitungSaldoAkhir('Debit', 7_654_321, -7_654_321, 0), '7654321',
            ],
        ];
    }

    /**
     * @dataProvider skenarioKegagalan
     */
    public function testPesanExceptionTidakMembocorkanNilai(callable $aksi, string $rahasia): void
    {
        try {
            $aksi();
            $this->fail('Seharusnya melempar exception.');
        } catch (\Throwable $e) {
            $this->assertStringNotContainsString($rahasia, $e->getMessage());
            $this->assertStringNotContainsString(
                number_format((float) $rahasia, 0, ',', '.'),
                $e->getMessage()
            );
        }
    }

    // ───────────── Alur gabungan ─────────────

    public function testAlurInvoiceLimitKreditDanStok(): void
    {
        $harga  = new HargaService();
        $kredit = new KreditService();
        $stok   = new StokService();

        // 2 item → subtotal 2.000.000 → diskon 10% → DPP 1.800.000 → PPN 198.000
        $invoice = $harga->kalkulasiInvoice([
            ['qty' => 10, 'harga_satuan' => 100_000],
            ['qty' => 5,  'harga_satuan' => 200_000],
        ]);
        $this->assertSame(1_998_000.0, $invoice['total_akhir']);

        // Limit 5jt, piutang berjalan 3jt → sisa 2jt → invoice 1.998.000 masih muat
        $this->assertTrue($kredit->cekLimitKredit(5_000_000, 3_000_000, $invoice['total_akhir']));

        // Stok mencukupi untuk item pertama, tidak untuk item kedua
        $validasi = $stok->validateOrderItems([
            ['nama_barang' => 'A', 'stok' => 10, 'qty' => 10],
            ['nama_barang' => 'B', 'stok' => 4,  'qty' => 5],
        ]);
        $this->assertFalse($validasi['valid']);
        $this->assertCount(1, $validasi['errors']);
    }
}
