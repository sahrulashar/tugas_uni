-- =======================================================
-- Database Initialization for Docker MySQL Container
-- Automatically executed by MySQL container entrypoint
-- =======================================================
CREATE DATABASE IF NOT EXISTS `kas_keluar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kas_keluar`;

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail semua aksi pengguna pada tabel master';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,0,'System','EDIT',NULL,'tbrbeli',2,NULL,'http://localhost:8080/index.php/aktivitas/aktivitas1/update','{\"before\":{\"id\":\"2\",\"no_rbeli\":\"RB-2026-02\",\"tgl\":\"2026-09-17\",\"id_supp\":\"4\",\"kete\":\"Beli Barang\",\"is_deleted\":\"0\"},\"after\":{\"no_rbeli\":\"RB-2026-02\",\"tgl\":\"2026-09-17\",\"id_supp\":4,\"kete\":\"Beli Barang Bagus\"}}','2026-09-21 13:01:28'),(2,1,'Administrator','EDIT',NULL,'tbuser',2,NULL,'http://localhost:8080/index.php/46124026/rbac/user/update','{\"before\":{\"kode\":\"lisa\",\"nama\":\"Meilisa\"},\"after\":{\"kode\":\"lisa\",\"nama\":\"Meilisa\"}}','2026-09-30 10:27:38'),(3,1,'Administrator','EDIT',NULL,'coa',4,NULL,'http://localhost:8080/index.php/46124026/kas_keluar/update_coa_l1H','{\"before\":{\"id\":\"4\",\"kode_coa\":\"1112\",\"nama_coa\":\"Kas Kecil\",\"saldo_normal\":\"Debit\",\"is_header\":\"D\",\"tipe\":\"kasbank\",\"saldo_awal\":\"0.00\",\"is_off\":\"0\"},\"after\":{\"kode_coa\":\"1112\",\"nama_coa\":\"Kas Kecil\",\"saldo_normal\":\"Debit\",\"is_header\":\"D\",\"tipe\":\"kasbank\",\"saldo_awal\":100000}}','2026-10-03 13:35:53'),(4,1,'Administrator','TAMBAH',NULL,'tbrbeli',3,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas1/simpan_l1H','{\"after\":{\"no_rbeli\":\"RB002\",\"tgl\":\"2026-10-03\",\"id_supp\":10,\"kete\":\"Beli\"}}','2026-10-03 13:37:13'),(5,1,'Administrator','TAMBAH',NULL,'tbbkk',4,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/simpan_l1H','{\"after\":{\"no_bkk\":\"BKK021\",\"tgl\":\"2026-10-03\",\"kete\":\"Ket\"}}','2026-10-03 13:40:19'),(6,1,'Administrator','SOFT_DELETE',NULL,'tbbkk',4,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/hapus_l1H/4','{\"before\":{\"id\":\"4\",\"no_bkk\":\"BKK021\",\"tgl\":\"2026-10-03\",\"kete\":\"Ket\",\"is_deleted\":\"0\"}}','2026-10-03 13:44:37'),(7,1,'Administrator','TAMBAH',NULL,'tbbkk',5,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/simpan_l1H','{\"after\":{\"no_bkk\":\"BKK-2026-01\",\"tgl\":\"2026-10-03\",\"kete\":null}}','2026-10-03 13:45:16'),(8,1,'Administrator','TAMBAH',NULL,'tbrbeli',4,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas1/simpan_l1H','{\"after\":{\"no_rbeli\":\"RB-2026-003\",\"tgl\":\"2026-10-03\",\"id_supp\":1,\"kete\":\"Oil\"}}','2026-10-03 13:49:28'),(9,1,'Administrator','TAMBAH',NULL,'tbbkk',6,NULL,'http://localhost:8080/index.php/46124026/aktivitas/aktivitas2/simpan_l1H','{\"after\":{\"no_bkk\":\"BKK-2026-0010\",\"tgl\":\"2026-10-03\",\"kete\":\"AC oil\"}}','2026-10-03 13:51:05');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coa`
--

