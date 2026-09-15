<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table            = 'supplier';
    protected $primaryKey       = 'id_supplier';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kode_supplier',
        'nama_supplier',
        'alamat',
        'status',
    ];

    protected $useTimestamps = false;

    /**
     * Mengambil semua data supplier diurutkan berdasarkan nama
     */
    public function getAll_l1H(): array
    {
        return $this->orderBy('nama_supplier', 'ASC')->findAll();
    }

    /**
     * Mengambil supplier yang statusnya Aktif
     */
    public function getAktif_l1H(): array
    {
        return $this->where('status', 'Aktif')
                    ->orderBy('nama_supplier', 'ASC')
                    ->findAll();
    }

    /**
     * Cek apakah kode supplier sudah digunakan
     */
    public function cekKode_l1H(string $kodeSupplier, ?int $id = null): bool
    {
        $builder = $this->where('kode_supplier', $kodeSupplier);
        if ($id !== null) {
            $builder->where('id_supplier !=', $id);
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
     * Mengaktifkan kembali supplier
     */
    public function aktifkan_l1H(int $id): bool
    {
        return $this->update($id, ['status' => 'Aktif']);
    }
}
