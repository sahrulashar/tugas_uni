-- ================================================================
--  MIGRATION: Tambah Kolom Detail ke Tabel audit_log
--  Database : kas_keluar
--  Jalankan : Satu kali saja di phpMyAdmin / MySQL CLI
-- ================================================================

USE kas_keluar;

-- ----------------------------------------------------------------
--  Tambah kolom nama_user (snapshot nama user saat aksi terjadi)
-- ----------------------------------------------------------------
ALTER TABLE audit_log
    ADD COLUMN nama_user VARCHAR(100) NULL
        COMMENT 'Nama user saat aksi (snapshot, tidak berubah jika data user diubah)'
    AFTER user_id;

-- ----------------------------------------------------------------
--  Tambah kolom modul (kategori fitur: Master, Transaksi, dll)
-- ----------------------------------------------------------------
ALTER TABLE audit_log
    ADD COLUMN modul VARCHAR(50) NULL
        COMMENT 'Kategori modul: Master | Transaksi | Laporan | dll'
    AFTER aksi;

-- ----------------------------------------------------------------
--  Tambah kolom keterangan (deskripsi teks bebas yang bisa dibaca manusia)
-- ----------------------------------------------------------------
ALTER TABLE audit_log
    ADD COLUMN keterangan VARCHAR(255) NULL
        COMMENT 'Deskripsi singkat aksi, contoh: Tambah Supplier: PT Maju Jaya'
    AFTER record_id;

-- ----------------------------------------------------------------
--  Tambah kolom url (endpoint/halaman yang diakses)
-- ----------------------------------------------------------------
ALTER TABLE audit_log
    ADD COLUMN url VARCHAR(500) NULL
        COMMENT 'URL endpoint yang dipanggil saat aksi terjadi'
    AFTER keterangan;

-- ----------------------------------------------------------------
--  Perlebar kolom aksi (dari VARCHAR(20) ke VARCHAR(30))
--  agar cukup untuk aksi panjang seperti 'SOFT_DELETE'
-- ----------------------------------------------------------------
ALTER TABLE audit_log
    MODIFY COLUMN aksi VARCHAR(30) NOT NULL
        COMMENT 'TAMBAH | EDIT | HAPUS | NONAKTIF | AKTIF | SOFT_DELETE';

-- ----------------------------------------------------------------
--  Tambah index untuk modul (mempercepat filter per modul)
-- ----------------------------------------------------------------
ALTER TABLE audit_log
    ADD INDEX idx_audit_modul (modul);

-- ================================================================
--  VERIFIKASI — Jalankan setelah ALTER selesai
--  Hasil: harus tampil semua kolom baru
-- ================================================================
-- DESCRIBE audit_log;

-- ================================================================
--  Struktur akhir tabel audit_log setelah migration:
--
--  id               INT          AUTO_INCREMENT PK
--  user_id          INT          ID user
--  nama_user        VARCHAR(100) Nama user (snapshot)           ← BARU
--  aksi             VARCHAR(30)  TAMBAH|EDIT|HAPUS|NONAKTIF|AKTIF|SOFT_DELETE
--  modul            VARCHAR(50)  Kategori modul                 ← BARU
--  tabel_terdampak  VARCHAR(50)  Nama tabel yang berubah
--  record_id        INT          ID record yang terdampak
--  keterangan       VARCHAR(255) Deskripsi teks bebas           ← BARU
--  user_agent       VARCHAR(500) Browser/device info            ← BARU
--  url              VARCHAR(500) Endpoint yang diakses          ← BARU
--  detail_perubahan TEXT         JSON before/after
--  waktu            DATETIME     Timestamp aksi
-- ================================================================
