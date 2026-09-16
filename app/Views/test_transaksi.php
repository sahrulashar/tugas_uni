<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengujian Transaksi ERP (Rollback & Audit Trail)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 10px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .badge-soft-success { background-color: #d1e7dd; color: #0f5132; }
        .badge-soft-danger { background-color: #f8d7da; color: #842029; }
    </style>
</head>
<body class="p-4">
<div class="container-fluid" style="max-width: 1200px;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h3 class="fw-bold text-primary mb-1">🧪 Panel Pengujian Database Transaction & Audit Trail</h3>
            <p class="text-muted mb-0">CodeIgniter 4 — Tugas ERP: TransStart, TransComplete, Rollback, Soft Delete & Audit Log</p>
        </div>
        <a href="/" class="btn btn-outline-secondary btn-sm">🏠 Beranda ERP</a>
    </div>

    <!-- NOTIFIKASI HASIL UJI -->
    <?php if ($pesan): ?>
        <div class="alert alert-<?= esc($tipe) ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- PANEL TOMBOL SKENARIO UJI COBA -->
    <div class="card mb-4">
        <div class="card-header bg-white fw-bold py-3">
            🎯 Jalankan Skenario Uji Coba:
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card h-100 border p-3">
                        <span class="badge bg-danger mb-2 w-auto">UJI COBA 1</span>
                        <h6 class="fw-bold">Rollback Harga Negatif</h6>
                        <p class="text-muted small">Input item dengan harga <code>-10,000</code>. Exception dilempar dan transaksi di-rollback secara utuh.</p>
                        <a href="/test-transaksi/uji1" class="btn btn-danger btn-sm mt-auto">Uji Rollback (Negatif)</a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border p-3">
                        <span class="badge bg-success mb-2 w-auto">TRANSAKSI NORMAL</span>
                        <h6 class="fw-bold">Simpan Sukses + Audit Log</h6>
                        <p class="text-muted small">Input master & detail dengan data valid. Transaksi di-commit dan otomatis tercatat di <code>audit_log</code>.</p>
                        <a href="/test-transaksi/sukses" class="btn btn-success btn-sm mt-auto">Simpan Transaksi Valid</a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border p-3">
                        <span class="badge bg-warning text-dark mb-2 w-auto">SOFT DELETE</span>
                        <h6 class="fw-bold">Uji Soft Delete</h6>
                        <p class="text-muted small">Update <code>is_deleted = 1</code> (tanpa query DELETE FROM) dan mencatat aksi ke <code>audit_log</code>.</p>
                        <a href="/test-transaksi/soft-delete" class="btn btn-warning btn-sm mt-auto">Jalankan Soft Delete</a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card h-100 border p-3">
                        <span class="badge bg-dark mb-2 w-auto">UJI COBA 2</span>
                        <h6 class="fw-bold">Simulasi Server Crash</h6>
                        <p class="text-muted small">Simulasi script terputus mendadak via <code>die()</code> di tengah loop detail sebelum commit.</p>
                        <a href="/test-transaksi/uji2" target="_blank" class="btn btn-outline-dark btn-sm mt-auto">Simulasi Crash (die) ↗</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LIVE DATA DATABASE VIEWER -->
    <div class="row g-4">
        <!-- TABEL 1: MASTER (tbbeli) -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold">📦 Tabel Master (<code>tbbeli</code>)</span>
                    <span class="badge bg-primary"><?= count($semuaBeli) ?> Data</span>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 350px;">
                    <table class="table table-hover table-striped mb-0 small">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>ID</th>
                                <th>No Beli</th>
                                <th>Tanggal</th>
                                <th>Toko</th>
                                <th>Status Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($semuaBeli)): ?>
                                <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada data di tabel tbbeli</td></tr>
                            <?php else: ?>
                                <?php foreach ($semuaBeli as $b): ?>
                                    <tr>
                                        <td><?= esc($b['id']) ?></td>
                                        <td><b><?= esc($b['no_beli']) ?></b></td>
                                        <td><?= esc($b['tgl']) ?></td>
                                        <td><?= esc($b['toko']) ?></td>
                                        <td>
                                            <?php if ($b['is_deleted'] == 1): ?>
                                                <span class="badge badge-soft-danger px-2 py-1">is_deleted = 1 (Soft Deleted)</span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-success px-2 py-1">is_deleted = 0 (Aktif)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TABEL 2: DETAIL (tbbeli_d) -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold">📑 Tabel Detail (<code>tbbeli_d</code>)</span>
                    <span class="badge bg-secondary"><?= count($semuaDetail) ?> Data</span>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 350px;">
                    <table class="table table-hover table-striped mb-0 small">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>ID</th>
                                <th>ID Beli</th>
                                <th>Barang</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($semuaDetail)): ?>
                                <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada data di tabel tbbeli_d</td></tr>
                            <?php else: ?>
                                <?php foreach ($semuaDetail as $d): ?>
                                    <tr>
                                        <td><?= esc($d['id']) ?></td>
                                        <td><span class="badge bg-light text-dark border">#<?= esc($d['id_beli']) ?></span></td>
                                        <td><?= esc($d['barng']) ?></td>
                                        <td class="fw-semibold">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TABEL 3: AUDIT LOG (audit_log) -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold">📋 Tabel Audit Trail (<code>audit_log</code>)</span>
                    <span class="badge bg-info text-dark"><?= count($semuaAudit) ?> Log Aktivitas</span>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 350px;">
                    <table class="table table-hover table-striped mb-0 small">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>ID Log</th>
                                <th>User ID</th>
                                <th>Aksi</th>
                                <th>Tabel Terdampak</th>
                                <th>Record ID</th>
                                <th>Waktu (NOW)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($semuaAudit)): ?>
                                <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada catatan di tabel audit_log</td></tr>
                            <?php else: ?>
                                <?php foreach ($semuaAudit as $a): ?>
                                    <tr>
                                        <td>#<?= esc($a['id']) ?></td>
                                        <td>User <?= esc($a['user_id']) ?></td>
                                        <td>
                                            <?php if ($a['aksi'] === 'TAMBAH'): ?>
                                                <span class="badge bg-success">TAMBAH</span>
                                            <?php elseif ($a['aksi'] === 'SOFT_DELETE'): ?>
                                                <span class="badge bg-warning text-dark">SOFT_DELETE</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= esc($a['aksi']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= esc($a['tabel_terdampak']) ?></code></td>
                                        <td>Record #<?= esc($a['record_id']) ?></td>
                                        <td><?= esc($a['waktu']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
