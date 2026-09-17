<?php

namespace App\Models;

use CodeIgniter\Model;

class TbRbeliModel extends Model
{
    protected $table         = 'tbrbeli';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'no_rbeli',
        'tgl',
        'id_supp',
        'kete',
        'is_deleted',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua Rencana Beli yang belum di-soft-delete,
     * beserta nama supplier.
     */
    public function getAll_l1H(): array
    {
        return $this->db->table('tbrbeli r')
            ->select('r.*, s.nama_supplier')
            ->join('supplier s', 's.id_supplier = r.id_supp', 'left')
            ->where('r.is_deleted', 0)
            ->orderBy('r.tgl', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu Rencana Beli berdasarkan ID (yang belum dihapus).
     */
    public function getById_l1H(int $id): ?array
    {
        $row = $this->db->table('tbrbeli r')
            ->select('r.*, s.nama_supplier')
            ->join('supplier s', 's.id_supplier = r.id_supp', 'left')
            ->where('r.id', $id)
            ->where('r.is_deleted', 0)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Cek apakah nomor rencana beli sudah digunakan.
     */
    public function cekNoRbeli_l1H(string $noRbeli, ?int $id = null): bool
    {
        $builder = $this->where('no_rbeli', $noRbeli)
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
