# 📊 Progress Report — Sistem ERP Kas Keluar

> **Proyek**: Sistem ERP Kas Keluar (CodeIgniter 4)  
> **Pemilik**: Sahruni Ashar (NIM: 46124026)  
> **Framework**: CodeIgniter 4.7+ / PHP 8.2+  
> **Database**: MySQL 8.0 (`kas_keluar`)  
> **Tanggal Laporan**: 19 Oktober 2026  

---

## 📌 Ringkasan Proyek

Sistem ERP berbasis web untuk pengelolaan **Kas Keluar** perusahaan, mencakup siklus lengkap dari perencanaan pembelian hingga rekapitulasi bukti kas keluar. Dibangun menggunakan arsitektur **MVC CodeIgniter 4** dengan frontend **React SPA + Tailwind CSS** (CDN-based), dilengkapi fitur keamanan **RBAC (Role-Based Access Control)** bertingkat dan **Audit Trail** otomatis.

---

## 🏗️ Arsitektur & Tech Stack

| Komponen | Teknologi |
|---|---|
| **Backend** | PHP 8.2+, CodeIgniter 4.7 |
| **Frontend** | React 18 (CDN), Tailwind CSS, Lucide Icons, Babel Standalone |
| **Database** | MySQL 8.0 / MySQLi Driver |
| **Autentikasi** | Session-based + Bcrypt password hashing |
| **Otorisasi** | Custom RBAC (tabel `tbuser`, `tblaman`, `tbakses`) |
| **Testing** | PHPUnit 10.5, Faker |
| **DevOps** | Docker Compose, Nginx Reverse Proxy, Cloudflare Tunnel |
| **Domain** | `https://3bd4muti.site/` |

---

## ✅ Fitur yang Sudah Diimplementasi

### 1. Sistem Autentikasi & Keamanan
- [x] Login dengan verifikasi bcrypt (`password_verify`)
- [x] Regenerasi Session ID (anti session-fixation)
- [x] Auth Filter untuk proteksi route terautentikasi
- [x] Logout dengan destroy session
- [x] Pencatatan audit log untuk event LOGIN & LOGOUT
- [x] Custom Session FileHandler untuk kompatibilitas Windows/OneDrive

### 2. RBAC — Role-Based Access Control
- [x] Manajemen User (CRUD + toggle aktif/nonaktif)
- [x] Manajemen Laman/Halaman (registrasi kode & aksi)
- [x] Manajemen Hak Akses (matriks checklist per user)
- [x] Granular permission: `daftar`, `tambah`, `edit`, `hapus`, `lihat`, `cetak`
- [x] Library `RbacChecker` — gate otomatis + menu builder dinamis
- [x] 3 role bawaan:
  - **Admin** — akses penuh ke semua modul
  - **Lisa** (Staff Referensi & Pembelian) — Supplier, COA view, Aktivitas 1
  - **Devi** (Staff Kas & Akuntansi) — COA full, Aktivitas 2 & 3

### 3. Master Data Keuangan (Kas Keluar)
- [x] **Chart of Accounts (COA)** — CRUD lengkap dengan kode akun, tipe, saldo normal, saldo awal, klasifikasi header/detail, statistik per tipe
- [x] **Supplier** — CRUD lengkap (kode, nama, alamat, telepon, kontak)
- [x] **Karyawan** — CRUD lengkap (NIK, nama, jabatan, departemen)
- [x] Soft Delete & Restore untuk semua master data
- [x] Hapus Permanen (hard delete)

### 4. Transaksi Keuangan — Siklus 3 Tahap

#### Aktivitas 1: Rencana Beli
- [x] CRUD Header (`tbrbeli`) + Detail multi-baris (`tbrbeli_d`)
- [x] Relasi ke Supplier
- [x] Database Transaction (atomic insert/update)
- [x] Audit trail otomatis

#### Aktivitas 2: Bukti Kas Keluar (BKK)
- [x] CRUD Header (`tbbkk`) + Detail (`tbbkk_d`)
- [x] Relasi ke COA kas/bank dan Rencana Beli
- [x] Database Transaction (atomic insert/update)
- [x] Audit trail otomatis

