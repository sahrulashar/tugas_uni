<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DbExport extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:export';
    protected $description = 'Ekspor database lokal saat ini ke docker/db/init.sql dan kas_keluar.sql.';

    public function run(array $params)
    {
        CLI::write("Mengekspor database lokal...", 'cyan');

        $db = \Config\Database::connect();
        $database = $db->database ?: 'kas_keluar';
        $username = $db->username ?: 'root';
        $password = $db->password ?: '';
        $hostname = $db->hostname ?: 'localhost';
        $port     = $db->port ?: 3306;

        $targetInit = ROOTPATH . 'docker/db/init.sql';
        $targetRoot = ROOTPATH . 'kas_keluar.sql';

        $passArg = $password !== '' ? "-p\"{$password}\"" : '';
        $cmd = "mysqldump -h {$hostname} -P {$port} -u {$username} {$passArg} {$database} --result-file=\"{$targetInit}\"";

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($targetInit) || filesize($targetInit) === 0) {
            CLI::error("Gagal mengekspor database dengan mysqldump (exit code: {$returnCode}).");
            return;
        }

        copy($targetInit, $targetRoot);

        // Update local hash agar laptop ini tidak meng-import ulang dump miliknya sendiri
        $newHash = md5_file($targetInit);
        @file_put_contents(WRITEPATH . 'init_sql.hash', $newHash);

        CLI::write("Berhasil mengekspor database ke:", 'green');
        CLI::write("  - " . $targetInit, 'white');
        CLI::write("  - " . $targetRoot, 'white');
        CLI::write("Ukuran file: " . number_format(filesize($targetInit) / 1024, 1) . " KB", 'yellow');
        CLI::write("Siap di-commit dan di-push ke GitHub!", 'green');
    }
}
