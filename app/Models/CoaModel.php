<?php

namespace App\Models;

use CodeIgniter\Model;

class CoaModel extends Model
{
    protected $table         = 'coa';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'kode_coa',
        'nama_coa',
        'saldo_normal',
        'is_header',
        'tipe',
        'is_off',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua COA, diurutkan berdasarkan kode_coa
     */
    public function getAll_l1H(): array
    {
        return $this->db->table($this->table)
            ->select('id, kode_coa, nama_coa, saldo_normal, is_header, tipe, is_off')
            ->orderBy('kode_coa', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil COA berdasarkan tipe (kolom `tipe`)
     */
    public function getByTipe_l1H(string $tipe): array
    {
        return $this->select('id, kode_coa, nama_coa, saldo_normal, is_header, tipe, is_off')
            ->where('tipe', $tipe)
            ->where('is_off', 0)
            ->orderBy('kode_coa', 'ASC')
            ->findAll();
    }

    /**
     * Ambil daftar tipe yang unik (untuk filter)
     */
    public function getTipeList_l1H(): array
    {
        return $this->db->table($this->table)
            ->select('DISTINCT tipe')
            ->where('tipe IS NOT NULL')
            ->orderBy('tipe', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Cek apakah kode_coa sudah digunakan (exclude id tertentu saat edit)
     */
    public function cekKode_l1H(string $kodeCoa, ?int $id = null): bool
    {
        $builder = $this->where('kode_coa', $kodeCoa);

        if ($id !== null) {
            $builder->where('id !=', $id);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Nonaktifkan: set is_off = 1
     */
    public function nonaktifkan_l1H(int $id): bool
    {
        return $this->update($id, ['is_off' => 1]);
    }

    /**
     * Aktifkan: set is_off = 0
     */
    public function aktifkan_l1H(int $id): bool
    {
        return $this->update($id, ['is_off' => 0]);
    }

    /**
     * Statistik total akun per tipe (untuk stat cards di halaman COA)
     */
    public function getTotalPerTipe_l1H(): array
    {
        return $this->db->table($this->table)
            ->select(
                "tipe,
                 COUNT(*) AS total,
                 SUM(CASE WHEN is_off = 0 THEN 1 ELSE 0 END) AS aktif,
                 SUM(CASE WHEN is_off = 1 THEN 1 ELSE 0 END) AS tidak_aktif",
                false
            )
            ->where('tipe IS NOT NULL')
            ->groupBy('tipe')
            ->orderBy('tipe', 'ASC')
            ->get()
            ->getResultArray();
    }
}
