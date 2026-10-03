<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AksesModel — Model untuk tabel tbakses
 *
 * @package App\Models
 */
class AksesModel extends Model
{
    protected $table         = 'tbakses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['id_user', 'id_laman', 'is_off'];
    protected $useTimestamps = false;

    // ───────────────────────────────────────────
    //  CEK AKSES (dipakai RbacChecker)
    // ───────────────────────────────────────────

    /**
     * Cek apakah user memiliki akses ke kombinasi kode_laman + aksi tertentu.
     *
     * @param int    $userId    ID user yang sedang login
     * @param string $kodeLaman Kode halaman (misal: 'ak1', 'ak2', 'coa')
     * @param string $aksi      Jenis aksi (daftar|tambah|edit|hapus|lihat|cetak)
     * @return bool             true = punya akses, false = tidak ada akses
     */
    public function punya(int $userId, string $kodeLaman, string $aksi): bool
    {
        $count = $this->db->table('tbakses a')
            ->join('tblaman l', 'l.id = a.id_laman')
            ->where('a.id_user',  $userId)
            ->where('a.is_off',   0)
            ->where('l.kode',     $kodeLaman)
            ->where('l.aksi',     $aksi)
            ->where('l.is_off',   0)
            ->countAllResults();

        return $count > 0;
    }

    // ───────────────────────────────────────────
    //  MANAJEMEN AKSES
    // ───────────────────────────────────────────

    /**
     * Ambil semua laman beserta status akses untuk satu user
     * (digunakan di form atur-akses)
     *
     * @return array  [['id' => laman_id, 'kode' => ..., 'nama' => ..., 'aksi' => ..., 'has_akses' => bool], ...]
     */
    public function getAksesUser(int $userId): array
    {
        return $this->db->table('tblaman l')
            ->select('l.id, l.kode, l.nama, l.aksi, l.is_off,
                      IF(a.id IS NOT NULL AND a.is_off = 0, 1, 0) AS has_akses,
                      a.id AS id_akses')
            ->join('tbakses a', "a.id_laman = l.id AND a.id_user = {$userId}", 'left')
            ->where('l.is_off', 0)
            ->orderBy('l.kode', 'ASC')
            ->orderBy('l.aksi', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Simpan ulang akses satu user (hapus semua lalu insert baru).
     * Dipanggil dari form atur-akses.
     *
     * @param int   $userId    ID user yang diatur
     * @param array $lamanIds  Array of id_laman yang dicentang
     * @return bool
     */
    public function simpanAksesUser(int $userId, array $lamanIds): bool
    {
        $db = $this->db;
        $db->transStart();

        // Hapus semua akses lama milik user ini
        $db->table('tbakses')->where('id_user', $userId)->delete();

        // Insert akses baru
        if (!empty($lamanIds)) {
            $insertData = [];
            foreach ($lamanIds as $lamanId) {
                $insertData[] = [
                    'id_user'  => $userId,
                    'id_laman' => (int) $lamanId,
                    'is_off'   => 0,
                ];
            }
            $db->table('tbakses')->insertBatch($insertData);
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Ambil daftar kode laman yang dimiliki user (untuk sidebar/menu)
     * Return array berisi kode dan aksi yang boleh diakses.
     *
     * @return array  [['kode' => 'ak1', 'aksi' => 'daftar'], ...]
     */
    public function getMenuUser(int $userId): array
    {
        return $this->db->table('tbakses a')
            ->select('l.kode, l.aksi')
            ->join('tblaman l', 'l.id = a.id_laman')
            ->where('a.id_user', $userId)
            ->where('a.is_off', 0)
            ->where('l.is_off', 0)
            ->get()
            ->getResultArray();
    }
}
