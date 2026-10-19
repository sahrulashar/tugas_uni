<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $DBGroup       = 'default';
    protected $table         = 'barang';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'harga_beli',
        'harga_jual',
        'stok',
        'satuan',
        'is_deleted',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua barang yang aktif (tidak soft deleted).
     */
    public function getAktif_l1H(): array
    {
        return $this->where('is_deleted', 0)->orderBy('kode_barang', 'ASC')->findAll();
    }

    /**
     * Cari barang berdasarkan kode_barang.
     */
    public function cariByKode_l1H(string $kode): ?array
    {
        return $this->where('kode_barang', $kode)->where('is_deleted', 0)->first();
    }
}
