<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LamanModel — Model untuk tabel tblaman
 *
 * @package App\Models
 */
class LamanModel extends Model
{
    protected $table         = 'tblaman';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['kode', 'nama', 'aksi', 'is_off'];
    protected $useTimestamps = false;

    /**
     * Ambil semua laman aktif, dikelompokkan per kode halaman
     */
    public function getAktif(): array
    {
        return $this->where('is_off', 0)
                    ->orderBy('kode', 'ASC')
                    ->orderBy('aksi', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil semua laman (untuk halaman manajemen akses)
     * dengan info berapa user yang punya akses ke setiap laman
     */
    public function getAllWithAksesCount(): array
    {
        return $this->db->table('tblaman l')
            ->select('l.*, COUNT(a.id) as jumlah_user')
            ->join('tbakses a', 'a.id_laman = l.id AND a.is_off = 0', 'left')
            ->groupBy('l.id')
            ->orderBy('l.kode', 'ASC')
            ->orderBy('l.aksi', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Cek apakah kombinasi kode+aksi sudah ada
     */
    public function cekDuplikat(string $kode, string $aksi, ?int $exceptId = null): bool
    {
        $builder = $this->where('kode', $kode)->where('aksi', $aksi);
        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }
        return $builder->countAllResults() > 0;
    }
}
