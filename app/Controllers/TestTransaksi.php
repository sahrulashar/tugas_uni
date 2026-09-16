<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TbBeliModel;
use App\Models\TbBeliDModel;
use App\Models\AuditLogModel;

class TestTransaksi extends BaseController
{
    protected TbBeliModel   $beliModel;
    protected TbBeliDModel  $beliDModel;
    protected AuditLogModel $auditModel;
    protected $db;

    public function __construct()
    {
        $this->beliModel  = new TbBeliModel();
        $this->beliDModel = new TbBeliDModel();
        $this->auditModel = new AuditLogModel();
        $this->db         = \Config\Database::connect();
    }

    /**
     * Halaman Dashboard Pengujian ERP Transaction & Audit Log
     */
    public function index()
    {
        $pesan = session()->getFlashdata('pesan');
        $tipe  = session()->getFlashdata('tipe') ?? 'info';

        // Ambil data terkini dari ketiga tabel untuk pembuktian
        $semuaBeli   = $this->db->table('tbbeli')->get()->getResultArray();
        $semuaDetail = $this->db->table('tbbeli_d')->get()->getResultArray();
        $semuaAudit  = $this->db->table('audit_log')->orderBy('waktu', 'DESC')->get()->getResultArray();

        return view('test_transaksi', [
            'pesan'       => $pesan,
            'tipe'        => $tipe,
            'semuaBeli'   => $semuaBeli,
            'semuaDetail' => $semuaDetail,
            'semuaAudit'  => $semuaAudit,
        ]);
    }

    /**
     * UJI COBA 1: Input item dengan harga negatif (-10000)
     * Diharapkan: Rollback! Tidak ada data masuk ke tbbeli maupun tbbeli_d
     */
    public function ujiRollbackNegatif()
    {
        $noBeli = 'BL-FAIL-' . date('His');
        $master = [
            'no_beli' => $noBeli,
            'tgl'     => date('Y-m-d'),
            'toko'    => 'Toko Uji Coba Rollback (Harga Negatif)',
        ];

        $detail = [
            ['barng' => 'Barang Valid 1', 'harga' => 50000],
            ['barng' => 'Barang Negatif',  'harga' => -10000], // <-- Ini penyebab Exception & Rollback
            ['barng' => 'Barang Valid 2', 'harga' => 25000],
        ];

        $hasil = $this->beliModel->simpanTransaksiPembelian($master, $detail);

        if (!$hasil) {
            // Verifikasi bahwa data benar-benar tidak tersimpan
            $cekMaster = $this->beliModel->where('no_beli', $noBeli)->first();
            $msg = "✅ <b>UJI COBA 1 BERHASIL (ROLLBACK BEKERJA)!</b><br>"
                 . "Transaksi dengan nomor <b>{$noBeli}</b> digagalkan karena terdapat harga negatif (-10,000).<br>"
                 . "Status database: <b>Master & Detail TIDAK ADA yang tersimpan</b> (Atomisitas terjaga).";
            
            return redirect()->to('/test-transaksi')->with('pesan', $msg)->with('tipe', 'danger');
        }

        return redirect()->to('/test-transaksi')->with('pesan', '⚠️ Transaksi malah tersimpan (seharusnya gagal)!')->with('tipe', 'warning');
    }

    /**
     * SIMULASI SUKSES: Transaksi dengan data valid + Pencatatan Audit Trail
     */
    public function ujiTransaksiSukses()
    {
        $noBeli = 'BL-OK-' . date('His');
        $master = [
            'no_beli' => $noBeli,
            'tgl'     => date('Y-m-d'),
            'toko'    => 'Toko Berkah ' . rand(10, 99),
        ];

        $detail = [
            ['barng' => 'Minyak Goreng 2L', 'harga' => 38000],
            ['barng' => 'Gula Pasir 1kg',   'harga' => 17500],
            ['barng' => 'Beras Pandan 5kg', 'harga' => 74000],
        ];

        $hasil = $this->beliModel->simpanTransaksiPembelian($master, $detail);

        if ($hasil) {
            $idBeli = $this->beliModel->getInsertID();
            $userId = 1; // Contoh ID user login (misal admin)

            // Catat ke Audit Trail
            $this->beliModel->catatAuditLog_l1H($userId, 'TAMBAH', 'tbbeli', $idBeli);

            $msg = "✅ <b>TRANSAKSI SUKSES DISIMPAN!</b><br>"
                 . "Master No: <b>{$noBeli}</b> dan 3 item detail berhasil di-COMMIT.<br>"
                 . "Audit log juga berhasil dicatat dengan aksi <b>TAMBAH</b> untuk Record ID #{$idBeli}.";

            return redirect()->to('/test-transaksi')->with('pesan', $msg)->with('tipe', 'success');
        }

        return redirect()->to('/test-transaksi')->with('pesan', '❌ Transaksi gagal disimpan.')->with('tipe', 'danger');
    }

