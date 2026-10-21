<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TbBkkModel;
use App\Models\TbBkkDModel;
use App\Services\JurnalService;

class SyncJurnalBkk extends BaseCommand
{
    protected $group       = 'Akuntansi';
    protected $name        = 'jurnal:sync-bkk';
    protected $description = 'Sinkronisasi BKK aktif ke dalam Jurnal Umum otomatis.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $bkkModel = new TbBkkModel();
        $bkkDModel = new TbBkkDModel();
        $jurnalService = new JurnalService();

        $bkkList = $bkkModel->getAll_l1H();
        $tercatat = 0;

        foreach ($bkkList as $bkk) {
            $noBkk = $bkk['no_bkk'];
            $exists = $db->table('jurnal_umum')
                ->where('sumber', 'BKK')
                ->where('no_referensi', $noBkk)
                ->countAllResults();

            if ($exists > 0) {
                CLI::write("BKK {$noBkk} sudah memiliki jurnal. Dilewati.", 'yellow');
                continue;
            }

            $detail = $bkkDModel->getByIdBkk_l1H((int) $bkk['id']);
            if (empty($detail)) {
                CLI::write("BKK {$noBkk} tidak memiliki detail baris. Dilewati.", 'light_red');
                continue;
            }

            try {
                $jurnalService->generateDanSimpanJurnalBkk($bkk, $detail, $db);
                CLI::write("Jurnal untuk BKK {$noBkk} berhasil dibuat.", 'green');
                $tercatat++;
            } catch (\Throwable $e) {
                CLI::error("Gagal menjurnal BKK {$noBkk}: " . $e->getMessage());
            }
        }

        CLI::write("Selesai. {$tercatat} BKK berhasil disinkronisasi ke Jurnal Umum.", 'cyan');
    }
}
