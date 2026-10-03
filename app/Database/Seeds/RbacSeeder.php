<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * RbacSeeder — Data awal RBAC sistem ERP
 *
 * Isi:
 *   [1] tbuser  → admin (admin123), lisa (asil), devi (ived)
 *   [2] tblaman → semua kombinasi halaman × aksi
 *   [3] tbakses →
 *       - Admin: memiliki semua akses (Referensi, Aktivitas, & RBAC)
 *       - Lisa: Staff Referensi & Pembelian (COA lihat, Supplier, Karyawan lihat, Aktivitas 1 Rencana Beli)
 *       - Devi: Staff Kas & Akuntansi (COA CRUD, Aktivitas 2 BKK, Aktivitas 3 Rekap BKK)
 *
 * Jalankan:
 *   php spark db:seed RbacSeeder
 */
class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // ─── [1] TBUSER ───────────────────────────────────────────────
        $users = [
            [
                'kode'     => 'admin',
                'nama'     => 'Administrator',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'is_off'   => 0,
            ],
            [
                'kode'     => 'lisa',
                'nama'     => 'Meilisa',
                'password' => password_hash('asil', PASSWORD_BCRYPT),
                'is_off'   => 0,
            ],
            [
                'kode'     => 'devi',
                'nama'     => 'Devi Sri',
                'password' => password_hash('ived', PASSWORD_BCRYPT),
                'is_off'   => 0,
            ],
        ];

        foreach ($users as $u) {
            $existing = $this->db->table('tbuser')->where('kode', $u['kode'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('tbuser')->where('id', $existing['id'])->update($u);
            } else {
                $this->db->table('tbuser')->insert($u);
            }
        }

        // ─── [2] TBLAMAN ──────────────────────────────────────────────
        $aksiList = ['daftar', 'tambah', 'edit', 'hapus', 'lihat'];

        $halaman = [
            'ak1'  => 'Rencana Beli',
            'ak2'  => 'Bukti Kas Keluar',
            'ak3'  => 'Rekap BKK',
            'coa'  => 'Chart of Account',
            'supp' => 'Supplier',
            'kary' => 'Karyawan',
            'usr'  => 'User',
            'lmn'  => 'Laman',
            'aks'  => 'Akses',
        ];

        foreach ($halaman as $kode => $nama) {
            foreach ($aksiList as $aksi) {
                if (in_array($kode, ['usr', 'lmn', 'aks']) && $aksi === 'lihat') {
                    continue;
                }
                $exists = $this->db->table('tblaman')->where('kode', $kode)->where('aksi', $aksi)->countAllResults() > 0;
                if (!$exists) {
                    $this->db->table('tblaman')->insert([
                        'kode'   => $kode,
                        'nama'   => $nama,
                        'aksi'   => $aksi,
                        'is_off' => 0,
                    ]);
                }
            }
            // Aksi cetak untuk transaksi
            if (in_array($kode, ['ak1', 'ak2', 'ak3'])) {
                $exists = $this->db->table('tblaman')->where('kode', $kode)->where('aksi', 'cetak')->countAllResults() > 0;
                if (!$exists) {
                    $this->db->table('tblaman')->insert([
                        'kode'   => $kode,
                        'nama'   => $nama,
                        'aksi'   => 'cetak',
                        'is_off' => 0,
                    ]);
                }
            }
        }

        // ─── [3] TBAKSES ──────────────────────────────────────────────
        // Ambil data users
        $admin = $this->db->table('tbuser')->where('kode', 'admin')->get()->getRowArray();
        $lisa  = $this->db->table('tbuser')->where('kode', 'lisa')->get()->getRowArray();
        $devi  = $this->db->table('tbuser')->where('kode', 'devi')->get()->getRowArray();

        // Ambil semua laman map [id => ['kode' => ..., 'aksi' => ...]]
        $allLaman = $this->db->table('tblaman')->get()->getResultArray();

        // Reset akses lama
        $this->db->table('tbakses')->truncate();

        $aksesData = [];

        // 1. Admin: Akses ke seluruh laman
        if ($admin) {
            foreach ($allLaman as $l) {
                $aksesData[] = [
                    'id_user'  => $admin['id'],
                    'id_laman' => $l['id'],
                    'is_off'   => 0,
                ];
            }
        }

        // 2. Lisa: Referensi Supplier (full), COA (lihat/daftar), Karyawan (lihat/daftar), Aktivitas 1 Rencana Beli (full)
        if ($lisa) {
            foreach ($allLaman as $l) {
                $k = $l['kode'];
                $a = $l['aksi'];

                $boleh = false;
                if ($k === 'ak1') {
                    $boleh = true; // Rencana Beli full
                } elseif ($k === 'supp') {
                    $boleh = in_array($a, ['daftar', 'tambah', 'edit', 'lihat']);
                } elseif (in_array($k, ['coa', 'kary'])) {
                    $boleh = in_array($a, ['daftar', 'lihat']);
                }

                if ($boleh) {
                    $aksesData[] = [
                        'id_user'  => $lisa['id'],
                        'id_laman' => $l['id'],
                        'is_off'   => 0,
                    ];
                }
            }
        }

        // 3. Devi: COA (full), Aktivitas 2 BKK (full), Aktivitas 3 Rekap BKK (full)
        if ($devi) {
            foreach ($allLaman as $l) {
                $k = $l['kode'];
                $a = $l['aksi'];

                $boleh = false;
                if (in_array($k, ['ak2', 'ak3', 'coa'])) {
                    $boleh = true;
                }

                if ($boleh) {
                    $aksesData[] = [
                        'id_user'  => $devi['id'],
                        'id_laman' => $l['id'],
                        'is_off'   => 0,
                    ];
                }
            }
        }

        if (!empty($aksesData)) {
            $this->db->table('tbakses')->insertBatch($aksesData);
        }
    }
}