#### Aktivitas 3: Rekap BKK
- [x] CRUD Rekapitulasi (`tbrecord`)
- [x] Relasi ke dokumen BKK
- [x] Audit trail otomatis

### 5. Audit Trail Log
- [x] Library `AuditLogger` — pencatatan otomatis setiap mutasi CRUD
- [x] Metadata lengkap: user, aksi, modul, tabel, record ID, URL, timestamp
- [x] Snapshot data sebelum & sesudah (JSON `detail_perubahan`)
- [x] Halaman penampil audit log dengan filter multi-kriteria
- [x] API endpoint detail log (JSON response)
- [x] Visualisasi diff before/after

### 6. Pengujian & Validasi ACID
- [x] Modul `TestTransaksi` — pembuktian ACID database:
  - Uji rollback otomatis saat harga negatif
  - Uji transaksi sukses end-to-end
  - Uji soft delete + audit log
  - Simulasi server crash / koneksi putus
- [x] Unit Tests (PHPUnit):
  - `HealthTest` — smoke test
  - `RbacTest` — pengujian RBAC
  - `BusinessRulesTest` — validasi aturan bisnis
  - `HargaServiceTest` — pengujian kalkulasi harga
  - `StokServiceTest` — pengujian layanan stok
  - `KreditServiceTest` — pengujian layanan kredit

### 7. UI/UX Dashboard
- [x] Layout dashboard responsif dengan React SPA
- [x] Sidebar navigasi dinamis (berdasarkan hak akses RBAC)
- [x] Topbar dengan info user login
- [x] Breadcrumb navigasi
- [x] Flash message (success/error/warning)
- [x] Stat cards pada halaman COA
- [x] Form interaktif dengan validasi
- [x] Modal detail dan konfirmasi hapus

### 8. Infrastruktur DevOps
- [x] Docker Compose (PHP 8.3 + Apache, MySQL 8.0, phpMyAdmin, Nginx)
- [x] Dockerfile production-ready
- [x] Auto-init database via Docker entrypoint
- [x] Cloudflare Tunnel Zero Trust
- [x] Panduan deployment lengkap (`DOCKER_GUIDE.md`, `CLOUDFLARE_TUNNEL_GUIDE.md`)

---

## 📁 Struktur Proyek

```
sistem_erp_ci4/
├── app/
│   ├── Controllers/           # 11 controller (termasuk subdirectori Aktivitas/)
│   │   ├── Auth.php           # Login & Logout
│   │   ├── CRbac.php          # Manajemen RBAC (User, Laman, Akses)
│   │   ├── KasKeluar.php      # Dashboard + CRUD COA, Supplier, Karyawan
│   │   ├── Audit.php          # Audit Trail Log viewer
│   │   ├── TestTransaksi.php  # Pengujian ACID
│   │   └── Aktivitas/
│   │       ├── Aktivitas1.php # Rencana Beli
│   │       ├── Aktivitas2.php # Bukti Kas Keluar (BKK)
│   │       └── Aktivitas3.php # Rekap BKK
│   ├── Models/                # 17 model
│   ├── Views/                 # 36 view files (React SPA + Tailwind)
│   ├── Libraries/             # RbacChecker, AuditLogger, Session FileHandler
│   ├── Filters/               # AuthFilter
│   ├── Database/
│   │   ├── Migrations/        # 3 migration files (tbuser, tblaman, tbakses)
│   │   └── Seeds/             # RbacSeeder (3 user + laman + akses)
│   └── Config/                # Routes, Filters, etc.
├── tests/                     # 6 unit/integration test files
├── docker/                    # Konfigurasi Docker & Nginx
├── public/                    # Webroot (index.php, favicon)
├── *.sql                      # 7 file SQL (schema, patch, migration)
└── docs                       # PANDUAN_SETUP.txt, DOCKER_GUIDE.md, dll.
```

---

## 🗄️ Skema Database — 15 Tabel

