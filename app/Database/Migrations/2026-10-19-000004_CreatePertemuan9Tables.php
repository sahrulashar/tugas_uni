<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration Pertemuan 9: Cross-Module Integration & Automated Journaling
 *
 * Modul:
 *  1. Master Barang (stok, harga beli/HPP, harga jual)
 *  2. Transaksi Penjualan / Kasir (Header + Detail)
 *  3. Automated Journaling (Jurnal Umum Header + Detail)
 *  4. Inisialisasi COA Persediaan & Pendapatan/HPP + Hak Akses RBAC
 */
class CreatePertemuan9Tables extends Migration
{
    public function up(): void
    {
        // ───────────────────────────────────────────
        // 1. TABEL BARANG (Master Produk & Stok)
        // ───────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'nama_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Bahan Baku',
            ],
            'harga_beli' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'comment'    => 'HPP satuan',
            ],
            'harga_jual' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'stok' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pcs',
            ],
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('kode_barang');
        $this->forge->createTable('barang', true);

        // ───────────────────────────────────────────
        // 2. TABEL PENJUALAN (Header Transaksi Kasir)
        // ───────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'no_faktur' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'tgl_jual' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'customer' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Pelanggan Umum',
            ],
            'total_bruto' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'diskon' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'total_netto' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'total_hpp' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'metode_bayar' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'Tunai',
            ],
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('no_faktur');
        $this->forge->createTable('penjualan', true);

        // ───────────────────────────────────────────
        // 3. TABEL PENJUALAN_D (Detail Item Penjualan)
        // ───────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_penjualan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'kode_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'nama_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'qty' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
            ],
            'harga_satuan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'hpp_satuan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('id_penjualan');
        $this->forge->createTable('penjualan_d', true);

        // ───────────────────────────────────────────
        // 4. TABEL JURNAL_UMUM (Header Jurnal Transaksi)
        // ───────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'no_jurnal' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'tgl_jurnal' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'no_referensi' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            'sumber' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'PENJUALAN',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_debit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'total_kredit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('no_jurnal');
        $this->forge->createTable('jurnal_umum', true);

        // ───────────────────────────────────────────
        // 5. TABEL JURNAL_UMUM_D (Detail Ayat Jurnal)
        // ───────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_jurnal' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'kode_coa' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'nama_coa' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'debit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'kredit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('id_jurnal');
        $this->forge->createTable('jurnal_umum_d', true);

        // ───────────────────────────────────────────
        // 6. SEED COA AKUN AKUNTANSI TERKAIT
        // ───────────────────────────────────────────
        $db = \Config\Database::connect();

        $coaAccounts = [
            [
                'kode_coa'     => '1131',
                'nama_coa'     => 'Persediaan Barang Dagang',
                'saldo_normal' => 'Debit',
                'is_header'    => 'D',
                'tipe'         => 'operasional',
                'saldo_awal'   => 5000000.00,
                'is_off'       => 0,
            ],
            [
                'kode_coa'     => '4110',
                'nama_coa'     => 'Pendapatan Penjualan',
                'saldo_normal' => 'Kredit',
                'is_header'    => 'D',
                'tipe'         => 'operasional',
                'saldo_awal'   => 0.00,
                'is_off'       => 0,
            ],
            [
                'kode_coa'     => '5120',
                'nama_coa'     => 'Beban Pokok Penjualan (HPP)',
                'saldo_normal' => 'Debit',
                'is_header'    => 'D',
                'tipe'         => 'operasional',
                'saldo_awal'   => 0.00,
                'is_off'       => 0,
            ],
        ];

        foreach ($coaAccounts as $acc) {
            $exists = $db->table('coa')->where('kode_coa', $acc['kode_coa'])->get()->getRowArray();
            if (!$exists) {
                $db->table('coa')->insert($acc);
            }
        }

        // Seimbangkan saldo awal modal pemilik (3111) agar persediaan 5jt tercermin pada neraca saldo seimbang
        $modalAcc = $db->table('coa')->where('kode_coa', '3111')->get()->getRowArray();
        if ($modalAcc && (float) $modalAcc['saldo_awal'] === 54250000.00) {
            $db->table('coa')->where('kode_coa', '3111')->update(['saldo_awal' => 59250000.00]);
        }

        // ───────────────────────────────────────────
        // 7. SEED INITIAL INVENTORY (Master Barang)
        // ───────────────────────────────────────────
        $initialBarang = [
            [
                'kode_barang' => 'BRG-001',
                'nama_barang' => 'Kopi Arabika Specialty 250g',
                'kategori'    => 'Bahan Baku',
                'harga_beli'  => 45000.00,
                'harga_jual'  => 65000.00,
                'stok'        => 40.00,
                'satuan'      => 'pck',
                'is_deleted'  => 0,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'kode_barang' => 'BRG-002',
                'nama_barang' => 'Susu Segar Pasteurisasi 1L',
                'kategori'    => 'Bahan Baku',
                'harga_beli'  => 18000.00,
                'harga_jual'  => 26000.00,
                'stok'        => 50.00,
                'satuan'      => 'btl',
                'is_deleted'  => 0,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'kode_barang' => 'BRG-003',
                'nama_barang' => 'Sirup Vanila Gourmet 750ml',
                'kategori'    => 'Sirup & Flavour',
                'harga_beli'  => 75000.00,
                'harga_jual'  => 110000.00,
                'stok'        => 20.00,
                'satuan'      => 'btl',
                'is_deleted'  => 0,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'kode_barang' => 'BRG-004',
                'nama_barang' => 'Gula Aren Cair Organik 1L',
                'kategori'    => 'Pemanis',
                'harga_beli'  => 32000.00,
                'harga_jual'  => 48000.00,
                'stok'        => 25.00,
                'satuan'      => 'btl',
                'is_deleted'  => 0,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($initialBarang as $b) {
            $exists = $db->table('barang')->where('kode_barang', $b['kode_barang'])->get()->getRowArray();
            if (!$exists) {
                $db->table('barang')->insert($b);
            }
        }

        // ───────────────────────────────────────────
        // 8. SEED RBAC LAMAN & AKSES PERTEMUAN 9
        // ───────────────────────────────────────────
        $modulBaru = [
            'pos' => ['nama' => 'Kasir Penjualan', 'aksi' => ['daftar', 'tambah', 'lihat', 'cetak']],
            'brg' => ['nama' => 'Master Barang',   'aksi' => ['daftar', 'tambah', 'edit', 'hapus']],
            'jrn' => ['nama' => 'Jurnal Umum',     'aksi' => ['daftar', 'lihat']],
            'nrc' => ['nama' => 'Neraca Saldo',    'aksi' => ['daftar', 'lihat', 'cetak']],
            'pkt' => ['nama' => 'Praktikum P9',    'aksi' => ['daftar', 'tambah']],
        ];

        foreach ($modulBaru as $kode => $info) {
            foreach ($info['aksi'] as $aksi) {
                $existingLaman = $db->table('tblaman')
                    ->where('kode', $kode)
                    ->where('aksi', $aksi)
                    ->get()
                    ->getRowArray();

                if (!$existingLaman) {
                    $db->table('tblaman')->insert([
                        'kode'   => $kode,
                        'nama'   => $info['nama'],
                        'aksi'   => $aksi,
                        'is_off' => 0,
                    ]);
                    $lamanId = $db->insertID();
                } else {
                    $lamanId = $existingLaman['id'];
                }

                // Berikan akses otomatis ke Admin (user_id = 1)
                $existingAkses = $db->table('tbakses')
                    ->where('id_user', 1)
                    ->where('id_laman', $lamanId)
                    ->get()
                    ->getRowArray();

                if (!$existingAkses) {
                    $db->table('tbakses')->insert([
                        'id_user'  => 1,
                        'id_laman' => $lamanId,
                        'is_off'   => 0,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('jurnal_umum_d', true);
        $this->forge->dropTable('jurnal_umum', true);
        $this->forge->dropTable('penjualan_d', true);
        $this->forge->dropTable('penjualan', true);
        $this->forge->dropTable('barang', true);
    }
}
