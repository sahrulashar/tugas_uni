<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════
 *  NeracaSaldoService — Trial Balance Aggregator
 * ═══════════════════════════════════════════════════════════════
 *
 * Mengagregasi seluruh saldo akun Chart of Accounts (COA)
 * dari saldo awal dan mutasi jurnal umum (debit/kredit)
 * untuk menghasilkan Neraca Saldo (Trial Balance) yang seimbang.
 *
 * @package App\Services
 */
class NeracaSaldoService
{
    /**
     * Hitung Neraca Saldo berdasarkan daftar akun dan mutasi jurnal.
     *
     * @param array $coaList  Daftar akun COA dari tabel `coa`
     * @param array $mutasi   Array agregat ['kode_coa' => ['debit' => x, 'kredit' => y]]
     * @return array          Data lengkap Neraca Saldo beserta total & status balance
     */
    public function hitungNeracaSaldo(array $coaList, array $mutasi = []): array
    {
        $baris = [];

        $totAwalDebit   = 0.0;
        $totAwalKredit  = 0.0;
        $totMutasiDebit = 0.0;
        $totMutasiKredit= 0.0;
        $totAkhirDebit  = 0.0;
        $totAkhirKredit = 0.0;

        foreach ($coaList as $c) {
            $kode        = $c['kode_coa'];
            $hasMutasi   = !empty($mutasi[$kode]) && (($mutasi[$kode]['debit'] ?? 0) > 0 || ($mutasi[$kode]['kredit'] ?? 0) > 0);

            // Lewati akun header HANYA JIKA tidak memiliki mutasi jurnal (jika ada mutasi, wajib dihitung agar trial balance balance)
            if (($c['is_header'] ?? 'D') === 'H' && !$hasMutasi) {
                continue;
            }

            $nama        = $c['nama_coa'];
            $saldoNormal = $c['saldo_normal'] ?? 'Debit';
            $saldoAwal   = (float) ($c['saldo_awal'] ?? 0);

            // Kolom Saldo Awal (Debit / Kredit)
            $awalDebit  = ($saldoNormal === 'Debit')  ? $saldoAwal : 0.0;
            $awalKredit = ($saldoNormal === 'Kredit') ? $saldoAwal : 0.0;

            // Mutasi dari Jurnal
            $mDebit  = (float) ($mutasi[$kode]['debit']  ?? 0);
            $mKredit = (float) ($mutasi[$kode]['kredit'] ?? 0);

            // Hitung Saldo Akhir berdasarkan Saldo Normal
            if ($saldoNormal === 'Debit') {
                $net = round($saldoAwal + $mDebit - $mKredit, 2);
                if ($net >= 0) {
                    $akhirDebit  = $net;
                    $akhirKredit = 0.0;
                } else {
                    $akhirDebit  = 0.0;
                    $akhirKredit = abs($net);
                }
            } else {
                $net = round($saldoAwal + $mKredit - $mDebit, 2);
                if ($net >= 0) {
                    $akhirDebit  = 0.0;
                    $akhirKredit = $net;
                } else {
                    $akhirDebit  = abs($net);
                    $akhirKredit = 0.0;
                }
            }

            // Akumulasi Total
            $totAwalDebit    += $awalDebit;
            $totAwalKredit   += $awalKredit;
            $totMutasiDebit  += $mDebit;
            $totMutasiKredit += $mKredit;
            $totAkhirDebit   += $akhirDebit;
            $totAkhirKredit  += $akhirKredit;

            $baris[] = [
                'kode_coa'     => $kode,
                'nama_coa'     => $nama,
                'saldo_normal' => $saldoNormal,
                'awal_debit'   => $awalDebit,
                'awal_kredit'  => $awalKredit,
                'mutasi_debit' => $mDebit,
                'mutasi_kredit'=> $mKredit,
                'akhir_debit'  => $akhirDebit,
                'akhir_kredit' => $akhirKredit,
            ];
        }

        $selisih = abs(round($totAkhirDebit - $totAkhirKredit, 2));
        $isBalance = $selisih < 0.01;

        return [
            'baris'            => $baris,
            'tot_awal_debit'   => round($totAwalDebit, 2),
            'tot_awal_kredit'  => round($totAwalKredit, 2),
            'tot_mutasi_debit' => round($totMutasiDebit, 2),
            'tot_mutasi_kredit'=> round($totMutasiKredit, 2),
            'tot_akhir_debit'  => round($totAkhirDebit, 2),
            'tot_akhir_kredit' => round($totAkhirKredit, 2),
            'selisih'          => $selisih,
            'is_balance'       => $isBalance,
        ];
    }

    /**
     * Ambil data dari database dan hasilkan neraca saldo lengkap.
     *
     * @param \CodeIgniter\Database\BaseConnection|null $db
     * @return array
     */
    public function ambilNeracaSaldoDariDb($db = null): array
    {
        $db = $db ?? \Config\Database::connect('default');

        // 1. Ambil agregat mutasi debit/kredit dari tabel jurnal_umum_d
        $mutasiRows = $db->table('jurnal_umum_d')
            ->select('kode_coa, SUM(debit) AS total_debit, SUM(kredit) AS total_kredit')
            ->groupBy('kode_coa')
            ->get()
            ->getResultArray();

        $mutasi = [];
        $kodeMutasi = [];
        foreach ($mutasiRows as $m) {
            $mutasi[$m['kode_coa']] = [
                'debit'  => (float) $m['total_debit'],
                'kredit' => (float) $m['total_kredit'],
            ];
            $kodeMutasi[] = $m['kode_coa'];
        }

        // 2. Ambil akun COA aktif: Detail ('D') ATAU Header yang pernah termutasi
        $builder = $db->table('coa')->where('is_off', 0);
        if (!empty($kodeMutasi)) {
            $builder->groupStart()
                ->where('is_header', 'D')
                ->orWhereIn('kode_coa', $kodeMutasi)
                ->groupEnd();
        } else {
            $builder->where('is_header', 'D');
        }

        $coaList = $builder->orderBy('kode_coa', 'ASC')->get()->getResultArray();

        return $this->hitungNeracaSaldo($coaList, $mutasi);
    }
}
