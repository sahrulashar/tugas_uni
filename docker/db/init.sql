-- MySQL dump 10.13  Distrib 8.0.31, for Win64 (x86_64)
--
-- Host: localhost    Database: kas_keluar
-- ------------------------------------------------------
-- Server version	8.0.31

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `kas_keluar`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `kas_keluar` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `kas_keluar`;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'ID user yang melakukan aksi',
  `nama_user` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama user saat aksi (snapshot, tidak berubah jika data user diubah)',
  `aksi` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'TAMBAH | EDIT | HAPUS | NONAKTIF | AKTIF | SOFT_DELETE',
  `modul` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Kategori modul: Master | Transaksi | Laporan | dll',
  `tabel_terdampak` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama tabel yang diubah',
  `record_id` int NOT NULL COMMENT 'ID record yang terdampak',
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Deskripsi singkat aksi, contoh: Tambah Supplier: PT Maju Jaya',
  `url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL endpoint yang dipanggil saat aksi terjadi',
  `detail_perubahan` text COLLATE utf8mb4_unicode_ci,
  `waktu` datetime NOT NULL COMMENT 'Waktu aksi dilakukan (NOW())',
  PRIMARY KEY (`id`),
  KEY `idx_audit_tabel_record` (`tabel_terdampak`,`record_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_waktu` (`waktu`),
  KEY `idx_audit_modul` (`modul`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail semua aksi pengguna pada tabel master';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,0,'System','EDIT',NULL,'tbrbeli',2,NULL,'http://localhost:8080/index.php/aktivitas/aktivitas1/update','{\"before\":{\"id\":\"2\",\"no_rbeli\":\"RB-2026-02\",\"tgl\":\"2026-09-17\",\"id_supp\":\"4\",\"kete\":\"Beli Barang\",\"is_deleted\":\"0\"},\"after\":{\"no_rbeli\":\"RB-2026-02\",\"tgl\":\"2026-09-17\",\"id_supp\":4,\"kete\":\"Beli Barang Bagus\"}}','2026-09-21 13:01:28'),(2,1,'Administrator','EDIT',NULL,'tbuser',2,NULL,'http://localhost:8080/index.php/46124026/rbac/user/update','{\"before\":{\"kode\":\"lisa\",\"nama\":\"Meilisa\"},\"after\":{\"kode\":\"lisa\",\"nama\":\"Meilisa\"}}','2026-09-30 10:27:38'),(3,1,'Administrator','EDIT',NULL,'coa',4,NULL,'http://localhost:8080/index.php/46124026/kas_keluar/update_coa_l1H','{\"before\":{\"id\":\"4\",\"kode_coa\":\"1112\",\"nama_coa\":\"Kas Kecil\",\"saldo_normal\":\"Debit\",\"is_header\":\"D\",\"tipe\":\"kasbank\",\"saldo_awal\":\"0.00\",\"is_off\":\"0\"},\"after\":{\"kode_coa\":\"1112\",\"nama_coa\":\"Kas Kecil\",\"saldo_normal\":\"Debit\",\"is_header\":\"D\",\"tipe\":\"kasbank\",\"saldo_awal\":100000}}','2026-10-03 13:35:53'),(4,1,'Administrator','TAMBAH',NULL,'tbrbeli',3,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas1/simpan_l1H','{\"after\":{\"no_rbeli\":\"RB002\",\"tgl\":\"2026-10-03\",\"id_supp\":10,\"kete\":\"Beli\"}}','2026-10-03 13:37:13'),(5,1,'Administrator','TAMBAH',NULL,'tbbkk',4,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/simpan_l1H','{\"after\":{\"no_bkk\":\"BKK021\",\"tgl\":\"2026-10-03\",\"kete\":\"Ket\"}}','2026-10-03 13:40:19'),(6,1,'Administrator','SOFT_DELETE',NULL,'tbbkk',4,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/hapus_l1H/4','{\"before\":{\"id\":\"4\",\"no_bkk\":\"BKK021\",\"tgl\":\"2026-10-03\",\"kete\":\"Ket\",\"is_deleted\":\"0\"}}','2026-10-03 13:44:37'),(7,1,'Administrator','TAMBAH',NULL,'tbbkk',5,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/simpan_l1H','{\"after\":{\"no_bkk\":\"BKK-2026-01\",\"tgl\":\"2026-10-03\",\"kete\":null}}','2026-10-03 13:45:16'),(8,1,'Administrator','TAMBAH',NULL,'tbrbeli',4,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas1/simpan_l1H','{\"after\":{\"no_rbeli\":\"RB-2026-003\",\"tgl\":\"2026-10-03\",\"id_supp\":1,\"kete\":\"Oil\"}}','2026-10-03 13:49:28'),(9,1,'Administrator','TAMBAH',NULL,'tbbkk',6,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/simpan_l1H','{\"after\":{\"no_bkk\":\"BKK-2026-0010\",\"tgl\":\"2026-10-03\",\"kete\":\"AC oil\"}}','2026-10-03 13:51:05'),(10,1,'Administrator','LOGIN','Auth','tbuser',1,'User Administrator (admin) login berhasil.','http://localhost:8080/index.php/login/proses',NULL,'2026-10-04 17:36:24'),(11,1,'Administrator','LOGIN','Auth','tbuser',1,'User Administrator (admin) login berhasil.','http://localhost:8080/index.php/login/proses',NULL,'2026-10-05 19:34:51'),(12,1,'Administrator','LOGIN','Auth','tbuser',1,'User Administrator (admin) login berhasil.','http://localhost:8080/index.php/login/proses',NULL,'2026-10-06 08:17:40'),(13,1,'Administrator','LOGIN','Auth','tbuser',1,'User Administrator (admin) login berhasil.','http://localhost:8080/index.php/login/proses',NULL,'2026-10-06 08:47:03'),(14,1,'Administrator','LOGIN','Auth','tbuser',1,'User Administrator (admin) login berhasil.','http://localhost:8080/index.php/login/proses',NULL,'2026-10-07 01:52:54'),(15,0,'System','TAMBAH','Transaksi','penjualan',2,'Penjualan Kasir Faktur #TEST-UNIT-135402-345 (Netto: Rp 65.000)','https://3bd4muti.site/','{\"after\":{\"no_faktur\":\"TEST-UNIT-135402-345\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Tester Unit P9\",\"total_bruto\":65000,\"diskon\":0,\"total_netto\":65000,\"total_hpp\":45000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 13:54:02\"}}','2026-10-19 13:54:02'),(16,0,'System','TAMBAH','Akuntansi','jurnal_umum',2,'Jurnal otomatis penjualan faktur #TEST-UNIT-135402-345','https://3bd4muti.site/','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"TEST-UNIT-135402-345\"}}','2026-10-19 13:54:02'),(17,0,'System','TAMBAH','Transaksi','penjualan',3,'Penjualan Kasir Faktur #TEST-UNIT-135408-364 (Netto: Rp 65.000)','https://3bd4muti.site/','{\"after\":{\"no_faktur\":\"TEST-UNIT-135408-364\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Tester Unit P9\",\"total_bruto\":65000,\"diskon\":0,\"total_netto\":65000,\"total_hpp\":45000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 13:54:08\"}}','2026-10-19 13:54:08'),(18,0,'System','TAMBAH','Akuntansi','jurnal_umum',3,'Jurnal otomatis penjualan faktur #TEST-UNIT-135408-364','https://3bd4muti.site/','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"TEST-UNIT-135408-364\"}}','2026-10-19 13:54:08'),(19,0,'System','TAMBAH','Transaksi','penjualan',4,'Penjualan Kasir Faktur #TEST-UNIT-135515-476 (Netto: Rp 65.000)','https://3bd4muti.site/','{\"after\":{\"no_faktur\":\"TEST-UNIT-135515-476\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Tester Unit P9\",\"total_bruto\":65000,\"diskon\":0,\"total_netto\":65000,\"total_hpp\":45000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 13:55:15\"}}','2026-10-19 13:55:15'),(20,0,'System','TAMBAH','Akuntansi','jurnal_umum',4,'Jurnal otomatis penjualan faktur #TEST-UNIT-135515-476','https://3bd4muti.site/','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"TEST-UNIT-135515-476\"}}','2026-10-19 13:55:15'),(21,0,'System','TAMBAH','Transaksi','penjualan',5,'Penjualan Kasir Faktur #TEST-UNIT-140655-612 (Netto: Rp 65.000)','http://localhost:8080/','{\"after\":{\"no_faktur\":\"TEST-UNIT-140655-612\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Tester Unit P9\",\"total_bruto\":65000,\"diskon\":0,\"total_netto\":65000,\"total_hpp\":45000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 14:06:55\"}}','2026-10-19 14:06:55'),(22,0,'System','TAMBAH','Akuntansi','jurnal_umum',5,'Jurnal otomatis penjualan faktur #TEST-UNIT-140655-612','http://localhost:8080/','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"TEST-UNIT-140655-612\"}}','2026-10-19 14:06:55'),(23,0,'System','TAMBAH','Transaksi','penjualan',6,'Penjualan Kasir Faktur #TEST-UNIT-140700-742 (Netto: Rp 65.000)','http://localhost:8080/','{\"after\":{\"no_faktur\":\"TEST-UNIT-140700-742\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Tester Unit P9\",\"total_bruto\":65000,\"diskon\":0,\"total_netto\":65000,\"total_hpp\":45000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 14:07:00\"}}','2026-10-19 14:07:00'),(24,0,'System','TAMBAH','Akuntansi','jurnal_umum',6,'Jurnal otomatis penjualan faktur #TEST-UNIT-140700-742','http://localhost:8080/','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"TEST-UNIT-140700-742\"}}','2026-10-19 14:07:00'),(25,0,'System','TAMBAH','Transaksi','penjualan',7,'Penjualan Kasir Faktur #TEST-UNIT-142059-131 (Netto: Rp 65.000)','http://localhost:8080/','{\"after\":{\"no_faktur\":\"TEST-UNIT-142059-131\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Tester Unit P9\",\"total_bruto\":65000,\"diskon\":0,\"total_netto\":65000,\"total_hpp\":45000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 14:20:59\"}}','2026-10-19 14:20:59'),(26,0,'System','TAMBAH','Akuntansi','jurnal_umum',7,'Jurnal otomatis penjualan faktur #TEST-UNIT-142059-131','http://localhost:8080/','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"TEST-UNIT-142059-131\"}}','2026-10-19 14:20:59'),(27,1,'Administrator','LOGIN','Auth','tbuser',1,'User Administrator (admin) login berhasil.','http://localhost:8080/login/proses',NULL,'2026-10-19 14:21:59'),(28,1,'Administrator','TAMBAH','Transaksi','penjualan',8,'Penjualan Kasir Faktur #FK-20261019-C4C1 (Netto: Rp 249.000)','http://localhost:8080/46124026/penjualan/proses_kasir_l1H','{\"after\":{\"no_faktur\":\"FK-20261019-C4C1\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Pelanggan Umum\",\"total_bruto\":249000,\"diskon\":0,\"total_netto\":249000,\"total_hpp\":170000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 14:22:33\"}}','2026-10-19 14:22:33'),(29,1,'Administrator','TAMBAH','Akuntansi','jurnal_umum',8,'Jurnal otomatis penjualan faktur #FK-20261019-C4C1','http://localhost:8080/46124026/penjualan/proses_kasir_l1H','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"FK-20261019-C4C1\"}}','2026-10-19 14:22:33'),(30,1,'Administrator','TAMBAH','Transaksi','penjualan',9,'Penjualan Kasir Faktur #TEST-P9-142305 (Netto: Rp 130.000)','http://localhost:8080/46124026/akuntansi/uji_sukses_l1H','{\"after\":{\"no_faktur\":\"TEST-P9-142305\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Uji Praktikum Mahasiswa P9\",\"total_bruto\":130000,\"diskon\":0,\"total_netto\":130000,\"total_hpp\":90000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 14:23:05\"}}','2026-10-19 14:23:05'),(31,1,'Administrator','TAMBAH','Akuntansi','jurnal_umum',9,'Jurnal otomatis penjualan faktur #TEST-P9-142305','http://localhost:8080/46124026/akuntansi/uji_sukses_l1H','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"TEST-P9-142305\"}}','2026-10-19 14:23:05'),(32,1,'Administrator','TAMBAH','Transaksi','penjualan',10,'Penjualan Kasir Faktur #FK-20261019-B30E (Netto: Rp 139.000)','http://localhost:8080/46124026/penjualan/proses_kasir_l1H','{\"after\":{\"no_faktur\":\"FK-20261019-B30E\",\"tgl_jual\":\"2026-10-19\",\"customer\":\"Pelanggan Umum\",\"total_bruto\":139000,\"diskon\":0,\"total_netto\":139000,\"total_hpp\":95000,\"metode_bayar\":\"Tunai\",\"id_user\":1,\"is_deleted\":0,\"created_at\":\"2026-10-19 14:24:52\"}}','2026-10-19 14:24:52'),(33,1,'Administrator','TAMBAH','Akuntansi','jurnal_umum',10,'Jurnal otomatis penjualan faktur #FK-20261019-B30E','http://localhost:8080/46124026/penjualan/proses_kasir_l1H','{\"after\":{\"sumber\":\"PENJUALAN\",\"no_referensi\":\"FK-20261019-B30E\"}}','2026-10-19 14:24:52');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_barang` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Bahan Baku',
  `harga_beli` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'HPP satuan',
  `harga_jual` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stok` decimal(10,2) NOT NULL DEFAULT '0.00',
  `satuan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pcs',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_barang` (`kode_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES (1,'BRG-001','Kopi Arabika Specialty 250g','Bahan Baku',45000.00,65000.00,30.00,'pck',0,'2026-10-19 13:41:54','2026-10-19 14:24:52'),(2,'BRG-002','Susu Segar Pasteurisasi 1L','Bahan Baku',18000.00,26000.00,48.00,'btl',0,'2026-10-19 13:41:54','2026-10-19 14:24:52'),(3,'BRG-003','Sirup Vanila Gourmet 750ml','Sirup & Flavour',75000.00,110000.00,19.00,'btl',0,'2026-10-19 13:41:54','2026-10-19 14:22:33'),(4,'BRG-004','Gula Aren Cair Organik 1L','Pemanis',32000.00,48000.00,23.00,'btl',0,'2026-10-19 13:41:54','2026-10-19 14:24:52');
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coa`
--

DROP TABLE IF EXISTS `coa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_coa` varchar(20) NOT NULL,
  `nama_coa` varchar(100) NOT NULL,
  `saldo_normal` enum('Debit','Kredit') NOT NULL,
  `is_header` enum('H','D') DEFAULT 'D',
  `tipe` varchar(50) DEFAULT NULL,
  `saldo_awal` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Saldo awal akun (sesuai saldo normal)',
  `is_off` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coa`
