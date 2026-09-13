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
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua rencana beli beserta nama supplier (JOIN)
     */
    public function getAll(): array
    {
        return $this->db->table('tbrbeli r')
            ->select('r.*, s.nama_supplier')
            ->join('supplier s', 's.id_supplier = r.id_supp', 'left')
            ->orderBy('r.tgl', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil satu rencana beli beserta nama supplier
     */
    public function getById(int $id): ?array
    {
        $row = $this->db->table('tbrbeli r')
            ->select('r.*, s.nama_supplier')
            ->join('supplier s', 's.id_supplier = r.id_supp', 'left')
            ->where('r.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Cek apakah nomor rencana beli sudah digunakan
     */
    public function cekNoRbeli(string $noRbeli, ?int $id = null): bool
    {
        $builder = $this->where('no_rbeli', $noRbeli);
        if ($id !== null) {
            $builder->where('id !=', $id);
        }
        return $builder->countAllResults() > 0;
    }
}
