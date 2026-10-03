<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════
 *  HargaService — Business Rule: Kalkulasi Pajak & Diskon Bertingkat
 * ═══════════════════════════════════════════════════════════════
 *
 * Mengunci aturan bisnis terkait perhitungan harga transaksi:
 *
 *  [A] DISKON BERTINGKAT (tiered discount)
 *      Diskon diberikan berdasarkan total nilai pembelian:
 *        < 500rb          → 0%   diskon
 *        500rb – <1jt     → 5%   diskon
 *        1jt   – <5jt     → 10%  diskon
 *        5jt   – <20jt    → 15%  diskon
 *        >= 20jt          → 20%  diskon
 *
 *  [B] PAJAK PPN (Nilai Tambah)
 *      PPN 11% dikenakan atas harga SETELAH diskon.
 *      (sesuai UU HPP No.7/2021 berlaku 1 April 2022)
 *
 *  [C] KALKULASI FINAL
 *      harga_akhir = (subtotal - diskon) + ppn
 *
 * Kelas ini bersifat PURE (tidak bergantung DB langsung).
 *
 * @package App\Services
 */
class HargaService
{
    // ───────────────────────────────────────────
    //  KONSTANTA TARIF
    // ───────────────────────────────────────────

    /** Tarif PPN berlaku (11% sesuai UU HPP 2022) */
    const TARIF_PPN = 0.11;

    /** Tabel tingkatan diskon [batas_minimum => persen_diskon] */
    const TABEL_DISKON = [
        20_000_000 => 0.20,   // >= 20jt   → 20%
         5_000_000 => 0.15,   // 5jt–20jt  → 15%
         1_000_000 => 0.10,   // 1jt–5jt   → 10%
           500_000 => 0.05,   // 500rb–1jt → 5%
                 0 => 0.00,   // < 500rb   → 0%
    ];

    // ───────────────────────────────────────────
    //  BUSINESS RULE 1: Hitung Diskon Bertingkat
    // ───────────────────────────────────────────

