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

    // ─────────────────────────────────────────────────────────────────
    //  SIKLUS KAS KELUAR: PENJURNALAN BUKTI KAS KELUAR (BKK)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Bentuk pasangan ayat jurnal untuk Bukti Kas Keluar (BKK).
     *
     * Setiap baris BKK memiliki:
     *   - id_coa    : Akun Debet (Beban / Utang / Aset)
     *   - id_coa_kb : Akun Kredit Kas/Bank (Kas Tangan, Kas Kecil, Bank BCA, dll.)
     *   - nilai     : Besaran nominal pengeluaran
     *
     * @param array                     $headerBkk    ['no_bkk' => ..., 'tgl' => ..., 'kete' => ...]
     * @param array                     $barisDetail  Array baris BKK ['id_coa', 'id_coa_kb', 'nilai']
     * @param \CodeIgniter\Database\BaseConnection|null $db
     * @return array                                  Array berisi 'entries', 'total_debit', 'total_kredit'
     *
     * @throws \InvalidArgumentException Jika detail kosong atau nilai tidak valid
     * @throws \RuntimeException         Jika jurnal tidak balance
     */
    public function buatEntriJurnalBkk(array $headerBkk, array $barisDetail, $db = null): array
    {
        if (empty($barisDetail)) {
            throw new \InvalidArgumentException('Detail BKK tidak boleh kosong untuk penjurnalan.');
        }

        $db = $db ?? \Config\Database::connect('default');

        // Cache master COA untuk lookup kode dan nama berdasarkan ID
        $coaRows = $db->table('coa')->select('id, kode_coa, nama_coa')->get()->getResultArray();
        $coaById = [];
        foreach ($coaRows as $c) {
            $coaById[(int)$c['id']] = $c;
        }

        $entries = [];
        $noBkk   = $headerBkk['no_bkk'] ?? 'BKK';
        $ketBkk  = $headerBkk['kete'] ?? '';

        foreach ($barisDetail as $idx => $b) {
            $nilai    = (float) ($b['nilai'] ?? 0);
            $idCoa    = (int) ($b['id_coa'] ?? 0);
            $idCoaKb  = (int) ($b['id_coa_kb'] ?? 0);

            if ($nilai <= 0) {
                throw new \InvalidArgumentException("Nilai baris ke-" . ($idx + 1) . " harus lebih besar dari 0.");
            }

            $debitCoa  = $coaById[$idCoa] ?? null;
            $kreditCoa = $coaById[$idCoaKb] ?? null;

            $namaDebit  = $debitCoa['nama_coa'] ?? 'Beban / Pengeluaran';
            $kodeDebit  = $debitCoa['kode_coa'] ?? '5000';
            $namaKredit = $kreditCoa['nama_coa'] ?? 'Kas / Bank';
            $kodeKredit = $kreditCoa['kode_coa'] ?? self::AKUN_KAS_DEFAULT;

            $ketBaris = !empty($b['keterangan']) ? $b['keterangan'] : ($ketBkk ?: "Pengeluaran kas BKK #{$noBkk}");

            // 1. Sisi DEBET: Beban / Utang / Persediaan
            $entries[] = [
                'kode_coa'   => $kodeDebit,
                'nama_coa'   => $namaDebit,
                'debit'      => round($nilai, 2),
                'kredit'     => 0.00,
                'keterangan' => $ketBaris,
            ];

            // 2. Sisi KREDIT: Akun Kas / Bank
            $entries[] = [
                'kode_coa'   => $kodeKredit,
                'nama_coa'   => $namaKredit,
                'debit'      => 0.00,
                'kredit'     => round($nilai, 2),
                'keterangan' => "Kas keluar via {$namaKredit} (BKK #{$noBkk})",
            ];
        }

        $totalDebit  = array_sum(array_column($entries, 'debit'));
        $totalKredit = array_sum(array_column($entries, 'kredit'));

        if (!$this->cekBalance($entries)) {
            throw new \RuntimeException('Jurnal BKK tidak balance antara debit dan kredit.');
        }

        return [
            'entries'      => $entries,
            'total_debit'  => round($totalDebit, 2),
            'total_kredit' => round($totalKredit, 2),
        ];
    }

    /**
     * Generate dan simpan jurnal kas keluar (BKK) langsung ke database di dalam transaksi aktif.
     * Otomatis membatalkan jurnal lama jika no_bkk sudah pernah dicatat (misal saat edit).
     *
     * @param array                     $headerBkk
     * @param array                     $barisDetail
     * @param \CodeIgniter\Database\BaseConnection|null $db
     * @return int                      ID jurnal yang berhasil disimpan
     */
    public function generateDanSimpanJurnalBkk(array $headerBkk, array $barisDetail, $db = null): int
    {
        $db = $db ?? \Config\Database::connect('default');

        $noBkk      = $headerBkk['no_bkk'] ?? 'BKK-' . date('YmdHis');
        $tglJurnal  = $headerBkk['tgl'] ?? date('Y-m-d');
        $keterangan = $headerBkk['kete'] ?: "Jurnal pengeluaran kas BKK #{$noBkk}";

        // Batalkan jurnal lama terlebih dahulu jika ada (mencegah duplikasi saat edit BKK)
        $this->batalkanJurnalByReferensi('BKK', $noBkk, $db);

        $jurnalData = $this->buatEntriJurnalBkk($headerBkk, $barisDetail, $db);

        $noJurnal = 'JU-BKK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        // Simpan Header Jurnal
        $header = [
            'no_jurnal'    => $noJurnal,
            'tgl_jurnal'   => $tglJurnal,
            'no_referensi' => $noBkk,
            'sumber'       => 'BKK',
            'keterangan'   => $keterangan,
            'total_debit'  => $jurnalData['total_debit'],
            'total_kredit' => $jurnalData['total_kredit'],
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        $db->table('jurnal_umum')->insert($header);
        $idJurnal = $db->insertID();

        // Simpan Detail Jurnal
        foreach ($jurnalData['entries'] as $entry) {
            $db->table('jurnal_umum_d')->insert([
                'id_jurnal'  => $idJurnal,
                'kode_coa'   => $entry['kode_coa'],
                'nama_coa'   => $entry['nama_coa'],
                'debit'      => $entry['debit'],
                'kredit'     => $entry['kredit'],
                'keterangan' => $entry['keterangan'],
            ]);
        }

        return $idJurnal;
    }

    /**
     * Batalkan dan hapus entri jurnal berdasarkan sumber dan nomor referensi.
     * Digunakan saat BKK diedit atau di-soft delete agar tidak menjadi jurnal hantu di Neraca Saldo.
     *
     * @param string                    $sumber      'BKK' | 'PENJUALAN'
     * @param string                    $noReferensi No BKK atau No Faktur
     * @param \CodeIgniter\Database\BaseConnection|null $db
     * @return bool
     */
    public function batalkanJurnalByReferensi(string $sumber, string $noReferensi, $db = null): bool
    {
        $db = $db ?? \Config\Database::connect('default');

        $jurnals = $db->table('jurnal_umum')
            ->select('id')
            ->where('sumber', $sumber)
            ->where('no_referensi', $noReferensi)
            ->get()->getResultArray();

        if (!empty($jurnals)) {
            $ids = array_column($jurnals, 'id');
            $db->table('jurnal_umum_d')->whereIn('id_jurnal', $ids)->delete();
            $db->table('jurnal_umum')->whereIn('id', $ids)->delete();
        }

        return true;
    }
}