| # | Tabel | Deskripsi | Modul |
|---|---|---|---|
| 1 | `tbuser` | Master user (kode, nama, password, status) | RBAC |
| 2 | `tblaman` | Master halaman & aksi | RBAC |
| 3 | `tbakses` | Matriks izin user ↔ laman | RBAC |
| 4 | `coa` | Chart of Accounts | Master |
| 5 | `supplier` | Master supplier | Master |
| 6 | `karyawan` | Master karyawan | Master |
| 7 | `tbrbeli` | Header Rencana Beli | Transaksi |
| 8 | `tbrbeli_d` | Detail Rencana Beli | Transaksi |
| 9 | `tbbkk` | Header Bukti Kas Keluar | Transaksi |
| 10 | `tbbkk_d` | Detail Bukti Kas Keluar | Transaksi |
| 11 | `tbrecord` | Rekap BKK | Transaksi |
| 12 | `tbbeli` | Transaksi Pembelian (testing) | Testing |
| 13 | `tbbeli_d` | Detail Pembelian (testing) | Testing |
| 14 | `audit_log` | Jejak Audit seluruh sistem | Audit |
| 15 | `migrations` | CI4 Migration tracker | System |

---

## 📈 Statistik Proyek

| Metrik | Nilai |
|---|---|
| Total file PHP di `app/` | **132 file** |
| Total baris kode (app/) | **~22.926 baris** |
| Controllers | **12** |
| Models | **17** |
| Views | **38** |
| Migrations | **5** |
| SQL Scripts | **7** |
| Unit Tests | **7 test files (114 tests)** |
| Routes | **~70+ route** |
| Git Commits | **29 commits** |

---

## 📅 Timeline Pengembangan

```
Sep 2026                                                    Okt 2026
──────────────────────────────────────────────────────────────────────
 5 Sep    First commit + Docker setup
 9 Sep    Database schema + COA module
13 Sep    Aktivitas 1 (Rencana Beli) + Aktivitas 2 (BKK)
14 Sep    Aktivitas 3 (Rekap BKK) + bug fixes
15 Sep    Refactor method naming (kode unik _l1H)
16 Sep    DB Transaction, Rollback, Soft Delete, Audit Log
17 Sep    Audit Trail + Soft Delete implementation
21 Sep    AuditLogger improvements + merge testing → main
26 Sep    Route prefix /46124026 + URL harmonization
 3 Okt    Saldo COA + BKK interaction + Unit Testing
 5 Okt    Bug fixes: audit trail, RBAC views, session resilience
 6 Okt    Docker environment rebuild (PHP 8.3, docs)
 7 Okt    Final view fixes + URL routing cleanup
19 Okt    Pertemuan 9: Cross-Module Integration (POS -> Stok -> Jurnal Otomatis -> Neraca Saldo)
19 Okt    Docker Nginx Proxy port preservation fix + Auth sync
21 Okt    Pertemuan 10-11: Advanced Data Aggregation & Executive Dashboard (Window Functions, Pivot, ApexCharts, Lab Playground)
```

### Fase Pengembangan:

| Fase | Periode | Deskripsi |
|---|---|---|
| **Fase 1** — Foundation | 5–9 Sep | Setup proyek, Docker, database schema, modul COA |
| **Fase 2** — Transaksi | 13–15 Sep | Modul Aktivitas 1, 2, 3 + refactoring |
| **Fase 3** — Keamanan & Audit | 16–21 Sep | DB Transaction, Audit Trail, Soft Delete, RBAC refinement |
| **Fase 4** — Routing & Polish | 26 Sep | Route prefix, URL harmonization |
| **Fase 5** — Testing & DevOps | 3–7 Okt | Unit testing, saldo COA, Docker rebuild, bug fixes |
| **Fase 6** — Cross-Module & Accounting | 19 Okt | Integrasi Penjualan, Stok, Automated Journaling, Neraca Saldo, Docker Nginx Proxy Fix |
| **Fase 7** — Advanced Aggregation & Dashboard | 21 Okt | Pertemuan 10–11: MySQL 8.0 Window Functions, Dynamic Pivot Matrix, ApexCharts CDN, Executive Dashboard, Lab Sandbox |

