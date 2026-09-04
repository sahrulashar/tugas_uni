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

    // Jika ingin otomatis menggunakan fitur timestamps dari CI4, 
    // ubah jadi true (pastikan ada field created_at & updated_at di DB)
    protected $useTimestamps = false;

    /**
     * Mengambil semua data supplier diurutkan berdasarkan nama
     */
    public function getAll(): array
    {
        return $this->orderBy('nama_supplier', 'ASC')->findAll();
    }

    /**
     * Mengambil supplier yang statusnya Aktif
     */
    public function getAktif(): array
    {
        return $this->where('status', 'Aktif')
                    ->orderBy('nama_supplier', 'ASC')
                    ->findAll();
    }

    /**
     * Cek apakah kode supplier sudah digunakan
     */
    public function cekKode(string $kodeSupplier, ?int $id = null): bool
    {
        $builder = $this->where('kode_supplier', $kodeSupplier);
        if ($id !== null) {
            $builder->where('id_supplier !=', $id);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Soft-delete: hanya menonaktifkan, tidak menghapus permanen
     */
    public function nonaktifkan(int $id): bool
    {
        return $this->update($id, ['status' => 'Nonaktif']);
    }

    /**
     * Mengaktifkan kembali supplier
     */
    public function aktifkan(int $id): bool
    {
        return $this->update($id, ['status' => 'Aktif']);
    }
}
