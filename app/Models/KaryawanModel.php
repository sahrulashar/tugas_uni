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

    public function getAll_l1H(): array
    {
        return $this->orderBy('nama_karyawan', 'ASC')->findAll();
    }

    public function getAktif_l1H(): array
    {
        return $this->where('status', 'Aktif')
                    ->orderBy('nama_karyawan', 'ASC')
                    ->findAll();
    }

    public function cekNip_l1H(string $nip, ?int $id = null): bool
    {
        $builder = $this->where('nip', $nip);
        if ($id !== null) {
            $builder->where('id_karyawan !=', $id);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Soft-delete: set status = 'Tidak Aktif'
     */
    public function nonaktifkan_l1H(int $id): bool
    {
        return $this->update($id, ['status' => 'Tidak Aktif']);
    }

    /**
     * Aktifkan kembali
     */
    public function aktifkan_l1H(int $id): bool
    {
        return $this->update($id, ['status' => 'Aktif']);
    }

    public function getTotalPerJabatan_l1H(): array
    {
        return $this->db->table($this->table)
            ->select(
                "jabatan,
                 COUNT(*) AS total,
                 SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) AS aktif,
                 SUM(CASE WHEN status = 'Tidak Aktif' THEN 1 ELSE 0 END) AS tidak_aktif",
                false
            )
            ->groupBy('jabatan')
            ->orderBy('jabatan', 'ASC')
            ->get()
            ->getResultArray();
    }
}
