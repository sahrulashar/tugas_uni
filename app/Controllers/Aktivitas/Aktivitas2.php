<?php

namespace App\Controllers\Aktivitas;

use App\Controllers\BaseController;
use App\Models\TbBkkModel;
use App\Models\TbBkkDModel;
use App\Libraries\AuditLogger;
use App\Services\JurnalService;
use CodeIgniter\Database\BaseConnection;

class Aktivitas2 extends BaseController
{
    protected TbBkkModel     $bkkModel;
    protected TbBkkDModel    $bkkDModel;
    protected JurnalService  $jurnalService;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->bkkModel      = new TbBkkModel();
        $this->bkkDModel     = new TbBkkDModel();
        $this->jurnalService = new JurnalService();
        $this->db            = \Config\Database::connect();
    }

    // ═══════════════════════════════════════════
    //  INDEX — Daftar BKK
    // ═══════════════════════════════════════════

    public function index()
    {
        $this->cekAkses('ak2', 'daftar');

        $data['bkk'] = $this->bkkModel->getAll_l1H();

        return view('aktivitas/aktivitas2/index', $data);
    }

    // ═══════════════════════════════════════════
    //  TAMBAH
    // ═══════════════════════════════════════════

    public function tambah_l1H()
    {
        $this->cekAkses('ak2', 'tambah');

        $coaModel = model('CoaModel');

        $data['coa']     = $coaModel->getAll_l1H();
        $data['rbeli_d'] = $this->getRbeliDOptions();

        return view('aktivitas/aktivitas2/tambah', $data);
    }

    public function simpan_l1H()
    {
        $this->cekAkses('ak2', 'tambah');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/aktivitas/aktivitas2');
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

        $baris = $this->kumpulkanBaris($nilaiArr, $coaArr, $coaKbArr, $rbeliDArr);

        if (empty($baris)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail yang lengkap wajib diisi.');
        }

        // Interaksi dengan saldo COA: saldo kas/bank harus mencukupi
        $errSaldo = model('CoaModel')->cekSaldoKas_l1H($baris);
        if ($errSaldo) {
            return redirect()->back()->withInput()->with('error', $errSaldo);
        }

        // Simpan header
        $dataHeader = [
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $kete ?: null,
        ];

        $this->db->transStart();

        $idBkk = $this->bkkModel->insert($dataHeader);

        // Simpan detail
        foreach ($baris as $b) {
            $this->bkkDModel->insert($b + ['id_bkk' => $idBkk]);
        }

        // Generate dan simpan entri jurnal umum kas keluar otomatis (ACID)
        $this->jurnalService->generateDanSimpanJurnalBkk($dataHeader, $baris, $this->db);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan BKK. Tidak ada data yang tersimpan.');
        }
        AuditLogger::catat('TAMBAH', 'tbbkk', (int) $idBkk, ['after' => $dataHeader], 'Transaksi', "Tambah Bukti Kas Keluar: {$noBkk}");

        return redirect()->to('/46124026/aktivitas/aktivitas2')
            ->with('success', 'Bukti Kas Keluar berhasil disimpan.');
    }

    // ═══════════════════════════════════════════
    //  LIHAT DETAIL
    // ═══════════════════════════════════════════

    public function lihat_l1H($id = null)
    {
        $this->cekAkses('ak2', 'lihat');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas2');
        }

        $bkk = $this->bkkModel->getById_l1H((int) $id);

        if (!$bkk) {
            return redirect()->to('/46124026/aktivitas/aktivitas2')
                ->with('error', 'Data BKK tidak ditemukan.');
        }

        $data['bkk']    = $bkk;
        $data['detail'] = $this->bkkDModel->getByIdBkk_l1H((int) $id);
        $data['total']  = $this->bkkDModel->getTotalNilai_l1H((int) $id);
        $data['jurnal'] = $this->db->table('jurnal_umum')
            ->where('sumber', 'BKK')
            ->where('no_referensi', $bkk['no_bkk'])
            ->get()->getRowArray();

        return view('aktivitas/aktivitas2/lihat', $data);
    }

    // ═══════════════════════════════════════════
    //  EDIT
    // ═══════════════════════════════════════════

    public function edit_l1H($id = null)
    {
        $this->cekAkses('ak2', 'edit');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas2');
        }

        $bkk = $this->bkkModel->getById_l1H((int) $id);

        if (!$bkk) {
            return redirect()->to('/46124026/aktivitas/aktivitas2')
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
        $this->cekAkses('ak2', 'edit');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/aktivitas/aktivitas2');
        }

        $id = (int) $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas2');
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

        $dataBaru = [
            'no_bkk' => $noBkk,
            'tgl'    => $tgl,
            'kete'   => $kete ?: null,
        ];

        $baris = $this->kumpulkanBaris($nilaiArr, $coaArr, $coaKbArr, $rbeliDArr);

        if (empty($baris)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal satu baris detail yang lengkap wajib diisi.');
        }

        // Interaksi dengan saldo COA: saldo kas/bank harus mencukupi (BKK ini dikecualikan)
        $errSaldo = model('CoaModel')->cekSaldoKas_l1H($baris, $id);
        if ($errSaldo) {
            return redirect()->back()->withInput()->with('error', $errSaldo);
        }

        $dataLama = $this->bkkModel->find($id);

        $this->db->transStart();

        // Update header
        $this->bkkModel->update($id, $dataBaru);

        // Hapus detail lama lalu insert ulang
        $this->bkkDModel->hapusByIdBkk_l1H($id);

        foreach ($baris as $b) {
            $this->bkkDModel->insert($b + ['id_bkk' => $id]);
        }

        // Batalkan jurnal lama (menggunakan nomor BKK lama jika ada pergantian nomor)
        if (!empty($dataLama['no_bkk'])) {
            $this->jurnalService->batalkanJurnalByReferensi('BKK', $dataLama['no_bkk'], $this->db);
        }

        // Buat ulang jurnal kas keluar yang telah disesuaikan
        $this->jurnalService->generateDanSimpanJurnalBkk($dataBaru, $baris, $this->db);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui BKK. Perubahan dibatalkan.');
        }
        AuditLogger::catat('EDIT', 'tbbkk', $id, [
            'before' => $dataLama,
            'after'  => $dataBaru,
        ], 'Transaksi', "Edit Bukti Kas Keluar: {$noBkk}");

        return redirect()->to('/46124026/aktivitas/aktivitas2')
            ->with('success', 'Bukti Kas Keluar berhasil diperbarui dan jurnal umum disesuaikan.');
    }

    // ═══════════════════════════════════════════
    //  HAPUS & RESTORE
    // ═══════════════════════════════════════════

    public function hapus_l1H($id = null)
    {
        $this->cekAkses('ak2', 'hapus');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas2');
        }

        $bkk = $this->bkkModel->find((int) $id);

        if (!$bkk) {
            return redirect()->to('/46124026/aktivitas/aktivitas2')
                ->with('error', 'Data BKK tidak ditemukan.');
        }

        $this->db->transStart();

        // Soft delete: tandai is_deleted = 1, data TIDAK dihapus dari DB
        $sukses = $this->bkkModel->softDelete_l1H((int) $id);
        if ($sukses) {
            // Batalkan entri jurnal kas keluar agar tidak menjadi jurnal hantu di Neraca Saldo
            $this->jurnalService->batalkanJurnalByReferensi('BKK', $bkk['no_bkk'], $this->db);
            AuditLogger::catat('SOFT_DELETE', 'tbbkk', (int) $id, ['before' => $bkk], 'Transaksi', "Soft Delete Bukti Kas Keluar: {$bkk['no_bkk']}");
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->to('/46124026/aktivitas/aktivitas2')
                ->with('error', 'Gagal menghapus Bukti Kas Keluar.');
        }

        return redirect()->to('/46124026/aktivitas/aktivitas2')
            ->with('success', 'Bukti Kas Keluar berhasil dihapus dan jurnal umum dibatalkan.');
    }

    public function restore_l1H($id = null)
    {
        $this->cekAkses('ak2', 'edit');

        if (!$id) {
            return redirect()->to('/46124026/aktivitas/aktivitas2');
        }

        $bkk = $this->bkkModel->find((int) $id);

        if (!$bkk || (int) ($bkk['is_deleted'] ?? 0) === 0) {
            return redirect()->to('/46124026/aktivitas/aktivitas2')
                ->with('error', 'Data BKK tidak ditemukan atau tidak dalam status terhapus.');
        }

        $this->db->transStart();

        $this->bkkModel->update((int) $id, ['is_deleted' => 0]);
        $detail = $this->bkkDModel->getByIdBkk_l1H((int) $id);

        if (!empty($detail)) {
            $this->jurnalService->generateDanSimpanJurnalBkk($bkk, $detail, $this->db);
        }

        AuditLogger::catat('RESTORE', 'tbbkk', (int) $id, ['after' => $bkk], 'Transaksi', "Restore Bukti Kas Keluar: {$bkk['no_bkk']}");

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->to('/46124026/aktivitas/aktivitas2')
                ->with('error', 'Gagal memulihkan Bukti Kas Keluar.');
        }

        return redirect()->to('/46124026/aktivitas/aktivitas2')
            ->with('success', 'Bukti Kas Keluar berhasil dipulihkan dan jurnal umum dicatat kembali.');
    }

    // ═══════════════════════════════════════════
    //  HELPER PRIVATE
    // ═══════════════════════════════════════════

    /**
     * Susun baris detail yang valid dari input form (baris tidak lengkap dilewati).
     *
     * @return array [['id_rbeli_d'=>?int,'nilai'=>float,'id_coa'=>int,'id_coa_kb'=>int], ...]
     */
    private function kumpulkanBaris(?array $nilaiArr, ?array $coaArr, ?array $coaKbArr, ?array $rbeliDArr): array
    {
        $baris = [];

        foreach (($nilaiArr ?? []) as $i => $nilaiRaw) {
            $nilai   = (float) str_replace(',', '', $nilaiRaw ?? 0);
            $idCoa   = (int) ($coaArr[$i]   ?? 0);
            $idCoaKb = (int) ($coaKbArr[$i] ?? 0);

            if ($nilai <= 0 || $idCoa <= 0 || $idCoaKb <= 0) {
                continue; // skip baris tidak lengkap
            }

            $baris[] = [
                'id_rbeli_d' => (int) ($rbeliDArr[$i] ?? 0) ?: null,
                'nilai'      => $nilai,
                'id_coa'     => $idCoa,
                'id_coa_kb'  => $idCoaKb,
            ];
        }

        return $baris;
    }
    /**
     * Ambil opsi dropdown Rencana Beli Detail (id, no_faktur, no_rbeli, nama_supplier)
     */
    private function getRbeliDOptions(): array
    {
        return $this->db->table('tbrbeli_d rd')
            ->select('rd.id, rd.no_faktur, rd.nilai, r.no_rbeli, s.nama_supplier')
            ->join('tbrbeli r',   'r.id = rd.id_rbeli', 'left')
            ->join('supplier s',  's.id_supplier = r.id_supp', 'left')
            ->where('r.is_deleted', 0)
            ->orderBy('r.no_rbeli', 'ASC')
            ->get()
            ->getResultArray();
    }
}