    /**
     * Tentukan persentase diskon berdasarkan nilai subtotal.
     *
     * @param float $subtotal  Total nilai sebelum diskon
     * @return float           Persentase diskon (0.0 – 0.20)
     *
     * @throws \InvalidArgumentException Jika subtotal negatif
     *
     * Contoh:
     *   hitungPersenDiskon(400_000)    → 0.00  (< 500rb)
     *   hitungPersenDiskon(500_000)    → 0.05  (500rb)
     *   hitungPersenDiskon(999_999)    → 0.05  (< 1jt)
     *   hitungPersenDiskon(1_000_000)  → 0.10  (tepat 1jt)
     *   hitungPersenDiskon(5_000_000)  → 0.15
     *   hitungPersenDiskon(20_000_000) → 0.20
     */
    public function hitungPersenDiskon(float $subtotal): float
    {
        if ($subtotal < 0) {
            throw new \InvalidArgumentException(
                "Subtotal tidak boleh negatif."
            );
        }

        // Iterasi dari tingkat tertinggi ke terendah
        foreach (self::TABEL_DISKON as $batas => $persen) {
            if ($subtotal >= $batas) {
                return $persen;
            }
        }

        return 0.0; // fallback (tidak seharusnya tercapai)
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 2: Hitung Nilai Diskon
    // ───────────────────────────────────────────

    /**
     * Hitung nilai rupiah diskon yang diberikan.
     *
     * @param float $subtotal
     * @return float  Nilai diskon dalam rupiah
     */
    public function hitungNilaiDiskon(float $subtotal): float
    {
        $persen = $this->hitungPersenDiskon($subtotal);
        return round($subtotal * $persen, 2);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 3: Hitung PPN
    // ───────────────────────────────────────────

    /**
     * Hitung PPN 11% dari nilai dasar pengenaan pajak (DPP).
     *
     * DPP = subtotal SETELAH dikurangi diskon.
     * PPN tidak dikenakan atas nilai diskon.
     *
     * @param float $dpp  Dasar Pengenaan Pajak (nilai setelah diskon)
     * @return float      Nilai PPN dalam rupiah
     *
     * @throws \InvalidArgumentException Jika DPP negatif
     */
    public function hitungPpn(float $dpp): float
    {
        if ($dpp < 0) {
            throw new \InvalidArgumentException(
                "DPP tidak boleh negatif."
            );
        }

        return round($dpp * self::TARIF_PPN, 2);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 4: Kalkulasi Harga Final
    // ───────────────────────────────────────────

    /**
     * Hitung harga akhir transaksi secara lengkap.
     *
     * Formula:
     *   diskon        = subtotal × persen_diskon
     *   dpp           = subtotal - diskon
     *   ppn           = dpp × 11%
     *   harga_akhir   = dpp + ppn
     *
     * @param float $subtotal  Total nilai sebelum diskon & pajak
     * @return array [
     *   'subtotal'      => float,
     *   'persen_diskon' => float,
     *   'nilai_diskon'  => float,
     *   'dpp'           => float,
     *   'ppn'           => float,
     *   'total_akhir'   => float,
     * ]
     *
     * Contoh (subtotal 2.000.000):
     *   diskon = 2.000.000 × 10% = 200.000
     *   dpp    = 2.000.000 - 200.000 = 1.800.000
     *   ppn    = 1.800.000 × 11% = 198.000
     *   total  = 1.800.000 + 198.000 = 1.998.000
     */
    public function kalkulasiHargaFinal(float $subtotal): array
    {
        if ($subtotal < 0) {
            throw new \InvalidArgumentException(
                "Subtotal tidak boleh negatif."
            );
        }

        $persenDiskon = $this->hitungPersenDiskon($subtotal);
        $nilaiDiskon  = round($subtotal * $persenDiskon, 2);
        $dpp          = round($subtotal - $nilaiDiskon, 2);
        $ppn          = $this->hitungPpn($dpp);
        $totalAkhir   = round($dpp + $ppn, 2);

        return [
            'subtotal'      => $subtotal,
            'persen_diskon' => $persenDiskon,
            'nilai_diskon'  => $nilaiDiskon,
            'dpp'           => $dpp,
            'ppn'           => $ppn,
            'total_akhir'   => $totalAkhir,
        ];
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 5: Kalkulasi Multi-Item
    // ───────────────────────────────────────────

    /**
     * Hitung subtotal dari array item pembelian.
     *
     * @param array $items Array of ['qty' => float, 'harga_satuan' => float]
     * @return float       Total subtotal semua item
     *
     * @throws \InvalidArgumentException Jika ada item dengan harga atau qty negatif
     */
    public function hitungSubtotalItems(array $items): float
    {
        if (empty($items)) {
            return 0.0;
        }

        $subtotal = 0.0;

        foreach ($items as $i => $item) {
            $qty   = (float) ($item['qty'] ?? 0);
            $harga = (float) ($item['harga_satuan'] ?? 0);

            if ($qty < 0) {
                throw new \InvalidArgumentException("Item ke-{$i}: qty tidak boleh negatif.");
            }
            if ($harga < 0) {
                throw new \InvalidArgumentException("Item ke-{$i}: harga satuan tidak boleh negatif.");
            }

            $subtotal += $qty * $harga;
        }

        return round($subtotal, 2);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 6: Kalkulasi Invoice Lengkap
    // ───────────────────────────────────────────

    /**
     * Hitung seluruh komponen invoice dari array item.
     *
     * Kombinasi hitungSubtotalItems() + kalkulasiHargaFinal()
     * untuk end-to-end invoice calculation.
     *
     * @param array $items
     * @return array  Hasil kalkulasi lengkap (sama dengan kalkulasiHargaFinal() + subtotal_items)
     */
    public function kalkulasiInvoice(array $items): array
    {
        $subtotal = $this->hitungSubtotalItems($items);
        return $this->kalkulasiHargaFinal($subtotal);
    }
}