### 9. Pertemuan 9: Cross-Module Integration & Automated Journaling (Status: Selesai 100%)
- [x] **Master Barang & Stok** — CRUD master barang, kategori, harga beli (HPP), harga jual, dan stok gudang
- [x] **Kasir Penjualan (POS)** — Antarmuka kasir interaktif, keranjang belanja, stepper kuantitas, kalkulasi diskon & netto
- [x] **Integrasi Lintas Modul Atomik (ACID)**:
  - Transaksi kasir otomatis memotong stok barang di gudang via `StokService`
  - Validasi pencegahan under-stock (penolakan order melebihi stok tersedia)
  - Rollback otomatis jika stok kurang atau terjadi kegagalan sistem
- [x] **Automated Journaling Engine** — Library `JurnalService`:
  - Pembentukan otomatis 4 ayat jurnal berimbang:
    - `[D] 1111 - Kas Tangan` (Nilai Netto)
    - `[K] 4110 - Pendapatan Penjualan` (Nilai Netto)
    - `[D] 5120 - Beban Pokok Penjualan (HPP)` (Nilai HPP)
    - `[K] 1131 - Persediaan Barang Dagang` (Nilai HPP)
  - Validasi ketat keseimbangan double-entry (Total Debit == Total Kredit)
- [x] **Neraca Saldo Otomatis (Trial Balance)** — Library `NeracaSaldoService`:
  - Rekapitulasi real-time dari saldo awal COA + akumulasi mutasi jurnal
  - Indikator status rekonsiliasi keseimbangan (100% Balanced)
- [x] **Laboratorium Praktikum Interaktif** — Halaman `/46124026/akuntansi/praktikum_l1H`:
  - 1-Klik Uji Sukses: Kasir → Stok Terpotong → Jurnal Otomatis → Neraca Saldo Seimbang
  - 1-Klik Uji Rollback: Order Over-stock → Eksepsi Stok → DB Rollback (database tetap utuh)
- [x] **Automated Integration Testing** — `tests/unit/IntegrationPenjualanJurnalTest.php` (7 test cases, 29 assertions)

---


### 10. Pertemuan 10–11: Advanced Data Aggregation & Executive Dashboard (Status: Selesai 100%)
- [x] **Advanced SQL Query Engine (MySQL 8.0)**:
  - *Query 1*: Tren Arus Kas Bulanan, Running Cash Balance via `SUM() OVER ()`, dan perbandingan MoM Growth via `LAG() OVER ()`.
  - *Query 2*: Dynamic Pivot Table Pengeluaran Kas per COA kuartalan (Q1–Q4) via Conditional Aggregation `SUM(CASE WHEN ...)`.
  - *Query 3*: Analisis Pareto Profitabilitas Produk terlaris via `DENSE_RANK() OVER (ORDER BY total_laba_kotor DESC)` & Cumulative Share %.
  - *Query 4*: Matriks Konsentrasi Belanja Vendor/Supplier via Window Function `SUM() OVER ()` & Share Pengeluaran %.
- [x] **Service Layer & API**:
  - Library `DashboardAnalyticsService` (`app/Services/DashboardAnalyticsService.php`) dengan method suffix `_l1H`.
  - Endpoint RESTful JSON `/46124026/dashboard/api_metrics_l1H` untuk dynamic filter tahun/bulan tanpa reload.
- [x] **Integrasi Library Chart Modern**:
  - Integrasi **ApexCharts CDN** ke dalam arsitektur React 18 CDN SPA tanpa build-step (Webpack/Vite).
  - Area Spline Chart (Tren Arus Kas & Saldo Berjalan), Donut Chart (Komposisi Beban COA), Grouped Bar Chart (Pareto Produk), Horizontal Bar (Vendor Concentration).
- [x] **Executive Dashboard UI/UX** (`/46124026/dashboard`):
  - 4 Executive Stat Cards (Revenue, BKK Outflow, Net Operating Flow, Gross Margin %).
  - Filter tahun & bulan interaktif.
  - Actionable Audit Table: Threshold Alerts pengeluaran kas bernilai tinggi (&ge; Rp 500.000).
- [x] **Laboratorium Praktikum Interaktif** (`/46124026/dashboard/praktikum_l1H`):
  - *Interactive SQL Inspector* untuk 4 query dengan rincian query asli dan tabel hasil eksekusi.
  - *Visual Sandbox Live Chart Switcher* (Area / Bar / Line).
  - *1-Click Demo Dataset Simulator* (Suntik & Bersihkan Data Multi-Bulan Juli–Oktober).
