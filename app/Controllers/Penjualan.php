<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\PenjualanModel;
use App\Models\PenjualanDModel;
use App\Libraries\AuditLogger;

class Penjualan extends BaseController
{
    protected BarangModel    $barangModel;
    protected PenjualanModel $penjualanModel;
    protected PenjualanDModel $penjualanDModel;

    public function __construct()
    {
        $this->barangModel    = new BarangModel();
        $this->penjualanModel = new PenjualanModel();
        $this->penjualanDModel= new PenjualanDModel();
    }

    // ═══════════════════════════════════════════
    //  KASIR / POINT OF SALES (POS)
    // ═══════════════════════════════════════════

    /**
     * Halaman Kasir / Transaksi Penjualan Baru
     */
    public function kasir_l1H()
    {
        $this->cekAkses('pos', 'daftar');

        $barang = $this->barangModel->getAktif_l1H();
        $recent = $this->penjualanModel
            ->where('is_deleted', 0)
            ->orderBy('id', 'DESC')
            ->findAll(10);

        return view('penjualan/kasir', [
            'barang' => $barang,
            'recent' => $recent,
        ]);
    }

    /**
     * Proses Transaksi Penjualan dari Kasir
     */
    public function proses_kasir_l1H()
    {
        $this->cekAkses('pos', 'tambah');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/penjualan/kasir_l1H');
        }

        $userId = (int) (session()->get('user_id') ?: 1);

        // Ambil data items (bisa dari JSON string di input hidden atau post array)
        $itemsRaw = $this->request->getPost('items_json');
        if (!empty($itemsRaw)) {
            $items = json_decode($itemsRaw, true);
        } else {
            $items = $this->request->getPost('items') ?: [];
        }

        $customer    = trim($this->request->getPost('customer')) ?: 'Pelanggan Umum';
        $metodeBayar = trim($this->request->getPost('metode_bayar')) ?: 'Tunai';
        $diskon      = (float) str_replace(',', '', trim((string) $this->request->getPost('diskon')));
        $noFaktur    = 'FK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

        $header = [
            'no_faktur'    => $noFaktur,
            'tgl_jual'     => date('Y-m-d'),
            'customer'     => $customer,
            'diskon'       => $diskon,
            'metode_bayar' => $metodeBayar,
        ];

        $hasil = $this->penjualanModel->simpanPenjualanAtomik_l1H($header, $items, $userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($hasil);
        }

        if ($hasil['success']) {
            return redirect()->to('/46124026/penjualan/kasir_l1H')
                ->with('success', $hasil['message']);
        }

