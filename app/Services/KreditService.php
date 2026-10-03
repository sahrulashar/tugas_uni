<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════
 *  KreditService — Business Rule: Limit Kredit Pelanggan
 * ═══════════════════════════════════════════════════════════════
 *
 * Mengunci aturan bisnis terkait limit kredit pelanggan:
 *   1. Transaksi kredit tidak boleh melebihi limit yang ditetapkan
 *   2. Hitung sisa kredit yang masih tersedia
 *   3. Klasifikasi risiko kredit berdasarkan utilisasi
 *   4. Validasi pelunasan piutang
 *
 * Kelas ini bersifat PURE (tidak bergantung DB langsung)
 * sehingga bisa diuji unit tanpa koneksi database.
 *
 * @package App\Services
 */
class KreditService
{
    // ───────────────────────────────────────────
    //  KONSTANTA BATAS RISIKO KREDIT
    // ───────────────────────────────────────────

    /** Batas utilisasi kredit untuk kategori "Aman" (0–70%) */
    const BATAS_AMAN      = 0.70;

    /** Batas utilisasi kredit untuk kategori "Perhatian" (70–90%) */
    const BATAS_PERHATIAN = 0.90;

    /** Di atas 90% = "Kritis" — perlu persetujuan manajer */
    const BATAS_KRITIS    = 1.00;

    // ───────────────────────────────────────────
    //  BUSINESS RULE 1: Cek Limit Kredit
    // ───────────────────────────────────────────

