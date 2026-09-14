<?php

namespace App\Controllers\Aktivitas;

use App\Controllers\BaseController;
use App\Models\TbRecordModel;

class Aktivitas3 extends BaseController
{
    protected TbRecordModel $recordModel;

    public function __construct()
    {
        $this->recordModel = new TbRecordModel();
    }

    // ═══════════════════════════════════════════
    //  INDEX — Daftar Rekap BKK
    // ═══════════════════════════════════════════

    public function index()
    {
        $data['rekap'] = $this->recordModel->getAll();

        return view('aktivitas/aktivitas3/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah()
    {
        $bkkModel = model('TbBkkModel');
        $data['bkk'] = $bkkModel->getAll();

        return view('aktivitas/aktivitas3/tambah', $data);
    }

    public function simpan()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $noRec = trim($this->request->getPost('no_rec'));
        $tgl   = trim($this->request->getPost('tgl'));
        $idBkk = (int) $this->request->getPost('id_bkk');
        $ket   = trim($this->request->getPost('ket'));

        // Validasi wajib
        if ($noRec === '' || $tgl === '' || $idBkk <= 0) {
            return redirect()->back()->withInput()
                ->with('error', 'No. Rekap, Tanggal, dan BKK wajib diisi.');
        }

        // Cek duplikat nomor rekap
        if ($this->recordModel->cekNoRec($noRec)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor Rekap sudah digunakan.');
        }

        try {
            $this->recordModel->insert([
                'no_rec' => $noRec,
                'tgl'    => $tgl,
                'id_bkk' => $idBkk,
                'ket'    => $ket ?: null,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }

        return redirect()->to('/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->getById((int) $id);

        if (!$rekap) {
            return redirect()->to('/aktivitas/aktivitas3')
                ->with('error', 'Data Rekap tidak ditemukan.');
        }

        $data['rekap'] = $rekap;

        return view('aktivitas/aktivitas3/lihat', $data);
    }

    // ═══════════════════════════════════════════
    //  EDIT
    // ═══════════════════════════════════════════

    public function edit($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->getById((int) $id);

        if (!$rekap) {
            return redirect()->to('/aktivitas/aktivitas3')
                ->with('error', 'Data Rekap tidak ditemukan.');
        }

        $bkkModel = model('TbBkkModel');
        $data['rekap'] = $rekap;
        $data['bkk']   = $bkkModel->getAll();

        return view('aktivitas/aktivitas3/edit', $data);
    }

    public function update()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $id    = (int) $this->request->getPost('id');
        $noRec = trim($this->request->getPost('no_rec'));
        $tgl   = trim($this->request->getPost('tgl'));
        $idBkk = (int) $this->request->getPost('id_bkk');
        $ket   = trim($this->request->getPost('ket'));

        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        // Validasi wajib
        if ($noRec === '' || $tgl === '' || $idBkk <= 0) {
            return redirect()->back()->withInput()
                ->with('error', 'No. Rekap, Tanggal, dan BKK wajib diisi.');
        }

        // Cek duplikat (kecuali milik sendiri)
        if ($this->recordModel->cekNoRec($noRec, $id)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor Rekap sudah digunakan.');
        }

        try {
            $this->recordModel->update($id, [
                'no_rec' => $noRec,
                'tgl'    => $tgl,
                'id_bkk' => $idBkk,
                'ket'    => $ket ?: null,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }

        return redirect()->to('/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS
    // ═══════════════════════════════════════════

    public function hapus($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->find((int) $id);

        if (!$rekap) {
            return redirect()->to('/aktivitas/aktivitas3')
                ->with('error', 'Data Rekap tidak ditemukan.');
        }

        try {
            $this->recordModel->delete((int) $id);
        } catch (\Throwable $e) {
            return redirect()->to('/aktivitas/aktivitas3')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        return redirect()->to('/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil dihapus.');
    }
}
