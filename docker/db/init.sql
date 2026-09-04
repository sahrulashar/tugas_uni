-- =======================================================
-- Inisialisasi Skema Database ERP Kas Keluar
-- Otomatis dieksekusi saat container MySQL pertama kali dibuat
-- =======================================================

CREATE DATABASE IF NOT EXISTS `kas_keluar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kas_keluar`;

-- 1. TABEL CHART OF ACCOUNTS (COA)
CREATE TABLE IF NOT EXISTS `coa` (
  `id_coa` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_coa` VARCHAR(50) NOT NULL UNIQUE,
  `nama_coa` VARCHAR(255) NOT NULL,
  `tipe_akun` ENUM('Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Beban') NOT NULL,
  `saldo_normal` ENUM('Debit', 'Kredit') NOT NULL,
  `status` ENUM('Aktif', 'Tidak Aktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABEL SUPPLIER
CREATE TABLE IF NOT EXISTS `supplier` (
  `id_supplier` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_supplier` VARCHAR(50) NOT NULL UNIQUE,
  `nama_supplier` VARCHAR(255) NOT NULL,
  `alamat` TEXT NULL,
  `status` ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABEL KARYAWAN
CREATE TABLE IF NOT EXISTS `karyawan` (
  `id_karyawan` INT AUTO_INCREMENT PRIMARY KEY,
  `nip` VARCHAR(50) NOT NULL UNIQUE,
  `nama_karyawan` VARCHAR(255) NOT NULL,
  `jabatan` VARCHAR(100) NULL,
  `status` ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- DATA AWAL (SEED SAMPLE DATA)
-- =======================================================

-- Data Awal COA
INSERT INTO `coa` (`kode_coa`, `nama_coa`, `tipe_akun`, `saldo_normal`, `status`) VALUES
('1111', 'Kas Utama (Brankas)', 'Aset', 'Debit', 'Aktif'),
('1112', 'Kas Operasional Tangan', 'Aset', 'Debit', 'Aktif'),
('1113', 'Kas Digital Gopay', 'Aset', 'Debit', 'Aktif'),
('1114', 'Kas Digital DANA', 'Aset', 'Debit', 'Aktif'),
('1115', 'Kas Digital ShopeePay', 'Aset', 'Debit', 'Aktif'),
('1116', 'Bank BCA Operasional', 'Aset', 'Debit', 'Aktif'),
('1117', 'Bank Mandiri Giro', 'Aset', 'Debit', 'Aktif'),
('1118', 'Bank BRI Simpanan', 'Aset', 'Debit', 'Aktif'),
('1119', 'Bank BNI Bisnis', 'Aset', 'Debit', 'Aktif'),
('2111', 'Utang Usaha Vendor', 'Liabilitas', 'Kredit', 'Aktif'),
('2112', 'Utang Gaji Karyawan', 'Liabilitas', 'Kredit', 'Aktif'),
('3111', 'Modal Pemilik', 'Ekuitas', 'Kredit', 'Aktif'),
('4111', 'Pendapatan Jasa & Operasional', 'Pendapatan', 'Kredit', 'Aktif'),
('5101', 'Beban Gaji & Upah', 'Beban', 'Debit', 'Aktif'),
('5102', 'Beban Listrik, Air & Internet', 'Beban', 'Debit', 'Aktif'),
('5103', 'Beban Alat Tulis Kantor (ATK)', 'Beban', 'Debit', 'Aktif'),
('5104', 'Beban Pemeliharaan & Perbaikan', 'Beban', 'Debit', 'Aktif')
ON DUPLICATE KEY UPDATE `nama_coa` = VALUES(`nama_coa`);

-- Data Awal Supplier
INSERT INTO `supplier` (`kode_supplier`, `nama_supplier`, `alamat`, `status`) VALUES
('SUP-001', 'PT Sumber Makmur ATK', 'Jl. Gajah Mada No. 12, Jakarta Barat', 'Aktif'),
('SUP-002', 'CV Graha Mandiri Teknik', 'Kawasan Industri Cikarang Blok B-4', 'Aktif'),
('SUP-003', 'PT Digital Solusindo Pratama', 'Gedung Cyber 2 Lt. 8, Kuningan, Jakarta Selatan', 'Aktif'),
('SUP-004', 'Toko Berkah Logistik', 'Jl. Raya Bogor KM 28, Depok', 'Nonaktif')
ON DUPLICATE KEY UPDATE `nama_supplier` = VALUES(`nama_supplier`);

-- Data Awal Karyawan
INSERT INTO `karyawan` (`nip`, `nama_karyawan`, `jabatan`, `status`) VALUES
('1990010101', 'Budi Santoso, S.Ak', 'Finance Manager', 'Aktif'),
('1993051402', 'Siti Rahmawati', 'Staff Kasir & Pembukuan', 'Aktif'),
('1995120803', 'Ahmad Fauzi', 'Purchasing & Logistik', 'Aktif'),
('1998032204', 'Dewi Lestari', 'Administrasi Umum', 'Aktif'),
('1992071905', 'Hendra Setiawan', 'Driver Operasional', 'Nonaktif')
ON DUPLICATE KEY UPDATE `nama_karyawan` = VALUES(`nama_karyawan`);
