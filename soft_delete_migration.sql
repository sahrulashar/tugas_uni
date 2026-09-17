-- ================================================================
-- MIGRASI: Tambah kolom is_deleted ke tbrbeli, tbbkk, tbrecord
-- Jalankan satu kali di database kas_keluar
-- ================================================================

ALTER TABLE tbrbeli
    ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '0=aktif, 1=soft-deleted'
    AFTER kete;

ALTER TABLE tbbkk
    ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '0=aktif, 1=soft-deleted'
    AFTER kete;

ALTER TABLE tbrecord
    ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '0=aktif, 1=soft-deleted'
    AFTER ket;
