<?php
/**
 * _session_inject.php — Partial untuk inject data session ke JavaScript
 *
 * Di-include di dalam tag <script> sebelum React render.
 * Pastikan dipanggil SEBELUM </script> di dalam blok script yang ada.
 *
 * Cara pakai di setiap view (setelah window.__CSRF__ = {...};):
 *   <?php include APPPATH . 'Views/_session_inject.php'; ?>
 */

$__nama    = session()->get('nama_user') ?: 'User';
$__kode    = session()->get('kode_user') ?: 'user';
$__initial = strtoupper(substr($__nama, 0, 1)) ?: 'U';
?>
  window._erpUser = {
    nama:    "<?= esc($__nama,    'js') ?>",
    kode:    "<?= esc($__kode,    'js') ?>",
    initial: "<?= esc($__initial, 'js') ?>"
  };
