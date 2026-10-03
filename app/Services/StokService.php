<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════
 *  StokService — Business Rule: Cek & Kurangi Stok Otomatis
 * ═══════════════════════════════════════════════════════════════
 *
 * Mengunci aturan bisnis terkait stok barang:
 *   1. Stok tidak boleh negatif (under-stock prevention)
 *   2. Kurangi stok saat transaksi penjualan terjadi (atomik)
 *   3. Tambah stok saat penerimaan barang (purchase receipt)
 *   4. Cek ketersediaan stok sebelum memproses order
 *
 * Kelas ini bersifat PURE (tidak bergantung DB secara langsung)
 * sehingga mudah diuji tanpa koneksi database.
 *
 * @package App\Services
 */
class StokService
{
    // ───────────────────────────────────────────
    //  BUSINESS RULE 1: Validasi Stok Cukup
    // ───────────────────────────────────────────

    /**
     * Cek apakah stok mencukupi untuk kuantitas yang diminta.
     *
     * Aturan bisnis:
     *   - stok_tersedia HARUS >= qty_diminta
     *   - qty_diminta HARUS > 0 (tidak boleh 0 atau negatif)
     *   - stok_tersedia TIDAK BOLEH negatif
     *
     * @param float $stokTersedia  Stok saat ini di gudang
     * @param float $qtyDiminta   Jumlah yang ingin diambil
     * @return bool                true = stok cukup, false = stok kurang
     *
     * @throws \InvalidArgumentException Jika qty_diminta <= 0 atau stok < 0
     *
     * Contoh:
     *   cekStokCukup(100, 50)  → true   (50 dari 100 tersedia)
     *   cekStokCukup(30, 50)   → false  (50 > 30, stok kurang)
     *   cekStokCukup(50, 50)   → true   (tepat habis, masih valid)
     *   cekStokCukup(100, -5)  → Exception (qty negatif tidak valid)
     */
    public function cekStokCukup(float $stokTersedia, float $qtyDiminta): bool
    {
        if ($qtyDiminta <= 0) {
            throw new \InvalidArgumentException(
                "Qty yang diminta harus lebih dari 0."
            );
        }

        if ($stokTersedia < 0) {
            throw new \InvalidArgumentException(
                "Stok tersedia tidak boleh negatif."
            );
        }

        return $stokTersedia >= $qtyDiminta;
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 2: Hitung Sisa Stok
    // ───────────────────────────────────────────

    /**
     * Hitung stok setelah pengurangan (simulasi transaksi keluar).
     *
     * Aturan bisnis:
     *   - Stok hasil TIDAK BOLEH < 0 (tidak boleh jual melebihi stok)
     *   - Jika stok kurang, lempar StokTidakCukupException
     *
     * @param float $stokTersedia
     * @param float $qtyKeluar
     * @return float  Sisa stok setelah dikurangi
     *
     * @throws \RuntimeException  Jika stok tidak mencukupi
     *
     * Contoh:
     *   kurangiStok(100, 30)  → 70.0
     *   kurangiStok(50, 50)   → 0.0
     *   kurangiStok(20, 50)   → throws RuntimeException
     */
    public function kurangiStok(float $stokTersedia, float $qtyKeluar): float
    {
        if (!$this->cekStokCukup($stokTersedia, $qtyKeluar)) {
            throw new \RuntimeException(
                "Stok tidak mencukupi."
            );
        }

        return round($stokTersedia - $qtyKeluar, 4);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 3: Tambah Stok (Penerimaan)
    // ───────────────────────────────────────────

    /**
     * Hitung stok setelah penambahan (penerimaan barang/retur).
     *
     * @param float $stokTersedia
     * @param float $qtyMasuk
     * @return float  Stok baru setelah ditambah
     *
     * @throws \InvalidArgumentException Jika qty_masuk <= 0
     *
     * Contoh:
     *   tambahStok(50, 100) → 150.0
     *   tambahStok(0, 25)   → 25.0
     */
    public function tambahStok(float $stokTersedia, float $qtyMasuk): float
    {
        if ($qtyMasuk <= 0) {
            throw new \InvalidArgumentException(
                "Qty masuk harus lebih dari 0."
            );
        }

        return round($stokTersedia + $qtyMasuk, 4);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 4: Hitung Nilai Stok
    // ───────────────────────────────────────────

    /**
     * Hitung total nilai stok (qty × harga_satuan).
     *
     * Digunakan untuk valuasi persediaan di laporan.
     *
     * @param float $qty
     * @param float $hargaSatuan
     * @return float  Total nilai stok
     *
     * @throws \InvalidArgumentException Jika qty atau harga negatif
     */
    public function hitungNilaiStok(float $qty, float $hargaSatuan): float
    {
        if ($qty < 0) {
            throw new \InvalidArgumentException("Qty tidak boleh negatif.");
        }
        if ($hargaSatuan < 0) {
            throw new \InvalidArgumentException("Harga satuan tidak boleh negatif.");
        }

        return round($qty * $hargaSatuan, 2);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 5: Validasi Multi-Item Order
    // ───────────────────────────────────────────

    /**
     * Validasi order multi-item: pastikan semua item memiliki stok cukup.
     *
     * @param array $items Array of ['nama_barang' => string, 'stok' => float, 'qty' => float]
     * @return array       ['valid' => bool, 'errors' => string[]]
     *
     * Contoh:
     *   validateOrderItems([
     *       ['nama_barang' => 'Beras', 'stok' => 100, 'qty' => 50],
     *       ['nama_barang' => 'Gula',  'stok' => 10,  'qty' => 20],  // kurang!
     *   ])
     *   → ['valid' => false, 'errors' => ['Gula: stok kurang (tersedia 10, diminta 20)']]
     */
    public function validateOrderItems(array $items): array
    {
        $errors = [];

        foreach ($items as $item) {
            $nama  = $item['nama_barang'] ?? 'Unknown';
            $stok  = (float) ($item['stok'] ?? 0);
            $qty   = (float) ($item['qty'] ?? 0);

            try {
                if (!$this->cekStokCukup($stok, $qty)) {
                    $errors[] = "{$nama}: stok kurang (tersedia {$stok}, diminta {$qty})";
                }
            } catch (\InvalidArgumentException $e) {
                $errors[] = "{$nama}: " . $e->getMessage();
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }
}