    /**
     * Cek apakah transaksi baru tidak melebihi limit kredit pelanggan.
     *
     * Aturan bisnis:
     *   - limit_kredit   : batas maksimum piutang yang dibolehkan
     *   - piutang_berjalan : total piutang yang belum dilunasi
     *   - nilai_transaksi : nilai order baru yang akan diproses
     *
     *   → OK jika (piutang_berjalan + nilai_transaksi) <= limit_kredit
     *
     * @param float $limitKredit       Batas kredit pelanggan (dari master data)
     * @param float $piutangBerjalan   Total piutang aktif saat ini
     * @param float $nilaiTransaksi    Nilai order baru yang akan ditambahkan
     * @return bool                    true = masih dalam limit, false = melebihi
     *
     * @throws \InvalidArgumentException Jika parameter tidak valid
     *
     * Contoh:
     *   cekLimitKredit(10_000_000, 3_000_000, 5_000_000)  → true  (8jt < 10jt)
     *   cekLimitKredit(10_000_000, 8_000_000, 5_000_000)  → false (13jt > 10jt)
     *   cekLimitKredit(10_000_000, 10_000_000, 1)         → false (tepat habis+1)
     */
    public function cekLimitKredit(
        float $limitKredit,
        float $piutangBerjalan,
        float $nilaiTransaksi
    ): bool {
        if ($limitKredit < 0) {
            throw new \InvalidArgumentException("Limit kredit tidak boleh negatif.");
        }
        if ($piutangBerjalan < 0) {
            throw new \InvalidArgumentException("Piutang berjalan tidak boleh negatif.");
        }
        if ($nilaiTransaksi <= 0) {
            throw new \InvalidArgumentException("Nilai transaksi harus lebih dari 0.");
        }

        $totalPiutangBaru = $piutangBerjalan + $nilaiTransaksi;

        return $totalPiutangBaru <= $limitKredit;
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 2: Hitung Sisa Kredit
    // ───────────────────────────────────────────

    /**
     * Hitung sisa limit kredit yang masih bisa digunakan pelanggan.
     *
     * @param float $limitKredit
     * @param float $piutangBerjalan
     * @return float  Sisa kredit (bisa 0 jika sudah penuh, tidak pernah negatif)
     */
    public function hitungSisaKredit(float $limitKredit, float $piutangBerjalan): float
    {
        if ($limitKredit < 0 || $piutangBerjalan < 0) {
            throw new \InvalidArgumentException("Parameter tidak boleh negatif.");
        }

        $sisa = $limitKredit - $piutangBerjalan;

        // Sisa kredit tidak pernah dilaporkan negatif (sudah melebihi = 0)
        return max(0.0, round($sisa, 2));
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 3: Hitung Utilisasi Kredit
    // ───────────────────────────────────────────

    /**
     * Hitung persentase utilisasi kredit (piutang / limit).
     *
     * @param float $limitKredit
     * @param float $piutangBerjalan
     * @return float  Utilisasi dalam bentuk desimal (0.0 – 1.0+)
     *                0.0 = tidak ada piutang
     *                1.0 = tepat di batas limit
     *                >1.0 = sudah over-limit
     *
     * @throws \InvalidArgumentException Jika limit = 0 (pembagian nol)
     */
    public function hitungUtilisasi(float $limitKredit, float $piutangBerjalan): float
    {
        if ($limitKredit <= 0) {
            throw new \InvalidArgumentException(
                "Limit kredit harus lebih dari 0 untuk menghitung utilisasi."
            );
        }

        return round($piutangBerjalan / $limitKredit, 4);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 4: Klasifikasi Risiko Kredit
    // ───────────────────────────────────────────

    /**
     * Klasifikasi risiko berdasarkan utilisasi kredit.
     *
     * Kategori:
     *   'AMAN'      → utilisasi <= 70%  (bisa proses order normal)
     *   'PERHATIAN' → utilisasi 70–90%  (perlu notifikasi ke manajemen)
     *   'KRITIS'    → utilisasi 90–100% (perlu approval manajer)
     *   'OVER_LIMIT'→ utilisasi > 100%  (order DITOLAK otomatis)
     *
     * @param float $utilisasi  Hasil dari hitungUtilisasi()
     * @return string           Kategori risiko
     */
    public function klasifikasiRisiko(float $utilisasi): string
    {
        if ($utilisasi > self::BATAS_KRITIS) {
            return 'OVER_LIMIT';
        }
        if ($utilisasi > self::BATAS_PERHATIAN) {
            return 'KRITIS';
        }
        if ($utilisasi > self::BATAS_AMAN) {
            return 'PERHATIAN';
        }
        return 'AMAN';
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 5: Proses Pelunasan Piutang
    // ───────────────────────────────────────────

    /**
     * Hitung sisa piutang setelah pembayaran diterima.
     *
     * Aturan bisnis:
     *   - Pembayaran tidak boleh melebihi total piutang (over-payment tidak diijinkan)
     *   - Pembayaran harus > 0
     *
     * @param float $totalPiutang   Total piutang yang harus dibayar
     * @param float $jumlahBayar    Jumlah yang dibayarkan pelanggan
     * @return float                Sisa piutang setelah pembayaran
     *
     * @throws \RuntimeException    Jika pembayaran melebihi piutang
     * @throws \InvalidArgumentException Jika parameter tidak valid
     */
    public function prosesPelunasan(float $totalPiutang, float $jumlahBayar): float
    {
        if ($totalPiutang < 0) {
            throw new \InvalidArgumentException("Total piutang tidak boleh negatif.");
        }
        if ($jumlahBayar <= 0) {
            throw new \InvalidArgumentException("Jumlah bayar harus lebih dari 0.");
        }
        if ($jumlahBayar > $totalPiutang) {
            throw new \RuntimeException(
                "Pembayaran melebihi total piutang. Over-payment tidak diizinkan."
            );
        }

        return round($totalPiutang - $jumlahBayar, 2);
    }

    // ───────────────────────────────────────────
    //  BUSINESS RULE 6: Evaluasi Order Kredit
    // ───────────────────────────────────────────

    /**
     * Evaluasi lengkap order kredit — gabungkan semua aturan bisnis.
     *
     * Return array berisi keputusan dan detail evaluasi.
     *
     * @param float  $limitKredit
     * @param float  $piutangBerjalan
     * @param float  $nilaiOrder
     * @return array [
     *   'disetujui'   => bool,
     *   'risiko'      => string,
     *   'utilisasi'   => float,
     *   'sisa_kredit' => float,
     *   'pesan'       => string,
     * ]
     */
    public function evaluasiOrderKredit(
        float $limitKredit,
        float $piutangBerjalan,
        float $nilaiOrder
    ): array {
        $disetujui   = $this->cekLimitKredit($limitKredit, $piutangBerjalan, $nilaiOrder);
        $utilisasi   = $this->hitungUtilisasi($limitKredit, $piutangBerjalan + ($disetujui ? $nilaiOrder : 0));
        $sisaKredit  = $this->hitungSisaKredit($limitKredit, $piutangBerjalan);
        $risiko      = $this->klasifikasiRisiko($utilisasi);

        $pesan = $disetujui
            ? "Order disetujui. Sisa kredit setelah order: Rp " . number_format($sisaKredit - $nilaiOrder, 0, ',', '.')
            : "Order DITOLAK. Limit kredit tidak mencukupi. Sisa kredit: Rp " . number_format($sisaKredit, 0, ',', '.');

        return [
            'disetujui'   => $disetujui,
            'risiko'      => $risiko,
            'utilisasi'   => $utilisasi,
            'sisa_kredit' => $sisaKredit,
            'pesan'       => $pesan,
        ];
    }
}
