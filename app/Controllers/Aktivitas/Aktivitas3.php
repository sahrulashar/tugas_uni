<?php

namespace App\Controllers\Aktivitas;

use App\Controllers\BaseController;
use App\Models\TbRecordModel;
use App\Libraries\AuditLogger;

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
        $data['rekap'] = $this->recordModel->getAll_l1H();

        return view('aktivitas/aktivitas3/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah_l1H()
    {
        $bkkModel = model('TbBkkModel');
        $data['bkk'] = $bkkModel->getAll_l1H();

        return view('aktivitas/aktivitas3/tambah', $data);
    }

    public function simpan_l1H()
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
        if ($this->recordModel->cekNoRec_l1H($noRec)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor Rekap sudah digunakan.');
        }

        $dataRekap = [
            'no_rec' => $noRec,
            'tgl'    => $tgl,
            'id_bkk' => $idBkk,
            'ket'    => $ket ?: null,
        ];

        try {
            $idRec = $this->recordModel->insert($dataRekap);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }

        AuditLogger::catat('TAMBAH', 'tbrecord', (int) $idRec, ['after' => $dataRekap]);

        return redirect()->to('/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->getById_l1H((int) $id);

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

    public function edit_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->getById_l1H((int) $id);

        if (!$rekap) {
            return redirect()->to('/aktivitas/aktivitas3')
                ->with('error', 'Data Rekap tidak ditemukan.');
        }

        $bkkModel = model('TbBkkModel');
        $data['rekap'] = $rekap;
        $data['bkk']   = $bkkModel->getAll_l1H();

        return view('aktivitas/aktivitas3/edit', $data);
    }

    public function update_l1H()
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
        if ($this->recordModel->cekNoRec_l1H($noRec, $id)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor Rekap sudah digunakan.');
        }

        $dataBaru = [
            'no_rec' => $noRec,
            'tgl'    => $tgl,
            'id_bkk' => $idBkk,
            'ket'    => $ket ?: null,
        ];

        $dataLama = $this->recordModel->find($id);

        try {
            $this->recordModel->update($id, $dataBaru);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }

        AuditLogger::catat('EDIT', 'tbrecord', $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ]);

        return redirect()->to('/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS
    // ═══════════════════════════════════════════

    public function hapus_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->find((int) $id);

        if (!$rekap) {
            return redirect()->to('/aktivitas/aktivitas3')
                ->with('error', 'Data Rekap tidak ditemukan.');
        }

        AuditLogger::catat('SOFT_DELETE', 'tbrecord', (int) $id, ['before' => $rekap]);

        try {
            $this->recordModel->softDelete_l1H((int) $id);
        } catch (\Throwable $e) {
            return redirect()->to('/aktivitas/aktivitas3')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        return redirect()->to('/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil dihapus.');
    }
}
