<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════
 *  SaldoService — Business Rule: Saldo Akun COA & Kecukupan Kas
 * ═══════════════════════════════════════════════════════════════
 *
 * Mengunci aturan bisnis saldo akun yang berinteraksi dengan BKK (Aktivitas 2):
 *   1. Saldo akhir = saldo awal + mutasi, tergantung saldo normal akun
 *        Debit  : saldo_awal + debit  - kredit
 *        Kredit : saldo_awal + kredit - debit
 *   2. BKK tidak boleh membuat saldo akun kas (saldo normal Debit) menjadi negatif.
 *
 * Kelas ini bersifat PURE (tidak bergantung DB) sehingga mudah diuji unit.
 * Pesan exception sengaja TIDAK memuat nilai transaksi/saldo.
 *
 * @package App\Services
 */
class SaldoService
{
    public const NORMAL_DEBIT  = 'Debit';
    public const NORMAL_KREDIT = 'Kredit';

    /**
     * Hitung saldo akhir akun.
     *
     * @throws \InvalidArgumentException Jika saldo normal tidak dikenal atau mutasi negatif
     */
    public function hitungSaldoAkhir(
        string $saldoNormal,
        float $saldoAwal,
        float $mutasiDebit,
        float $mutasiKredit
    ): float {
        if ($mutasiDebit < 0 || $mutasiKredit < 0) {
            throw new \InvalidArgumentException('Mutasi tidak boleh negatif.');
        }

        return match ($saldoNormal) {
            self::NORMAL_DEBIT  => round($saldoAwal + $mutasiDebit - $mutasiKredit, 2),
            self::NORMAL_KREDIT => round($saldoAwal + $mutasiKredit - $mutasiDebit, 2),
            default             => throw new \InvalidArgumentException('Saldo normal harus Debit atau Kredit.'),
        };
    }

    /**
     * Cek apakah saldo akun cukup untuk dikreditkan sebesar $nilai.
     *
     * Hanya akun bersaldo normal Debit (kas/bank) yang dibatasi;
     * akun bersaldo normal Kredit selalu lolos.
     *
     * @throws \InvalidArgumentException Jika nilai <= 0 atau saldo normal tidak dikenal
     */
    public function cekSaldoCukup(string $saldoNormal, float $saldoSaatIni, float $nilai): bool
    {
        if ($nilai <= 0) {
            throw new \InvalidArgumentException('Nilai transaksi harus lebih dari 0.');
        }

        if (!in_array($saldoNormal, [self::NORMAL_DEBIT, self::NORMAL_KREDIT], true)) {
            throw new \InvalidArgumentException('Saldo normal harus Debit atau Kredit.');
        }

        if ($saldoNormal === self::NORMAL_KREDIT) {
            return true;
        }

        return round($saldoSaatIni - $nilai, 2) >= 0;
    }
}
