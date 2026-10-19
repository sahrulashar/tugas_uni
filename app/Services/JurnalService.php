<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════
 *  JurnalService — Automated Journaling Engine
 * ═══════════════════════════════════════════════════════════════
 *
 * Mengotomatisasi penjurnalan akuntansi ganda (double-entry):
 *   1. Penjualan Tunai / Kasir:
 *        [D] Kas / Bank (1111)           = Total Netto
 *        [K] Pendapatan Penjualan (4110) = Total Netto
 *        [D] Beban Pokok Penjualan (5120) = Total HPP
 *        [K] Persediaan Barang (1131)     = Total HPP
 *   2. Memastikan setiap transaksi jurnal berimbang (Total Debit == Total Kredit).
 *   3. Menghindari kebocoran nilai sensitif pada pesan exception.
 *
 * @package App\Services
 */
class JurnalService
{
    public const AKUN_KAS_DEFAULT     = '1111'; // Kas Tangan
    public const AKUN_PERSEDIAAN       = '1131'; // Persediaan Barang Dagang
    public const AKUN_PENJUALAN        = '4110'; // Pendapatan Penjualan
    public const AKUN_HPP              = '5120'; // Beban Pokok Penjualan (HPP)

    /**
     * Bentuk pasangan ayat jurnal untuk transaksi penjualan (Pure Logic).
     *
     * @param float  $totalNetto  Nilai penjualan setelah diskon
     * @param float  $totalHpp    Nilai modal pokok barang terjual
     * @param string $kodeKas     Kode akun kas penerima
     * @param string $keterangan  Keterangan transaksi
     * @return array              Array berisi 'entries', 'total_debit', 'total_kredit'
     *
     * @throws \InvalidArgumentException Jika nilai tidak valid
     */
    public function buatEntriJurnalPenjualan(
        float $totalNetto,
        float $totalHpp,
        string $kodeKas = self::AKUN_KAS_DEFAULT,
        string $keterangan = ''
    ): array {
        if ($totalNetto <= 0) {
            throw new \InvalidArgumentException('Total penjualan harus lebih dari 0.');
        }

        if ($totalHpp < 0) {
            throw new \InvalidArgumentException('Total HPP tidak boleh negatif.');
        }

        $entries = [];

        // 1. Pengakuan Penerimaan Kas & Pendapatan Penjualan
        $entries[] = [
            'kode_coa'   => $kodeKas,
            'nama_coa'   => 'Kas Tangan',
            'debit'      => round($totalNetto, 2),
            'kredit'     => 0.00,
            'keterangan' => $keterangan ?: 'Penerimaan kas penjualan',
        ];

        $entries[] = [
            'kode_coa'   => self::AKUN_PENJUALAN,
            'nama_coa'   => 'Pendapatan Penjualan',
            'debit'      => 0.00,
            'kredit'     => round($totalNetto, 2),
            'keterangan' => $keterangan ?: 'Pengakuan pendapatan penjualan',
        ];

        // 2. Pengakuan Beban Pokok Penjualan (HPP) & Pengurangan Persediaan
        if ($totalHpp > 0) {
            $entries[] = [
                'kode_coa'   => self::AKUN_HPP,
                'nama_coa'   => 'Beban Pokok Penjualan (HPP)',
                'debit'      => round($totalHpp, 2),
                'kredit'     => 0.00,
                'keterangan' => 'Pengakuan HPP barang terjual',
            ];

            $entries[] = [
                'kode_coa'   => self::AKUN_PERSEDIAAN,
                'nama_coa'   => 'Persediaan Barang Dagang',
                'debit'      => 0.00,
                'kredit'     => round($totalHpp, 2),
                'keterangan' => 'Pengurangan persediaan barang',
            ];
        }

        $totalDebit  = array_sum(array_column($entries, 'debit'));
        $totalKredit = array_sum(array_column($entries, 'kredit'));

        if (!$this->cekBalance($entries)) {
            throw new \RuntimeException('Jurnal tidak balance antara debit dan kredit.');
        }

        return [
            'entries'      => $entries,
            'total_debit'  => round($totalDebit, 2),
            'total_kredit' => round($totalKredit, 2),
        ];
    }

    /**
     * Validasi keseimbangan ayat jurnal (Debit == Kredit).
     *
     * @param array $entries
     * @return bool
     */
    public function cekBalance(array $entries): bool
    {
        if (empty($entries)) {
            return false;
        }

        $totalDebit  = 0.0;
        $totalKredit = 0.0;

        foreach ($entries as $e) {
            $d = (float) ($e['debit'] ?? 0);
            $k = (float) ($e['kredit'] ?? 0);

            if ($d < 0 || $k < 0) {
                return false;
            }

            $totalDebit  += $d;
            $totalKredit += $k;
        }

        return abs(round($totalDebit - $totalKredit, 2)) < 0.01;
    }

    /**
     * Generate dan simpan jurnal umum langsung ke database di dalam transaksi aktif.
     *
     * @param array                     $penjualan  Header penjualan
     * @param array                     $detail     Rincian penjualan
     * @param \CodeIgniter\Database\BaseConnection|null $db
     * @return int                      ID jurnal yang berhasil disimpan
     *
     * @throws \RuntimeException Jika gagal menyimpan jurnal
     */
    public function generateDanSimpanJurnal(array $penjualan, array $detail, $db = null): int
    {
        $db = $db ?? \Config\Database::connect('default');

        $totalNetto = (float) ($penjualan['total_netto'] ?? 0);
        $totalHpp   = (float) ($penjualan['total_hpp'] ?? 0);
        $noFaktur   = $penjualan['no_faktur'] ?? 'INV-' . date('YmdHis');
        $tglJurnal  = $penjualan['tgl_jual'] ?? date('Y-m-d');
        $keterangan = "Jurnal otomatis penjualan faktur #{$noFaktur}";

        $jurnalData = $this->buatEntriJurnalPenjualan($totalNetto, $totalHpp, self::AKUN_KAS_DEFAULT, $keterangan);

        $noJurnal = 'JU-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        // Simpan Header Jurnal
        $header = [
            'no_jurnal'    => $noJurnal,
            'tgl_jurnal'   => $tglJurnal,
            'no_referensi' => $noFaktur,
            'sumber'       => 'PENJUALAN',
            'keterangan'   => $keterangan,
            'total_debit'  => $jurnalData['total_debit'],
            'total_kredit' => $jurnalData['total_kredit'],
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        $db->table('jurnal_umum')->insert($header);
        $idJurnal = $db->insertID();

        // Ambil nama COA dari DB jika ada
        $coaList = $db->table('coa')->select('kode_coa, nama_coa')->get()->getResultArray();
        $coaMap  = [];
        foreach ($coaList as $c) {
            $coaMap[$c['kode_coa']] = $c['nama_coa'];
        }

        // Simpan Detail Jurnal
        foreach ($jurnalData['entries'] as $entry) {
            $namaCoa = $coaMap[$entry['kode_coa']] ?? $entry['nama_coa'];
            $db->table('jurnal_umum_d')->insert([
                'id_jurnal'  => $idJurnal,
                'kode_coa'   => $entry['kode_coa'],
                'nama_coa'   => $namaCoa,
                'debit'      => $entry['debit'],
                'kredit'     => $entry['kredit'],
                'keterangan' => $entry['keterangan'],
            ]);
        }

        return $idJurnal;
    }
}
