<?php

namespace App\Models;

use CodeIgniter\Model;

class TbBkkDModel extends Model
{
    protected $table         = 'tbbkk_d';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id_bkk',
        'id_rbeli_d',
        'nilai',
        'id_coa',
        'id_coa_kb',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua detail BKK berdasarkan id_bkk,
     * JOIN ke tbcoa (debit & kredit) dan tbrbeli_d (opsional)
     */
    public function getByIdBkk(int $idBkk): array
    {
        return $this->db->table('tbbkk_d d')
            ->select([
                'd.*',
                'c1.kode_coa  AS kode_coa_debit',
                'c1.nama_coa  AS nama_coa_debit',
                'c2.kode_coa  AS kode_coa_kredit',
                'c2.nama_coa  AS nama_coa_kredit',
                'rd.no_faktur AS no_faktur_rbeli',
            ])
            ->join('coa c1',       'c1.id = d.id_coa',          'left')
            ->join('coa c2',       'c2.id = d.id_coa_kb',       'left')
            ->join('tbrbeli_d rd', 'rd.id = d.id_rbeli_d',      'left')
            ->where('d.id_bkk', $idBkk)
            ->get()
            ->getResultArray();
    }

    /**
     * Hapus semua detail milik satu BKK
     */
    public function hapusByIdBkk(int $idBkk): bool
    {
        return $this->where('id_bkk', $idBkk)->delete();
    }

    /**
     * Hitung total nilai dari semua detail satu BKK
     */
    public function getTotalNilai(int $idBkk): float
    {
        $result = $this->selectSum('nilai')
                       ->where('id_bkk', $idBkk)
                       ->first();
        return (float) ($result['nilai'] ?? 0);
    }
}
