<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk mendaftarkan laman & akses RBAC Pertemuan 10-11:
 *   - dsh   : Executive Dashboard
 *   - pkt10 : Praktikum Pertemuan 10
 */
class CreatePertemuan10Laman extends Migration
{
    public function up(): void
    {
        $db = $this->db;

        $lamanList = [
            'dsh' => [
                'nama'   => 'Executive Dashboard',
                'aksi'   => ['daftar', 'lihat', 'cetak'],
                'users'  => [1, 3], // Admin & Devi
            ],
            'pkt10' => [
                'nama'   => 'Praktikum Pertemuan 10',
                'aksi'   => ['daftar', 'lihat', 'tambah'],
                'users'  => [1, 3], // Admin & Devi
            ],
        ];

        foreach ($lamanList as $kode => $info) {
            foreach ($info['aksi'] as $aksi) {
                $existing = $db->table('tblaman')
                    ->where('kode', $kode)
                    ->where('aksi', $aksi)
                    ->get()
                    ->getRowArray();

                if (!$existing) {
                    $db->table('tblaman')->insert([
                        'kode'   => $kode,
                        'nama'   => $info['nama'],
                        'aksi'   => $aksi,
                        'is_off' => 0,
                    ]);
                    $lamanId = $db->insertID();
                } else {
                    $lamanId = $existing['id'];
                }

                foreach ($info['users'] as $userId) {
                    $hasAkses = $db->table('tbakses')
                        ->where('id_user', $userId)
                        ->where('id_laman', $lamanId)
                        ->get()
                        ->getRowArray();

                    if (!$hasAkses) {
                        $db->table('tbakses')->insert([
                            'id_user'  => $userId,
                            'id_laman' => $lamanId,
                            'is_off'   => 0,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        $db = $this->db;
        $lamans = $db->table('tblaman')->whereIn('kode', ['dsh', 'pkt10'])->get()->getResultArray();
        if (!empty($lamans)) {
            $ids = array_column($lamans, 'id');
            $db->table('tbakses')->whereIn('id_laman', $ids)->delete();
            $db->table('tblaman')->whereIn('id', $ids)->delete();
        }
    }
}
