-- ============================================================
--  SQL MIGRATION / PATCH — Database Transaction & Audit Trail
--  Tabel: tbbeli, tbbeli_d, audit_log
--  Database: kas_keluar
-- ============================================================

USE kas_keluar;

-- ────────────────────────────────────────────────────────────
--  1. TABEL MASTER PEMBELIAN (tbbeli)
--
--  Kolom is_deleted digunakan untuk SOFT DELETE:
--  → nilai 0 = aktif, nilai 1 = sudah dihapus (tapi data tetap ada)
--  → TIDAK boleh menggunakan DELETE FROM untuk menghapus data
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tbbeli (
    id         INT          NOT NULL AUTO_INCREMENT,
    no_beli    VARCHAR(30)  NOT NULL UNIQUE COMMENT 'Nomor transaksi pembelian',
    tgl        DATE         NOT NULL             COMMENT 'Tanggal transaksi',
    toko       VARCHAR(100) NOT NULL             COMMENT 'Nama toko/vendor',
    is_deleted TINYINT(1)   NOT NULL DEFAULT 0   COMMENT '0=aktif, 1=soft-deleted',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Header transaksi pembelian';


-- ────────────────────────────────────────────────────────────
--  2. TABEL DETAIL PEMBELIAN (tbbeli_d)
--
--  Setiap baris detail terhubung ke satu header via id_beli.
--  Validasi: kolom harga TIDAK boleh bernilai negatif
--  (dijaga di aplikasi level — lihat TbBeliModel::simpanTransaksiPembelian)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tbbeli_d (
    id       INT           NOT NULL AUTO_INCREMENT,
    id_beli  INT           NOT NULL             COMMENT 'FK ke tbbeli.id',
    barng    VARCHAR(150)  NOT NULL             COMMENT 'Nama barang',
    harga    DECIMAL(15,2) NOT NULL DEFAULT 0   COMMENT 'Harga barang (tidak boleh negatif)',
    PRIMARY KEY (id),
    CONSTRAINT fk_tbbeli_d_beli
        FOREIGN KEY (id_beli) REFERENCES tbbeli (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Detail item transaksi pembelian';


-- ────────────────────────────────────────────────────────────
--  3. TABEL AUDIT LOG (audit_log)
--
--  Mencatat setiap aksi penting yang dilakukan user:
--   aksi: 'TAMBAH' | 'EDIT' | 'SOFT_DELETE'
--
--  Cara baca:
--   "Pada [waktu], user [user_id] melakukan aksi [aksi]
--    terhadap record id=[record_id] di tabel [tabel_terdampak]"
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
    id               INT          NOT NULL AUTO_INCREMENT,
    user_id          INT          NOT NULL             COMMENT 'ID user yang melakukan aksi',
    aksi             VARCHAR(20)  NOT NULL             COMMENT 'TAMBAH | EDIT | SOFT_DELETE',
    tabel_terdampak  VARCHAR(50)  NOT NULL             COMMENT 'Nama tabel yang diubah',
    record_id        INT          NOT NULL             COMMENT 'ID record yang terdampak',
    waktu            DATETIME     NOT NULL             COMMENT 'Waktu aksi dilakukan (NOW())',
    PRIMARY KEY (id),
    INDEX idx_audit_tabel_record (tabel_terdampak, record_id),
    INDEX idx_audit_user        (user_id),
    INDEX idx_audit_waktu       (waktu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit trail semua aksi pengguna pada tabel master';


-- ────────────────────────────────────────────────────────────
--  DATA CONTOH — Uji Coba Manual
-- ────────────────────────────────────────────────────────────

-- Transaksi valid
INSERT IGNORE INTO tbbeli (no_beli, tgl, toko, is_deleted) VALUES
    ('BL-001', '2026-09-15', 'Toko Sumber Makmur', 0),
    ('BL-002', '2026-09-16', 'Toko Berkah Jaya',   0);

-- Detail valid (harga semua positif)
INSERT IGNORE INTO tbbeli_d (id, id_beli, barng, harga) VALUES
    (1, 1, 'Beras 5kg',   60000),
    (2, 1, 'Minyak 2L',   38000),
    (3, 1, 'Gula 1kg',    16000),
    (4, 2, 'Tepung 1kg',  12000),
    (5, 2, 'Telur 1kg',   28000);

-- Contoh audit log
INSERT IGNORE INTO audit_log (id, user_id, aksi, tabel_terdampak, record_id, waktu) VALUES
    (1, 1, 'TAMBAH',      'tbbeli', 1, NOW()),
    (2, 1, 'TAMBAH',      'tbbeli', 2, NOW()),
    (3, 1, 'SOFT_DELETE', 'tbbeli', 2, NOW());


-- ────────────────────────────────────────────────────────────
--  QUERY VERIFIKASI ROLLBACK
--  Jalankan ini SETELAH uji coba untuk memastikan rollback bekerja:
-- ────────────────────────────────────────────────────────────

-- Cek apakah 'BL-TEST' masuk ke tbbeli (harusnya KOSONG setelah rollback):
-- SELECT * FROM tbbeli WHERE no_beli = 'BL-TEST';

-- Cek apakah detail 'BL-TEST' masuk ke tbbeli_d (harusnya KOSONG):
-- SELECT d.* FROM tbbeli_d d
-- JOIN tbbeli m ON m.id = d.id_beli
-- WHERE m.no_beli = 'BL-TEST';

-- Lihat semua audit log:
-- SELECT * FROM audit_log ORDER BY waktu DESC;

-- Lihat semua data aktif (is_deleted = 0):
-- SELECT * FROM tbbeli WHERE is_deleted = 0;

-- Lihat data yang sudah di-soft-delete:
-- SELECT * FROM tbbeli WHERE is_deleted = 1;
