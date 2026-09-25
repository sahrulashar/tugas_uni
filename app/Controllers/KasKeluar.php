<?php

namespace App\Controllers;

use App\Models\CoaModel;
use App\Libraries\AuditLogger;

class KasKeluar extends BaseController
{
    protected CoaModel $coaModel;

    public function __construct()
    {
        $this->coaModel = new CoaModel();
    }

    // ═══════════════════════════════════════════
    //  COA — CRUD
    // ═══════════════════════════════════════════

    public function coa_l1H()
    {
        $data['coa']   = $this->coaModel->getAll_l1H();
        $data['stats'] = $this->coaModel->getTotalPerTipe_l1H();

        return view('kas_keluar/coa', $data);
    }

    public function tambah_coa_l1H()
    {
        return view('kas_keluar/tambah_coa');
    }

    public function simpan_coa_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H');
        }

        $kodeCoa    = trim($this->request->getPost('kode_coa'));
        $namaCoa    = trim($this->request->getPost('nama_coa'));
        $saldoNormal = trim($this->request->getPost('saldo_normal'));
        $isHeader   = trim($this->request->getPost('is_header')) ?: 'D';
        $tipe       = trim($this->request->getPost('tipe'));

        if ($kodeCoa === '' || $namaCoa === '' || $saldoNormal === '') {
            return redirect()->back()->with('error', 'Kode COA, Nama COA, dan Saldo Normal wajib diisi.');
        }

        if ($this->coaModel->cekKode_l1H($kodeCoa)) {
            return redirect()->back()->with('error', 'Kode COA sudah digunakan.');
        }

        $dataCoa = [
            'kode_coa'    => $kodeCoa,
            'nama_coa'    => $namaCoa,
            'saldo_normal' => $saldoNormal,
            'is_header'   => $isHeader,
            'tipe'        => $tipe ?: null,
            'is_off'      => 0,
        ];

        $idCoa = $this->coaModel->insert($dataCoa);

        AuditLogger::catat('TAMBAH', 'coa', (int) $idCoa, ['after' => $dataCoa]);

        return redirect()->to('/46124026/kas_keluar/coa_l1H')
            ->with('success', 'Akun COA berhasil ditambahkan.');
    }

    public function edit_coa_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H');
        }

        $coa = $this->coaModel->find($id);

        if (!$coa) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H')
                ->with('error', 'Data COA tidak ditemukan.');
        }

        return view('kas_keluar/edit_coa', ['coa' => $coa]);
    }

    public function update_coa_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H');
        }

        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H');
        }

        $kodeCoa    = trim($this->request->getPost('kode_coa'));
        $namaCoa    = trim($this->request->getPost('nama_coa'));
        $saldoNormal = trim($this->request->getPost('saldo_normal'));
        $isHeader   = trim($this->request->getPost('is_header')) ?: 'D';
        $tipe       = trim($this->request->getPost('tipe'));

        if ($kodeCoa === '' || $namaCoa === '' || $saldoNormal === '') {
            return redirect()->back()->with('error', 'Kode COA, Nama COA, dan Saldo Normal wajib diisi.');
        }

        if ($this->coaModel->cekKode_l1H($kodeCoa, (int) $id)) {
            return redirect()->back()->with('error', 'Kode COA sudah digunakan.');
        }

        $dataBaru = [
            'kode_coa'    => $kodeCoa,
            'nama_coa'    => $namaCoa,
            'saldo_normal' => $saldoNormal,
            'is_header'   => $isHeader,
            'tipe'        => $tipe ?: null,
        ];

        $dataLama = $this->coaModel->find($id);
        $this->coaModel->update($id, $dataBaru);

        AuditLogger::catat('EDIT', 'coa', (int) $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ]);

        return redirect()->to('/46124026/kas_keluar/coa_l1H')
            ->with('success', 'Akun COA berhasil diperbarui.');
    }

    public function lihat_coa_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H');
        }

        $coa = $this->coaModel->find($id);

        if (!$coa) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H')
                ->with('error', 'Data COA tidak ditemukan.');
        }

        return view('kas_keluar/lihat_coa', ['coa' => $coa]);
    }

    public function hapus_coa_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H');
        }

        $coa = $this->coaModel->find($id);

        if (!$coa) {
            return redirect()->to('/46124026/kas_keluar/coa_l1H')
                ->with('error', 'Data COA tidak ditemukan.');
        }

        $this->coaModel->nonaktifkan_l1H($id);

        AuditLogger::catat('NONAKTIF', 'coa', (int) $id, [
            'before' => $coa,
            'after'  => ['is_off' => 1],
        ]);

        return redirect()->to('/46124026/kas_keluar/coa_l1H')
            ->with('success', 'Akun COA berhasil dinonaktifkan.');
    }

    public function aktifkan_coa_l1H($id = null)
    {
        if ($id) {
            $this->coaModel->aktifkan_l1H($id);

            AuditLogger::catat('AKTIF', 'coa', (int) $id, [
                'after' => ['is_off' => 0],
            ]);
        }

        return redirect()->to('/46124026/kas_keluar/coa_l1H')
            ->with('success', 'Akun COA berhasil diaktifkan.');
    }

    public function hapus_permanen_coa_l1H($id = null)
    {
        // Dialihkan ke soft delete (nonaktifkan) demi integritas data dan audit trail
        return $this->hapus_coa_l1H($id);
    }


    // ═══════════════════════════════════════════
    //  SUPPLIER — CRUD
    // ═══════════════════════════════════════════

    public function supplier_l1H()
    {
        $supplierModel = model('SupplierModel');
        $data['supplier'] = $supplierModel->findAll();

        return view('kas_keluar/supplier', $data);
    }

    public function tambah_supplier_l1H()
    {
        return view('kas_keluar/tambah_supplier');
    }

    public function simpan_supplier_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H');
        }

        $supplierModel = model('SupplierModel');

        $kodeSupplier = trim($this->request->getPost('kode_supplier'));
        $namaSupplier = trim($this->request->getPost('nama_supplier'));
        $alamat       = trim($this->request->getPost('alamat'));
        $status       = trim($this->request->getPost('status'));

        if ($kodeSupplier === '' || $namaSupplier === '') {
            return redirect()->back()->with('error', 'Kode dan Nama Supplier wajib diisi.');
        }

        if ($supplierModel->cekKode_l1H($kodeSupplier)) {
            return redirect()->back()->with('error', 'Kode Supplier sudah digunakan.');
        }

        $dataSupplier = [
            'kode_supplier' => $kodeSupplier,
            'nama_supplier' => $namaSupplier,
            'alamat'        => $alamat,
            'status'        => $status ?: 'Aktif',
        ];

        $idSupplier = $supplierModel->insert($dataSupplier);

        AuditLogger::catat('TAMBAH', 'supplier', (int) $idSupplier, ['after' => $dataSupplier]);

        return redirect()->to('/46124026/kas_keluar/supplier_l1H')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit_supplier_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H');
        }

        $supplierModel = model('SupplierModel');
        $supplier      = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H')
                ->with('error', 'Data Supplier tidak ditemukan.');
        }

        return view('kas_keluar/edit_supplier', ['supplier' => $supplier]);
    }

    public function update_supplier_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H');
        }

        $id = $this->request->getPost('id_supplier');

        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H');
        }

        $supplierModel = model('SupplierModel');

        $kodeSupplier = trim($this->request->getPost('kode_supplier'));
        $namaSupplier = trim($this->request->getPost('nama_supplier'));
        $alamat       = trim($this->request->getPost('alamat'));
        $status       = trim($this->request->getPost('status'));

        if ($kodeSupplier === '' || $namaSupplier === '') {
            return redirect()->back()->with('error', 'Kode dan Nama Supplier wajib diisi.');
        }

        if ($supplierModel->cekKode_l1H($kodeSupplier, (int) $id)) {
            return redirect()->back()->with('error', 'Kode Supplier sudah digunakan.');
        }

        $dataBaru = [
            'kode_supplier' => $kodeSupplier,
            'nama_supplier' => $namaSupplier,
            'alamat'        => $alamat,
            'status'        => $status,
        ];

        $dataLama = $supplierModel->find($id);
        $supplierModel->update($id, $dataBaru);

        AuditLogger::catat('EDIT', 'supplier', (int) $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ]);

        return redirect()->to('/46124026/kas_keluar/supplier_l1H')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function hapus_supplier_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H');
        }

        $supplierModel = model('SupplierModel');
        $supplier      = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H')
                ->with('error', 'Data Supplier tidak ditemukan.');
        }

        $supplierModel->nonaktifkan_l1H($id);

        AuditLogger::catat('NONAKTIF', 'supplier', (int) $id, [
            'before' => $supplier,
            'after'  => ['status' => 'Tidak Aktif'],
        ]);

        return redirect()->to('/46124026/kas_keluar/supplier_l1H')
            ->with('success', 'Supplier berhasil dinonaktifkan.');
    }

    public function aktifkan_supplier_l1H($id = null)
    {
        if ($id) {
            $supplierModel = model('SupplierModel');
            $supplierModel->aktifkan_l1H($id);

            AuditLogger::catat('AKTIF', 'supplier', (int) $id, [
                'after' => ['status' => 'Aktif'],
            ]);
        }

        return redirect()->to('/46124026/kas_keluar/supplier_l1H')
            ->with('success', 'Supplier berhasil diaktifkan.');
    }

    public function hapus_permanen_supplier_l1H($id = null)
    {
        // Dialihkan ke soft delete (nonaktifkan) demi integritas data dan audit trail
        return $this->hapus_supplier_l1H($id);
    }


    public function lihat_supplier_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H');
        }

        $supplierModel = model('SupplierModel');
        $supplier      = $supplierModel->find($id);

        if (!$supplier) {
            return redirect()->to('/46124026/kas_keluar/supplier_l1H')
                ->with('error', 'Data Supplier tidak ditemukan.');
        }

        return view('kas_keluar/lihat_supplier', ['supplier' => $supplier]);
    }

    // ═══════════════════════════════════════════
    //  KARYAWAN — CRUD
    // ═══════════════════════════════════════════

    public function karyawan_l1H()
    {
        $karyawanModel    = model('KaryawanModel');
        $data['karyawan'] = $karyawanModel->getAll_l1H();
        $data['stats']    = $karyawanModel->getTotalPerJabatan_l1H();

        return view('kas_keluar/karyawan', $data);
    }

    public function tambah_karyawan_l1H()
    {
        return view('kas_keluar/tambah_karyawan');
    }

    public function simpan_karyawan_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H');
        }

        $karyawanModel = model('KaryawanModel');

        $nip          = trim($this->request->getPost('nip'));
        $namaKaryawan = trim($this->request->getPost('nama_karyawan'));
        $jabatan      = trim($this->request->getPost('jabatan'));
        $status       = trim($this->request->getPost('status'));

        if ($nip === '' || $namaKaryawan === '') {
            return redirect()->back()->with('error', 'NIP dan Nama Karyawan wajib diisi.');
        }

        if ($karyawanModel->cekNip_l1H($nip)) {
            return redirect()->back()->with('error', 'NIP sudah digunakan.');
        }

        $dataKaryawan = [
            'nip'           => $nip,
            'nama_karyawan' => $namaKaryawan,
            'jabatan'       => $jabatan,
            'status'        => $status ?: 'Aktif',
        ];

        $idKaryawan = $karyawanModel->insert($dataKaryawan);

        AuditLogger::catat('TAMBAH', 'karyawan', (int) $idKaryawan, ['after' => $dataKaryawan]);

        return redirect()->to('/46124026/kas_keluar/karyawan_l1H')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit_karyawan_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H');
        }

        $karyawanModel = model('KaryawanModel');
        $karyawan      = $karyawanModel->find($id);

        if (!$karyawan) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H')
                ->with('error', 'Data Karyawan tidak ditemukan.');
        }

        return view('kas_keluar/edit_karyawan', ['karyawan' => $karyawan]);
    }

    public function update_karyawan_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H');
        }

        $id = $this->request->getPost('id_karyawan');
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H');
        }

        $karyawanModel = model('KaryawanModel');

        $nip          = trim($this->request->getPost('nip'));
        $namaKaryawan = trim($this->request->getPost('nama_karyawan'));
        $jabatan      = trim($this->request->getPost('jabatan'));
        $status       = trim($this->request->getPost('status'));

        if ($nip === '' || $namaKaryawan === '') {
            return redirect()->back()->with('error', 'NIP dan Nama Karyawan wajib diisi.');
        }

        if ($karyawanModel->cekNip_l1H($nip, (int) $id)) {
            return redirect()->back()->with('error', 'NIP sudah digunakan.');
        }

        $dataBaru = [
            'nip'           => $nip,
            'nama_karyawan' => $namaKaryawan,
            'jabatan'       => $jabatan,
            'status'        => $status,
        ];

        $dataLama = $karyawanModel->find($id);
        $karyawanModel->update($id, $dataBaru);

        AuditLogger::catat('EDIT', 'karyawan', (int) $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ]);

        return redirect()->to('/46124026/kas_keluar/karyawan_l1H')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function lihat_karyawan_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H');
        }

        $karyawanModel = model('KaryawanModel');
        $karyawan      = $karyawanModel->find($id);

        if (!$karyawan) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H')
                ->with('error', 'Data Karyawan tidak ditemukan.');
        }

        return view('kas_keluar/lihat_karyawan', ['karyawan' => $karyawan]);
    }

    public function hapus_karyawan_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H');
        }

        $karyawanModel = model('KaryawanModel');
        $karyawan      = $karyawanModel->find($id);

        if (!$karyawan) {
            return redirect()->to('/46124026/kas_keluar/karyawan_l1H')
                ->with('error', 'Data Karyawan tidak ditemukan.');
        }

        $karyawanModel->nonaktifkan_l1H($id);

        AuditLogger::catat('NONAKTIF', 'karyawan', (int) $id, [
            'before' => $karyawan,
            'after'  => ['status' => 'Tidak Aktif'],
        ]);

        return redirect()->to('/46124026/kas_keluar/karyawan_l1H')
            ->with('success', 'Karyawan berhasil dinonaktifkan.');
    }

    public function aktifkan_karyawan_l1H($id = null)
    {
        if ($id) {
            model('KaryawanModel')->aktifkan_l1H($id);

            AuditLogger::catat('AKTIF', 'karyawan', (int) $id, [
                'after' => ['status' => 'Aktif'],
            ]);
        }

        return redirect()->to('/46124026/kas_keluar/karyawan_l1H')
            ->with('success', 'Karyawan berhasil diaktifkan.');
    }

    public function hapus_permanen_karyawan_l1H($id = null)
    {
        // Dialihkan ke soft delete (nonaktifkan) demi integritas data dan audit trail
        return $this->hapus_karyawan_l1H($id);
    }
}