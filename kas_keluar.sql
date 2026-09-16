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
  `aksi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'TAMBAH | EDIT | SOFT_DELETE',
  `tabel_terdampak` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama tabel yang diubah',
  `record_id` int NOT NULL COMMENT 'ID record yang terdampak',
  `waktu` datetime NOT NULL COMMENT 'Waktu aksi dilakukan (NOW())',
  PRIMARY KEY (`id`),
  KEY `idx_audit_tabel_record` (`tabel_terdampak`,`record_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_waktu` (`waktu`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail semua aksi pengguna pada tabel master';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,'TAMBAH','tbbeli',1,'2026-09-16 19:30:41'),(2,1,'TAMBAH','tbbeli',2,'2026-09-16 19:30:41'),(3,1,'SOFT_DELETE','tbbeli',2,'2026-09-16 19:30:41');
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
  `is_off` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coa`
--

LOCK TABLES `coa` WRITE;
/*!40000 ALTER TABLE `coa` DISABLE KEYS */;
INSERT INTO `coa` VALUES (1,'1000','ASET','Debit','H',NULL,0),(2,'1100','ASET LANCAR','Debit','H',NULL,0),(3,'1111','Kas Tangan','Debit','D','kasbank',0),(4,'1112','Kas Kecil','Debit','D','kasbank',0),(5,'1113','Kas Gopay','Debit','D','kasbank',0),(6,'1114','Kas DANA','Debit','D','kasbank',0),(7,'1115','Kas ShopeePay','Debit','D','kasbank',0),(8,'1116','Kas BCA','Debit','D','kasbank',0),(9,'1117','Kas Mandiri','Debit','D','kasbank',0),(10,'1118','Kas BRI','Debit','D','kasbank',0),(11,'1119','Kas BNI','Debit','D','kasbank',0),(12,'1120','Kas Paylater','Debit','D','kasbank',0),(13,'2000','KEWAJIBAN','Kredit','H',NULL,0),(14,'2100','KEWAJIBAN LANCAR','Kredit','H',NULL,0),(15,'2111','Utang Usaha','Kredit','D','operasional',0),(16,'2112','Utang Gaji','Kredit','D','operasional',0),(17,'2113','Utang Pajak','Kredit','D','operasional',0),(18,'3000','EKUITAS','Kredit','H',NULL,0),(19,'3111','Modal Pemilik','Kredit','D','pendanaan',0),(20,'4000','PENDAPATAN','Kredit','H',NULL,0),(21,'4111','Pendapatan Jasa','Kredit','D','operasional',0),(22,'5000','BEBAN','Debit','H',NULL,0),(23,'5111','Pembelian','Debit','D','operasional',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Header transaksi pembelian';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbeli`
--

LOCK TABLES `tbbeli` WRITE;
/*!40000 ALTER TABLE `tbbeli` DISABLE KEYS */;
INSERT INTO `tbbeli` VALUES (1,'BL-001','2026-09-15','Toko Sumber Makmur',0),(2,'BL-002','2026-09-16','Toko Berkah Jaya',0);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detail item transaksi pembelian';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbeli_d`
--

LOCK TABLES `tbbeli_d` WRITE;
/*!40000 ALTER TABLE `tbbeli_d` DISABLE KEYS */;
INSERT INTO `tbbeli_d` VALUES (1,1,'Beras 5kg',60000.00),(2,1,'Minyak 2L',38000.00),(3,1,'Gula 1kg',16000.00),(4,2,'Tepung 1kg',12000.00),(5,2,'Telur 1kg',28000.00);
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbkk`
--

LOCK TABLES `tbbkk` WRITE;
/*!40000 ALTER TABLE `tbbkk` DISABLE KEYS */;
INSERT INTO `tbbkk` VALUES (2,'BKK01','2026-09-14','Bukti');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbbkk_d`
--

LOCK TABLES `tbbkk_d` WRITE;
/*!40000 ALTER TABLE `tbbkk_d` DISABLE KEYS */;
INSERT INTO `tbbkk_d` VALUES (2,2,1,200000.00,1,8);
/*!40000 ALTER TABLE `tbbkk_d` ENABLE KEYS */;
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
  PRIMARY KEY (`id`),
  KEY `id_supp` (`id_supp`),
  CONSTRAINT `tbrbeli_ibfk_1` FOREIGN KEY (`id_supp`) REFERENCES `supplier` (`id_supplier`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrbeli`
--

LOCK TABLES `tbrbeli` WRITE;
/*!40000 ALTER TABLE `tbrbeli` DISABLE KEYS */;
INSERT INTO `tbrbeli` VALUES (1,'RB001','2026-09-13',1,'Tunai');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrbeli_d`
--

LOCK TABLES `tbrbeli_d` WRITE;
/*!40000 ALTER TABLE `tbrbeli_d` DISABLE KEYS */;
INSERT INTO `tbrbeli_d` VALUES (1,1,'INV-001',200000.00);
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
INSERT INTO `tbrecord` VALUES (1,'REC-2026-01','2026-09-14',2,'Rekap');
/*!40000 ALTER TABLE `tbrecord` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-16 19:33:28
