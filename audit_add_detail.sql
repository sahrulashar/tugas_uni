-- ================================================================
-- MIGRASI: Tambah kolom detail_perubahan ke tabel audit_log
-- Jalankan satu kali di database kas_keluar
-- ================================================================

ALTER TABLE audit_log
    ADD COLUMN detail_perubahan TEXT NULL COMMENT 'JSON before/after: {"before":{...},"after":{...}}'
    AFTER record_id;