--

LOCK TABLES `coa` WRITE;
/*!40000 ALTER TABLE `coa` DISABLE KEYS */;
INSERT INTO `coa` VALUES (1,'1000','ASET','Debit','H',NULL,0.00,0),(2,'1100','ASET LANCAR','Debit','H',NULL,0.00,0),(3,'1111','Kas Tangan','Debit','D','kasbank',5000000.00,0),(4,'1112','Kas Kecil','Debit','D','kasbank',1000000.00,0),(5,'1113','Kas Gopay','Debit','D','kasbank',500000.00,0),(6,'1114','Kas DANA','Debit','D','kasbank',500000.00,0),(7,'1115','Kas ShopeePay','Debit','D','kasbank',250000.00,0),(8,'1116','Kas BCA','Debit','D','kasbank',25000000.00,0),(9,'1117','Kas Mandiri','Debit','D','kasbank',10000000.00,0),(10,'1118','Kas BRI','Debit','D','kasbank',5000000.00,0),(11,'1119','Kas BNI','Debit','D','kasbank',5000000.00,0),(12,'1120','Kas Paylater','Debit','D','kasbank',2000000.00,0),(13,'2000','KEWAJIBAN','Kredit','H',NULL,0.00,0),(14,'2100','KEWAJIBAN LANCAR','Kredit','H',NULL,0.00,0),(15,'2111','Utang Usaha','Kredit','D','operasional',0.00,0),(16,'2112','Utang Gaji','Kredit','D','operasional',0.00,0),(17,'2113','Utang Pajak','Kredit','D','operasional',0.00,0),(18,'3000','EKUITAS','Kredit','H',NULL,0.00,0),(19,'3111','Modal Pemilik','Kredit','D','pendanaan',59250000.00,0),(20,'4000','PENDAPATAN','Kredit','H',NULL,0.00,0),(21,'4111','Pendapatan Jasa','Kredit','D','operasional',0.00,0),(22,'5000','BEBAN','Debit','H',NULL,0.00,0),(23,'5111','Pembelian','Debit','D','operasional',0.00,0),(24,'1141','Utang Usaha','Debit','D','operasional',0.00,0),(25,'1131','Persediaan Barang Dagang','Debit','D','operasional',5000000.00,0),(26,'4110','Pendapatan Penjualan','Kredit','D','operasional',0.00,0),(27,'5120','Beban Pokok Penjualan (HPP)','Debit','D','operasional',0.00,0);
/*!40000 ALTER TABLE `coa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_umum`
--

DROP TABLE IF EXISTS `jurnal_umum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal_umum` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `no_jurnal` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `tgl_jurnal` date NOT NULL,
  `no_referensi` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `sumber` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENJUALAN',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `total_debit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_kredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_jurnal` (`no_jurnal`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_umum`
--

LOCK TABLES `jurnal_umum` WRITE;
/*!40000 ALTER TABLE `jurnal_umum` DISABLE KEYS */;
INSERT INTO `jurnal_umum` VALUES (2,'JU-20261019-219AB3','2026-10-19','TEST-UNIT-135402-345','PENJUALAN','Jurnal otomatis penjualan faktur #TEST-UNIT-135402-345',110000.00,110000.00,'2026-10-19 13:54:02'),(3,'JU-20261019-E55651','2026-10-19','TEST-UNIT-135408-364','PENJUALAN','Jurnal otomatis penjualan faktur #TEST-UNIT-135408-364',110000.00,110000.00,'2026-10-19 13:54:08'),(4,'JU-20261019-CF915B','2026-10-19','TEST-UNIT-135515-476','PENJUALAN','Jurnal otomatis penjualan faktur #TEST-UNIT-135515-476',110000.00,110000.00,'2026-10-19 13:55:15'),(5,'JU-20261019-8CA51F','2026-10-19','TEST-UNIT-140655-612','PENJUALAN','Jurnal otomatis penjualan faktur #TEST-UNIT-140655-612',110000.00,110000.00,'2026-10-19 14:06:55'),(6,'JU-20261019-D2565D','2026-10-19','TEST-UNIT-140700-742','PENJUALAN','Jurnal otomatis penjualan faktur #TEST-UNIT-140700-742',110000.00,110000.00,'2026-10-19 14:07:00'),(7,'JU-20261019-056561','2026-10-19','TEST-UNIT-142059-131','PENJUALAN','Jurnal otomatis penjualan faktur #TEST-UNIT-142059-131',110000.00,110000.00,'2026-10-19 14:20:59'),(8,'JU-20261019-7AB994','2026-10-19','FK-20261019-C4C1','PENJUALAN','Jurnal otomatis penjualan faktur #FK-20261019-C4C1',419000.00,419000.00,'2026-10-19 14:22:33'),(9,'JU-20261019-13A7BF','2026-10-19','TEST-P9-142305','PENJUALAN','Jurnal otomatis penjualan faktur #TEST-P9-142305',220000.00,220000.00,'2026-10-19 14:23:05'),(10,'JU-20261019-997F70','2026-10-19','FK-20261019-B30E','PENJUALAN','Jurnal otomatis penjualan faktur #FK-20261019-B30E',234000.00,234000.00,'2026-10-19 14:24:52');
/*!40000 ALTER TABLE `jurnal_umum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_umum_d`
--

DROP TABLE IF EXISTS `jurnal_umum_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal_umum_d` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_jurnal` int unsigned NOT NULL,
  `kode_coa` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_coa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kredit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_jurnal` (`id_jurnal`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_umum_d`
--

LOCK TABLES `jurnal_umum_d` WRITE;
/*!40000 ALTER TABLE `jurnal_umum_d` DISABLE KEYS */;
INSERT INTO `jurnal_umum_d` VALUES (5,2,'1111','Kas Tangan',65000.00,0.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-135402-345'),(6,2,'4110','Pendapatan Penjualan',0.00,65000.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-135402-345'),(7,2,'5120','Beban Pokok Penjualan (HPP)',45000.00,0.00,'Pengakuan HPP barang terjual'),(8,2,'1131','Persediaan Barang Dagang',0.00,45000.00,'Pengurangan persediaan barang'),(9,3,'1111','Kas Tangan',65000.00,0.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-135408-364'),(10,3,'4110','Pendapatan Penjualan',0.00,65000.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-135408-364'),(11,3,'5120','Beban Pokok Penjualan (HPP)',45000.00,0.00,'Pengakuan HPP barang terjual'),(12,3,'1131','Persediaan Barang Dagang',0.00,45000.00,'Pengurangan persediaan barang'),(13,4,'1111','Kas Tangan',65000.00,0.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-135515-476'),(14,4,'4110','Pendapatan Penjualan',0.00,65000.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-135515-476'),(15,4,'5120','Beban Pokok Penjualan (HPP)',45000.00,0.00,'Pengakuan HPP barang terjual'),(16,4,'1131','Persediaan Barang Dagang',0.00,45000.00,'Pengurangan persediaan barang'),(17,5,'1111','Kas Tangan',65000.00,0.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-140655-612'),(18,5,'4110','Pendapatan Penjualan',0.00,65000.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-140655-612'),(19,5,'5120','Beban Pokok Penjualan (HPP)',45000.00,0.00,'Pengakuan HPP barang terjual'),(20,5,'1131','Persediaan Barang Dagang',0.00,45000.00,'Pengurangan persediaan barang'),(21,6,'1111','Kas Tangan',65000.00,0.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-140700-742'),(22,6,'4110','Pendapatan Penjualan',0.00,65000.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-140700-742'),(23,6,'5120','Beban Pokok Penjualan (HPP)',45000.00,0.00,'Pengakuan HPP barang terjual'),(24,6,'1131','Persediaan Barang Dagang',0.00,45000.00,'Pengurangan persediaan barang'),(25,7,'1111','Kas Tangan',65000.00,0.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-142059-131'),(26,7,'4110','Pendapatan Penjualan',0.00,65000.00,'Jurnal otomatis penjualan faktur #TEST-UNIT-142059-131'),(27,7,'5120','Beban Pokok Penjualan (HPP)',45000.00,0.00,'Pengakuan HPP barang terjual'),(28,7,'1131','Persediaan Barang Dagang',0.00,45000.00,'Pengurangan persediaan barang'),(29,8,'1111','Kas Tangan',249000.00,0.00,'Jurnal otomatis penjualan faktur #FK-20261019-C4C1'),(30,8,'4110','Pendapatan Penjualan',0.00,249000.00,'Jurnal otomatis penjualan faktur #FK-20261019-C4C1'),(31,8,'5120','Beban Pokok Penjualan (HPP)',170000.00,0.00,'Pengakuan HPP barang terjual'),(32,8,'1131','Persediaan Barang Dagang',0.00,170000.00,'Pengurangan persediaan barang'),(33,9,'1111','Kas Tangan',130000.00,0.00,'Jurnal otomatis penjualan faktur #TEST-P9-142305'),(34,9,'4110','Pendapatan Penjualan',0.00,130000.00,'Jurnal otomatis penjualan faktur #TEST-P9-142305'),(35,9,'5120','Beban Pokok Penjualan (HPP)',90000.00,0.00,'Pengakuan HPP barang terjual'),(36,9,'1131','Persediaan Barang Dagang',0.00,90000.00,'Pengurangan persediaan barang'),(37,10,'1111','Kas Tangan',139000.00,0.00,'Jurnal otomatis penjualan faktur #FK-20261019-B30E'),(38,10,'4110','Pendapatan Penjualan',0.00,139000.00,'Jurnal otomatis penjualan faktur #FK-20261019-B30E'),(39,10,'5120','Beban Pokok Penjualan (HPP)',95000.00,0.00,'Pengakuan HPP barang terjual'),(40,10,'1131','Persediaan Barang Dagang',0.00,95000.00,'Pengurangan persediaan barang');
/*!40000 ALTER TABLE `jurnal_umum_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawan`
--

DROP TABLE IF EXISTS `karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `karyawan` (
  `id_karyawan` int NOT NULL AUTO_INCREMENT,
  `nip` varchar(20) NOT NULL,
  `nama_karyawan` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  PRIMARY KEY (`id_karyawan`),
  UNIQUE KEY `nip` (`nip`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawan`
--

LOCK TABLES `karyawan` WRITE;
/*!40000 ALTER TABLE `karyawan` DISABLE KEYS */;
INSERT INTO `karyawan` VALUES (1,'K001','Drs. Hidayanto','Penyetuju','Aktif'),(2,'K002','Mariana R','Petugas Pembayaran','Aktif'),(3,'K003','Sri','Petugas Pembukuan','Aktif'),(4,'K004','Muhammad','Petugas Pembayaran','Aktif'),(5,'K005','Akmal','Petugas Pembukuan','Aktif'),(6,'K006','Dr. Andi Wijaya','Penyetuju','Aktif'),(7,'K007','Siti Rahma, S.E.','Petugas Pembayaran','Aktif'),(8,'K008','Budi Santoso','Petugas Pembukuan','Aktif'),(9,'K009','Dewi Lestari','Petugas Pembayaran','Aktif'),(10,'K010','Rudi Hartono','Penyetuju','Aktif'),(11,'K011','Nia Kurnia','Petugas Pembukuan','Aktif'),(12,'K012','Fajar Pratama','Petugas Pembayaran','Aktif');
/*!40000 ALTER TABLE `karyawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026-09-30-000001','App\\Database\\Migrations\\CreateTbuser','default','App',1790760884,1),(2,'2026-09-30-000002','App\\Database\\Migrations\\CreateTblaman','default','App',1790760884,1),(3,'2026-09-30-000003','App\\Database\\Migrations\\CreateTbakses','default','App',1790760884,1),(4,'2026-10-19-000004','App\\Database\\Migrations\\CreatePertemuan9Tables','default','App',1792417315,2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `no_faktur` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `tgl_jual` date NOT NULL,
  `customer` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pelanggan Umum',
  `total_bruto` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_netto` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_hpp` decimal(15,2) NOT NULL DEFAULT '0.00',
  `metode_bayar` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Tunai',
  `id_user` int NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_faktur` (`no_faktur`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (2,'TEST-UNIT-135402-345','2026-10-19','Tester Unit P9',65000.00,0.00,65000.00,45000.00,'Tunai',1,0,'2026-10-19 13:54:02'),(3,'TEST-UNIT-135408-364','2026-10-19','Tester Unit P9',65000.00,0.00,65000.00,45000.00,'Tunai',1,0,'2026-10-19 13:54:08'),(4,'TEST-UNIT-135515-476','2026-10-19','Tester Unit P9',65000.00,0.00,65000.00,45000.00,'Tunai',1,0,'2026-10-19 13:55:15'),(5,'TEST-UNIT-140655-612','2026-10-19','Tester Unit P9',65000.00,0.00,65000.00,45000.00,'Tunai',1,0,'2026-10-19 14:06:55'),(6,'TEST-UNIT-140700-742','2026-10-19','Tester Unit P9',65000.00,0.00,65000.00,45000.00,'Tunai',1,0,'2026-10-19 14:07:00'),(7,'TEST-UNIT-142059-131','2026-10-19','Tester Unit P9',65000.00,0.00,65000.00,45000.00,'Tunai',1,0,'2026-10-19 14:20:59'),(8,'FK-20261019-C4C1','2026-10-19','Pelanggan Umum',249000.00,0.00,249000.00,170000.00,'Tunai',1,0,'2026-10-19 14:22:33'),(9,'TEST-P9-142305','2026-10-19','Uji Praktikum Mahasiswa P9',130000.00,0.00,130000.00,90000.00,'Tunai',1,0,'2026-10-19 14:23:05'),(10,'FK-20261019-B30E','2026-10-19','Pelanggan Umum',139000.00,0.00,139000.00,95000.00,'Tunai',1,0,'2026-10-19 14:24:52');
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan_d`
--

DROP TABLE IF EXISTS `penjualan_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan_d` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_penjualan` int unsigned NOT NULL,
  `kode_barang` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_barang` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `hpp_satuan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id_penjualan` (`id_penjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan_d`
--

LOCK TABLES `penjualan_d` WRITE;
/*!40000 ALTER TABLE `penjualan_d` DISABLE KEYS */;
INSERT INTO `penjualan_d` VALUES (2,2,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(3,3,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(4,4,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(5,5,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(6,6,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(7,7,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(8,8,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(9,8,'BRG-002','Susu Segar Pasteurisasi 1L',1.00,26000.00,18000.00,26000.00),(10,8,'BRG-003','Sirup Vanila Gourmet 750ml',1.00,110000.00,75000.00,110000.00),(11,8,'BRG-004','Gula Aren Cair Organik 1L',1.00,48000.00,32000.00,48000.00),(12,9,'BRG-001','Kopi Arabika Specialty 250g',2.00,65000.00,45000.00,130000.00),(13,10,'BRG-001','Kopi Arabika Specialty 250g',1.00,65000.00,45000.00,65000.00),(14,10,'BRG-002','Susu Segar Pasteurisasi 1L',1.00,26000.00,18000.00,26000.00),(15,10,'BRG-004','Gula Aren Cair Organik 1L',1.00,48000.00,32000.00,48000.00);
/*!40000 ALTER TABLE `penjualan_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier` (
  `id_supplier` int NOT NULL AUTO_INCREMENT,
  `kode_supplier` varchar(20) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  PRIMARY KEY (`id_supplier`),
  UNIQUE KEY `kode_supplier` (`kode_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` VALUES (1,'SUP001','CV Aci Oil','Yogyakarta','Aktif'),(2,'SUP002','PT Ansyar','Bandung','Aktif'),(3,'SUP003','PT Cahaya','Surabaya','Aktif'),(4,'SUP004','CV Makmur','Soppeng','Aktif'),(5,'SUP005','PT Indofood','Jakarta','Aktif'),(6,'SUP006','CV Sumber Makmur','Semarang','Aktif'),(7,'SUP007','PT Tunas Jaya','Medan','Aktif'),(8,'SUP008','CV Berkat Abadi','Makassar','Aktif'),(9,'SUP009','PT Sinar Terang','Bali','Aktif'),(10,'SUP010','UD Mulya Jaya','Malang','Aktif');
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbakses`
--

DROP TABLE IF EXISTS `tbakses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbakses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned NOT NULL,
  `id_laman` int unsigned NOT NULL,
  `is_off` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=aktif, 1=dicabut',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_user_id_laman` (`id_user`,`id_laman`),
  KEY `tbakses_id_laman_foreign` (`id_laman`),
  CONSTRAINT `tbakses_id_laman_foreign` FOREIGN KEY (`id_laman`) REFERENCES `tblaman` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tbakses_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `tbuser` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbakses`
--

LOCK TABLES `tbakses` WRITE;
/*!40000 ALTER TABLE `tbakses` DISABLE KEYS */;
INSERT INTO `tbakses` VALUES (1,1,1,0),(2,1,2,0),(3,1,3,0),(4,1,4,0),(5,1,5,0),(6,1,6,0),(7,1,7,0),(8,1,8,0),(9,1,9,0),(10,1,10,0),(11,1,11,0),(12,1,12,0),(13,1,13,0),(14,1,14,0),(15,1,15,0),(16,1,16,0),(17,1,17,0),(18,1,18,0),(19,1,19,0),(20,1,20,0),(21,1,21,0),(22,1,22,0),(23,1,23,0),(24,1,24,0),(25,1,25,0),(26,1,26,0),(27,1,27,0),(28,1,28,0),(29,1,29,0),(30,1,30,0),(31,1,31,0),(32,1,32,0),(33,1,33,0),(34,1,34,0),(35,1,35,0),(36,1,36,0),(37,1,37,0),(38,1,38,0),(39,1,39,0),(40,1,40,0),(41,1,41,0),(42,1,42,0),(43,1,43,0),(44,1,44,0),(45,1,45,0),(46,2,1,0),(47,2,2,0),(48,2,3,0),(49,2,4,0),(50,2,5,0),(51,2,6,0),(52,2,19,0),(53,2,23,0),(54,2,24,0),(55,2,25,0),(56,2,26,0),(57,2,28,0),(58,2,29,0),(59,2,33,0),(60,3,7,0),(61,3,8,0),(62,3,9,0),(63,3,10,0),(64,3,11,0),(65,3,12,0),(66,3,13,0),(67,3,14,0),(68,3,15,0),(69,3,16,0),(70,3,17,0),(71,3,18,0),(72,3,19,0),(73,3,20,0),(74,3,21,0),(75,3,22,0),(76,3,23,0),(77,1,46,0),(78,1,47,0),(79,1,48,0),(80,1,49,0),(81,1,50,0),(82,1,51,0),(83,1,52,0),(84,1,53,0),(85,1,54,0),(86,1,55,0),(87,1,56,0),(88,1,57,0),(89,1,58,0),(90,1,59,0),(91,1,60,0);
/*!40000 ALTER TABLE `tbakses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbbeli`
--

DROP TABLE IF EXISTS `tbbeli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbbeli` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_beli` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nomor transaksi pembelian',
  `tgl` date NOT NULL COMMENT 'Tanggal transaksi',
  `toko` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama toko/vendor',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=aktif, 1=soft-deleted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_beli` (`no_beli`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Header transaksi pembelian';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbeli`
--

LOCK TABLES `tbbeli` WRITE;
/*!40000 ALTER TABLE `tbbeli` DISABLE KEYS */;
INSERT INTO `tbbeli` VALUES (1,'BL-001','2026-09-15','Toko Sumber Makmur',0),(2,'BL-002','2026-09-16','Toko Berkah Jaya',1),(4,'BL-OK-113946','2026-09-16','Toko Berkah 10',0);
/*!40000 ALTER TABLE `tbbeli` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbbeli_d`
--

DROP TABLE IF EXISTS `tbbeli_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbbeli_d` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_beli` int NOT NULL COMMENT 'FK ke tbbeli.id',
  `barng` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama barang',
  `harga` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Harga barang (tidak boleh negatif)',
  PRIMARY KEY (`id`),
  KEY `fk_tbbeli_d_beli` (`id_beli`),
  CONSTRAINT `fk_tbbeli_d_beli` FOREIGN KEY (`id_beli`) REFERENCES `tbbeli` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail item transaksi pembelian';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbeli_d`
--

LOCK TABLES `tbbeli_d` WRITE;
/*!40000 ALTER TABLE `tbbeli_d` DISABLE KEYS */;
INSERT INTO `tbbeli_d` VALUES (1,1,'Beras 5kg',60000.00),(2,1,'Minyak 2L',38000.00),(3,1,'Gula 1kg',16000.00),(4,2,'Tepung 1kg',12000.00),(5,2,'Telur 1kg',28000.00),(7,4,'Minyak Goreng 2L',38000.00),(8,4,'Gula Pasir 1kg',17500.00),(9,4,'Beras Pandan 5kg',74000.00);
/*!40000 ALTER TABLE `tbbeli_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbbkk`
--

DROP TABLE IF EXISTS `tbbkk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbbkk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_bkk` varchar(50) NOT NULL,
  `tgl` date NOT NULL,
  `kete` text,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=aktif, 1=soft-deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbkk`
--

LOCK TABLES `tbbkk` WRITE;
/*!40000 ALTER TABLE `tbbkk` DISABLE KEYS */;
INSERT INTO `tbbkk` VALUES (2,'BKK01','2026-09-14','Prove',1),(3,'BKK-2026-04','2026-09-17','word',0),(4,'BKK021','2026-10-03','Ket',1),(5,'BKK-2026-01','2026-10-03',NULL,0),(6,'BKK-2026-0010','2026-10-03','AC oil',0);
/*!40000 ALTER TABLE `tbbkk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbbkk_d`
--

DROP TABLE IF EXISTS `tbbkk_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbbkk_d` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_bkk` int NOT NULL,
  `id_rbeli_d` int DEFAULT NULL,
  `nilai` decimal(15,2) NOT NULL DEFAULT '0.00',
  `id_coa` int NOT NULL,
  `id_coa_kb` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_bkk` (`id_bkk`),
  KEY `id_rbeli_d` (`id_rbeli_d`),
  KEY `id_coa` (`id_coa`),
  KEY `id_coa_kb` (`id_coa_kb`),
  CONSTRAINT `tbbkk_d_ibfk_1` FOREIGN KEY (`id_bkk`) REFERENCES `tbbkk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbbkk_d_ibfk_2` FOREIGN KEY (`id_rbeli_d`) REFERENCES `tbrbeli_d` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbbkk_d_ibfk_3` FOREIGN KEY (`id_coa`) REFERENCES `coa` (`id`),
  CONSTRAINT `tbbkk_d_ibfk_4` FOREIGN KEY (`id_coa_kb`) REFERENCES `coa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbkk_d`
--

LOCK TABLES `tbbkk_d` WRITE;
/*!40000 ALTER TABLE `tbbkk_d` DISABLE KEYS */;
INSERT INTO `tbbkk_d` VALUES (3,2,1,200000.00,1,8),(8,5,8,100000.00,13,4),(9,6,9,200000.00,24,4);
/*!40000 ALTER TABLE `tbbkk_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblaman`
--

DROP TABLE IF EXISTS `tblaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tblaman` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `aksi` enum('daftar','tambah','edit','hapus','lihat','cetak') COLLATE utf8mb4_general_ci NOT NULL,
  `is_off` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=aktif, 1=nonaktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_aksi` (`kode`,`aksi`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblaman`
--

LOCK TABLES `tblaman` WRITE;
/*!40000 ALTER TABLE `tblaman` DISABLE KEYS */;
INSERT INTO `tblaman` VALUES (1,'ak1','Rencana Beli','daftar',0),(2,'ak1','Rencana Beli','tambah',0),(3,'ak1','Rencana Beli','edit',0),(4,'ak1','Rencana Beli','hapus',0),(5,'ak1','Rencana Beli','lihat',0),(6,'ak1','Rencana Beli','cetak',0),(7,'ak2','Bukti Kas Keluar','daftar',0),(8,'ak2','Bukti Kas Keluar','tambah',0),(9,'ak2','Bukti Kas Keluar','edit',0),(10,'ak2','Bukti Kas Keluar','hapus',0),(11,'ak2','Bukti Kas Keluar','lihat',0),(12,'ak2','Bukti Kas Keluar','cetak',0),(13,'ak3','Rekap BKK','daftar',0),(14,'ak3','Rekap BKK','tambah',0),(15,'ak3','Rekap BKK','edit',0),(16,'ak3','Rekap BKK','hapus',0),(17,'ak3','Rekap BKK','lihat',0),(18,'ak3','Rekap BKK','cetak',0),(19,'coa','Chart of Account','daftar',0),(20,'coa','Chart of Account','tambah',0),(21,'coa','Chart of Account','edit',0),(22,'coa','Chart of Account','hapus',0),(23,'coa','Chart of Account','lihat',0),(24,'supp','Supplier','daftar',0),(25,'supp','Supplier','tambah',0),(26,'supp','Supplier','edit',0),(27,'supp','Supplier','hapus',0),(28,'supp','Supplier','lihat',0),(29,'kary','Karyawan','daftar',0),(30,'kary','Karyawan','tambah',0),(31,'kary','Karyawan','edit',0),(32,'kary','Karyawan','hapus',0),(33,'kary','Karyawan','lihat',0),(34,'usr','User','daftar',0),(35,'usr','User','tambah',0),(36,'usr','User','edit',0),(37,'usr','User','hapus',0),(38,'lmn','Laman','daftar',0),(39,'lmn','Laman','tambah',0),(40,'lmn','Laman','edit',0),(41,'lmn','Laman','hapus',0),(42,'aks','Akses','daftar',0),(43,'aks','Akses','tambah',0),(44,'aks','Akses','edit',0),(45,'aks','Akses','hapus',0),(46,'pos','Kasir Penjualan','daftar',0),(47,'pos','Kasir Penjualan','tambah',0),(48,'pos','Kasir Penjualan','lihat',0),(49,'pos','Kasir Penjualan','cetak',0),(50,'brg','Master Barang','daftar',0),(51,'brg','Master Barang','tambah',0),(52,'brg','Master Barang','edit',0),(53,'brg','Master Barang','hapus',0),(54,'jrn','Jurnal Umum','daftar',0),(55,'jrn','Jurnal Umum','lihat',0),(56,'nrc','Neraca Saldo','daftar',0),(57,'nrc','Neraca Saldo','lihat',0),(58,'nrc','Neraca Saldo','cetak',0),(59,'pkt','Praktikum P9','daftar',0),(60,'pkt','Praktikum P9','tambah',0);
/*!40000 ALTER TABLE `tblaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbrbeli`
--

DROP TABLE IF EXISTS `tbrbeli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbrbeli` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_rbeli` varchar(50) NOT NULL,
  `tgl` date NOT NULL,
  `id_supp` int NOT NULL,
  `kete` text,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=aktif, 1=soft-deleted',
  PRIMARY KEY (`id`),
  KEY `id_supp` (`id_supp`),
  CONSTRAINT `tbrbeli_ibfk_1` FOREIGN KEY (`id_supp`) REFERENCES `supplier` (`id_supplier`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrbeli`
--

LOCK TABLES `tbrbeli` WRITE;
/*!40000 ALTER TABLE `tbrbeli` DISABLE KEYS */;
INSERT INTO `tbrbeli` VALUES (1,'RB001','2026-09-13',1,'Tunai',0),(2,'RB-2026-02','2026-09-17',4,'Beli Barang Bagus',0),(3,'RB002','2026-10-03',10,'Beli',0),(4,'RB-2026-003','2026-10-03',1,'Oil',0);
/*!40000 ALTER TABLE `tbrbeli` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbrbeli_d`
--

DROP TABLE IF EXISTS `tbrbeli_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbrbeli_d` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_rbeli` int NOT NULL,
  `no_faktur` varchar(50) NOT NULL,
  `nilai` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id_rbeli` (`id_rbeli`),
  CONSTRAINT `tbrbeli_d_ibfk_1` FOREIGN KEY (`id_rbeli`) REFERENCES `tbrbeli` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrbeli_d`
--

LOCK TABLES `tbrbeli_d` WRITE;
/*!40000 ALTER TABLE `tbrbeli_d` DISABLE KEYS */;
INSERT INTO `tbrbeli_d` VALUES (1,1,'INV-001',200000.00),(7,2,'INV-002',2000000.00),(8,3,'INV-010',100000.00),(9,4,'INV-002',200000.00);
/*!40000 ALTER TABLE `tbrbeli_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbrecord`
--

DROP TABLE IF EXISTS `tbrecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbrecord` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_rec` varchar(50) NOT NULL,
  `tgl` date NOT NULL,
  `id_bkk` int NOT NULL,
  `ket` text,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=aktif, 1=soft-deleted',
  PRIMARY KEY (`id`),
  KEY `id_bkk` (`id_bkk`),
  CONSTRAINT `tbrecord_ibfk_1` FOREIGN KEY (`id_bkk`) REFERENCES `tbbkk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrecord`
--

LOCK TABLES `tbrecord` WRITE;
/*!40000 ALTER TABLE `tbrecord` DISABLE KEYS */;
INSERT INTO `tbrecord` VALUES (1,'REC-2026-01','2026-09-14',2,'Rekap',0);
/*!40000 ALTER TABLE `tbrecord` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbuser`
--

DROP TABLE IF EXISTS `tbuser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbuser` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_off` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=aktif, 1=nonaktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbuser`
--

LOCK TABLES `tbuser` WRITE;
/*!40000 ALTER TABLE `tbuser` DISABLE KEYS */;
INSERT INTO `tbuser` VALUES (1,'admin','Administrator','$2y$12$05d6DZ3wFzYa0LcPdgqWROEAiD2aBfge67qDsznBdmqSyL3FVAb.C',0),(2,'lisa','Meilisa','$2y$12$bCXcGYVagb44RkxEqeFLTOiTP4SGm9BEpHht8RG4gGM68FGLGh33q',0),(3,'devi','Devi Sri','$2y$12$Mn4gg4h4NLxz9vji2Q1VgeFHltjYhOg3hVbohU6BQEooVaaGd0Ua.',0);
/*!40000 ALTER TABLE `tbuser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'kas_keluar'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-19 22:26:32
