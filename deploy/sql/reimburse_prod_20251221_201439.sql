-- MySQL dump 10.13  Distrib 8.0.44, for Linux (aarch64)
--
-- Host: localhost    Database: reimburse
-- ------------------------------------------------------
-- Server version	8.0.44

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
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
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
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  KEY `categories_is_active_sort_order_index` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Transportasi','car','Biaya transportasi seperti taksi, ojol, bensin',1,1,'2025-12-19 08:21:16','2025-12-19 08:21:16'),(2,'Makan','utensils','Biaya makan dan minum',2,1,'2025-12-19 08:21:16','2025-12-19 08:21:16'),(3,'Parkir','parking','Biaya parkir kendaraan',3,1,'2025-12-19 08:21:16','2025-12-19 08:21:16'),(4,'Tol','road','Biaya tol',4,1,'2025-12-19 08:21:16','2025-12-19 08:21:16'),(5,'Akomodasi','hotel','Biaya hotel dan penginapan',5,1,'2025-12-19 08:21:16','2025-12-19 08:21:16'),(6,'Supplies','box','Pembelian ATK dan perlengkapan',6,1,'2025-12-19 08:21:16','2025-12-19 08:21:16'),(7,'Komunikasi','phone','Pulsa, internet, dan komunikasi',7,1,'2025-12-19 08:21:16','2025-12-19 08:21:16'),(8,'Lainnya','ellipsis-h','Biaya lain-lain',99,1,'2025-12-19 08:21:16','2025-12-19 08:21:16');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `is_auto_registered` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_name_unique` (`name`),
  KEY `clients_created_by_foreign` (`created_by`),
  KEY `clients_name_index` (`name`),
  CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'ACE Medical',NULL,0,'2025-12-19 03:21:14','2025-12-19 03:21:14'),(2,'Perjalalan Stasiun',NULL,0,'2025-12-19 03:21:14','2025-12-19 03:21:14'),(3,'Agung Sedayu',NULL,0,'2025-12-19 03:21:14','2025-12-19 03:21:14'),(4,'Hometown',NULL,0,'2025-12-19 03:21:14','2025-12-19 03:21:14'),(5,'Industropolis Batang',NULL,0,'2025-12-19 03:21:14','2025-12-19 03:21:14'),(6,'Wadimor',NULL,0,'2025-12-19 03:21:14','2025-12-19 03:21:14'),(7,'Office Equipment',NULL,0,'2025-12-19 10:45:31','2025-12-19 10:45:31');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
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
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
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
  `attempts` tinyint unsigned NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_01_000001_create_clients_table',1),(5,'2024_01_01_000002_create_categories_table',1),(6,'2024_01_01_000003_create_reports_table',1),(7,'2024_01_01_000004_create_reimbursements_table',1),(8,'2025_12_19_154919_create_personal_access_tokens_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
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
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (6,'App\\Models\\User',1,'mobile-app','3a196ba2266e182b450ba46b58511b5c5e1f4b91e99a708052809cbfd2466fe4','[\"*\"]','2025-12-21 12:16:44',NULL,'2025-12-21 12:06:41','2025-12-21 12:16:44');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reimbursements`
--

DROP TABLE IF EXISTS `reimbursements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reimbursements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','in_report','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `report_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reimbursements_report_id_foreign` (`report_id`),
  KEY `reimbursements_user_id_status_index` (`user_id`,`status`),
  KEY `reimbursements_user_id_transaction_date_index` (`user_id`,`transaction_date`),
  KEY `reimbursements_client_id_index` (`client_id`),
  KEY `reimbursements_category_id_index` (`category_id`),
  CONSTRAINT `reimbursements_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reimbursements_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reimbursements_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reimbursements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reimbursements`
--

