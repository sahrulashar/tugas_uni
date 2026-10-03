<?php

namespace App\Libraries;

use App\Models\AksesModel;

/**
 * RbacChecker — Helper terpusat untuk cek hak akses RBAC
 *
 * Dipanggil dari controller sebelum mengeksekusi aksi apapun.
 *
 * Cara pakai di Controller:
 *
 *   // Di awal setiap method:
 *   RbacChecker::gate('ak1', 'daftar');   // cek + redirect otomatis jika tidak boleh
 *
 *   // Atau manual (return bool):
 *   if (!RbacChecker::boleh('ak1', 'tambah')) {
 *       return redirect()->back()->with('error', 'Akses ditolak.');
 *   }
 *
 * @package App\Libraries
 */
class RbacChecker
{
    /**
     * Cek apakah user yang sedang login punya akses ke kode+aksi tertentu.
     *
     * @param string $kodeLaman  Kode halaman (misal: 'ak1', 'ak2', 'coa')
     * @param string $aksi       Jenis aksi (daftar|tambah|edit|hapus|lihat|cetak)
     * @return bool
     */
    public static function boleh(string $kodeLaman, string $aksi): bool
    {
        $userId = (int) session()->get('user_id');

        // Belum login → anggap tidak boleh
        if ($userId <= 0) {
            return false;
        }

        $aksesModel = new AksesModel();
        return $aksesModel->punya($userId, $kodeLaman, $aksi);
    }

    /**
     * Gerbang akses — cek dan REDIRECT otomatis jika ditolak.
     *
     * Panggil di awal setiap method controller yang perlu dilindungi.
     * Jika tidak punya akses, redirect ke halaman sebelumnya dengan flash error.
     *
     * Contoh:
     *   public function index() {
     *       RbacChecker::gate('ak1', 'daftar');
     *       // ... lanjut kode normal
     *   }
     *
     * @param string $kodeLaman
     * @param string $aksi
     * @return void
     */
    public static function gate(string $kodeLaman, string $aksi): void
    {
        if (!self::boleh($kodeLaman, $aksi)) {
            session()->setFlashdata('error', '⛔ Akses ditolak. Anda tidak memiliki izin untuk tindakan ini (' . $kodeLaman . ' - ' . $aksi . ').');

            $target = base_url('46124026');
            $prev   = previous_url();
            $curr   = current_url();

            // Hindari redirect ke URL yang sama agar tidak terjadi infinite loop
            if ($prev && $prev !== $curr && !str_contains($prev, 'login')) {
                $target = $prev;
            }

            header('Location: ' . $target);
            exit;
        }
    }

    /**
     * Ambil daftar akses user yang sedang login (untuk membangun menu sidebar)
     *
     * @return array  [['kode' => 'ak1', 'aksi' => 'daftar'], ...]
     */
    public static function menuUser(): array
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) return [];

        $aksesModel = new AksesModel();
        return $aksesModel->getMenuUser($userId);
    }

    /**
     * Cek apakah user punya akses ke kode laman tertentu (aksi apapun)
     * Berguna untuk menampilkan/menyembunyikan item menu.
     *
     * @param string $kodeLaman
     * @return bool
     */
    public static function bolehLaman(string $kodeLaman): bool
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) return false;

        $count = db_connect()->table('tbakses a')
            ->join('tblaman l', 'l.id = a.id_laman')
            ->where('a.id_user', $userId)
            ->where('a.is_off', 0)
            ->where('l.kode', $kodeLaman)
            ->where('l.is_off', 0)
            ->countAllResults();

        return $count > 0;
    }
}
