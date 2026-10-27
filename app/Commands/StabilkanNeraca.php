<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\JurnalService;
use App\Services\NeracaSaldoService;
use App\Models\TbBkkModel;
use App\Models\TbBkkDModel;

class StabilkanNeraca extends BaseCommand
{
    protected $group       = 'Akuntansi';
    protected $name        = 'akuntansi:stabilkan-neraca';
    protected $description = 'Menstabilkan akun dan jurnal pada Neraca Saldo agar sesuai kaidah akuntansi.';

    public function run(array $params)
    {
        $db            = \Config\Database::connect();
        $jurnalService = new JurnalService();
        $bkkModel      = new TbBkkModel();
        $bkkDModel     = new TbBkkDModel();
        $neracaService = new NeracaSaldoService();

        CLI::write("=== MENSTABILKAN NERACA SALDO ===", 'cyan');

        // 1. Perbaiki nama akun 1141 di COA menjadi 'Piutang Usaha' (bukan Utang Usaha)
        $coa1141 = $db->table('coa')->where('kode_coa', '1141')->get()->getRowArray();
        if ($coa1141 && $coa1141['nama_coa'] !== 'Piutang Usaha') {
            $db->table('coa')->where('kode_coa', '1141')->update(['nama_coa' => 'Piutang Usaha']);
            CLI::write("1. Akun 1141 diperbarui: 'Utang Usaha' -> 'Piutang Usaha' (Aset Lancar).", 'green');
        } else {
            CLI::write("1. Akun 1141 sudah bernama 'Piutang Usaha'.", 'white');
        }

        // 2. Ambil ID COA Utang Usaha (2111)
        $coaUtang = $db->table('coa')->where('kode_coa', '2111')->get()->getRowArray();
        if (!$coaUtang) {
            CLI::error("Akun 2111 (Utang Usaha) tidak ditemukan!");
            return;
        }
        $idCoaUtang = (int) $coaUtang['id'];

        // 3. Bersihkan transaksi BKK dummy test lama (BKK-TEST-001812-596)
        $dummyBkk = $db->table('tbbkk')->like('no_bkk', 'BKK-TEST-')->get()->getResultArray();
        foreach ($dummyBkk as $d) {
            $jurnalService->batalkanJurnalByReferensi('BKK', $d['no_bkk'], $db);
            $db->table('tbbkk_d')->where('id_bkk', $d['id'])->delete();
            $db->table('tbbkk')->where('id', $d['id'])->delete();
            CLI::write("2. Bersihkan residu test BKK: {$d['no_bkk']}.", 'yellow');
        }

        // 4. Perbaiki akun debit BKK pembayaran Rencana Beli (BKK-2026-01 & BKK-2026-0010) ke Utang Usaha (2111)
        $bkkPelunasan = ['BKK-2026-01', 'BKK-2026-0010'];
        foreach ($bkkPelunasan as $noBkk) {
            $bkk = $bkkModel->where('no_bkk', $noBkk)->first();
            if (!$bkk) {
                continue;
            }

            // Update baris tbbkk_d yang terkait rbeli agar id_coa = Utang Usaha (2111)
            $db->table('tbbkk_d')
                ->where('id_bkk', $bkk['id'])
                ->where('id_rbeli_d IS NOT NULL')
                ->update(['id_coa' => $idCoaUtang]);

            // Regenerasi jurnal BKK yang sudah disesuaikan
            $detail = $bkkDModel->getByIdBkk_l1H((int) $bkk['id']);
            if (!empty($detail)) {
                $jurnalService->generateDanSimpanJurnalBkk($bkk, $detail, $db);
                CLI::write("3. Jurnal BKK {$noBkk} disesuaikan ke akun [2111] Utang Usaha.", 'green');
            }
        }

        // 5. Validasi hasil Neraca Saldo
        $neraca = $neracaService->ambilNeracaSaldoDariDb($db);

        CLI::newLine();
        CLI::write("HASIL VALIDASI NERACA SALDO TERAKHIR:", 'cyan');
        CLI::write("Total Saldo Awal Debit  : Rp " . number_format($neraca['tot_awal_debit'], 2), 'white');
        CLI::write("Total Saldo Awal Kredit : Rp " . number_format($neraca['tot_awal_kredit'], 2), 'white');
        CLI::write("Total Mutasi Debit      : Rp " . number_format($neraca['tot_mutasi_debit'], 2), 'white');
        CLI::write("Total Mutasi Kredit     : Rp " . number_format($neraca['tot_mutasi_kredit'], 2), 'white');
        CLI::write("Total Saldo Akhir Debit : Rp " . number_format($neraca['tot_akhir_debit'], 2), 'white');
        CLI::write("Total Saldo Akhir Kredit: Rp " . number_format($neraca['tot_akhir_kredit'], 2), 'white');
        CLI::write("Selisih                 : Rp " . number_format($neraca['selisih'], 2), $neraca['is_balance'] ? 'green' : 'red');
        CLI::write("Status Keseimbangan     : " . ($neraca['is_balance'] ? '100% BALANCED & STABIL' : 'TIDAK SEIMBANG!'), $neraca['is_balance'] ? 'green' : 'red');
    }
}
