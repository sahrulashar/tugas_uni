<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel tbuser — master data pengguna sistem ERP
 *
 * Kolom:
 *   id       → primary key auto increment
 *   kode     → kode unik login (username)
 *   nama     → nama lengkap user
 *   password → bcrypt hash
 *   is_off   → 0 = aktif, 1 = nonaktif
 */
class CreateTbuser extends Migration
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
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'is_off' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=aktif, 1=nonaktif',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('tbuser', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbuser', true);
    }
}
