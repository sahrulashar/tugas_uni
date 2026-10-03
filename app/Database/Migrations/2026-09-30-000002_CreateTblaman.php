<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel tblaman — daftar halaman + aksi yang bisa dikendalikan aksesnya
 *
 * Setiap baris = satu kombinasi HALAMAN × AKSI
 *
 * Kolom:
 *   id     → primary key
 *   kode   → kode halaman (misal: 'ak1', 'ak2', 'bkk', 'supp', 'usr')
 *   nama   → nama halaman yang bisa dibaca manusia
 *   aksi   → jenis aksi: daftar | tambah | edit | hapus | lihat | cetak
 *   is_off → 0 = aktif, 1 = nonaktif
 *
 * Contoh data:
 *   [1, 'ak1', 'Rencana Beli',     'daftar', 0]
 *   [2, 'ak1', 'Rencana Beli',     'tambah', 0]
 *   [3, 'ak1', 'Rencana Beli',     'edit',   0]
 *   [4, 'ak1', 'Rencana Beli',     'hapus',  0]
 *   [5, 'ak1', 'Rencana Beli',     'lihat',  0]
 */
class CreateTblaman extends Migration
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
            'aksi' => [
                'type'       => 'ENUM',
                'constraint' => ['daftar', 'tambah', 'edit', 'hapus', 'lihat', 'cetak'],
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
        $this->forge->addUniqueKey(['kode', 'aksi']); // satu kode hanya punya 1 baris per aksi
        $this->forge->createTable('tblaman', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tblaman', true);
    }
}
