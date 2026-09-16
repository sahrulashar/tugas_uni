<?php

namespace App\Models;

use CodeIgniter\Model;

class TbBeliDModel extends Model
{
    protected $table         = 'tbbeli_d';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id_beli',
        'barng',
        'harga',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua detail berdasarkan id_beli (header)
     */
    public function getByIdBeli_l1H(int $idBeli): array
    {
        return $this->where('id_beli', $idBeli)->findAll();
    }

    /**
     * Hapus semua detail milik satu header
     */
    public function hapusByIdBeli_l1H(int $idBeli): bool
    {
        return $this->where('id_beli', $idBeli)->delete();
    }

    /**
     * Hitung total harga dari semua detail satu transaksi
     */
    public function getTotalHarga_l1H(int $idBeli): float
    {
        $result = $this->selectSum('harga')
                       ->where('id_beli', $idBeli)
                       ->first();
        return (float) ($result['harga'] ?? 0);
    }
}