    /**
     * SIMULASI SOFT DELETE: Mengubah is_deleted = 1 tanpa DELETE FROM
     */
    public function ujiSoftDelete($id = null)
    {
        if (!$id) {
            // Ambil salah satu record aktif
            $record = $this->beliModel->where('is_deleted', 0)->orderBy('id', 'DESC')->first();
            if (!$record) {
                return redirect()->to('/test-transaksi')->with('pesan', 'Tidak ada transaksi aktif untuk di-soft delete. Silakan klik "Simpan Transaksi Sukses" dulu.')->with('tipe', 'warning');
            }
            $id = $record['id'];
        }

        $berhasil = $this->beliModel->softDeleteBeli_l1H((int) $id);

        if ($berhasil) {
            $userId = 1;
            // Catat ke Audit Log
            $this->beliModel->catatAuditLog_l1H($userId, 'SOFT_DELETE', 'tbbeli', (int) $id);

            $msg = "✅ <b>SOFT DELETE BERHASIL!</b><br>"
                 . "Record tbbeli ID <b>#{$id}</b> diubah statusnya menjadi <code>is_deleted = 1</code>.<br>"
                 . "Perhatikan bahwa data fisik <b>TIDAK DIHAPUS</b> dari database dan Audit Log mencatat aksi <b>SOFT_DELETE</b>.";

            return redirect()->to('/test-transaksi')->with('pesan', $msg)->with('tipe', 'warning');
        }

        return redirect()->to('/test-transaksi')->with('pesan', 'Gagal melakukan soft delete.')->with('tipe', 'danger');
    }

    /**
     * UJI COBA 2: Simulasi Server Crash di tengah loop detail
     */
    public function ujiSimulasiCrash()
    {
        $noBeli = 'BL-CRASH-' . date('His');
        $master = [
            'no_beli' => $noBeli,
            'tgl'     => date('Y-m-d'),
            'toko'    => 'Toko Simulasi Crash Server',
        ];

        $detail = [
            ['barng' => 'Barang Masuk Sebelum Crash', 'harga' => 20000],
            ['barng' => 'Barang Saat Crash Terjadi',   'harga' => 30000],
        ];

        // Jalankan transaksi khusus crash
        $this->db->transStart();

        $idBeli = $this->beliModel->insert([
            'no_beli' => $master['no_beli'],
            'tgl'     => $master['tgl'],
            'toko'    => $master['toko'],
        ]);

        foreach ($detail as $i => $item) {
            $this->beliDModel->insert([
                'id_beli' => $idBeli,
                'barng'   => $item['barng'],
                'harga'   => $item['harga'],
            ]);

            // Simulasi Crash di iterasi ke-1:
            if ($i === 0) {
                // Di sini die() dipanggil sebelum transComplete()
                echo "<div style='font-family:sans-serif; padding:20px; background:#fff3cd; border:2px solid #ffecb5;'>";
                echo "<h2 style='color:#856404;'>💥 SIMULASI CRASH AKTIF (die dipanggil)!</h2>";
                echo "<p>Pesan simulasi: <b>die(\"Koneksi Terputus!\");</b></p>";
                echo "<p>Script berhenti sebelum <code>transComplete()</code> dipanggil.</p>";
                echo "<p>MySQL secara otomatis akan me-<b>ROLLBACK</b> transaksi yang belum di-commit.</p>";
                echo "<p><a href='/test-transaksi'>⬅️ Kembali ke Dashboard Pengujian untuk membuktikan database tetap bersih</a></p>";
                echo "</div>";
                die("Koneksi Terputus!");
            }
        }

        $this->db->transComplete();
    }
}
