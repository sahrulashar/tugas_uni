<?php

namespace App\Controllers\Aktivitas;

use App\Controllers\BaseController;
use App\Models\TbRbeliModel;
use App\Models\TbRbeliDModel;
use App\Libraries\AuditLogger;

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
        $data['rbeli'] = $this->rbeliModel->getAll_l1H();

        return view('aktivitas/aktivitas1/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah_l1H()
    {
        $supplierModel      = model('SupplierModel');
        $data['supplier']   = $supplierModel->getAktif_l1H();

        return view('aktivitas/aktivitas1/tambah', $data);
    }

    public function simpan_l1H()
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

        if ($this->rbeliModel->cekNoRbeli_l1H($noRbeli)) {
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
        $dataHeader = [
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => $idSupp,
            'kete'     => $kete ?: null,
        ];

        $idRbeli = $this->rbeliModel->insert($dataHeader);

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

        AuditLogger::catat('TAMBAH', 'tbrbeli', (int) $idRbeli, ['after' => $dataHeader]);

        return redirect()->to('/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->getById_l1H((int) $id);

        if (!$rbeli) {
            return redirect()->to('/aktivitas/aktivitas1')
                ->with('error', 'Data Rencana Beli tidak ditemukan.');
        }

        $data['rbeli']  = $rbeli;
        $data['detail'] = $this->rbeliDModel->getByIdRbeli_l1H((int) $id);
        $data['total']  = $this->rbeliDModel->getTotalNilai_l1H((int) $id);

        return view('aktivitas/aktivitas1/lihat', $data);
    }

    // ═══════════════════════════════════════════
    //  EDIT
    // ═══════════════════════════════════════════

    public function edit_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->getById_l1H((int) $id);

        if (!$rbeli) {
            return redirect()->to('/aktivitas/aktivitas1')
                ->with('error', 'Data Rencana Beli tidak ditemukan.');
        }

        $supplierModel    = model('SupplierModel');
        $data['rbeli']    = $rbeli;
        $data['detail']   = $this->rbeliDModel->getByIdRbeli_l1H((int) $id);
        $data['supplier'] = $supplierModel->getAktif_l1H();

        return view('aktivitas/aktivitas1/edit', $data);
    }

    public function update_l1H()
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

        if ($this->rbeliModel->cekNoRbeli_l1H($noRbeli, $id)) {
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

        $dataBaru = [
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => $idSupp,
            'kete'     => $kete ?: null,
        ];

        // Update header
        $dataLama = $this->rbeliModel->find($id);
        $this->rbeliModel->update($id, $dataBaru);

        // Hapus detail lama lalu insert ulang
        $this->rbeliDModel->hapusByIdRbeli_l1H($id);

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

        AuditLogger::catat('EDIT', 'tbrbeli', $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ]);

        return redirect()->to('/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS
    // ═══════════════════════════════════════════

    public function hapus_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->find((int) $id);

        if (!$rbeli) {
            return redirect()->to('/aktivitas/aktivitas1')
                ->with('error', 'Data Rencana Beli tidak ditemukan.');
        }

        AuditLogger::catat('SOFT_DELETE', 'tbrbeli', (int) $id, ['before' => $rbeli]);

        // Soft delete: tandai is_deleted = 1, data TIDAK dihapus dari DB
        $this->rbeliModel->softDelete_l1H((int) $id);

        return redirect()->to('/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil dihapus.');
    }
}
