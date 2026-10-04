<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Auth — Controller untuk Login & Logout
 *
 * Route:
 *   GET  /login   → form login
 *   POST /login   → proses login
 *   GET  /logout  → proses logout
 *
 * @package App\Controllers
 */
class Auth extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ═══════════════════════════════════════════
    //  FORM LOGIN
    // ═══════════════════════════════════════════

    /**
     * Tampilkan form login.
     * Jika sudah login → redirect ke dashboard.
     */
    public function login()
    {
        // Sudah login? langsung ke dalam
        if (session()->get('is_logged_in')) {
            return redirect()->to(base_url('46124026'));
        }

        return view('auth/login');
    }

    // ═══════════════════════════════════════════
    //  PROSES LOGIN
    // ═══════════════════════════════════════════

    /**
     * Proses form login (POST).
     *
     * Validasi:
     *   1. kode & password tidak boleh kosong
     *   2. kode harus ada di database dan is_off = 0
     *   3. password harus cocok (bcrypt verify)
     */
    public function prosesLogin()
    {
        if (!$this->request->is('post')) {
            return redirect()->to(base_url('login'));
        }

        $kode     = trim($this->request->getPost('kode'));
        $password = $this->request->getPost('password');

        // Validasi input kosong
        if (empty($kode) || empty($password)) {
            return redirect()->back()->withInput()
                ->with('error', 'Kode dan password wajib diisi.');
        }

        // Verifikasi ke database
        $user = $this->userModel->login($kode, $password);

        if (!$user) {
            // Log percobaan gagal (opsional)
            log_message('warning', "[Auth] Percobaan login gagal untuk kode: {$kode}");

            return redirect()->back()->withInput()
                ->with('error', 'Kode atau password salah, atau akun tidak aktif.');
        }

        // ─── Login berhasil: regenerasi session ID demi keamanan (anti session fixation) ───
        session()->regenerate();
        session()->set([
            'is_logged_in' => true,
            'user_id'      => $user['id'],
            'kode_user'    => $user['kode'],
            'nama_user'    => $user['nama'],
        ]);

        \App\Libraries\AuditLogger::catat('LOGIN', 'tbuser', (int) $user['id'], [], 'Auth', "User {$user['nama']} ({$user['kode']}) login berhasil.");
        log_message('info', "[Auth] Login berhasil: {$user['kode']} ({$user['nama']})");

        return redirect()->to(base_url('46124026'))
            ->with('success', 'Selamat datang, ' . $user['nama'] . '!');
    }

    // ═══════════════════════════════════════════
    //  LOGOUT
    // ═══════════════════════════════════════════

    /**
     * Hapus semua data sesi dan redirect ke login.
     */
    public function logout()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $nama   = session()->get('nama_user') ?? 'User';

        if ($userId > 0) {
            \App\Libraries\AuditLogger::catat('LOGOUT', 'tbuser', $userId, [], 'Auth', "User {$nama} logout.");
        }

        log_message('info', "[Auth] Logout: {$nama}");

        session()->destroy();

        return redirect()->to(base_url('login'))
            ->with('success', 'Anda berhasil keluar. Sampai jumpa!');
    }
}
