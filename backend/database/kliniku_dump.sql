-- MySQL dump 10.13  Distrib 8.4.11, for Linux (x86_64)
--
-- Host: localhost    Database: kliniku
-- ------------------------------------------------------
-- Server version	8.4.11

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-test_key','s:8:\"test_val\";',1795326905);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dokter`
--

DROP TABLE IF EXISTS `dokter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dokter` (
  `dokter_user_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dokter_role` enum('dokter','staf','pasien') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dokter',
  `dokter_spesialisasi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dokter_sip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dokter_kuotaharian` smallint unsigned NOT NULL DEFAULT '25',
  `dokter_poli_id` tinyint unsigned NOT NULL,
  PRIMARY KEY (`dokter_user_id`),
  UNIQUE KEY `uq_dokter_sip` (`dokter_sip`),
  KEY `idx_dokter_poli` (`dokter_poli_id`),
  KEY `fk_dokter_user` (`dokter_user_id`,`dokter_role`),
  CONSTRAINT `fk_dokter_poli` FOREIGN KEY (`dokter_poli_id`) REFERENCES `poli` (`poli_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_dokter_user` FOREIGN KEY (`dokter_user_id`, `dokter_role`) REFERENCES `user` (`user_id`, `user_role`) ON DELETE CASCADE,
  CONSTRAINT `ck_dokter_role` CHECK ((`dokter_role` = _utf8mb4'dokter'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dokter`
--

LOCK TABLES `dokter` WRITE;
/*!40000 ALTER TABLE `dokter` DISABLE KEYS */;
INSERT INTO `dokter` VALUES ('USR-DOC-001','dokter','Penyakit Dalam','503/SIP.01/DKS/2023',25,1),('USR-DOC-002','dokter','Gigi & Mulut','503/SIP.02/DKS/2024',25,4),('USR-DOC-003','dokter','Kesehatan Anak','503/SIP.03/DKS/2022',25,3),('USR-DOC-004','dokter','Kandungan & Kebidanan','503/SIP.04/DKS/2023',25,5),('USR-DOC-005','dokter','Geriatri (Lansia)','503/SIP.05/DKS/2024',25,2);
/*!40000 ALTER TABLE `dokter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jadwal`
--

DROP TABLE IF EXISTS `jadwal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal` (
  `jadwal_id` int unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_dokter_user_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadwal_hari` tinyint unsigned NOT NULL,
  `jadwal_jammulai` time NOT NULL,
  `jadwal_jamselesai` time NOT NULL,
  PRIMARY KEY (`jadwal_id`),
  UNIQUE KEY `uq_jadwal_dokterharijam` (`jadwal_dokter_user_id`,`jadwal_hari`,`jadwal_jammulai`),
  CONSTRAINT `fk_jadwal_dokter` FOREIGN KEY (`jadwal_dokter_user_id`) REFERENCES `dokter` (`dokter_user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ck_jadwal_hari` CHECK ((`jadwal_hari` between 1 and 7)),
  CONSTRAINT `ck_jadwal_jam` CHECK ((`jadwal_jamselesai` > `jadwal_jammulai`))
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jadwal`
--

LOCK TABLES `jadwal` WRITE;
/*!40000 ALTER TABLE `jadwal` DISABLE KEYS */;
INSERT INTO `jadwal` VALUES (1,'USR-DOC-001',1,'08:00:00','12:00:00'),(2,'USR-DOC-001',2,'08:00:00','12:00:00'),(3,'USR-DOC-001',3,'08:00:00','12:00:00'),(4,'USR-DOC-001',4,'08:00:00','12:00:00'),(5,'USR-DOC-001',5,'08:00:00','12:00:00'),(6,'USR-DOC-002',1,'13:00:00','17:00:00'),(7,'USR-DOC-002',2,'13:00:00','17:00:00'),(8,'USR-DOC-002',3,'13:00:00','17:00:00'),(9,'USR-DOC-002',4,'13:00:00','17:00:00'),(10,'USR-DOC-003',1,'08:00:00','12:00:00'),(11,'USR-DOC-003',3,'08:00:00','12:00:00'),(12,'USR-DOC-003',5,'08:00:00','12:00:00'),(13,'USR-DOC-004',2,'08:00:00','12:00:00'),(14,'USR-DOC-004',4,'08:00:00','12:00:00'),(15,'USR-DOC-004',6,'08:00:00','12:00:00'),(16,'USR-DOC-005',1,'13:00:00','17:00:00'),(17,'USR-DOC-005',2,'13:00:00','17:00:00'),(18,'USR-DOC-005',3,'13:00:00','17:00:00'),(19,'USR-DOC-005',4,'13:00:00','17:00:00'),(20,'USR-DOC-005',5,'13:00:00','17:00:00');
/*!40000 ALTER TABLE `jadwal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_21_000001_create_poli_table',1),(5,'2026_11_21_000002_create_user_table',1),(6,'2026_11_21_000003_create_dokter_table',1),(7,'2026_11_21_000004_create_staf_table',1),(8,'2026_11_21_000005_create_pasien_table',1),(9,'2026_11_21_000006_create_jadwal_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pasien`
--

DROP TABLE IF EXISTS `pasien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pasien` (
  `pasien_user_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pasien_role` enum('dokter','staf','pasien') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pasien',
  `pasien_norm` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pasien_jeniskelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `pasien_tgllahir` date NOT NULL,
  `pasien_alamat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pasien_nobpjs` char(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pasien_goldarah` enum('A','B','AB','O') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pasien_alergiobat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pasien_poli_id` tinyint unsigned DEFAULT NULL,
  PRIMARY KEY (`pasien_user_id`),
  UNIQUE KEY `uq_pasien_norm` (`pasien_norm`),
  UNIQUE KEY `uq_pasien_nobpjs` (`pasien_nobpjs`),
  KEY `idx_pasien_poli` (`pasien_poli_id`),
  KEY `fk_pasien_user` (`pasien_user_id`,`pasien_role`),
  CONSTRAINT `fk_pasien_poli` FOREIGN KEY (`pasien_poli_id`) REFERENCES `poli` (`poli_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pasien_user` FOREIGN KEY (`pasien_user_id`, `pasien_role`) REFERENCES `user` (`user_id`, `user_role`) ON DELETE CASCADE,
  CONSTRAINT `ck_pasien_role` CHECK ((`pasien_role` = _utf8mb4'pasien'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pasien`
--

LOCK TABLES `pasien` WRITE;
/*!40000 ALTER TABLE `pasien` DISABLE KEYS */;
INSERT INTO `pasien` VALUES ('USR-PAS-0001','pasien','RM-2026-0001','Laki-laki','2025-12-09','Jl. Ki Hajar Dewantara No. 23, Palur, Karanganyar','0001136505587','O',NULL,3),('USR-PAS-0002','pasien','RM-2026-0002','Perempuan','2016-01-18','Jl. Veteran Pasarkliwon No. 72, Jebres, Surakarta','0007110082321','B',NULL,3),('USR-PAS-0003','pasien','RM-2026-0003','Laki-laki','2024-02-13','Jl. Kapten Mulyadi No. 12, Colomadu, Karanganyar','0003303082117','AB',NULL,3),('USR-PAS-0004','pasien','RM-2026-0004','Perempuan','2015-10-28','Jl. Slamet Riyadi No. 59, Serengan, Surakarta','0005231494220','B',NULL,3),('USR-PAS-0005','pasien','RM-2026-0005','Laki-laki','2019-05-15','Jl. Ir. Sutami No. 69, Banjarsari, Surakarta','0004131575764','A','Sulfonamida',3),('USR-PAS-0006','pasien','RM-2026-0006','Perempuan','2015-12-18','Jl. Slamet Riyadi No. 81, Palur, Karanganyar','0002149938334','A','Ibuprofen',3),('USR-PAS-0007','pasien','RM-2026-0007','Laki-laki','2022-11-16','Jl. Veteran No. 64, Serengan, Surakarta','0007801222128','B',NULL,3),('USR-PAS-0008','pasien','RM-2026-0008','Perempuan','2018-02-25','Jl. Veteran No. 109, Banjarsari, Surakarta','0006947530309','O','Sulfonamida',3),('USR-PAS-0009','pasien','RM-2026-0009','Laki-laki','2017-01-22','Jl. Ahmad Yani No. 29, Serengan, Surakarta','0002867302554','AB',NULL,3),('USR-PAS-0010','pasien','RM-2026-0010','Perempuan','2017-03-17','Jl. Brigjen Slamet Riyadi No. 51, Laweyan, Surakarta','0004944899549','O','Penisilin',3),('USR-PAS-0011','pasien','RM-2026-0011','Laki-laki','2024-06-27','Jl. Brigjen Slamet Riyadi No. 21, Banjarsari, Surakarta','0008438486479','B',NULL,3),('USR-PAS-0012','pasien','RM-2026-0012','Perempuan','2023-03-22','Jl. Brigjen Slamet Riyadi No. 109, Pasar Kliwon, Surakarta','0003962958940','A',NULL,3),('USR-PAS-0013','pasien','RM-2026-0013','Laki-laki','2018-09-15','Jl. Ahmad Yani No. 6, Pasar Kliwon, Surakarta','0003527331058','O',NULL,3),('USR-PAS-0014','pasien','RM-2026-0014','Perempuan','2022-02-02','Jl. Kapten Mulyadi No. 125, Pasar Kliwon, Surakarta','0003315992115','A','Ibuprofen',3),('USR-PAS-0015','pasien','RM-2026-0015','Laki-laki','2022-08-26','Jl. Kolonel Sugiyono No. 91, Palur, Karanganyar','0007060638140','B',NULL,3),('USR-PAS-0016','pasien','RM-2026-0016','Perempuan','2005-07-24','Jl. Ir. Sutami No. 138, Colomadu, Karanganyar','0005897045684','A',NULL,5),('USR-PAS-0017','pasien','RM-2026-0017','Laki-laki','2004-09-04','Jl. Ir. Sutami No. 43, Palur, Karanganyar','0007380780055','A','Amoksisilin',1),('USR-PAS-0018','pasien','RM-2026-0018','Perempuan','2001-07-01','Jl. Kolonel Sugiyono No. 143, Colomadu, Karanganyar','0001664847319','B',NULL,5),('USR-PAS-0019','pasien','RM-2026-0019','Laki-laki','2005-06-02','Jl. Veteran No. 15, Banjarsari, Surakarta','0004656488400','O','Ibuprofen',4),('USR-PAS-0020','pasien','RM-2026-0020','Perempuan','1994-02-19','Jl. Kolonel Sugiyono No. 150, Kartasura, Sukoharjo','0009309500117','A','Penisilin',5),('USR-PAS-0021','pasien','RM-2026-0021','Laki-laki','1983-11-21','Jl. Slamet Riyadi No. 118, Banjarsari, Surakarta','0009904586976','A','Aspirin',1),('USR-PAS-0022','pasien','RM-2026-0022','Perempuan','1995-02-08','Jl. Ki Hajar Dewantara No. 78, Jebres, Surakarta','0007676965974','A',NULL,5),('USR-PAS-0023','pasien','RM-2026-0023','Laki-laki','1992-02-24','Jl. Brigjen Slamet Riyadi No. 54, Kartasura, Sukoharjo','0007393195616','B',NULL,1),('USR-PAS-0024','pasien','RM-2026-0024','Perempuan','1998-01-01','Jl. Veteran No. 114, Palur, Karanganyar','0003409076656','O',NULL,5),('USR-PAS-0025','pasien','RM-2026-0025','Laki-laki','2006-10-18','Jl. Kapten Mulyadi No. 94, Jebres, Surakarta','0009155819200','A',NULL,1),('USR-PAS-0026','pasien','RM-2026-0026','Perempuan','1986-07-20','Jl. Kolonel Sugiyono No. 7, Laweyan, Surakarta','0009292791081','B',NULL,4),('USR-PAS-0027','pasien','RM-2026-0027','Laki-laki','2002-01-28','Jl. Ahmad Yani No. 79, Pasar Kliwon, Surakarta','0001957449367','A',NULL,1),('USR-PAS-0028','pasien','RM-2026-0028','Perempuan','1991-05-12','Jl. Ahmad Yani No. 8, Banjarsari, Surakarta','0002121910732','O',NULL,1),('USR-PAS-0029','pasien','RM-2026-0029','Laki-laki','1981-12-26','Jl. Urip Sumoharjo No. 99, Pasar Kliwon, Surakarta','0002094024844','A','Amoksisilin',1),('USR-PAS-0030','pasien','RM-2026-0030','Perempuan','1996-07-03','Jl. Kapten Mulyadi No. 130, Serengan, Surakarta','0008159250505','B','Amoksisilin',1),('USR-PAS-0031','pasien','RM-2026-0031','Laki-laki','2000-07-22','Jl. Kapten Mulyadi No. 104, Jebres, Surakarta','0006600100279','A','Amoksisilin',1),('USR-PAS-0032','pasien','RM-2026-0032','Perempuan','1988-08-15','Jl. Urip Sumoharjo No. 73, Kartasura, Sukoharjo','0002333241445','B',NULL,1),('USR-PAS-0033','pasien','RM-2026-0033','Laki-laki','2006-08-20','Jl. Brigjen Slamet Riyadi No. 50, Palur, Karanganyar','0007418301077','A',NULL,1),('USR-PAS-0034','pasien','RM-2026-0034','Perempuan','2002-04-06','Jl. Ir. Sutami No. 32, Colomadu, Karanganyar','0008995059760','AB',NULL,1),('USR-PAS-0035','pasien','RM-2026-0035','Laki-laki','1991-08-21','Jl. Kapten Mulyadi No. 61, Serengan, Surakarta','0006737421496','O',NULL,4),('USR-PAS-0036','pasien','RM-2026-0036','Perempuan','1994-12-05','Jl. Ahmad Yani No. 139, Colomadu, Karanganyar','0002785736751','A','Amoksisilin',1),('USR-PAS-0037','pasien','RM-2026-0037','Laki-laki','2007-07-16','Jl. Kolonel Sugiyono No. 138, Pasar Kliwon, Surakarta','0003096929657','B','Amoksisilin',4),('USR-PAS-0038','pasien','RM-2026-0038','Perempuan','1984-11-22','Jl. Brigjen Slamet Riyadi No. 137, Jebres, Surakarta','0009190197115','O','Ibuprofen',1),('USR-PAS-0039','pasien','RM-2026-0039','Laki-laki','2003-07-05','Jl. Kolonel Sugiyono No. 84, Pasar Kliwon, Surakarta','0007247886041','B','Amoksisilin',4),('USR-PAS-0040','pasien','RM-2026-0040','Perempuan','1992-08-01','Jl. Urip Sumoharjo No. 11, Jebres, Surakarta','0005078551333','A',NULL,1),('USR-PAS-0041','pasien','RM-2026-0041','Laki-laki','1993-08-22','Jl. Ahmad Yani No. 43, Banjarsari, Surakarta','0002335901990','O',NULL,1),('USR-PAS-0042','pasien','RM-2026-0042','Perempuan','1984-12-07','Jl. Brigjen Slamet Riyadi No. 31, Jebres, Surakarta','0002591589823','O','Penisilin',1),('USR-PAS-0043','pasien','RM-2026-0043','Laki-laki','1977-07-12','Jl. Ki Hajar Dewantara No. 70, Colomadu, Karanganyar','0007291584955','B','Ibuprofen',1),('USR-PAS-0044','pasien','RM-2026-0044','Perempuan','1993-05-15','Jl. Kolonel Sugiyono No. 87, Jebres, Surakarta','0002395925222','AB',NULL,1),('USR-PAS-0045','pasien','RM-2026-0045','Laki-laki','1987-10-23','Jl. Urip Sumoharjo No. 62, Palur, Karanganyar','0004256292393','A','Sulfonamida',1),('USR-PAS-0046','pasien','RM-2026-0046','Perempuan','1980-02-10','Jl. Kapten Mulyadi No. 149, Kartasura, Sukoharjo','0007575259637','AB','Aspirin',1),('USR-PAS-0047','pasien','RM-2026-0047','Laki-laki','1979-05-09','Jl. Ahmad Yani No. 31, Laweyan, Surakarta','0001822662216','A','Sulfonamida',1),('USR-PAS-0048','pasien','RM-2026-0048','Perempuan','1990-04-10','Jl. Slamet Riyadi No. 137, Laweyan, Surakarta','0002178158203','O','Aspirin',1),('USR-PAS-0049','pasien','RM-2026-0049','Laki-laki','1977-01-19','Jl. Ahmad Yani No. 48, Jebres, Surakarta','0008996039244','O',NULL,1),('USR-PAS-0050','pasien','RM-2026-0050','Perempuan','2004-11-22','Jl. Kapten Mulyadi No. 22, Pasar Kliwon, Surakarta','0008578226896','O','Ibuprofen',4),('USR-PAS-0051','pasien','RM-2026-0051','Laki-laki','1994-07-15','Jl. Kapten Mulyadi No. 146, Jebres, Surakarta','0004178659381','B',NULL,4),('USR-PAS-0052','pasien','RM-2026-0052','Perempuan','1992-02-06','Jl. Veteran No. 1, Palur, Karanganyar','0007845366946','B',NULL,4),('USR-PAS-0053','pasien','RM-2026-0053','Laki-laki','1990-08-03','Jl. Ir. Sutami No. 109, Banjarsari, Surakarta','0003338877621','A',NULL,4),('USR-PAS-0054','pasien','RM-2026-0054','Perempuan','1999-01-06','Jl. Veteran Pasarkliwon No. 78, Palur, Karanganyar','0009343730920','O','Aspirin',1),('USR-PAS-0055','pasien','RM-2026-0055','Laki-laki','2003-01-14','Jl. Ir. Sutami No. 148, Jebres, Surakarta','0004248149900','AB','Aspirin',4),('USR-PAS-0056','pasien','RM-2026-0056','Perempuan','1991-10-14','Jl. Kolonel Sugiyono No. 86, Kartasura, Sukoharjo','0003878201524','B',NULL,1),('USR-PAS-0057','pasien','RM-2026-0057','Laki-laki','1977-11-13','Jl. Ahmad Yani No. 65, Kartasura, Sukoharjo','0008613035386','B','Aspirin',1),('USR-PAS-0058','pasien','RM-2026-0058','Perempuan','1979-01-07','Jl. Veteran Pasarkliwon No. 14, Pasar Kliwon, Surakarta','0006532043071','A','Sulfonamida',1),('USR-PAS-0059','pasien','RM-2026-0059','Laki-laki','1993-03-10','Jl. Urip Sumoharjo No. 58, Banjarsari, Surakarta','0004574027737','AB',NULL,4),('USR-PAS-0060','pasien','RM-2026-0060','Perempuan','1982-08-08','Jl. Brigjen Slamet Riyadi No. 131, Laweyan, Surakarta','0004710967433','B','Amoksisilin',1),('USR-PAS-0061','pasien','RM-2026-0061','Laki-laki','1991-05-24','Jl. Ki Hajar Dewantara No. 124, Kartasura, Sukoharjo','0006915072151','B',NULL,1),('USR-PAS-0062','pasien','RM-2026-0062','Perempuan','1984-07-02','Jl. Kolonel Sugiyono No. 39, Colomadu, Karanganyar','0005143889422','A','Aspirin',1),('USR-PAS-0063','pasien','RM-2026-0063','Laki-laki','2002-02-17','Jl. Kolonel Sugiyono No. 40, Banjarsari, Surakarta','0009457665185','B','Ibuprofen',1),('USR-PAS-0064','pasien','RM-2026-0064','Perempuan','2003-11-28','Jl. Veteran Pasarkliwon No. 139, Jebres, Surakarta','0003651968205','A',NULL,1),('USR-PAS-0065','pasien','RM-2026-0065','Laki-laki','1981-11-23','Jl. Slamet Riyadi No. 12, Kartasura, Sukoharjo','0004419410162','B','Penisilin',1),('USR-PAS-0066','pasien','RM-2026-0066','Perempuan','1999-09-14','Jl. Urip Sumoharjo No. 98, Pasar Kliwon, Surakarta','0003504640817','A','Sulfonamida',1),('USR-PAS-0067','pasien','RM-2026-0067','Laki-laki','1979-11-01','Jl. Veteran No. 19, Colomadu, Karanganyar','0009354723429','O',NULL,1),('USR-PAS-0068','pasien','RM-2026-0068','Perempuan','1992-05-07','Jl. Kolonel Sugiyono No. 147, Kartasura, Sukoharjo','0007763765757','A',NULL,1),('USR-PAS-0069','pasien','RM-2026-0069','Laki-laki','1991-10-28','Jl. Brigjen Slamet Riyadi No. 91, Pasar Kliwon, Surakarta','0003734252963','O',NULL,1),('USR-PAS-0070','pasien','RM-2026-0070','Perempuan','2002-11-02','Jl. Ahmad Yani No. 34, Banjarsari, Surakarta','0009201789018','B','Amoksisilin',4),('USR-PAS-0071','pasien','RM-2026-0071','Laki-laki','1971-09-12','Jl. Kapten Mulyadi No. 124, Serengan, Surakarta','0005789617427','O',NULL,2),('USR-PAS-0072','pasien','RM-2026-0072','Perempuan','1954-12-22','Jl. Slamet Riyadi No. 68, Banjarsari, Surakarta','0007248258423','A',NULL,2),('USR-PAS-0073','pasien','RM-2026-0073','Laki-laki','1955-06-04','Jl. Ki Hajar Dewantara No. 84, Pasar Kliwon, Surakarta','0003781645815','A','Sulfonamida',2),('USR-PAS-0074','pasien','RM-2026-0074','Perempuan','1955-07-04','Jl. Veteran Pasarkliwon No. 30, Banjarsari, Surakarta','0003308745090','AB','Sulfonamida',2),('USR-PAS-0075','pasien','RM-2026-0075','Laki-laki','1962-10-24','Jl. Veteran Pasarkliwon No. 105, Serengan, Surakarta','0009730449971','B',NULL,2),('USR-PAS-0076','pasien','RM-2026-0076','Perempuan','1968-06-04','Jl. Slamet Riyadi No. 102, Serengan, Surakarta','0002952996248','A',NULL,2),('USR-PAS-0077','pasien','RM-2026-0077','Laki-laki','1975-01-26','Jl. Ir. Sutami No. 18, Pasar Kliwon, Surakarta','0003518496186','B',NULL,2),('USR-PAS-0078','pasien','RM-2026-0078','Perempuan','1956-01-09','Jl. Veteran No. 29, Jebres, Surakarta','0001566090561','B',NULL,2),('USR-PAS-0079','pasien','RM-2026-0079','Laki-laki','1975-03-09','Jl. Ki Hajar Dewantara No. 30, Banjarsari, Surakarta','0007340428136','B','Penisilin',2),('USR-PAS-0080','pasien','RM-2026-0080','Perempuan','1972-08-17','Jl. Ki Hajar Dewantara No. 78, Colomadu, Karanganyar','0001133790981','AB','Amoksisilin',2),('USR-PAS-0081','pasien','RM-2026-0081','Laki-laki','1960-12-15','Jl. Veteran No. 17, Laweyan, Surakarta','0006691628146','O','Aspirin',2),('USR-PAS-0082','pasien','RM-2026-0082','Perempuan','1959-10-14','Jl. Ki Hajar Dewantara No. 56, Palur, Karanganyar','0006276339732','B','Sulfonamida',2),('USR-PAS-0083','pasien','RM-2026-0083','Laki-laki','1952-02-11','Jl. Ahmad Yani No. 40, Colomadu, Karanganyar','0001288335378','B',NULL,2),('USR-PAS-0084','pasien','RM-2026-0084','Perempuan','1972-12-24','Jl. Brigjen Slamet Riyadi No. 144, Kartasura, Sukoharjo','0003877685081','AB','Penisilin',2),('USR-PAS-0085','pasien','RM-2026-0085','Laki-laki','1952-01-10','Jl. Brigjen Slamet Riyadi No. 130, Pasar Kliwon, Surakarta','0003070987704','B',NULL,2),('USR-PAS-0086','pasien','RM-2026-0086','Perempuan','1964-02-25','Jl. Brigjen Slamet Riyadi No. 143, Jebres, Surakarta','0008273403366','O',NULL,2),('USR-PAS-0087','pasien','RM-2026-0087','Laki-laki','1965-06-01','Jl. Kolonel Sugiyono No. 18, Laweyan, Surakarta','0005173893550','O','Aspirin',2),('USR-PAS-0088','pasien','RM-2026-0088','Perempuan','1962-08-11','Jl. Brigjen Slamet Riyadi No. 22, Jebres, Surakarta','0001668210614','B','Ibuprofen',2),('USR-PAS-0089','pasien','RM-2026-0089','Laki-laki','1967-08-22','Jl. Kolonel Sugiyono No. 70, Pasar Kliwon, Surakarta','0005783809766','AB',NULL,2),('USR-PAS-0090','pasien','RM-2026-0090','Perempuan','1960-09-22','Jl. Brigjen Slamet Riyadi No. 15, Jebres, Surakarta','0006172957943','A',NULL,2),('USR-PAS-0091','pasien','RM-2026-0091','Laki-laki','1966-06-04','Jl. Veteran No. 34, Palur, Karanganyar','0002521136431','AB',NULL,2),('USR-PAS-0092','pasien','RM-2026-0092','Perempuan','1961-02-28','Jl. Kolonel Sugiyono No. 75, Banjarsari, Surakarta','0002739600515','B',NULL,2),('USR-PAS-0093','pasien','RM-2026-0093','Laki-laki','1961-12-12','Jl. Kolonel Sugiyono No. 103, Banjarsari, Surakarta','0002458198451','B',NULL,2),('USR-PAS-0094','pasien','RM-2026-0094','Perempuan','1955-02-17','Jl. Veteran No. 61, Banjarsari, Surakarta','0005923940434','A',NULL,2),('USR-PAS-0095','pasien','RM-2026-0095','Laki-laki','1951-11-03','Jl. Brigjen Slamet Riyadi No. 115, Kartasura, Sukoharjo','0007988845536','A','Sulfonamida',2),('USR-PAS-0096','pasien','RM-2026-0096','Perempuan','1961-11-10','Jl. Urip Sumoharjo No. 80, Colomadu, Karanganyar','0002940955281','O','Penisilin',2),('USR-PAS-0097','pasien','RM-2026-0097','Laki-laki','1973-11-28','Jl. Brigjen Slamet Riyadi No. 142, Jebres, Surakarta','0003799620568','B','Ibuprofen',2),('USR-PAS-0098','pasien','RM-2026-0098','Perempuan','1971-01-15','Jl. Ki Hajar Dewantara No. 45, Jebres, Surakarta','0005976777081','B',NULL,2),('USR-PAS-0099','pasien','RM-2026-0099','Laki-laki','1951-06-22','Jl. Ahmad Yani No. 17, Kartasura, Sukoharjo','0009995834531','A','Amoksisilin',2),('USR-PAS-0100','pasien','RM-2026-0100','Perempuan','1952-11-20','Jl. Veteran No. 90, Serengan, Surakarta','0002683446724','O',NULL,2),('USR-PAS-0101','pasien','RM-2026-0101','Laki-laki','1995-01-01','Jl. Melati No. 45, Jebres, Surakarta',NULL,'O',NULL,1),('USR-PAS-0103','pasien','RM-2026-0103','Laki-laki','1999-10-10','Jl. Slamet Riyadi No. 99, Surakarta',NULL,NULL,NULL,2),('USR-PAS-0104','pasien','RM-2026-0104','Perempuan','1999-05-10','Jl. Kemuning No. 15, Jebres, Surakarta',NULL,'B','Amoksisilin',3);
/*!40000 ALTER TABLE `pasien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poli`
--

DROP TABLE IF EXISTS `poli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poli` (
  `poli_id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `poli_nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poli_keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`poli_id`),
  UNIQUE KEY `uq_poli_nama` (`poli_nama`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poli`
--

LOCK TABLES `poli` WRITE;
/*!40000 ALTER TABLE `poli` DISABLE KEYS */;
INSERT INTO `poli` VALUES (1,'Poli Umum',NULL),(2,'Poli Umum & Geriatri (Lansia)',NULL),(3,'Poli KIA / Anak',NULL),(4,'Poli Gigi & Mulut',NULL),(5,'Poli Kandungan & Kebidanan',NULL);
/*!40000 ALTER TABLE `poli` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('5SY5B4bUxGm5WW1vtqcZXgEqnN5Buln9zdb7T1Jp',NULL,'172.18.0.1','curl/7.81.0','eyJfdG9rZW4iOiJ6VVNEWnM2dk9RNWVISFpSMU40d2lxWkNDcVQyOWZlMXAwMmZwRlNnIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795261589),('ETkFVlZ4LtKcq3zKTUUZp6Cl5pjGIOq8bMsnqHif',NULL,'172.18.0.1','curl/7.81.0','eyJfdG9rZW4iOiJUQlNmNXpjQXRmZko5aGQ1c1FUQmhVSmxISjJzM3FSelRMT2RwaERnIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795261589),('skJ42j6kZvAe9AcquhSZ1RjbDLixCj6TgK629zNP',NULL,'172.18.0.1','curl/7.81.0','eyJfdG9rZW4iOiJIUFFhSGNIUmxQbnpKUUFXeTVleW01TGhnd3JQbDZ1MXVvekIxdzYzIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1795261589);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staf`
--

DROP TABLE IF EXISTS `staf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staf` (
  `staf_user_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staf_role` enum('dokter','staf','pasien') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staf',
  `staf_jabatan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staf_shiftnama` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staf_shiftmulai` time NOT NULL,
  `staf_shiftselesai` time NOT NULL,
  PRIMARY KEY (`staf_user_id`),
  KEY `fk_staf_user` (`staf_user_id`,`staf_role`),
  CONSTRAINT `fk_staf_user` FOREIGN KEY (`staf_user_id`, `staf_role`) REFERENCES `user` (`user_id`, `user_role`) ON DELETE CASCADE,
  CONSTRAINT `ck_staf_role` CHECK ((`staf_role` = _utf8mb4'staf'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staf`
--

LOCK TABLES `staf` WRITE;
/*!40000 ALTER TABLE `staf` DISABLE KEYS */;
INSERT INTO `staf` VALUES ('USR-ADM-001','staf','Admin Loket (Perekam Medis)','Pagi','07:30:00','14:30:00'),('USR-ADM-002','staf','Admin Loket (IT & Operasional)','Siang','13:30:00','20:30:00');
/*!40000 ALTER TABLE `staf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_role` enum('dokter','staf','pasien') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_nik` char(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_nowa` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `user_createdat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_updatedat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_user_idrole` (`user_id`,`user_role`),
  UNIQUE KEY `uq_user_nik` (`user_nik`),
  UNIQUE KEY `uq_user_email` (`user_email`),
  KEY `idx_user_role` (`user_role`),
  KEY `idx_user_nama` (`user_nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('USR-ADM-001','staf','Rina Wijayanti, A.Md.RMIK','3372045508950001','081334567801','rina.admin@kliniku.id','$2y$12$RGYhBPtv6GIpjJc06Gym6O7CM8hB71pEI05nBRD5NBKMxqdXbFRoq','aktif','2026-11-21 11:29:06','2026-11-21 11:29:06'),('USR-ADM-002','staf','Dedi Kurniawan, S.Kom','3372051210920002','081334567802','dedi.admin@kliniku.id','$2y$12$RGYhBPtv6GIpjJc06Gym6O7CM8hB71pEI05nBRD5NBKMxqdXbFRoq','aktif','2026-11-21 11:29:06','2026-11-21 11:29:06'),('USR-DOC-001','dokter','dr. Andi Pratama, Sp.PD','3372011205820001','081225678901','dr.andi@kliniku.id','$2y$12$/.K1rdL9mCJxUSW6P4cgme/IqKo7DBGXoj6c336y8tX9jCiycdm/2','aktif','2026-11-21 11:29:06','2026-11-21 11:29:06'),('USR-DOC-002','dokter','drg. Siti Nurhaliza','3372024809880002','081225678902','drg.siti@kliniku.id','$2y$12$/.K1rdL9mCJxUSW6P4cgme/IqKo7DBGXoj6c336y8tX9jCiycdm/2','aktif','2026-11-21 11:29:06','2026-11-21 11:29:06'),('USR-DOC-003','dokter','dr. Budi Santoso, Sp.A','3372031503790003','081225678903','dr.budi@kliniku.id','$2y$12$/.K1rdL9mCJxUSW6P4cgme/IqKo7DBGXoj6c336y8tX9jCiycdm/2','aktif','2026-11-21 11:29:06','2026-11-21 11:29:06'),('USR-DOC-004','dokter','dr. Ratna Kartika, Sp.OG','3372046207850004','081225678904','dr.ratna@kliniku.id','$2y$12$/.K1rdL9mCJxUSW6P4cgme/IqKo7DBGXoj6c336y8tX9jCiycdm/2','aktif','2026-11-21 11:29:06','2026-11-21 11:29:06'),('USR-DOC-005','dokter','dr. Hendra Gunawan, Sp.PD','3372051811860005','081225678905','dr.hendra@kliniku.id','$2y$12$/.K1rdL9mCJxUSW6P4cgme/IqKo7DBGXoj6c336y8tX9jCiycdm/2','aktif','2026-11-21 11:29:06','2026-11-21 11:29:06'),('USR-PAS-0001','pasien','Yusuf Hidayat','3372020912254658','081323756669',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0002','pasien','Hesti Pangestu','3372025801169929','085739587039',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0003','pasien','Gilang Susanto','3372011302246882','085291030736',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0004','pasien','Sari Wijaya','3372036810154151','089619335534',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0005','pasien','Hendra Hidayat','3372031505193665','085257683626',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0006','pasien','Maya Nugraha','3372025812156314','081240742311',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0007','pasien','Irfan Susanto','3372041611228518','081345551614',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0008','pasien','Endang Pangestu','3372016502182797','081394214382',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0009','pasien','Rian Nugraha','3372012201179798','085296028436',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0010','pasien','Ayu Nugraha','3372015703175890','089678137358',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0011','pasien','Prasetyo Pratama','3372032706244924','081242329237',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0012','pasien','Bella Gunawan','3372046203233706','085280823176',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0013','pasien','Muhammad Handayani','3372011509185062','081318593410',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0014','pasien','Zahra Saputra','3372034202222161','087841944441',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0015','pasien','Tri Firmansyah','3372042608224120','081223009833',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0016','pasien','Yuni Hidayat','3372036407052791','081335714784',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0017','pasien','Oki Utomo','3372010409049857','081222517517',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0018','pasien','Hanifah Saputra','3372044107015346','085748285503',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0019','pasien','Bambang Syahputra','3372010206058812','087881286543',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0020','pasien','Citra Utomo','3372025902941652','087821003626',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0021','pasien','Hadi Nugraha','3372032111838492','085219736572',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0022','pasien','Intan Santoso','3372034802955670','081368812137',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0023','pasien','Irfan Santoso','3372052402923547','085247816686',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0024','pasien','Citra Lestari','3372034101983144','089645159040',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0025','pasien','Irfan Santoso','3372021810068042','081315614174',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0026','pasien','Yuni Hidayat','3372026007864879','081333763566',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0027','pasien','Indra Wibowo','3372042801024645','081371780854',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0028','pasien','Maya Susanto','3372055205917549','089681969657',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0029','pasien','Bambang Hidayat','3372032612818150','087878642041',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0030','pasien','Anisa Pangestu','3372034307966144','089626726926',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0031','pasien','Aris Wijaya','3372042207003852','087886384014',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0032','pasien','Dian Syahputra','3372025508889376','085732775593',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0033','pasien','Eko Pratama','3372012008068462','085794525678',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0034','pasien','Wulandari Pratama','3372054604028612','081284813739',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0035','pasien','Danang Utomo','3372022108915500','085720399639',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0036','pasien','Endang Santoso','3372024512942053','085764707061',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0037','pasien','Muhammad Syahputra','3372011607076764','085262343121',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0038','pasien','Putri Pratama','3372046211843705','085727129912',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0039','pasien','Zainal Pratama','3372040507033978','081244917589',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0040','pasien','Intan Lestari','3372054108921854','085240094589',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0041','pasien','Wahyu Santoso','3372012208934572','085744392446',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0042','pasien','Indah Wijaya','3372014712844979','081250477742',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0043','pasien','Ahmad Lestari','3372041207773507','085733640702',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0044','pasien','Intan Susanto','3372025505938614','087891917334',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0045','pasien','Lukman Nugraha','3372032310871167','087835643159',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0046','pasien','Wulandari Firmansyah','3372025002807626','089642655866',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0047','pasien','Kurniawan Handayani','3372020905792977','089635848226',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0048','pasien','Intan Syahputra','3372025004906913','081350569669',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0049','pasien','Joko Santoso','3372031901778691','085769118721',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0050','pasien','Maya Firmansyah','3372016211043486','081385543051',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0051','pasien','Febri Kurnia','3372041507945872','087867556566',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0052','pasien','Wulandari Setiawan','3372024602923848','087820075036',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0053','pasien','Hadi Wijaya','3372020308905335','089689160218',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0054','pasien','Indah Nugraha','3372034601995729','085726690465',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0055','pasien','Prasetyo Sulistyo','3372031401035103','081222257687',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0056','pasien','Wulandari Sulistyo','3372045410912495','085756702542',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0057','pasien','Kurniawan Lestari','3372051311771602','085721819185',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0058','pasien','Eka Pratama','3372054701796928','087876906611',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0059','pasien','Dimas Pratama','3372051003931225','087864759583',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0060','pasien','Ratna Nugraha','3372044808823370','085735583290',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0061','pasien','Kurniawan Pangestu','3372032405919023','081369934576',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0062','pasien','Zahra Utomo','3372034207848750','089661167978',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0063','pasien','Tri Susanto','3372041702021252','089629348212',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0064','pasien','Zahra Rahayu','3372056811037227','085294127561',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0065','pasien','Hadi Kusuma','3372012311818270','081350193656',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0066','pasien','Lestari Lestari','3372055409993951','081333497411',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0067','pasien','Yusuf Nugraha','3372040111795713','089683363160',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0068','pasien','Wulandari Lestari','3372044705928917','081241837210',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0069','pasien','Ahmad Rahayu','3372012810919139','085240885967',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0070','pasien','Yuni Santoso','3372034211028223','081287789079',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0071','pasien','Fajar Setiawan','3372051209719222','085232084778',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0072','pasien','Bella Utomo','3372046212546993','081262958901',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0073','pasien','Tri Rahayu','3372020406558725','081293154172',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0074','pasien','Indah Wijaya','3372024407551743','081250851122',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0075','pasien','Lukman Gunawan','3372022410627798','089623290458',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0076','pasien','Octavia Sulistyo','3372034406689921','089658140388',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0077','pasien','Yusuf Kurnia','3372032601754991','081385776373',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0078','pasien','Kartika Santoso','3372024901563130','087843642236',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0079','pasien','Tri Susanto','3372010903753077','089666500369',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0080','pasien','Ratna Syahputra','3372025708721711','089698436473',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0081','pasien','Nugroho Hidayat','3372011512602324','085291644298',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0082','pasien','Juwita Sulistyo','3372015410592875','089697364555',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0083','pasien','Muhammad Lestari','3372041102526121','089644210729',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0084','pasien','Citra Lestari','3372036412723132','087818050510',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0085','pasien','Hendra Lestari','3372051001526120','085223905032',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0086','pasien','Lestari Gunawan','3372036502644706','085785291541',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0087','pasien','Indra Wijaya','3372020106653348','087898227303',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0088','pasien','Gita Rahayu','3372025108627063','085253537934',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0089','pasien','Bambang Kusuma','3372042208678958','087869330166',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0090','pasien','Juwita Syahputra','3372036209601745','081363051354',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0091','pasien','Danang Nugraha','3372010406669150','089667801537',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0092','pasien','Nurul Pratama','3372036802618034','087864280720',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0093','pasien','Febri Kurnia','3372011212618156','081242657384',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0094','pasien','Citra Pangestu','3372055702554177','085257123808',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0095','pasien','Wahyu Santoso','3372020311519096','085785663295',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0096','pasien','Citra Firmansyah','3372035011611921','085278093604',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0097','pasien','Guntur Wijaya','3372012811739308','085772104107',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0098','pasien','Putri Pangestu','3372015501716627','089621318464',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0099','pasien','Muhammad Lestari','3372052206511858','089696879854',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0100','pasien','Juwita Nugraha','3372026011526462','081288213973',NULL,'$2y$12$g7sLANiTkkZw0v.qbwZbvumkIMgzx4fCyheTOARbpJ/Tzvdob0DCy','aktif','2026-11-21 11:29:07','2026-11-21 11:29:07'),('USR-PAS-0101','pasien','Budi Hartono','3372010101950001','081234567890',NULL,'$2y$12$d.OT/jURSAGaac7blMoRW.kxXJTLhlQMvAoe1J1kEBne6.QRf8eXu','aktif','2026-11-21 11:44:54','2026-11-21 11:44:54'),('USR-PAS-0103','pasien','Citra Lestari','3372091010990001','081234567899',NULL,'$2y$12$DbsL8ZfZqjKjuXpjzHwk6.4zxYpa/IlIKH9AsOGbE/OYwXcIK2Wju','aktif','2026-11-22 04:08:11','2026-11-22 04:08:11'),('USR-PAS-0104','pasien','Siti Nurhaliza Test','3372085005990002','081298765432',NULL,'$2y$04$pa4I./Y8cp/qtdGOrHKsA.mvm7pZPL95YEa/aTaxhl/fwDC65ejfS','aktif','2026-11-22 08:16:24','2026-11-22 08:16:24');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@example.com','2026-11-21 04:02:05','$2y$12$ksY5Xcwh280Kxim3ejJeR..0qfvmeJJtQwm4tSY06KZvfpHTLWHye','rf6ibqi2lZ','2026-11-21 04:02:05','2026-11-21 04:02:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-22 18:00:09
