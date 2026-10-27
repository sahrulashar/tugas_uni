<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TbRbeliModel;
use App\Models\TbRbeliDModel;
use App\Services\JurnalService;

class SyncJurnalRbeli extends BaseCommand
{
    protected $group       = 'Akuntansi';
    protected $name        = 'jurnal:sync-rbeli';
    protected $description = 'Sinkronisasi Rencana Beli aktif ke dalam Jurnal Umum otomatis.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $rbeliModel = new TbRbeliModel();
        $rbeliDModel = new TbRbeliDModel();
        $jurnalService = new JurnalService();

        $rbeliList = $rbeliModel->getAll_l1H();
        $tercatat = 0;

        foreach ($rbeliList as $rbeli) {
            $noRbeli = $rbeli['no_rbeli'];
            $exists = $db->table('jurnal_umum')
                ->where('sumber', 'RBELI')
                ->where('no_referensi', $noRbeli)
                ->countAllResults();

            if ($exists > 0) {
                CLI::write("Rencana Beli {$noRbeli} sudah memiliki jurnal. Dilewati.", 'yellow');
                continue;
            }

            $detail = $rbeliDModel->getByIdRbeli_l1H((int) $rbeli['id']);
            if (empty($detail)) {
                CLI::write("Rencana Beli {$noRbeli} tidak memiliki detail baris. Dilewati.", 'light_red');
                continue;
            }

            try {
                $jurnalService->generateDanSimpanJurnalRbeli($rbeli, $detail, $db);
                CLI::write("Jurnal untuk Rencana Beli {$noRbeli} berhasil dibuat.", 'green');
                $tercatat++;
            } catch (\Throwable $e) {
                CLI::error("Gagal menjurnal Rencana Beli {$noRbeli}: " . $e->getMessage());
            }
        }

        CLI::write("Selesai. {$tercatat} Rencana Beli berhasil disinkronisasi ke Jurnal Umum.", 'cyan');
    }
}
