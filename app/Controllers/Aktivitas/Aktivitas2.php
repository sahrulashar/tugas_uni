<?php

namespace App\Controllers\Aktivitas;

use App\Controllers\BaseController;
use App\Models\TbBkkModel;
use App\Models\TbBkkDModel;
use CodeIgniter\Database\BaseConnection;

class Aktivitas2 extends BaseController
{
    protected TbBkkModel     $bkkModel;
    protected TbBkkDModel    $bkkDModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->bkkModel  = new TbBkkModel();
        $this->bkkDModel = new TbBkkDModel();
        $this->db        = \Config\Database::connect();
    }

    // ═══════════════════════════════════════════
    //  INDEX — Daftar BKK
    // ═══════════════════════════════════════════

    public function index()
    {
        $data['bkk'] = $this->bkkModel->getAll_l1H();

        return view('aktivitas/aktivitas2/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah_l1H()
    {
        $coaModel = model('CoaModel');

        $data['coa']     = $coaModel->getAll_l1H();
        $data['rbeli_d'] = $this->getRbeliDOptions();

        return view('aktivitas/aktivitas2/tambah', $data);
    }

    public function simpan_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/aktivitas/aktivitas2');
        }

        $noBkk = trim($this->request->getPost('no_bkk'));
        $tgl   = trim($this->request->getPost('tgl'));
        $kete  = trim($this->request->getPost('kete'));

        // Validasi header
        if ($noBkk === '' || $tgl === '') {
            return redirect()->back()->withInput()
                ->with('error', 'No. BKK dan Tanggal wajib diisi.');
        }

        if ($this->bkkModel->cekNoBkk_l1H($noBkk)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor BKK sudah digunakan.');
        }

        // Ambil data detail (array dari form)
        $rbeliDArr  = $this->request->getPost('id_rbeli_d');
        $nilaiArr   = $this->request->getPost('nilai');
        $coaArr     = $this->request->getPost('id_coa');
        $coaKbArr   = $this->request->getPost('id_coa_kb');

        if (empty($nilaiArr)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail wajib diisi.');
        }

        // Simpan header
        $idBkk = $this->bkkModel->insert([
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $kete ?: null,
        ]);

        // Simpan detail
        foreach ($nilaiArr as $i => $nilaiRaw) {
            $nilai    = (float) str_replace(',', '', $nilaiRaw ?? 0);
            $idCoa    = (int) ($coaArr[$i]   ?? 0);
            $idCoaKb  = (int) ($coaKbArr[$i] ?? 0);

            if ($nilai <= 0 || $idCoa <= 0 || $idCoaKb <= 0) {
                continue; // skip baris tidak lengkap
            }

            $idRbeliD = (int) ($rbeliDArr[$i] ?? 0) ?: null;

            $this->bkkDModel->insert([
                'id_bkk'     => $idBkk,
                'id_rbeli_d' => $idRbeliD,
                'nilai'      => $nilai,
                'id_coa'     => $idCoa,
                'id_coa_kb'  => $idCoaKb,
            ]);
        }

        return redirect()->to('/aktivitas/aktivitas2')
            ->with('success', 'Bukti Kas Keluar berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas2');
        }

        $bkk = $this->bkkModel->getById_l1H((int) $id);

        if (!$bkk) {
            return redirect()->to('/aktivitas/aktivitas2')
                ->with('error', 'Data BKK tidak ditemukan.');
        }

        $data['bkk']    = $bkk;
        $data['detail'] = $this->bkkDModel->getByIdBkk_l1H((int) $id);
        $data['total']  = $this->bkkDModel->getTotalNilai_l1H((int) $id);

        return view('aktivitas/aktivitas2/lihat', $data);
    }

    // ═══════════════════════════════════════════
    //  EDIT
    // ═══════════════════════════════════════════

    public function edit_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas2');
        }

        $bkk = $this->bkkModel->getById_l1H((int) $id);

        if (!$bkk) {
            return redirect()->to('/aktivitas/aktivitas2')
                ->with('error', 'Data BKK tidak ditemukan.');
        }

        $coaModel = model('CoaModel');

        $data['bkk']     = $bkk;
        $data['detail']  = $this->bkkDModel->getByIdBkk_l1H((int) $id);
        $data['coa']     = $coaModel->getAll_l1H();
        $data['rbeli_d'] = $this->getRbeliDOptions();

        return view('aktivitas/aktivitas2/edit', $data);
    }

    public function update_l1H()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/aktivitas/aktivitas2');
        }

        $id = (int) $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas2');
        }

        $noBkk = trim($this->request->getPost('no_bkk'));
        $tgl   = trim($this->request->getPost('tgl'));
        $kete  = trim($this->request->getPost('kete'));

        // Validasi header
        if ($noBkk === '' || $tgl === '') {
            return redirect()->back()->withInput()
                ->with('error', 'No. BKK dan Tanggal wajib diisi.');
        }

        if ($this->bkkModel->cekNoBkk_l1H($noBkk, $id)) {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor BKK sudah digunakan.');
        }

        // Ambil data detail baru
        $rbeliDArr = $this->request->getPost('id_rbeli_d');
        $nilaiArr  = $this->request->getPost('nilai');
        $coaArr    = $this->request->getPost('id_coa');
        $coaKbArr  = $this->request->getPost('id_coa_kb');

        if (empty($nilaiArr)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail wajib diisi.');
        }

        // Update header
        $this->bkkModel->update($id, [
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $kete ?: null,
        ]);

        // Hapus detail lama lalu insert ulang
        $this->bkkDModel->hapusByIdBkk_l1H($id);

        foreach ($nilaiArr as $i => $nilaiRaw) {
            $nilai   = (float) str_replace(',', '', $nilaiRaw ?? 0);
            $idCoa   = (int) ($coaArr[$i]   ?? 0);
            $idCoaKb = (int) ($coaKbArr[$i] ?? 0);

            if ($nilai <= 0 || $idCoa <= 0 || $idCoaKb <= 0) {
                continue;
            }

            $idRbeliD = (int) ($rbeliDArr[$i] ?? 0) ?: null;

            $this->bkkDModel->insert([
                'id_bkk'     => $id,
                'id_rbeli_d' => $idRbeliD,
                'nilai'      => $nilai,
                'id_coa'     => $idCoa,
                'id_coa_kb'  => $idCoaKb,
            ]);
        }

        return redirect()->to('/aktivitas/aktivitas2')
            ->with('success', 'Bukti Kas Keluar berhasil diperbarui.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS
    // ═══════════════════════════════════════════

    public function hapus_l1H($id = null)
    {
        if (!$id) {
            return redirect()->to('/aktivitas/aktivitas2');
        }

        $bkk = $this->bkkModel->find((int) $id);

        if (!$bkk) {
            return redirect()->to('/aktivitas/aktivitas2')
                ->with('error', 'Data BKK tidak ditemukan.');
        }

        // Hapus detail terlebih dahulu (foreign key)
        $this->bkkDModel->hapusByIdBkk_l1H((int) $id);

        // Hapus header
        $this->bkkModel->delete((int) $id);

        return redirect()->to('/aktivitas/aktivitas2')
            ->with('success', 'Bukti Kas Keluar berhasil dihapus.');
    }

    // ═══════════════════════════════════════════
    //  HELPER PRIVATE
    // ═══════════════════════════════════════════

    /**
     * Ambil opsi dropdown Rencana Beli Detail (id, no_faktur, no_rbeli, nama_supplier)
     */
    private function getRbeliDOptions(): array
    {
        return $this->db->table('tbrbeli_d rd')
            ->select('rd.id, rd.no_faktur, rd.nilai, r.no_rbeli, s.nama_supplier')
            ->join('tbrbeli r',   'r.id = rd.id_rbeli', 'left')
            ->join('supplier s',  's.id_supplier = r.id_supp', 'left')
            ->orderBy('r.no_rbeli', 'ASC')
            ->get()
            ->getResultArray();
    }
}
