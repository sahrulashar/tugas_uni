<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel — Model untuk tabel tbuser
 *
 * @package App\Models
 */
class UserModel extends Model
{
    protected $table         = 'tbuser';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['kode', 'nama', 'password', 'is_off'];
    protected $useTimestamps = false;

    // ───────────────────────────────────────────
    //  QUERY HELPERS
    // ───────────────────────────────────────────

    /**
     * Ambil semua user yang aktif (is_off = 0)
     */
    public function getAktif(): array
    {
        return $this->where('is_off', 0)->orderBy('nama', 'ASC')->findAll();
    }

    /**
     * Cari user berdasarkan kode login
     */
    public function findByKode(string $kode): ?array
    {
        return $this->where('kode', $kode)->first();
    }

    /**
     * Cek apakah kode sudah dipakai (untuk validasi duplikat)
     */
    public function cekKode(string $kode, ?int $exceptId = null): bool
    {
        $builder = $this->where('kode', $kode);
        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Aktifkan / nonaktifkan user
     */
    public function toggleStatus(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) return false;
        return $this->update($id, ['is_off' => $user['is_off'] ? 0 : 1]);
    }

    // ───────────────────────────────────────────
    //  AUTENTIKASI
    // ───────────────────────────────────────────

    /**
     * Verifikasi login: cari user aktif dan cocokkan password.
     *
     * @param string $kode      Kode/username yang diinput
     * @param string $password  Password plain text
     * @return array|null       Data user jika cocok, null jika gagal
     */
    public function login(string $kode, string $password): ?array
    {
        $user = $this->where('kode', $kode)
                     ->where('is_off', 0)
                     ->first();

        if (!$user) {
            return null; // user tidak ditemukan / nonaktif
        }

        if (!password_verify($password, $user['password'])) {
            return null; // password salah
        }

        return $user;
    }
}
