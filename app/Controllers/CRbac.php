<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LamanModel;
use App\Models\AksesModel;
use App\Libraries\RbacChecker;
use App\Libraries\AuditLogger;

/**
 * CRbac — Controller manajemen RBAC (User, Laman, Akses)
 *
 * Hanya bisa diakses oleh user yang punya akses ke kode 'usr', 'lmn', 'aks'
 *
 * Routes:
 *   /46124026/rbac/user/*
 *   /46124026/rbac/laman/*
 *   /46124026/rbac/akses/*
 *
 * @package App\Controllers
 */
class CRbac extends BaseController
{
    private UserModel  $userModel;
    private LamanModel $lamanModel;
    private AksesModel $aksesModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->lamanModel = new LamanModel();
        $this->aksesModel = new AksesModel();
    }

    // ═══════════════════════════════════════════════════════════════
    //  SECTION A: MANAJEMEN USER
    // ═══════════════════════════════════════════════════════════════

    public function daftarUser(): string
    {
        RbacChecker::gate('usr', 'daftar');

        return view('rbac/user/index', [
            'users' => $this->userModel->findAll(),
        ]);
    }

    public function tambahUser(): string
    {
        RbacChecker::gate('usr', 'tambah');
        return view('rbac/user/tambah');
    }

    public function simpanUser()
    {
        RbacChecker::gate('usr', 'tambah');

        $kode     = trim($this->request->getPost('kode'));
        $nama     = trim($this->request->getPost('nama'));
        $password = $this->request->getPost('password');

        if (empty($kode) || empty($nama) || empty($password)) {
            return redirect()->back()->withInput()
                ->with('error', 'Kode, nama, dan password wajib diisi.');
        }

        if ($this->userModel->cekKode($kode)) {
            return redirect()->back()->withInput()
                ->with('error', 'Kode user sudah digunakan.');
        }

        $id = $this->userModel->insert([
            'kode'     => $kode,
            'nama'     => $nama,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'is_off'   => 0,
        ]);

        AuditLogger::catat('TAMBAH', 'tbuser', (int) $id, ['after' => compact('kode', 'nama')]);

        return redirect()->to(base_url('46124026/rbac/user'))
            ->with('success', "User '{$nama}' berhasil ditambahkan.");
    }

    public function editUser(int $id)
    {
        RbacChecker::gate('usr', 'edit');

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('46124026/rbac/user'))
                ->with('error', 'User tidak ditemukan.');
        }

        return view('rbac/user/edit', ['user' => $user]);
    }

    public function updateUser()
    {
        RbacChecker::gate('usr', 'edit');

        $id   = (int) $this->request->getPost('id');
        $kode = trim($this->request->getPost('kode'));
        $nama = trim($this->request->getPost('nama'));
        $pass = $this->request->getPost('password');

        if (empty($kode) || empty($nama)) {
            return redirect()->back()->withInput()
                ->with('error', 'Kode dan nama wajib diisi.');
        }

        if ($this->userModel->cekKode($kode, $id)) {
            return redirect()->back()->withInput()
                ->with('error', 'Kode user sudah digunakan.');
        }

        $data = ['kode' => $kode, 'nama' => $nama];
        if (!empty($pass)) {
            $data['password'] = password_hash($pass, PASSWORD_BCRYPT);
        }

        $dataLama = $this->userModel->find($id);
        $this->userModel->update($id, $data);

        AuditLogger::catat('EDIT', 'tbuser', $id, [
            'before' => ['kode' => $dataLama['kode'], 'nama' => $dataLama['nama']],
            'after'  => compact('kode', 'nama'),
        ]);

        return redirect()->to(base_url('46124026/rbac/user'))
            ->with('success', "User '{$nama}' berhasil diperbarui.");
    }

    public function toggleUser(int $id)
    {
        RbacChecker::gate('usr', 'hapus');

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('46124026/rbac/user'))
                ->with('error', 'User tidak ditemukan.');
        }

        // Cegah menonaktifkan diri sendiri
        if ($id === (int) session()->get('user_id')) {
            return redirect()->to(base_url('46124026/rbac/user'))
                ->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        $this->userModel->toggleStatus($id);
        $status = $user['is_off'] ? 'diaktifkan' : 'dinonaktifkan';

        AuditLogger::catat('EDIT', 'tbuser', $id, ['aksi' => "toggle_status → {$status}"]);

        return redirect()->to(base_url('46124026/rbac/user'))
            ->with('success', "User '{$user['nama']}' berhasil {$status}.");
    }

    // ═══════════════════════════════════════════════════════════════
    //  SECTION B: MANAJEMEN LAMAN
    // ═══════════════════════════════════════════════════════════════

    public function daftarLaman(): string
    {
        RbacChecker::gate('lmn', 'daftar');

        return view('rbac/laman/index', [
            'laman' => $this->lamanModel->getAllWithAksesCount(),
        ]);
    }

    public function tambahLaman(): string
    {
        RbacChecker::gate('lmn', 'tambah');
        return view('rbac/laman/tambah');
    }

    public function simpanLaman()
    {
        RbacChecker::gate('lmn', 'tambah');

        $kode = trim($this->request->getPost('kode'));
        $nama = trim($this->request->getPost('nama'));
        $aksi = trim($this->request->getPost('aksi'));

        if (empty($kode) || empty($nama) || empty($aksi)) {
            return redirect()->back()->withInput()
                ->with('error', 'Semua kolom wajib diisi.');
        }

        if ($this->lamanModel->cekDuplikat($kode, $aksi)) {
            return redirect()->back()->withInput()
                ->with('error', "Kombinasi kode '{$kode}' + aksi '{$aksi}' sudah ada.");
        }

        $id = $this->lamanModel->insert(compact('kode', 'nama', 'aksi') + ['is_off' => 0]);

        AuditLogger::catat('TAMBAH', 'tblaman', (int) $id, ['after' => compact('kode', 'nama', 'aksi')]);

        return redirect()->to(base_url('46124026/rbac/laman'))
            ->with('success', "Laman '{$nama} – {$aksi}' berhasil ditambahkan.");
    }

    public function editLaman(int $id)
    {
        RbacChecker::gate('lmn', 'edit');

        $laman = $this->lamanModel->find($id);
        if (!$laman) {
            return redirect()->to(base_url('46124026/rbac/laman'))
                ->with('error', 'Laman tidak ditemukan.');
        }

        return view('rbac/laman/edit', ['laman' => $laman]);
    }

    public function updateLaman()
    {
        RbacChecker::gate('lmn', 'edit');

        $id   = (int) $this->request->getPost('id');
        $kode = trim($this->request->getPost('kode'));
        $nama = trim($this->request->getPost('nama'));
        $aksi = trim($this->request->getPost('aksi'));

        if ($this->lamanModel->cekDuplikat($kode, $aksi, $id)) {
            return redirect()->back()->withInput()
                ->with('error', "Kombinasi kode + aksi sudah ada.");
        }

        $this->lamanModel->update($id, compact('kode', 'nama', 'aksi'));

        AuditLogger::catat('EDIT', 'tblaman', $id, ['after' => compact('kode', 'nama', 'aksi')]);

        return redirect()->to(base_url('46124026/rbac/laman'))
            ->with('success', 'Laman berhasil diperbarui.');
    }

    public function hapusLaman(int $id)
    {
        RbacChecker::gate('lmn', 'hapus');

        $laman = $this->lamanModel->find($id);
        if (!$laman) {
            return redirect()->to(base_url('46124026/rbac/laman'))
                ->with('error', 'Laman tidak ditemukan.');
        }

        AuditLogger::catat('HAPUS', 'tblaman', $id, ['before' => $laman]);
        $this->lamanModel->delete($id);

        return redirect()->to(base_url('46124026/rbac/laman'))
            ->with('success', 'Laman berhasil dihapus.');
    }

    // ═══════════════════════════════════════════════════════════════
    //  SECTION C: MANAJEMEN AKSES (atur akses per user)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Daftar user beserta tombol "Atur Akses"
     */
    public function daftarAkses(): string
    {
        RbacChecker::gate('aks', 'daftar');

        return view('rbac/akses/index', [
            'users' => $this->userModel->getAktif(),
        ]);
    }

    /**
     * Form atur akses untuk satu user (checklist semua laman)
     */
    public function aturAkses(int $userId)
    {
        RbacChecker::gate('aks', 'edit');

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to(base_url('46124026/rbac/akses'))
                ->with('error', 'User tidak ditemukan.');
        }

        return view('rbac/akses/atur', [
            'user'  => $user,
            'laman' => $this->aksesModel->getAksesUser($userId),
        ]);
    }

    /**
     * Simpan akses yang dicentang untuk satu user
     */
    public function simpanAkses()
    {
        RbacChecker::gate('aks', 'edit');

        $userId   = (int) $this->request->getPost('id_user');
        $lamanIds = $this->request->getPost('laman_ids') ?? [];

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to(base_url('46124026/rbac/akses'))
                ->with('error', 'User tidak ditemukan.');
        }

        $berhasil = $this->aksesModel->simpanAksesUser($userId, $lamanIds);

        AuditLogger::catat('EDIT', 'tbakses', $userId, [
            'aksi'     => 'atur_akses',
            'id_laman' => $lamanIds,
        ]);

        if (!$berhasil) {
            return redirect()->back()->with('error', 'Gagal menyimpan akses.');
        }

        return redirect()->to(base_url('46124026/rbac/akses'))
            ->with('success', "Akses user '{$user['nama']}' berhasil diperbarui.");
    }
}
