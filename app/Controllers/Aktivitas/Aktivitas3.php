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
        $this->cekAkses('ak3', 'daftar');

        $data['rekap'] = $this->recordModel->getAll_l1H();

        return view('aktivitas/aktivitas3/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah_l1H()
    {
        $this->cekAkses('ak3', 'tambah');

        $bkkModel = model('TbBkkModel');
        $data['bkk'] = $bkkModel->getAll_l1H();

        return view('aktivitas/aktivitas3/tambah', $data);
    }

    public function simpan_l1H()
    {
        $this->cekAkses('ak3', 'tambah');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/aktivitas/aktivitas3');
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

        // Pastikan BKK valid dan belum di-soft delete
        $bkk = model('TbBkkModel')->getById_l1H($idBkk);
        if (!$bkk) {
            return redirect()->back()->withInput()
                ->with('error', 'Bukti Kas Keluar tidak valid atau telah dinonaktifkan.');
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
            log_message('error', '[Aktivitas3::simpan_l1H] ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data rekap BKK.');
        }

        AuditLogger::catat('TAMBAH', 'tbrecord', (int) $idRec, ['after' => $dataRekap], 'Transaksi', "Tambah Rekap BKK: {$noRec}");

        return redirect()->to('/46124026/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat_l1H($id = null)
    {
        $this->cekAkses('ak3', 'lihat');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->getById_l1H((int) $id);

        if (!$rekap) {
            return redirect()->to('/46124026/aktivitas/aktivitas3')
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
        $this->cekAkses('ak3', 'edit');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->getById_l1H((int) $id);

        if (!$rekap) {
            return redirect()->to('/46124026/aktivitas/aktivitas3')
                ->with('error', 'Data Rekap tidak ditemukan.');
        }

        $bkkModel = model('TbBkkModel');
        $data['rekap'] = $rekap;
        $data['bkk']   = $bkkModel->getAll_l1H();

        return view('aktivitas/aktivitas3/edit', $data);
    }

    public function update_l1H()
    {
        $this->cekAkses('ak3', 'edit');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/aktivitas/aktivitas3');
        }

        $id    = (int) $this->request->getPost('id');
        $noRec = trim($this->request->getPost('no_rec'));
        $tgl   = trim($this->request->getPost('tgl'));
        $idBkk = (int) $this->request->getPost('id_bkk');
        $ket   = trim($this->request->getPost('ket'));

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas3');
        }

        // Validasi wajib
        if ($noRec === '' || $tgl === '' || $idBkk <= 0) {
            return redirect()->back()->withInput()
                ->with('error', 'No. Rekap, Tanggal, dan BKK wajib diisi.');
        }

        // Pastikan BKK valid dan belum di-soft delete
        $bkk = model('TbBkkModel')->getById_l1H($idBkk);
        if (!$bkk) {
            return redirect()->back()->withInput()
                ->with('error', 'Bukti Kas Keluar tidak valid atau telah dinonaktifkan.');
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
            log_message('error', '[Aktivitas3::update_l1H] ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data rekap BKK.');
        }

        AuditLogger::catat('EDIT', 'tbrecord', $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ], 'Transaksi', "Edit Rekap BKK: {$noRec}");

        return redirect()->to('/46124026/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS
    // ═══════════════════════════════════════════

    public function hapus_l1H($id = null)
    {
        $this->cekAkses('ak3', 'hapus');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas3');
        }

        $rekap = $this->recordModel->find((int) $id);

        if (!$rekap) {
            return redirect()->to('/46124026/aktivitas/aktivitas3')
                ->with('error', 'Data Rekap tidak ditemukan.');
        }

        try {
            $sukses = $this->recordModel->softDelete_l1H((int) $id);
            if ($sukses) {
                AuditLogger::catat('SOFT_DELETE', 'tbrecord', (int) $id, ['before' => $rekap], 'Transaksi', "Soft Delete Rekap BKK: {$rekap['no_rec']}");
            }
        } catch (\Throwable $e) {
            log_message('error', '[Aktivitas3::hapus_l1H] ' . $e->getMessage());
            return redirect()->to('/46124026/aktivitas/aktivitas3')
                ->with('error', 'Gagal menghapus data rekap BKK.');
        }

        return redirect()->to('/46124026/aktivitas/aktivitas3')
            ->with('success', 'Rekap BKK berhasil dihapus.');
    }
}
