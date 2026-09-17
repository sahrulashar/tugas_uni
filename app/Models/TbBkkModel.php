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
        'is_deleted',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua BKK yang belum di-soft-delete.
     */
    public function getAll_l1H(): array
    {
        return $this->db->table('tbbkk b')
            ->select('b.*')
            ->where('b.is_deleted', 0)
            ->orderBy('b.tgl', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu BKK berdasarkan ID (yang belum dihapus).
     */
    public function getById_l1H(int $id): ?array
    {
        $row = $this->db->table('tbbkk b')
            ->select('b.*')
            ->where('b.id', $id)
            ->where('b.is_deleted', 0)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Cek apakah nomor BKK sudah digunakan.
     */
    public function cekNoBkk_l1H(string $noBkk, ?int $id = null): bool
    {
        $builder = $this->where('no_bkk', $noBkk)
                        ->where('is_deleted', 0);
        if ($id !== null) {
            $builder->where('id !=', $id);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Soft delete: set is_deleted = 1 (TIDAK menghapus data dari DB).
     */
    public function softDelete_l1H(int $id): bool
    {
        return $this->update($id, ['is_deleted' => 1]);
    }
}
