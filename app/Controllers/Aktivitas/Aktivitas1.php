<?php

namespace App\Controllers\Aktivitas;

use App\Controllers\BaseController;
use App\Models\TbRbeliModel;
use App\Models\TbRbeliDModel;

class Aktivitas1 extends BaseController
{
    protected TbRbeliModel  $rbeliModel;
    protected TbRbeliDModel $rbeliDModel;

    public function __construct()
    {
        $this->rbeliModel  = new TbRbeliModel();
        $this->rbeliDModel = new TbRbeliDModel();
    }

    // ═══════════════════════════════════════════
    //  INDEX — Daftar Rencana Beli
    // ═══════════════════════════════════════════

    public function index()
    {
        $data['rbeli'] = $this->rbeliModel->getAll();

        return view('aktivitas/aktivitas1/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah()
    {
        $supplierModel      = model('SupplierModel');
        $data['supplier']   = $supplierModel->getAktif();

        return view('aktivitas/aktivitas1/tambah', $data);
    }

    public function simpan()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $noRbeli = trim($this->request->getPost('no_rbeli'));
        $tgl     = trim($this->request->getPost('tgl'));
        $idSupp  = (int) $this->request->getPost('id_supp');
        $kete    = trim($this->request->getPost('kete'));

        // Validasi header
        if ($noRbeli === '' || $tgl === '' || $idSupp <= 0) {
            return redirect()->back()->withInput()
                ->with('error', 'No. Rencana Beli, Tanggal, dan Supplier wajib diisi.');
        }

        if ($this->rbeliModel->cekNoRbeli($noRbeli)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor Rencana Beli sudah digunakan.');
        }

        // Ambil data detail (array dari form)
        $noFakturArr = $this->request->getPost('no_faktur');
        $nilaiArr    = $this->request->getPost('nilai');

        if (empty($noFakturArr)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail wajib diisi.');
        }

        // Simpan header
        $idRbeli = $this->rbeliModel->insert([
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => $idSupp,
            'kete'     => $kete ?: null,
        ]);

        // Simpan detail
        foreach ($noFakturArr as $i => $noFaktur) {
            $noFaktur = trim($noFaktur);
            $nilai    = (float) str_replace(',', '', $nilaiArr[$i] ?? 0);

            if ($noFaktur === '' || $nilai <= 0) {
                continue; // skip baris kosong
            }

            $this->rbeliDModel->insert([
                'id_rbeli'  => $idRbeli,
                'no_faktur' => $noFaktur,
                'nilai'     => $nilai,
            ]);
        }

        return redirect()->to('/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->getById((int) $id);

        if (!$rbeli) {
            return redirect()->to('/aktivitas/aktivitas1')
                ->with('error', 'Data Rencana Beli tidak ditemukan.');
        }

        $data['rbeli']  = $rbeli;
        $data['detail'] = $this->rbeliDModel->getByIdRbeli((int) $id);
        $data['total']  = $this->rbeliDModel->getTotalNilai((int) $id);

        return view('aktivitas/aktivitas1/lihat', $data);
    }

    // ═══════════════════════════════════════════
    //  EDIT
    // ═══════════════════════════════════════════

    public function edit($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->getById((int) $id);

        if (!$rbeli) {
            return redirect()->to('/aktivitas/aktivitas1')
                ->with('error', 'Data Rencana Beli tidak ditemukan.');
        }

        $supplierModel    = model('SupplierModel');
        $data['rbeli']    = $rbeli;
        $data['detail']   = $this->rbeliDModel->getByIdRbeli((int) $id);
        $data['supplier'] = $supplierModel->getAktif();

        return view('aktivitas/aktivitas1/edit', $data);
    }

    public function update()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $id = (int) $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $noRbeli = trim($this->request->getPost('no_rbeli'));
        $tgl     = trim($this->request->getPost('tgl'));
        $idSupp  = (int) $this->request->getPost('id_supp');
        $kete    = trim($this->request->getPost('kete'));

        // Validasi header
        if ($noRbeli === '' || $tgl === '' || $idSupp <= 0) {
            return redirect()->back()->withInput()
                ->with('error', 'No. Rencana Beli, Tanggal, dan Supplier wajib diisi.');
        }

        if ($this->rbeliModel->cekNoRbeli($noRbeli, $id)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor Rencana Beli sudah digunakan.');
        }

        // Ambil data detail baru
        $noFakturArr = $this->request->getPost('no_faktur');
        $nilaiArr    = $this->request->getPost('nilai');

        if (empty($noFakturArr)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail wajib diisi.');
        }

        // Update header
        $this->rbeliModel->update($id, [
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => $idSupp,
            'kete'     => $kete ?: null,
        ]);

        // Hapus detail lama lalu insert ulang
        $this->rbeliDModel->hapusByIdRbeli($id);

        foreach ($noFakturArr as $i => $noFaktur) {
            $noFaktur = trim($noFaktur);
            $nilai    = (float) str_replace(',', '', $nilaiArr[$i] ?? 0);

            if ($noFaktur === '' || $nilai <= 0) {
                continue;
            }

            $this->rbeliDModel->insert([
                'id_rbeli'  => $id,
                'no_faktur' => $noFaktur,
                'nilai'     => $nilai,
            ]);
        }

        return redirect()->to('/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS
    // ═══════════════════════════════════════════

    public function hapus($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->find((int) $id);

        if (!$rbeli) {
            return redirect()->to('/aktivitas/aktivitas1')
                ->with('error', 'Data Rencana Beli tidak ditemukan.');
        }

        // Hapus detail terlebih dahulu (foreign key)
        $this->rbeliDModel->hapusByIdRbeli((int) $id);

        // Hapus header
        $this->rbeliModel->delete((int) $id);

        return redirect()->to('/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil dihapus.');
    }
}
