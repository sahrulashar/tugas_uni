<?php

namespace App\Models;

use CodeIgniter\Model;

class TbRecordModel extends Model
{
    protected $table         = 'tbrecord';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'no_rec',
        'tgl',
        'id_bkk',
        'ket',
        'is_deleted',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua Rekap BKK yang belum di-soft-delete, beserta info BKK terkait.
     */
    public function getAll_l1H(): array
    {
        return $this->db->table('tbrecord r')
            ->select('r.*, b.no_bkk, b.tgl AS tgl_bkk, b.kete AS kete_bkk')
            ->join('tbbkk b', 'b.id = r.id_bkk', 'left')
            ->where('r.is_deleted', 0)
            ->orderBy('r.tgl', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu Rekap berdasarkan ID (yang belum dihapus).
     */
    public function getById_l1H(int $id): ?array
    {
        $row = $this->db->table('tbrecord r')
            ->select('r.*, b.no_bkk, b.tgl AS tgl_bkk, b.kete AS kete_bkk')
            ->join('tbbkk b', 'b.id = r.id_bkk', 'left')
            ->where('r.id', $id)
            ->where('r.is_deleted', 0)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Cek apakah nomor rekap sudah digunakan.
     */
    public function cekNoRec_l1H(string $noRec, ?int $id = null): bool
    {
        $builder = $this->where('no_rec', $noRec)
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
