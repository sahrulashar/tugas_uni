
<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('kas_keluar', function ($routes) {
    $routes->get('coa', 'KasKeluar::coa_l1H');
    $routes->get('tambah_coa', 'KasKeluar::tambah_coa_l1H');
    $routes->post('simpan_coa', 'KasKeluar::simpan_coa_l1H');
    $routes->get('edit_coa/(:num)', 'KasKeluar::edit_coa_l1H/$1');
    $routes->post('update_coa', 'KasKeluar::update_coa_l1H');
    $routes->get('lihat_coa/(:num)', 'KasKeluar::lihat_coa_l1H/$1');
    $routes->get('hapus_coa/(:num)', 'KasKeluar::hapus_coa_l1H/$1');
    $routes->get('aktifkan_coa/(:num)', 'KasKeluar::aktifkan_coa_l1H/$1');
    $routes->get('hapus_permanen_coa/(:num)', 'KasKeluar::hapus_permanen_coa_l1H/$1');

    $routes->get('supplier', 'KasKeluar::supplier_l1H');
    $routes->get('tambah_supplier', 'KasKeluar::tambah_supplier_l1H');
    $routes->post('simpan_supplier', 'KasKeluar::simpan_supplier_l1H');
    $routes->get('edit_supplier/(:num)', 'KasKeluar::edit_supplier_l1H/$1');
    $routes->post('update_supplier', 'KasKeluar::update_supplier_l1H');
    $routes->get('hapus_supplier/(:num)', 'KasKeluar::hapus_supplier_l1H/$1');
    $routes->get('aktifkan_supplier/(:num)', 'KasKeluar::aktifkan_supplier_l1H/$1');
    $routes->get('hapus_permanen_supplier/(:num)', 'KasKeluar::hapus_permanen_supplier_l1H/$1');
    $routes->get('lihat_supplier/(:num)', 'KasKeluar::lihat_supplier_l1H/$1');

    $routes->get('karyawan', 'KasKeluar::karyawan_l1H');
    $routes->get('tambah_karyawan', 'KasKeluar::tambah_karyawan_l1H');
    $routes->post('simpan_karyawan', 'KasKeluar::simpan_karyawan_l1H');
    $routes->get('edit_karyawan/(:num)', 'KasKeluar::edit_karyawan_l1H/$1');
    $routes->post('update_karyawan', 'KasKeluar::update_karyawan_l1H');
    $routes->get('lihat_karyawan/(:num)', 'KasKeluar::lihat_karyawan_l1H/$1');
    $routes->get('hapus_karyawan/(:num)', 'KasKeluar::hapus_karyawan_l1H/$1');
    $routes->get('aktifkan_karyawan/(:num)', 'KasKeluar::aktifkan_karyawan_l1H/$1');
    $routes->get('hapus_permanen_karyawan/(:num)', 'KasKeluar::hapus_permanen_karyawan_l1H/$1');
});

$routes->group('aktivitas', function ($routes) {
    // ── Aktivitas 1: Rencana Beli ──────────────────────────────────
    $routes->get('aktivitas1', 'Aktivitas\Aktivitas1::index');
    $routes->get('aktivitas1/tambah', 'Aktivitas\Aktivitas1::tambah_l1H');
    $routes->post('aktivitas1/simpan', 'Aktivitas\Aktivitas1::simpan_l1H');
    $routes->get('aktivitas1/lihat/(:num)', 'Aktivitas\Aktivitas1::lihat_l1H/$1');
    $routes->get('aktivitas1/edit/(:num)', 'Aktivitas\Aktivitas1::edit_l1H/$1');
    $routes->post('aktivitas1/update', 'Aktivitas\Aktivitas1::update_l1H');
    $routes->get('aktivitas1/hapus/(:num)', 'Aktivitas\Aktivitas1::hapus_l1H/$1');

    // ── Aktivitas 2: Bukti Kas Keluar (BKK) ───────────────────────
    $routes->get('aktivitas2', 'Aktivitas\Aktivitas2::index');
    $routes->get('aktivitas2/tambah', 'Aktivitas\Aktivitas2::tambah_l1H');
    $routes->post('aktivitas2/simpan', 'Aktivitas\Aktivitas2::simpan_l1H');
    $routes->get('aktivitas2/lihat/(:num)', 'Aktivitas\Aktivitas2::lihat_l1H/$1');
    $routes->get('aktivitas2/edit/(:num)', 'Aktivitas\Aktivitas2::edit_l1H/$1');
    $routes->post('aktivitas2/update', 'Aktivitas\Aktivitas2::update_l1H');
    $routes->get('aktivitas2/hapus/(:num)', 'Aktivitas\Aktivitas2::hapus_l1H/$1');

    // ── Aktivitas 3: Rekap BKK ────────────────────────────────────
    $routes->get('aktivitas3', 'Aktivitas\Aktivitas3::index');
    $routes->get('aktivitas3/tambah', 'Aktivitas\Aktivitas3::tambah_l1H');
    $routes->post('aktivitas3/simpan', 'Aktivitas\Aktivitas3::simpan_l1H');
    $routes->get('aktivitas3/lihat/(:num)', 'Aktivitas\Aktivitas3::lihat_l1H/$1');
    $routes->get('aktivitas3/edit/(:num)', 'Aktivitas\Aktivitas3::edit_l1H/$1');
    $routes->post('aktivitas3/update', 'Aktivitas\Aktivitas3::update_l1H');
    $routes->get('aktivitas3/hapus/(:num)', 'Aktivitas\Aktivitas3::hapus_l1H/$1');
});

// ── Pengujian ERP: Database Transaction & Audit Trail ─────────
$routes->group('test-transaksi', function ($routes) {
    $routes->get('/', 'TestTransaksi::index');
    $routes->get('uji1', 'TestTransaksi::ujiRollbackNegatif');
    $routes->get('sukses', 'TestTransaksi::ujiTransaksiSukses');
    $routes->get('soft-delete', 'TestTransaksi::ujiSoftDelete');
    $routes->get('soft-delete/(:num)', 'TestTransaksi::ujiSoftDelete/$1');
    $routes->get('uji2', 'TestTransaksi::ujiSimulasiCrash');
});

