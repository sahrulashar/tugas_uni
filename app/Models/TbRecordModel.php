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
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua Rekap BKK beserta info BKK terkait
     */
    public function getAll_l1H(): array
    {
        return $this->db->table('tbrecord r')
            ->select('r.*, b.no_bkk, b.tgl AS tgl_bkk, b.kete AS kete_bkk')
            ->join('tbbkk b', 'b.id = r.id_bkk', 'left')
            ->orderBy('r.tgl', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu Rekap berdasarkan ID
     */
    public function getById_l1H(int $id): ?array
    {
        $row = $this->db->table('tbrecord r')
            ->select('r.*, b.no_bkk, b.tgl AS tgl_bkk, b.kete AS kete_bkk')
            ->join('tbbkk b', 'b.id = r.id_bkk', 'left')
            ->where('r.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Cek apakah nomor rekap sudah digunakan
     */
    public function cekNoRec_l1H(string $noRec, ?int $id = null): bool
    {
        $builder = $this->where('no_rec', $noRec);
        if ($id !== null) {
            $builder->where('id !=', $id);
        }
        return $builder->countAllResults() > 0;
    }
}
