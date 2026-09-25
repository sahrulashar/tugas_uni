
<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('46124026', function ($routes) {

    $routes->group('kas_keluar', function ($routes) {
        $routes->get('coa_l1H', 'KasKeluar::coa_l1H');
        $routes->get('tambah_coa_l1H', 'KasKeluar::tambah_coa_l1H');
        $routes->post('simpan_coa_l1H', 'KasKeluar::simpan_coa_l1H');
        $routes->get('edit_coa_l1H/(:num)', 'KasKeluar::edit_coa_l1H/$1');
        $routes->post('update_coa_l1H', 'KasKeluar::update_coa_l1H');
        $routes->get('lihat_coa_l1H/(:num)', 'KasKeluar::lihat_coa_l1H/$1');
        $routes->get('hapus_coa_l1H/(:num)', 'KasKeluar::hapus_coa_l1H/$1');
        $routes->get('aktifkan_coa_l1H/(:num)', 'KasKeluar::aktifkan_coa_l1H/$1');
        $routes->get('hapus_permanen_coa_l1H/(:num)', 'KasKeluar::hapus_permanen_coa_l1H/$1');

        $routes->get('supplier_l1H', 'KasKeluar::supplier_l1H');
        $routes->get('tambah_supplier_l1H', 'KasKeluar::tambah_supplier_l1H');
        $routes->post('simpan_supplier_l1H', 'KasKeluar::simpan_supplier_l1H');
        $routes->get('edit_supplier_l1H/(:num)', 'KasKeluar::edit_supplier_l1H/$1');
        $routes->post('update_supplier_l1H', 'KasKeluar::update_supplier_l1H');
        $routes->get('hapus_supplier_l1H/(:num)', 'KasKeluar::hapus_supplier_l1H/$1');
        $routes->get('aktifkan_supplier_l1H/(:num)', 'KasKeluar::aktifkan_supplier_l1H/$1');
        $routes->get('hapus_permanen_supplier_l1H/(:num)', 'KasKeluar::hapus_permanen_supplier_l1H/$1');
        $routes->get('lihat_supplier_l1H/(:num)', 'KasKeluar::lihat_supplier_l1H/$1');

        $routes->get('karyawan_l1H', 'KasKeluar::karyawan_l1H');
        $routes->get('tambah_karyawan_l1H', 'KasKeluar::tambah_karyawan_l1H');
        $routes->post('simpan_karyawan_l1H', 'KasKeluar::simpan_karyawan_l1H');
        $routes->get('edit_karyawan_l1H/(:num)', 'KasKeluar::edit_karyawan_l1H/$1');
        $routes->post('update_karyawan_l1H', 'KasKeluar::update_karyawan_l1H');
        $routes->get('lihat_karyawan_l1H/(:num)', 'KasKeluar::lihat_karyawan_l1H/$1');
        $routes->get('hapus_karyawan_l1H/(:num)', 'KasKeluar::hapus_karyawan_l1H/$1');
        $routes->get('aktifkan_karyawan_l1H/(:num)', 'KasKeluar::aktifkan_karyawan_l1H/$1');
        $routes->get('hapus_permanen_karyawan_l1H/(:num)', 'KasKeluar::hapus_permanen_karyawan_l1H/$1');
    });

    $routes->group('aktivitas', function ($routes) {
        // ── Aktivitas 1: Rencana Beli ──────────────────────────────────
        $routes->get('aktivitas1', 'Aktivitas\Aktivitas1::index');
        $routes->get('aktivitas1/tambah_l1H', 'Aktivitas\Aktivitas1::tambah_l1H');
        $routes->post('aktivitas1/simpan_l1H', 'Aktivitas\Aktivitas1::simpan_l1H');
        $routes->get('aktivitas1/lihat_l1H/(:num)', 'Aktivitas\Aktivitas1::lihat_l1H/$1');
        $routes->get('aktivitas1/edit_l1H/(:num)', 'Aktivitas\Aktivitas1::edit_l1H/$1');
        $routes->post('aktivitas1/update_l1H', 'Aktivitas\Aktivitas1::update_l1H');
        $routes->get('aktivitas1/hapus_l1H/(:num)', 'Aktivitas\Aktivitas1::hapus_l1H/$1');

        // ── Aktivitas 2: Bukti Kas Keluar (BKK) ───────────────────────
        $routes->get('aktivitas2', 'Aktivitas\Aktivitas2::index');
        $routes->get('aktivitas2/tambah_l1H', 'Aktivitas\Aktivitas2::tambah_l1H');
        $routes->post('aktivitas2/simpan_l1H', 'Aktivitas\Aktivitas2::simpan_l1H');
        $routes->get('aktivitas2/lihat_l1H/(:num)', 'Aktivitas\Aktivitas2::lihat_l1H/$1');
        $routes->get('aktivitas2/edit_l1H/(:num)', 'Aktivitas\Aktivitas2::edit_l1H/$1');
        $routes->post('aktivitas2/update_l1H', 'Aktivitas\Aktivitas2::update_l1H');
        $routes->get('aktivitas2/hapus_l1H/(:num)', 'Aktivitas\Aktivitas2::hapus_l1H/$1');

        // ── Aktivitas 3: Rekap BKK ────────────────────────────────────
        $routes->get('aktivitas3', 'Aktivitas\Aktivitas3::index');
        $routes->get('aktivitas3/tambah_l1H', 'Aktivitas\Aktivitas3::tambah_l1H');
        $routes->post('aktivitas3/simpan_l1H', 'Aktivitas\Aktivitas3::simpan_l1H');
        $routes->get('aktivitas3/lihat_l1H/(:num)', 'Aktivitas\Aktivitas3::lihat_l1H/$1');
        $routes->get('aktivitas3/edit_l1H/(:num)', 'Aktivitas\Aktivitas3::edit_l1H/$1');
        $routes->post('aktivitas3/update_l1H', 'Aktivitas\Aktivitas3::update_l1H');
        $routes->get('aktivitas3/hapus_l1H/(:num)', 'Aktivitas\Aktivitas3::hapus_l1H/$1');
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

});