        return redirect()->to('/46124026/penjualan/kasir_l1H')
            ->with('error', $hasil['message']);
    }

    /**
     * Riwayat Transaksi Penjualan
     */
    public function riwayat_l1H()
    {
        $this->cekAkses('pos', 'daftar');

        $riwayat = $this->penjualanModel
            ->where('is_deleted', 0)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('penjualan/riwayat', [
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Detail Transaksi Penjualan
     */
    public function detail_l1H($id = null)
    {
        $this->cekAkses('pos', 'lihat');

        $penjualan = $this->penjualanModel->find($id);
        if (!$penjualan) {
            return redirect()->to('/46124026/penjualan/kasir_l1H')->with('error', 'Data penjualan tidak ditemukan.');
        }

        $detail = $this->penjualanDModel->getDetailByPenjualan_l1H((int) $id);

        $db = \Config\Database::connect();
        $jurnal = $db->table('jurnal_umum')
            ->where('no_referensi', $penjualan['no_faktur'])
            ->get()
            ->getRowArray();

        $jurnalDetails = [];
        if ($jurnal) {
            $jurnalDetails = $db->table('jurnal_umum_d')
                ->where('id_jurnal', $jurnal['id'])
                ->get()
                ->getResultArray();
        }

        return view('penjualan/detail', [
            'penjualan'     => $penjualan,
            'detail'        => $detail,
            'jurnal'        => $jurnal,
            'jurnalDetails' => $jurnalDetails,
        ]);
    }

    // ═══════════════════════════════════════════
    //  MASTER BARANG
    // ═══════════════════════════════════════════

    /**
     * Daftar Master Barang & Stok
     */
    public function barang_l1H()
    {
        $this->cekAkses('brg', 'daftar');

        $barang = $this->barangModel->getAktif_l1H();

        return view('penjualan/barang', [
            'barang' => $barang,
        ]);
    }

    /**
     * Tambah Barang Baru
     */
    public function simpan_barang_l1H()
    {
        $this->cekAkses('brg', 'tambah');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/penjualan/barang_l1H');
        }

        $kodeBarang = trim($this->request->getPost('kode_barang'));
        $namaBarang = trim($this->request->getPost('nama_barang'));
        $kategori   = trim($this->request->getPost('kategori')) ?: 'Umum';
        $hargaBeli  = (float) str_replace(',', '', trim((string) $this->request->getPost('harga_beli')));
        $hargaJual  = (float) str_replace(',', '', trim((string) $this->request->getPost('harga_jual')));
        $stok       = (float) str_replace(',', '', trim((string) $this->request->getPost('stok')));
        $satuan     = trim($this->request->getPost('satuan')) ?: 'pcs';

        if (empty($kodeBarang) || empty($namaBarang)) {
            return redirect()->back()->with('error', 'Kode barang dan nama barang wajib diisi.');
        }

        $exists = $this->barangModel->where('kode_barang', $kodeBarang)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Kode barang sudah terdaftar.');
        }

        $data = [
            'kode_barang' => $kodeBarang,
            'nama_barang' => $namaBarang,
            'kategori'    => $kategori,
            'harga_beli'  => $hargaBeli,
            'harga_jual'  => $hargaJual,
            'stok'        => $stok,
            'satuan'      => $satuan,
            'is_deleted'  => 0,
        ];

        $this->barangModel->insert($data);
        $insertId = $this->barangModel->getInsertID();

        AuditLogger::catat('TAMBAH', 'barang', $insertId, ['after' => $data], 'Master', "Tambah Barang: {$namaBarang}");

        return redirect()->to('/46124026/penjualan/barang_l1H')
            ->with('success', "Barang '{$namaBarang}' berhasil ditambahkan.");
    }

    /**
     * Update Data Barang
     */
    public function update_barang_l1H()
    {
        $this->cekAkses('brg', 'edit');

        if (!$this->request->is('post')) {
            return redirect()->to('/46124026/penjualan/barang_l1H');
        }

        $id = (int) $this->request->getPost('id');
        $barang = $this->barangModel->find($id);
        if (!$barang) {
            return redirect()->to('/46124026/penjualan/barang_l1H')->with('error', 'Barang tidak ditemukan.');
        }

        $data = [
            'nama_barang' => trim($this->request->getPost('nama_barang')),
            'kategori'    => trim($this->request->getPost('kategori')) ?: $barang['kategori'],
            'harga_beli'  => (float) str_replace(',', '', trim((string) $this->request->getPost('harga_beli'))),
            'harga_jual'  => (float) str_replace(',', '', trim((string) $this->request->getPost('harga_jual'))),
            'stok'        => (float) str_replace(',', '', trim((string) $this->request->getPost('stok'))),
            'satuan'      => trim($this->request->getPost('satuan')) ?: $barang['satuan'],
        ];

        $this->barangModel->update($id, $data);

        AuditLogger::catat('EDIT', 'barang', $id, ['before' => $barang, 'after' => $data], 'Master', "Edit Barang: {$barang['nama_barang']}");

        return redirect()->to('/46124026/penjualan/barang_l1H')
            ->with('success', "Barang '{$barang['nama_barang']}' berhasil diperbarui.");
    }

    /**
     * Soft Delete Barang
     */
    public function hapus_barang_l1H($id = null)
    {
        $this->cekAkses('brg', 'hapus');

        $barang = $this->barangModel->find($id);
        if (!$barang) {
            return redirect()->to('/46124026/penjualan/barang_l1H')->with('error', 'Barang tidak ditemukan.');
        }

        $this->barangModel->update($id, ['is_deleted' => 1]);

        AuditLogger::catat('SOFT_DELETE', 'barang', (int) $id, ['before' => $barang], 'Master', "Hapus Barang: {$barang['nama_barang']}");

        return redirect()->to('/46124026/penjualan/barang_l1H')
            ->with('success', "Barang '{$barang['nama_barang']}' berhasil dihapus.");
    }
}
