-- ================================================================
-- MIGRASI: Tambah kolom saldo_awal ke tabel coa
-- Jalankan satu kali di database kas_keluar
--
-- Saldo akhir TIDAK disimpan, tetapi dihitung dari saldo_awal + mutasi
-- BKK (tbbkk_d yang header-nya belum di-soft-delete):
--   id_coa    (akun tujuan) = DEBIT
--   id_coa_kb (kas/bank)    = KREDIT
--   saldo normal Debit  : saldo_awal + debit - kredit
--   saldo normal Kredit : saldo_awal + kredit - debit
-- ================================================================

USE kas_keluar;

ALTER TABLE coa
    ADD COLUMN saldo_awal DECIMAL(15,2) NOT NULL DEFAULT 0.00
        COMMENT 'Saldo awal akun (sesuai saldo normal)'
    AFTER tipe;

DESCRIBE coa;