LOCK TABLES `coa` WRITE;
/*!40000 ALTER TABLE `coa` DISABLE KEYS */;
INSERT INTO `coa` VALUES (1,'1000','ASET','Debit','H',NULL,0.00,0),(2,'1100','ASET LANCAR','Debit','H',NULL,0.00,0),(3,'1111','Kas Tangan','Debit','D','kasbank',5000000.00,0),(4,'1112','Kas Kecil','Debit','D','kasbank',1000000.00,0),(5,'1113','Kas Gopay','Debit','D','kasbank',500000.00,0),(6,'1114','Kas DANA','Debit','D','kasbank',500000.00,0),(7,'1115','Kas ShopeePay','Debit','D','kasbank',250000.00,0),(8,'1116','Kas BCA','Debit','D','kasbank',25000000.00,0),(9,'1117','Kas Mandiri','Debit','D','kasbank',10000000.00,0),(10,'1118','Kas BRI','Debit','D','kasbank',5000000.00,0),(11,'1119','Kas BNI','Debit','D','kasbank',5000000.00,0),(12,'1120','Kas Paylater','Debit','D','kasbank',2000000.00,0),(13,'2000','KEWAJIBAN','Kredit','H',NULL,0.00,0),(14,'2100','KEWAJIBAN LANCAR','Kredit','H',NULL,0.00,0),(15,'2111','Utang Usaha','Kredit','D','operasional',0.00,0),(16,'2112','Utang Gaji','Kredit','D','operasional',0.00,0),(17,'2113','Utang Pajak','Kredit','D','operasional',0.00,0),(18,'3000','EKUITAS','Kredit','H',NULL,0.00,0),(19,'3111','Modal Pemilik','Kredit','D','pendanaan',54250000.00,0),(20,'4000','PENDAPATAN','Kredit','H',NULL,0.00,0),(21,'4111','Pendapatan Jasa','Kredit','D','operasional',0.00,0),(22,'5000','BEBAN','Debit','H',NULL,0.00,0),(23,'5111','Pembelian','Debit','D','operasional',0.00,0),(24,'1141','Utang Usaha','Debit','D','operasional',0.00,0);
/*!40000 ALTER TABLE `coa` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026-09-30-000001','App\\Database\\Migrations\\CreateTbuser','default','App',1790760884,1),(2,'2026-09-30-000002','App\\Database\\Migrations\\CreateTblaman','default','App',1790760884,1),(3,'2026-09-30-000003','App\\Database\\Migrations\\CreateTbakses','default','App',1790760884,1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbakses`
--

LOCK TABLES `tbakses` WRITE;
/*!40000 ALTER TABLE `tbakses` DISABLE KEYS */;
INSERT INTO `tbakses` VALUES (1,1,1,0),(2,1,2,0),(3,1,3,0),(4,1,4,0),(5,1,5,0),(6,1,6,0),(7,1,7,0),(8,1,8,0),(9,1,9,0),(10,1,10,0),(11,1,11,0),(12,1,12,0),(13,1,13,0),(14,1,14,0),(15,1,15,0),(16,1,16,0),(17,1,17,0),(18,1,18,0),(19,1,19,0),(20,1,20,0),(21,1,21,0),(22,1,22,0),(23,1,23,0),(24,1,24,0),(25,1,25,0),(26,1,26,0),(27,1,27,0),(28,1,28,0),(29,1,29,0),(30,1,30,0),(31,1,31,0),(32,1,32,0),(33,1,33,0),(34,1,34,0),(35,1,35,0),(36,1,36,0),(37,1,37,0),(38,1,38,0),(39,1,39,0),(40,1,40,0),(41,1,41,0),(42,1,42,0),(43,1,43,0),(44,1,44,0),(45,1,45,0),(46,2,1,0),(47,2,2,0),(48,2,3,0),(49,2,4,0),(50,2,5,0),(51,2,6,0),(52,2,19,0),(53,2,23,0),(54,2,24,0),(55,2,25,0),(56,2,26,0),(57,2,28,0),(58,2,29,0),(59,2,33,0),(60,3,7,0),(61,3,8,0),(62,3,9,0),(63,3,10,0),(64,3,11,0),(65,3,12,0),(66,3,13,0),(67,3,14,0),(68,3,15,0),(69,3,16,0),(70,3,17,0),(71,3,18,0),(72,3,19,0),(73,3,20,0),(74,3,21,0),(75,3,22,0),(76,3,23,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblaman`
--

LOCK TABLES `tblaman` WRITE;
/*!40000 ALTER TABLE `tblaman` DISABLE KEYS */;
INSERT INTO `tblaman` VALUES (1,'ak1','Rencana Beli','daftar',0),(2,'ak1','Rencana Beli','tambah',0),(3,'ak1','Rencana Beli','edit',0),(4,'ak1','Rencana Beli','hapus',0),(5,'ak1','Rencana Beli','lihat',0),(6,'ak1','Rencana Beli','cetak',0),(7,'ak2','Bukti Kas Keluar','daftar',0),(8,'ak2','Bukti Kas Keluar','tambah',0),(9,'ak2','Bukti Kas Keluar','edit',0),(10,'ak2','Bukti Kas Keluar','hapus',0),(11,'ak2','Bukti Kas Keluar','lihat',0),(12,'ak2','Bukti Kas Keluar','cetak',0),(13,'ak3','Rekap BKK','daftar',0),(14,'ak3','Rekap BKK','tambah',0),(15,'ak3','Rekap BKK','edit',0),(16,'ak3','Rekap BKK','hapus',0),(17,'ak3','Rekap BKK','lihat',0),(18,'ak3','Rekap BKK','cetak',0),(19,'coa','Chart of Account','daftar',0),(20,'coa','Chart of Account','tambah',0),(21,'coa','Chart of Account','edit',0),(22,'coa','Chart of Account','hapus',0),(23,'coa','Chart of Account','lihat',0),(24,'supp','Supplier','daftar',0),(25,'supp','Supplier','tambah',0),(26,'supp','Supplier','edit',0),(27,'supp','Supplier','hapus',0),(28,'supp','Supplier','lihat',0),(29,'kary','Karyawan','daftar',0),(30,'kary','Karyawan','tambah',0),(31,'kary','Karyawan','edit',0),(32,'kary','Karyawan','hapus',0),(33,'kary','Karyawan','lihat',0),(34,'usr','User','daftar',0),(35,'usr','User','tambah',0),(36,'usr','User','edit',0),(37,'usr','User','hapus',0),(38,'lmn','Laman','daftar',0),(39,'lmn','Laman','tambah',0),(40,'lmn','Laman','edit',0),(41,'lmn','Laman','hapus',0),(42,'aks','Akses','daftar',0),(43,'aks','Akses','tambah',0),(44,'aks','Akses','edit',0),(45,'aks','Akses','hapus',0);
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
INSERT INTO `tbuser` VALUES (1,'admin','Administrator','$2y$12$05d6DZ3wFzYa0LcPdgqWROEAiD2aBfge67qDsznBdmqSyL3FVAb.C',0),(2,'lisa','Meilisa','$2y$12$QZJHTNdc71xSFgRlKj.n1ebGao02GDfOeqYqnwJlAP/zNjPAkISZ.',0),(3,'devi','Devi Sri','$2y$12$Mn4gg4h4NLxz9vji2Q1VgeFHltjYhOg3hVbohU6BQEooVaaGd0Ua.',0);
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

-- Dump completed on 2026-10-03 21:58:11
