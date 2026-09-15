<?php

namespace App\Models;

use CodeIgniter\Model;

class TbBkkModel extends Model
{
    protected $table         = 'tbbkk';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'no_bkk',
        'tgl',
        'kete',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua BKK
     */
    public function getAll_l1H(): array
    {
        return $this->db->table('tbbkk b')
            ->select('b.*')
            ->orderBy('b.tgl', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu BKK berdasarkan ID
     */
    public function getById_l1H(int $id): ?array
    {
        $row = $this->db->table('tbbkk b')
            ->select('b.*')
            ->where('b.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Cek apakah nomor BKK sudah digunakan
     */
    public function cekNoBkk_l1H(string $noBkk, ?int $id = null): bool
    {
        $builder = $this->where('no_bkk', $noBkk);
        if ($id !== null) {
            $builder->where('id !=', $id);
        }
        return $builder->countAllResults() > 0;
    }
}
