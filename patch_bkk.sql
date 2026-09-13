-- ============================================================
--  PATCH: Fix tbbkk_d untuk modul Bukti Kas Keluar (BKK)
--  Jalankan file ini di database laptop client
--  Database: kas_keluar
-- ============================================================

USE kas_keluar;

-- Ubah id_rbeli_d agar boleh NULL (referensi Rencana Beli opsional)
ALTER TABLE tbbkk_d
    MODIFY COLUMN id_rbeli_d int NULL;

-- Verifikasi perubahan
DESCRIBE tbbkk_d;
