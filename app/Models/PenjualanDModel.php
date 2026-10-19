<?php

namespace App\Models;

use CodeIgniter\Model;

class PenjualanDModel extends Model
{
    protected $DBGroup       = 'default';
    protected $table         = 'penjualan_d';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id_penjualan',
        'kode_barang',
        'nama_barang',
        'qty',
        'harga_satuan',
        'hpp_satuan',
        'subtotal',
    ];
    protected $useTimestamps = false;

    /**
     * Ambil rincian barang berdasarkan ID penjualan.
     */
    public function getDetailByPenjualan_l1H(int $idPenjualan): array
    {
        return $this->where('id_penjualan', $idPenjualan)->findAll();
    }
}
