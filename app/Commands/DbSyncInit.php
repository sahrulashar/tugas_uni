<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DbSyncInit extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:sync-init';
    protected $description = 'Otomatis sinkronisasi database dari docker/db/init.sql jika ada perubahan file.';

    public function run(array $params)
    {
        $sqlPath = ROOTPATH . 'docker/db/init.sql';
        if (!file_exists($sqlPath)) {
            CLI::write("File {$sqlPath} tidak ditemukan.", 'yellow');
            return;
        }

        $hashFile = WRITEPATH . 'init_sql.hash';
        $currentHash = md5_file($sqlPath);
        $lastHash = file_exists($hashFile) ? trim(file_get_contents($hashFile)) : '';

        $force = array_key_exists('force', $params) || in_array('--force', $params, true) || in_array('-f', $params, true);

        if ($currentHash === $lastHash && !$force) {
            CLI::write("Database sudah sinkron dengan init.sql. Dilewati.", 'green');
            return;
        }

        CLI::write("Memperbarui database dari init.sql...", 'cyan');

        $db = \Config\Database::connect();
        $db->initialize();
        $mysqli = $db->connID;

        if (!$mysqli instanceof \mysqli) {
            CLI::error("Koneksi database bukan MySQLi (" . (is_object($mysqli) ? get_class($mysqli) : gettype($mysqli)) . ").");
            return;
        }

        $sqlContent = file_get_contents($sqlPath);

        if ($mysqli->multi_query($sqlContent)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            if ($mysqli->errno) {
                CLI::error("Error saat eksekusi SQL: " . $mysqli->error);
                return;
            }

            @file_put_contents($hashFile, $currentHash);
            CLI::write("Database berhasil disinkronisasi 100% dengan init.sql!", 'green');
        } else {
            CLI::error("Gagal menjalankan multi_query: " . $mysqli->error);
        }
    }
}
