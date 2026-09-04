
<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('kas_keluar', function ($routes) {
    $routes->get('coa', 'KasKeluar::coa');
    $routes->get('tambah_coa', 'KasKeluar::tambah_coa');
    $routes->post('simpan_coa', 'KasKeluar::simpan_coa');
    $routes->get('edit_coa/(:num)', 'KasKeluar::edit_coa/$1');
    $routes->post('update_coa', 'KasKeluar::update_coa');
    $routes->get('lihat_coa/(:num)', 'KasKeluar::lihat_coa/$1');
    $routes->get('hapus_coa/(:num)', 'KasKeluar::hapus_coa/$1');
    $routes->get('aktifkan_coa/(:num)', 'KasKeluar::aktifkan_coa/$1');
    $routes->get('hapus_permanen_coa/(:num)', 'KasKeluar::hapus_permanen_coa/$1');

    $routes->get('supplier', 'KasKeluar::supplier');
    $routes->get('tambah_supplier', 'KasKeluar::tambah_supplier');
    $routes->post('simpan_supplier', 'KasKeluar::simpan_supplier');
    $routes->get('edit_supplier/(:num)', 'KasKeluar::edit_supplier/$1');
    $routes->post('update_supplier', 'KasKeluar::update_supplier');
    $routes->get('hapus_supplier/(:num)', 'KasKeluar::hapus_supplier/$1');
    $routes->get('aktifkan_supplier/(:num)', 'KasKeluar::aktifkan_supplier/$1');
    $routes->get('hapus_permanen_supplier/(:num)', 'KasKeluar::hapus_permanen_supplier/$1');
    $routes->get('lihat_supplier/(:num)', 'KasKeluar::lihat_supplier/$1');

    $routes->get('karyawan', 'KasKeluar::karyawan');
    $routes->get('tambah_karyawan', 'KasKeluar::tambah_karyawan');
    $routes->post('simpan_karyawan', 'KasKeluar::simpan_karyawan');
    $routes->get('edit_karyawan/(:num)', 'KasKeluar::edit_karyawan/$1');
    $routes->post('update_karyawan', 'KasKeluar::update_karyawan');
    $routes->get('lihat_karyawan/(:num)', 'KasKeluar::lihat_karyawan/$1');
    $routes->get('hapus_karyawan/(:num)', 'KasKeluar::hapus_karyawan/$1');
    $routes->get('aktifkan_karyawan/(:num)', 'KasKeluar::aktifkan_karyawan/$1');
    $routes->get('hapus_permanen_karyawan/(:num)', 'KasKeluar::hapus_permanen_karyawan/$1');
});