- [x] **Automated Testing & RBAC**:
  - Migrasi `CreatePertemuan10Laman` untuk registrasi laman `dsh` dan `pkt10`.
  - Unit Test `tests/unit/DashboardAnalyticsTest.php` (6 test cases, 66 assertions, 100% pass).
  - Total unit test proyek meningkat menjadi **114 tests passing, 285 assertions**.

---

## 🔮 Roadmap / Fitur yang Belum Diimplementasi

Berikut fitur-fitur yang bisa dikembangkan selanjutnya:

- [ ] **Laporan Keuangan Ekspor** — Cetak laporan kas keluar & neraca saldo ke PDF/Excel
- [x] **Dashboard Analitik** — Grafik/chart ringkasan transaksi (ApexCharts: area spline, donut, bar chart)
- [x] **Kas Masuk & Penjualan** — Modul kasir dan penerimaan kas
- [x] **Jurnal Umum** — Pencatatan jurnal otomatis dari transaksi
- [ ] **Buku Besar** — Ledger per akun COA
- [x] **Neraca Saldo (Trial Balance)** — Laporan neraca saldo real-time
- [ ] **Laba Rugi Periodik** — Laporan perhitungan laba/rugi bulanan/tahunan
- [ ] **Multi-Periode Akuntansi** — Tutup buku bulanan/tahunan
- [ ] **Approval Workflow** — Persetujuan bertingkat untuk transaksi
- [ ] **Upload Dokumen** — Lampiran bukti transaksi (scan/foto)
- [ ] **Notifikasi Real-time** — Pemberitahuan untuk event penting
- [ ] **API RESTful** — Endpoint JSON untuk integrasi pihak ketiga
- [ ] **Password Reset** — Fitur lupa password via email
- [ ] **Log Activity** — Login history per user
- [ ] **Export/Import Data** — CSV/Excel untuk master data
- [ ] **Pagination & Search** — Optimasi performa untuk data besar
- [ ] **Responsive Mobile** — Optimasi tampilan untuk perangkat mobile

---

## 🧪 Cara Menjalankan

### Lokal (XAMPP/PHP Built-in Server)
```bash
# 1. Clone / extract proyek
# 2. Import database: kas_keluar.sql → MySQL
# 3. Salin .env → sesuaikan konfigurasi database
# 4. Jalankan:
php spark serve
```

### Docker
```bash
docker compose up -d --build
# Akses: http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

### Akun Login Default
| Kode | Password | Role |
|---|---|---|
| `admin` | `admin123` | Administrator (Full Access) |
| `lisa` | `asil` | Staff Referensi & Pembelian |
| `devi` | `ived` | Staff Kas & Akuntansi |

---

## 📝 Catatan Pengembangan

1. **Prefix Route `46124026`**: Seluruh route terproteksi menggunakan prefix NIM sebagai namespace URL.
2. **Suffix Method `_l1H`**: Seluruh method controller dan model menggunakan suffix kode unik untuk membedakan dari boilerplate framework.
3. **CDN-based Frontend**: Tidak ada build pipeline (Webpack/Vite). React, Tailwind, dan Babel dimuat langsung dari CDN untuk mempermudah deployment.
4. **Views sebagai React SPA**: Setiap halaman merupakan komponen React independen yang menerima data dari PHP via `window.__DATA__`.
5. **Kesiapan Menuju Pertemuan 10**:
   - Fondasi tabel `jurnal_umum` dan `jurnal_umum_d` telah siap untuk agregasi Buku Besar (General Ledger).
   - Seluruh mutasi kas masuk (Penjualan) dan kas keluar (BKK) telah memiliki COA yang seimbang untuk penyusunan Laporan Laba Rugi dan Neraca Lajur pada pertemuan berikutnya.
   - Database Docker & Localhost telah sinkron 100% dan dilengkapi automated unit testing (108 tests passing).

---

> **Status Proyek**: 🟢 **Aktif** — Pertemuan 10–11 Selesai 100% (Advanced Aggregation & Executive Dashboard)  
> **Terakhir Diperbarui**: 21 Oktober 2026  
> **Laporan Dibuat**: 19 Oktober 2026
