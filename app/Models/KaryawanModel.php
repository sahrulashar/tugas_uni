<?php

namespace App\Models;

use CodeIgniter\Model;

class KaryawanModel extends Model
{
    protected $table         = 'karyawan';
    protected $primaryKey    = 'id_karyawan';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'nip',
        'nama_karyawan',
        'jabatan',
        'status',
    ];

    protected $useTimestamps = false;

    public function getAll(): array
    {
        return $this->orderBy('nama_karyawan', 'ASC')->findAll();
    }

    public function getAktif(): array
    {
        return $this->where('status', 'Aktif')
                    ->orderBy('nama_karyawan', 'ASC')
                    ->findAll();
    }

    public function cekNip(string $nip, ?int $id = null): bool
    {
        $builder = $this->where('nip', $nip);
        if ($id !== null) {
            $builder->where('id_karyawan !=', $id);
        }
        return $builder->countAllResults() > 0;
    }

    public function nonaktifkan(int $id): bool
    {
        return $this->update($id, ['status' => 'Nonaktif']);
    }

    public function aktifkan(int $id): bool
    {
        return $this->update($id, ['status' => 'Aktif']);
    }

    public function getTotalPerJabatan(): array
    {
        return $this->db->table($this->table)
            ->select("jabatan, COUNT(*) AS total,
                SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) AS aktif,
                SUM(CASE WHEN status = 'Nonaktif' THEN 1 ELSE 0 END) AS tidak_aktif", false)
            ->groupBy('jabatan')
            ->orderBy('jabatan', 'ASC')
            ->get()
            ->getResultArray();
    }
}
