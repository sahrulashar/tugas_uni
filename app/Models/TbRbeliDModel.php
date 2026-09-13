<?php

namespace App\Models;

use CodeIgniter\Model;

class TbRbeliDModel extends Model
{
    protected $table         = 'tbrbeli_d';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id_rbeli',
        'no_faktur',
        'nilai',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua detail berdasarkan id_rbeli (header)
     */
    public function getByIdRbeli(int $idRbeli): array
    {
        return $this->where('id_rbeli', $idRbeli)->findAll();
    }

    /**
     * Hapus semua detail milik satu header
     */
    public function hapusByIdRbeli(int $idRbeli): bool
    {
        return $this->where('id_rbeli', $idRbeli)->delete();
    }

    /**
     * Hitung total nilai dari semua detail satu header
     */
    public function getTotalNilai(int $idRbeli): float
    {
        $result = $this->selectSum('nilai')
                       ->where('id_rbeli', $idRbeli)
                       ->first();
        return (float) ($result['nilai'] ?? 0);
    }
}
