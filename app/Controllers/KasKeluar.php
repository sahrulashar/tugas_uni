<?php

namespace App\Controllers;

use App\Models\CoaModel;

class KasKeluar extends BaseController
{
    protected CoaModel $coaModel;

    public function __construct()
    {
        $this->coaModel = new CoaModel();
    }

    public function coa()
    {
        $data['coa'] = $this->coaModel->getAll();
        $data['stats'] = $this->coaModel->getTotalPerTipe();

        return view('kas_keluar/coa', $data);
    }

    public function tambah_coa()
    {
        return view('kas_keluar/tambah_coa');
    }

    public function simpan_coa()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/kas_keluar/coa');
        }

        $kodeCoa = trim($this->request->getPost('kode_coa'));
        $namaCoa = trim($this->request->getPost('nama_coa'));
        $tipeAkun = trim($this->request->getPost('tipe_akun'));
        $saldoNormal = trim($this->request->getPost('saldo_normal'));
        $status = trim($this->request->getPost('status'));

        if ($kodeCoa === '' || $namaCoa === '' || $tipeAkun === '' || $saldoNormal === '') {
            return redirect()->back()->with('error', 'Semua field wajib diisi.');
        }

        if ($this->coaModel->cekKode($kodeCoa)) {
            return redirect()->back()->with('error', 'Kode COA sudah digunakan.');
        }

        $this->coaModel->insert([
            'kode_coa' => $kodeCoa,
            'nama_coa' => $namaCoa,
            'tipe_akun' => $tipeAkun,
            'saldo_normal' => $saldoNormal,
            'status' => $status ?: 'Aktif',
        ]);

        return redirect()->to('/kas_keluar/coa')
            ->with('success', 'Akun COA berhasil ditambahkan.');
    }

    public function edit_coa($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/coa');
        }

        $coa = $this->coaModel->find($id);

        if (!$coa) {
            return redirect()->to('/kas_keluar/coa')
                ->with('error', 'Data COA tidak ditemukan.');
        }

        return view('kas_keluar/edit_coa', ['coa' => $coa]);
    }

    public function update_coa()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/kas_keluar/coa');
        }

        $id = $this->request->getPost('id_coa');

        if (!$id) {
            return redirect()->to('/kas_keluar/coa');
        }

        $kodeCoa = trim($this->request->getPost('kode_coa'));
        $namaCoa = trim($this->request->getPost('nama_coa'));
        $tipeAkun = trim($this->request->getPost('tipe_akun'));
        $saldoNormal = trim($this->request->getPost('saldo_normal'));
        $status = trim($this->request->getPost('status'));

        if ($kodeCoa === '' || $namaCoa === '' || $tipeAkun === '' || $saldoNormal === '') {
            return redirect()->back()->with('error', 'Semua field wajib diisi.');
        }

        if ($this->coaModel->cekKode($kodeCoa, (int) $id)) {
            return redirect()->back()->with('error', 'Kode COA sudah digunakan.');
        }

        $this->coaModel->update($id, [
            'kode_coa' => $kodeCoa,
            'nama_coa' => $namaCoa,
            'tipe_akun' => $tipeAkun,
            'saldo_normal' => $saldoNormal,
            'status' => $status,
        ]);

        return redirect()->to('/kas_keluar/coa')
            ->with('success', 'Akun COA berhasil diperbarui.');
    }

    public function lihat_coa($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/coa');
        }

        $coa = $this->coaModel->find($id);

        if (!$coa) {
            return redirect()->to('/kas_keluar/coa')
                ->with('error', 'Data COA tidak ditemukan.');
        }

        return view('kas_keluar/lihat_coa', ['coa' => $coa]);
    }

    public function hapus_coa($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/coa');
        }

        $coa = $this->coaModel->find($id);

        if (!$coa) {
            return redirect()->to('/kas_keluar/coa')
                ->with('error', 'Data COA tidak ditemukan.');
        }

        $this->coaModel->nonaktifkan($id);

        return redirect()->to('/kas_keluar/coa')
            ->with('success', 'Akun COA berhasil dinonaktifkan.');
    }

    public function aktifkan_coa($id = null)
    {
        if ($id) {
            $this->coaModel->aktifkan($id);
        }

        return redirect()->to('/kas_keluar/coa')
            ->with('success', 'Akun COA berhasil diaktifkan.');
    }

    public function hapus_permanen_coa($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/coa');
        }

        $coa = $this->coaModel->find($id);

        if (!$coa) {
            return redirect()->to('/kas_keluar/coa')
                ->with('error', 'Data COA tidak ditemukan.');
        }

        $this->coaModel->delete($id);

        return redirect()->to('/kas_keluar/coa')
            ->with('success', 'Akun COA berhasil dihapus permanen.');
    }

    public function supplier()
    {
        $supplierModel = model('SupplierModel');

        $data['supplier'] = $supplierModel->findAll();

        return view('kas_keluar/supplier', $data);
    }

    public function tambah_supplier()
    {
        return view('kas_keluar/tambah_supplier');
    }

    public function simpan_supplier()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/kas_keluar/supplier');
        }

        $supplierModel = model('SupplierModel');

        $kodeSupplier = trim($this->request->getPost('kode_supplier'));
        $namaSupplier = trim($this->request->getPost('nama_supplier'));
        $alamat = trim($this->request->getPost('alamat'));
        $status = trim($this->request->getPost('status'));

        if ($kodeSupplier === '' || $namaSupplier === '') {
            return redirect()->back()->with('error', 'Kode dan Nama Supplier wajib diisi.');
        }

        if ($supplierModel->cekKode($kodeSupplier)) {
            return redirect()->back()->with('error', 'Kode Supplier sudah digunakan.');
        }

        $supplierModel->insert([
            'kode_supplier' => $kodeSupplier,
            'nama_supplier' => $namaSupplier,
            'alamat' => $alamat,
            'status' => $status ?: 'Aktif',
        ]);

        return redirect()->to('/kas_keluar/supplier')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit_supplier($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/supplier');
        }

        $supplierModel = model('SupplierModel');
        $supplier = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/kas_keluar/supplier')
                ->with('error', 'Data Supplier tidak ditemukan.');
        }

        return view('kas_keluar/edit_supplier', ['supplier' => $supplier]);
    }

    public function update_supplier()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/kas_keluar/supplier');
        }

        $id = $this->request->getPost('id_supplier');

        if (!$id) {
            return redirect()->to('/kas_keluar/supplier');
        }

        $supplierModel = model('SupplierModel');

        $kodeSupplier = trim($this->request->getPost('kode_supplier'));
        $namaSupplier = trim($this->request->getPost('nama_supplier'));
        $alamat = trim($this->request->getPost('alamat'));
        $status = trim($this->request->getPost('status'));

        if ($kodeSupplier === '' || $namaSupplier === '') {
            return redirect()->back()->with('error', 'Kode dan Nama Supplier wajib diisi.');
        }

        if ($supplierModel->cekKode($kodeSupplier, (int) $id)) {
            return redirect()->back()->with('error', 'Kode Supplier sudah digunakan.');
        }

        $supplierModel->update($id, [
            'kode_supplier' => $kodeSupplier,
            'nama_supplier' => $namaSupplier,
            'alamat' => $alamat,
            'status' => $status,
        ]);

        return redirect()->to('/kas_keluar/supplier')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function hapus_supplier($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/supplier');
        }

        $supplierModel = model('SupplierModel');
        $supplier = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/kas_keluar/supplier')
                ->with('error', 'Data Supplier tidak ditemukan.');
        }

        $supplierModel->nonaktifkan($id);

        return redirect()->to('/kas_keluar/supplier')
            ->with('success', 'Supplier berhasil dinonaktifkan.');
    }

    public function aktifkan_supplier($id = null)
    {
        if ($id) {
            $supplierModel = model('SupplierModel');
            $supplierModel->aktifkan($id);
        }

        return redirect()->to('/kas_keluar/supplier')
            ->with('success', 'Supplier berhasil diaktifkan.');
    }

    public function hapus_permanen_supplier($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/supplier');
        }

        $supplierModel = model('SupplierModel');
        $supplier      = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/kas_keluar/supplier')
                ->with('error', 'Data Supplier tidak ditemukan.');
        }

        $supplierModel->delete($id);

        return redirect()->to('/kas_keluar/supplier')
            ->with('success', 'Supplier berhasil dihapus permanen.');
    }

    public function lihat_supplier($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/supplier');
        }

        $supplierModel = model('SupplierModel');
        $supplier      = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/kas_keluar/supplier')
                ->with('error', 'Data Supplier tidak ditemukan.');
        }

        return view('kas_keluar/lihat_supplier', ['supplier' => $supplier]);
    }

    // ═══════════════════════════════════════════
    //  KARYAWAN — CRUD
    // ═══════════════════════════════════════════

    public function karyawan()
    {
        $karyawanModel    = model('KaryawanModel');
        $data['karyawan'] = $karyawanModel->getAll();
        $data['stats']    = $karyawanModel->getTotalPerJabatan();

        return view('kas_keluar/karyawan', $data);
    }

    public function tambah_karyawan()
    {
        return view('kas_keluar/tambah_karyawan');
    }

    public function simpan_karyawan()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/kas_keluar/karyawan');
        }

        $karyawanModel = model('KaryawanModel');

        $nip           = trim($this->request->getPost('nip'));
        $namaKaryawan  = trim($this->request->getPost('nama_karyawan'));
        $jabatan       = trim($this->request->getPost('jabatan'));
        $status        = trim($this->request->getPost('status'));

        if ($nip === '' || $namaKaryawan === '') {
            return redirect()->back()->with('error', 'NIP dan Nama Karyawan wajib diisi.');
        }

        if ($karyawanModel->cekNip($nip)) {
            return redirect()->back()->with('error', 'NIP sudah digunakan.');
        }

        $karyawanModel->insert([
            'nip'           => $nip,
            'nama_karyawan' => $namaKaryawan,
            'jabatan'       => $jabatan,
            'status'        => $status ?: 'Aktif',
        ]);

        return redirect()->to('/kas_keluar/karyawan')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit_karyawan($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/karyawan');
        }

        $karyawanModel = model('KaryawanModel');
        $karyawan      = $karyawanModel->find($id);

        if (!$karyawan) {
            return redirect()->to('/kas_keluar/karyawan')
                ->with('error', 'Data Karyawan tidak ditemukan.');
        }

        return view('kas_keluar/edit_karyawan', ['karyawan' => $karyawan]);
    }

    public function update_karyawan()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/kas_keluar/karyawan');
        }

        $id = $this->request->getPost('id_karyawan');
        if (!$id) {
            return redirect()->to('/kas_keluar/karyawan');
        }

        $karyawanModel = model('KaryawanModel');

        $nip          = trim($this->request->getPost('nip'));
        $namaKaryawan = trim($this->request->getPost('nama_karyawan'));
        $jabatan      = trim($this->request->getPost('jabatan'));
        $status       = trim($this->request->getPost('status'));

        if ($nip === '' || $namaKaryawan === '') {
            return redirect()->back()->with('error', 'NIP dan Nama Karyawan wajib diisi.');
        }

        if ($karyawanModel->cekNip($nip, (int) $id)) {
            return redirect()->back()->with('error', 'NIP sudah digunakan.');
        }

        $karyawanModel->update($id, [
            'nip'           => $nip,
            'nama_karyawan' => $namaKaryawan,
            'jabatan'       => $jabatan,
            'status'        => $status,
        ]);

        return redirect()->to('/kas_keluar/karyawan')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function lihat_karyawan($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/karyawan');
        }

        $karyawanModel = model('KaryawanModel');
        $karyawan      = $karyawanModel->find($id);

        if (!$karyawan) {
            return redirect()->to('/kas_keluar/karyawan')
                ->with('error', 'Data Karyawan tidak ditemukan.');
        }

        return view('kas_keluar/lihat_karyawan', ['karyawan' => $karyawan]);
    }

    public function hapus_karyawan($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/karyawan');
        }

        $karyawanModel = model('KaryawanModel');
        $karyawan      = $karyawanModel->find($id);

        if (!$karyawan) {
            return redirect()->to('/kas_keluar/karyawan')
                ->with('error', 'Data Karyawan tidak ditemukan.');
        }

        $karyawanModel->nonaktifkan($id);

        return redirect()->to('/kas_keluar/karyawan')
            ->with('success', 'Karyawan berhasil dinonaktifkan.');
    }

    public function aktifkan_karyawan($id = null)
    {
        if ($id) {
            model('KaryawanModel')->aktifkan($id);
        }

        return redirect()->to('/kas_keluar/karyawan')
            ->with('success', 'Karyawan berhasil diaktifkan.');
    }

    public function hapus_permanen_karyawan($id = null)
    {
        if (!$id) {
            return redirect()->to('/kas_keluar/karyawan');
        }

        $karyawanModel = model('KaryawanModel');
        $karyawan      = $karyawanModel->find($id);

        if (!$karyawan) {
            return redirect()->to('/kas_keluar/karyawan')
                ->with('error', 'Data Karyawan tidak ditemukan.');
        }

        $karyawanModel->delete($id);

        return redirect()->to('/kas_keluar/karyawan')
            ->with('success', 'Karyawan berhasil dihapus permanen.');
    }

}