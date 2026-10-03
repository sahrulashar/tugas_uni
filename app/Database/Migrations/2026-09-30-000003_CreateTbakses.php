<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel tbakses — peta hak akses user ke halaman tertentu
 *
 * Kolom:
 *   id       → primary key
 *   id_user  → FK ke tbuser.id
 *   id_laman → FK ke tblaman.id
 *   is_off   → 0 = aktif (punya akses), 1 = dicabut
 *
 * Contoh:
 *   [1, 1, 1, 0]  → user 1 (lisa) punya akses ke laman 1 (ak1-daftar)
 *   [2, 1, 2, 0]  → user 1 (lisa) punya akses ke laman 2 (ak1-tambah)
 *   [3, 2, 1, 0]  → user 2 (devi) punya akses ke laman 1 (ak1-daftar)
 */
class CreateTbakses extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned'  => true,
                'null'      => false,
            ],
            'id_laman' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned'  => true,
                'null'      => false,
            ],
            'is_off' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=aktif, 1=dicabut',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['id_user', 'id_laman']); // satu user hanya 1 baris per laman
        $this->forge->addForeignKey('id_user',  'tbuser',  'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_laman', 'tblaman', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbakses', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbakses', true);
    }
}
