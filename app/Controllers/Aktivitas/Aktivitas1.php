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
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->rbeliModel  = new TbRbeliModel();
        $this->rbeliDModel = new TbRbeliDModel();
        $this->db          = \Config\Database::connect();
    }

    // ═══════════════════════════════════════════
    //  INDEX — Daftar Rencana Beli
    // ═══════════════════════════════════════════

    public function index()
    {
        $this->cekAkses('ak1', 'daftar'); // ← RBAC gate

        $data['rbeli'] = $this->rbeliModel->getAll_l1H();

        return view('aktivitas/aktivitas1/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah_l1H()
    {
        $this->cekAkses('ak1', 'tambah'); // ← RBAC gate

        $supplierModel      = model('SupplierModel');
        $data['supplier']   = $supplierModel->getAktif_l1H();

        return view('aktivitas/aktivitas1/tambah', $data);
    }

    public function simpan_l1H()
    {
        $this->cekAkses('ak1', 'tambah'); // ← RBAC gate

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/aktivitas/aktivitas1');
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

        // Simpan header & detail dalam transaksi atomik
        $dataHeader = [
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => $idSupp,
            'kete'     => $kete ?: null,
        ];

        $this->db->transStart();

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

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan Rencana Beli. Transaksi dibatalkan.');
        }

        AuditLogger::catat('TAMBAH', 'tbrbeli', (int) $idRbeli, ['after' => $dataHeader], 'Transaksi', "Tambah Rencana Beli: {$noRbeli}");

        return redirect()->to('/46124026/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat_l1H($id = null)
    {
        $this->cekAkses('ak1', 'lihat');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->getById_l1H((int) $id);

        if (!$rbeli) {
            return redirect()->to('/46124026/aktivitas/aktivitas1')
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
        $this->cekAkses('ak1', 'edit');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->getById_l1H((int) $id);

        if (!$rbeli) {
            return redirect()->to('/46124026/aktivitas/aktivitas1')
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
        $this->cekAkses('ak1', 'edit');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/aktivitas/aktivitas1');
        }

        $id = (int) $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas1');
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
        $idDetailArr = $this->request->getPost('id_detail') ?? [];
        $noFakturArr = $this->request->getPost('no_faktur') ?? [];
        $nilaiArr    = $this->request->getPost('nilai') ?? [];

        if (empty($noFakturArr)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail wajib diisi.');
        }

        // Ambil detail eksisting untuk proteksi terhadap CASCADE deletion ke tbbkk_d
        $existingRows = $this->rbeliDModel->where('id_rbeli', $id)->findAll();
        $existingMap  = [];
        foreach ($existingRows as $er) {
            $existingMap[(int) $er['id']] = $er;
        }

        // Kumpulkan baris yang valid
        $validItems   = [];
        $submittedIds = [];

        foreach ($noFakturArr as $i => $noFaktur) {
            $noFaktur = trim($noFaktur);
            $nilai    = (float) str_replace(',', '', $nilaiArr[$i] ?? 0);
            $detId    = (int) ($idDetailArr[$i] ?? 0);

            if ($noFaktur === '' || $nilai <= 0) {
                continue;
            }

            $validItems[] = [
                'id'        => $detId > 0 && isset($existingMap[$detId]) ? $detId : null,
                'no_faktur' => $noFaktur,
                'nilai'     => $nilai,
            ];

            if ($detId > 0 && isset($existingMap[$detId])) {
                $submittedIds[] = $detId;
            }
        }

        if (empty($validItems)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail dengan faktur dan nilai yang valid wajib diisi.');
        }

        // Cek baris yang dihapus oleh user: apakah sudah dipakai di Bukti Kas Keluar (tbbkk_d)?
        foreach ($existingMap as $oldId => $oldRow) {
            if (!in_array($oldId, $submittedIds, true)) {
                $usedCount = $this->db->table('tbbkk_d')->where('id_rbeli_d', $oldId)->countAllResults();
                if ($usedCount > 0) {
                    return redirect()->back()->withInput()
                        ->with('error', "Faktur '{$oldRow['no_faktur']}' tidak dapat dihapus karena sudah digunakan dalam transaksi Bukti Kas Keluar (BKK).");
                }
            }
        }

        $dataBaru = [
            'no_rbeli' => $noRbeli,
            'tgl'      => $tgl,
            'id_supp'  => $idSupp,
            'kete'     => $kete ?: null,
        ];

        $dataLama = $this->rbeliModel->find($id);

        $this->db->transStart();

        // 1. Update header
        $this->rbeliModel->update($id, $dataBaru);

        // 2. Hapus hanya detail lama yang memang tidak disubmit dan TIDAK terkait BKK
        foreach ($existingMap as $oldId => $oldRow) {
            if (!in_array($oldId, $submittedIds, true)) {
                $this->rbeliDModel->delete($oldId);
            }
        }

        // 3. Update in-place baris lama, atau insert baris baru
        foreach ($validItems as $item) {
            if (!empty($item['id'])) {
                $this->rbeliDModel->update($item['id'], [
                    'no_faktur' => $item['no_faktur'],
                    'nilai'     => $item['nilai'],
                ]);
            } else {
                $this->rbeliDModel->insert([
                    'id_rbeli'  => $id,
                    'no_faktur' => $item['no_faktur'],
                    'nilai'     => $item['nilai'],
                ]);
            }
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui Rencana Beli. Perubahan dibatalkan.');
        }

        AuditLogger::catat('EDIT', 'tbrbeli', $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ], 'Transaksi', "Edit Rencana Beli: {$noRbeli}");

        return redirect()->to('/46124026/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS
    // ═══════════════════════════════════════════

    public function hapus_l1H($id = null)
    {
        $this->cekAkses('ak1', 'hapus');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas1');
        }

        $rbeli = $this->rbeliModel->find((int) $id);

        if (!$rbeli) {
            return redirect()->to('/46124026/aktivitas/aktivitas1')
                ->with('error', 'Data Rencana Beli tidak ditemukan.');
        }

        // Soft delete: tandai is_deleted = 1, data TIDAK dihapus dari DB
        $sukses = $this->rbeliModel->softDelete_l1H((int) $id);

        if ($sukses) {
            AuditLogger::catat('SOFT_DELETE', 'tbrbeli', (int) $id, ['before' => $rbeli], 'Transaksi', "Soft Delete Rencana Beli: {$rbeli['no_rbeli']}");
        }

        return redirect()->to('/46124026/aktivitas/aktivitas1')
            ->with('success', 'Rencana Beli berhasil dihapus.');
    }
}