LOCK TABLES `reimbursements` WRITE;
/*!40000 ALTER TABLE `reimbursements` DISABLE KEYS */;
INSERT INTO `reimbursements` VALUES (1,1,1,1,100000.00,'2025-11-04','Bensin','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(2,1,1,2,67000.00,'2025-11-04','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(3,1,1,2,29000.00,'2025-11-04','Kopi','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(4,1,2,1,100000.00,'2025-11-09','Bensin','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(5,1,3,1,45000.00,'2025-11-10','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(6,1,3,1,111400.00,'2025-11-10','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(7,1,3,1,115500.00,'2025-11-10','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(8,1,3,2,79000.00,'2025-11-10','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(9,1,3,1,62000.00,'2025-11-10','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(10,1,3,3,22000.00,'2025-11-10','Parkir & Toll','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(11,1,3,2,61000.00,'2025-11-10','Kopi','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(12,1,3,2,35000.00,'2025-11-10','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(13,1,3,1,26000.00,'2025-11-10','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(14,1,4,1,31400.00,'2025-11-11','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(15,1,4,2,45000.00,'2025-11-11','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(16,1,4,3,5000.00,'2025-11-11','Parkir','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(17,1,4,1,60000.00,'2025-11-11','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(18,1,4,3,5000.00,'2025-11-11','Parkir','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(19,1,4,1,34000.00,'2025-11-11','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(20,1,4,2,110000.00,'2025-11-11','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(21,1,2,3,100000.00,'2025-11-11','Parkir stasiun','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(22,1,2,1,100000.00,'2025-11-11','Bensin','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(23,1,4,1,217000.00,'2025-11-20','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(24,1,4,2,54000.00,'2025-11-20','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(25,1,4,2,26500.00,'2025-11-20','Kopi','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(26,1,4,1,33700.00,'2025-11-20','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(27,1,4,2,54000.00,'2025-11-20','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(28,1,4,1,34500.00,'2025-11-20','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(29,1,4,4,61000.00,'2025-11-20','Toll & Parkir','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(31,1,4,1,34500.00,'2025-11-20','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(32,1,4,2,80000.00,'2025-11-20','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(33,1,4,2,48000.00,'2025-11-21','Kopi','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(34,1,4,1,59500.00,'2025-11-21','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(35,1,4,2,95800.00,'2025-11-21','Kopi','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(36,1,4,1,120000.00,'2025-11-21','Taxi / Ojol','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(37,1,4,4,18500.00,'2025-11-21','Toll & Parkir','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(38,1,4,2,131000.00,'2025-11-21','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(39,1,2,3,100000.00,'2025-11-22','Parkir Stasiun','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(40,1,2,1,200000.00,'2025-11-22','Bensin','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(41,1,2,2,50800.00,'2025-11-22','Makan','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(42,1,5,1,103000.00,'2025-11-26','Bensin','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(43,1,6,4,100000.00,'2025-11-26','Top-Up Etoll','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(44,1,6,2,47700.00,'2025-11-26','Kopi','placeholders/receipt_placeholder.jpg','in_report',2,'2025-12-19 03:24:39','2025-12-19 16:55:29'),(45,1,1,1,50000.00,'2025-12-19',NULL,'receipts/JgODnzcciOnURgNciyekGSAqqwE6A8QvXXLnxifV.jpg','pending',NULL,'2025-12-19 16:02:13','2025-12-19 16:02:13'),(47,1,3,4,50000.00,'2025-12-19',NULL,'receipts/Xa4EMWdFjreoHVBmDFADfXmIVYjQy51PTPJHCley.jpg','pending',NULL,'2025-12-19 16:39:24','2025-12-19 16:39:24'),(49,1,1,3,50000.00,'2025-12-19',NULL,'receipts/Vx4WjsZTNQltRLDYv86WBoB6aM7dz4uw9W1sET4l.jpg','pending',NULL,'2025-12-19 19:54:10','2025-12-19 19:54:10'),(50,1,1,3,25000.00,'2025-12-19',NULL,'receipts/VYAzh0iEnQXD3jTr6TaBaY8Nw7oUmefkOWfh1BTl.jpg','pending',NULL,'2025-12-19 20:02:41','2025-12-19 20:02:41'),(51,1,1,3,25000.00,'2025-12-19',NULL,'receipts/Z85AqhoSqd6kNyU0yW7nTbF2pHYWIKc5dgyFr2B3.jpg','pending',NULL,'2025-12-19 20:02:41','2025-12-19 20:02:41'),(52,1,1,1,10000.00,'2025-12-19',NULL,'receipts/D473u0whO0Je257qqhK2Eay9HT7PHFHHaEQiRdN2.jpg','pending',NULL,'2025-12-19 20:06:01','2025-12-19 20:06:01');
/*!40000 ALTER TABLE `reimbursements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entry_count` int NOT NULL DEFAULT '0',
  `status` enum('draft','generated','submitted','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_user_id_status_index` (`user_id`,`status`),
  KEY `reports_period_start_period_end_index` (`period_start`,`period_end`),
  CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (2,1,'2025-11-01','2025-11-30',3012800.00,43,'generated','reports/Admin_Crocodic_202511_2.pdf',NULL,'2025-12-19 16:55:29','2025-12-19 18:42:25');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('8xP26tOBBhfZOAxzx3bsTyRB4GS9WuqJFCRJTcYj',NULL,'172.21.0.5','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTnVRMm04eHdjZk1CaFJ1M0FtQ2pJQUJJNk1XTzhjVG1GcUh4UnZ4UCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly9yZWltYnVyc2UuZGFkaS53ZWIuaWQiO319',1766217149);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
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
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Crocodic','admin@crocodic.com',1,NULL,'$2y$12$Qx8pcxN9Wa6OppDe9ACyFue9.ir2qK8tobYvf.WEr0UQXEiGio5SC',NULL,'2025-12-19 02:14:31','2025-12-19 11:34:55'),(2,'ady','ady@crocodic.com',0,NULL,'$2y$12$heJqmkaqDNHpzxeE5p42rOiykdyFpan/J0PfQiNAicqaSpVDY1Fxy',NULL,'2025-12-19 11:38:27','2025-12-19 11:38:27');
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

-- Dump completed on 2025-12-21 13:14:40
