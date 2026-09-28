-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: p4i_publisher
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `authors`
--

DROP TABLE IF EXISTS `authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `pen_name` varchar(150) NOT NULL,
  `bio` text DEFAULT NULL,
  `id_card_number` text NOT NULL,
  `id_card_path` varchar(255) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_holder_name` varchar(150) DEFAULT NULL,
  `kyc_status` enum('unverified','pending','verified','rejected') NOT NULL DEFAULT 'unverified',
  `rejection_reason` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authors_user_id_unique` (`user_id`),
  CONSTRAINT `authors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authors`
--

LOCK TABLES `authors` WRITE;
/*!40000 ALTER TABLE `authors` DISABLE KEYS */;
INSERT INTO `authors` VALUES (1,5,'Dr. Hendra Wijaya, M.Kom.','Dosen dan peneliti di bidang Software Architecture dan Distributed Systems.','eyJpdiI6InBVeThSVHpvalZKVUc4OUkyaEcvVnc9PSIsInZhbHVlIjoiOEhRclYzMWdEa2xUa3kvZ2J6bjA5ekc3cDFjbGp3dThBQ2xUdzFSRWpKYz0iLCJtYWMiOiI4YTMxMmJlZGE0ZDljYjU4MmVmZmIzMDdkNGYzY2Q5ZDU4YjYwNTM1YzY1NTc1NDRiM2M1NzRmYTdhMmFkZmM5IiwidGFnIjoiIn0=','private/kyc/dummy_ktp.png','Bank Central Asia (BCA)','8830123456','Hendra Wijaya','verified',NULL,'2026-08-05 00:30:58','2026-09-04 00:30:58','2026-09-04 00:30:58'),(2,6,'Ahmad F.','Penulis lepas buku metodologi riset.','eyJpdiI6IkJtdTd1VU9wdWFQU2dmb2FyS2VUU1E9PSIsInZhbHVlIjoiclBYZWl5bFFMRWR4NWVFV3kzWjRuT3ZTTU1HMzNCOEVJVHowSTVmamlNWT0iLCJtYWMiOiI1MjE3N2UwMTU4ZjFkODFmYzJhYzA0OTI1NDdhNzNiMmM4ODJhNzc5ZTU2MTA0MDM0MTFjZTkyNTdkYjFmYTY0IiwidGFnIjoiIn0=','private/kyc/dummy_ktp.png','Bank Mandiri','1330098765432','Ahmad Fauzi','pending',NULL,NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `authors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `book_category`
--

DROP TABLE IF EXISTS `book_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_category_book_id_category_id_unique` (`book_id`,`category_id`),
  KEY `book_category_category_id_foreign` (`category_id`),
  CONSTRAINT `book_category_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `book_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_category`
--

LOCK TABLES `book_category` WRITE;
/*!40000 ALTER TABLE `book_category` DISABLE KEYS */;
INSERT INTO `book_category` VALUES (1,1,3,NULL,NULL),(2,1,5,NULL,NULL),(3,2,3,NULL,NULL),(4,3,1,NULL,NULL),(5,4,1,NULL,NULL);
/*!40000 ALTER TABLE `book_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `book_licenses`
--

DROP TABLE IF EXISTS `book_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_licenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `book_id` bigint(20) unsigned NOT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `license_key` varchar(255) NOT NULL,
  `status` enum('active','revoked') NOT NULL DEFAULT 'active',
  `revocation_reason` text DEFAULT NULL,
  `last_read_page` int(11) NOT NULL DEFAULT 1,
  `valid_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_licenses_license_key_unique` (`license_key`),
  UNIQUE KEY `book_licenses_user_book_unique` (`user_id`,`book_id`),
  KEY `book_licenses_order_id_foreign` (`order_id`),
  KEY `book_licenses_book_id_foreign` (`book_id`),
  CONSTRAINT `book_licenses_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  CONSTRAINT `book_licenses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `book_licenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_licenses`
--

LOCK TABLES `book_licenses` WRITE;
/*!40000 ALTER TABLE `book_licenses` DISABLE KEYS */;
INSERT INTO `book_licenses` VALUES (1,3,3,NULL,'9c7dcb00-fb73-4ba9-90a1-c2b83a9516f2','active',NULL,1,NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(2,4,4,NULL,'01ee6f45-9907-4f23-a0e0-73841b87957e','active',NULL,1,NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(3,3,2,NULL,'7f2e692d-0529-49b9-8461-c0f80a67fa02','active',NULL,1,NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `book_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `book_submissions`
--

DROP TABLE IF EXISTS `book_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_submissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `synopsis` text NOT NULL,
  `proposed_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `manuscript_path` varchar(255) NOT NULL,
  `cover_preview_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','in_review','revision_requested','approved','rejected','published') NOT NULL DEFAULT 'draft',
  `book_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `book_submissions_author_id_foreign` (`author_id`),
  KEY `book_submissions_category_id_foreign` (`category_id`),
  KEY `book_submissions_book_id_foreign` (`book_id`),
  CONSTRAINT `book_submissions_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `book_submissions_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL,
  CONSTRAINT `book_submissions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_submissions`
--

LOCK TABLES `book_submissions` WRITE;
/*!40000 ALTER TABLE `book_submissions` DISABLE KEYS */;
INSERT INTO `book_submissions` VALUES (1,1,NULL,'Desain Sistem Terdistribusi untuk Pemula','Buku ini membahas dasar-dasar arsitektur sistem terdistribusi, load balancing, dan caching.',110000.00,'submissions/manuscript_draft.pdf',NULL,'in_review',NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(2,1,NULL,'Optimasi Query Database SQL','Panduan praktis melakukan optimasi query pada database SQL untuk menangani data berskala besar.',85000.00,'submissions/manuscript_draft.pdf',NULL,'revision_requested',NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(3,2,NULL,'Panduan Menulis Opini Ilmiah','Buku saku untuk mahasiswa dan akademisi dalam menyusun karya tulis opini yang berkualitas.',50000.00,'submissions/manuscript_draft.pdf',NULL,'submitted',NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `book_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `books` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `isbn` varchar(255) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `cover_image_path` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `books_slug_unique` (`slug`),
  KEY `books_published_created_index` (`is_published`,`created_at`),
  KEY `books_author_id_foreign` (`author_id`),
  CONSTRAINT `books_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books`
--

LOCK TABLES `books` WRITE;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` VALUES (1,NULL,'Panduan Penulisan Skripsi & Tesis Sistem Informasi','panduan-penulisan-skripsi-tesis-sistem-informasi','Tim P4I',NULL,45000.00,NULL,NULL,NULL,NULL,'private_books/sample_book_1.pdf',1,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(2,NULL,'Dasar Metodologi Riset Kuantitatif','dasar-metodologi-riset-kuantitatif','Tim P4I',NULL,0.00,NULL,NULL,NULL,NULL,'private_books/sample_book_1.pdf',1,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(3,1,'Clean Architecture di Laravel: Panduan Praktisi','clean-architecture-di-laravel-panduan-praktisi','Dr. Hendra Wijaya, M.Kom.',NULL,120000.00,NULL,NULL,NULL,NULL,'private_books/sample_book_2.pdf',1,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(4,1,'Mikroservis dan Cloud Storage Skalabilitas Tinggi','mikroservis-dan-cloud-storage-skalabilitas-tinggi','Dr. Hendra Wijaya, M.Kom.',NULL,95000.00,NULL,NULL,NULL,NULL,'private_books/sample_book_2.pdf',1,'2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-test@example.com|127.0.0.1','i:1;',1788512723),('laravel-cache-test@example.com|127.0.0.1:timer','i:1788512723;',1788512723);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Rekayasa Perangkat Lunak','rekayasa-perangkat-lunak','2026-09-04 00:30:58','2026-09-04 00:30:58'),(2,'Kecerdasan Buatan','kecerdasan-buatan','2026-09-04 00:30:58','2026-09-04 00:30:58'),(3,'Metodologi Penelitian','metodologi-penelitian','2026-09-04 00:30:58','2026-09-04 00:30:58'),(4,'Manajemen Bisnis Digital','manajemen-bisnis-digital','2026-09-04 00:30:58','2026-09-04 00:30:58'),(5,'Pendidikan & Sosial','pendidikan-sosial','2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_31_024127_create_books_table',1),(5,'2026_08_31_024128_create_orders_table',1),(6,'2026_08_31_024129_create_order_items_table',1),(7,'2026_08_31_024130_create_book_licenses_table',1),(8,'2026_08_31_150000_add_is_admin_to_users_table',1),(9,'2026_09_01_023554_add_unique_constraint_to_book_licenses_table',1),(10,'2026_09_01_025645_add_performance_indexes_to_tables',1),(11,'2026_09_01_070613_create_categories_table',1),(12,'2026_09_01_070615_create_book_category_table',1),(13,'2026_09_01_070616_create_reviews_table',1),(14,'2026_09_01_070618_add_last_read_page_to_book_licenses_table',1),(15,'2026_09_01_121528_add_details_to_books_table',1),(16,'2026_09_03_065408_change_book_id_foreign_on_book_licenses_table',1),(17,'2026_09_03_071658_add_is_active_to_users_table',1),(18,'2026_09_03_071704_add_revocation_reason_to_book_licenses_table',1),(19,'2026_09_03_073507_create_authors_table',1),(20,'2026_09_03_073514_add_author_id_to_books_table',1),(21,'2026_09_03_073522_create_book_submissions_table',1),(22,'2026_09_03_073528_create_submission_reviews_table',1),(23,'2026_09_03_073533_create_royalty_ledgers_table',1),(24,'2026_09_03_073537_create_payout_requests_table',1),(25,'2026_09_04_042504_update_payout_and_royalty_schema',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `book_id` bigint(20) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_book_id_foreign` (`book_id`),
  CONSTRAINT `order_items_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,'01M1NN7W7YFMHB261256VNMKD5',3,120000.00,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(2,'01M1NN7W92A06ABW9B1DTAEYKJ',4,95000.00,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(3,'01M1NN7W9GCV19G7BPRSW1037D',2,0.00,'2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
  `payment_type` varchar(255) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_created_index` (`user_id`,`created_at`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES ('01M1NN7W7YFMHB261256VNMKD5',3,120000.00,'success','bank_transfer','eyJpdiI6IkZwU3BmSnJTK3NBTzRDNmZjcnJjN0E9PSIsInZhbHVlIjoiKzZVU3JXaFBvWDVKdEwrdU1STUprbXA5c3JRQzBKQmx6RFdnc1BrcnE0OD0iLCJtYWMiOiJlNTk3MDk1OTBlNGNlYWNjNjgyOWVmN2E4M2EwZjQxNTg1MzBmMjQ2ZWFlNjZkYTcxMjM5NDgzZDY4Mjc1MGMyIiwidGFnIjoiIn0=','2026-09-04 00:30:58','2026-09-04 00:30:58'),('01M1NN7W92A06ABW9B1DTAEYKJ',4,95000.00,'success','echannel',NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58'),('01M1NN7W9GCV19G7BPRSW1037D',3,0.00,'success','free',NULL,'2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
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
-- Table structure for table `payout_requests`
--

DROP TABLE IF EXISTS `payout_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payout_requests` (
  `id` varchar(26) NOT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `bank_name_snapshot` varchar(50) DEFAULT NULL,
  `bank_account_snapshot` varchar(50) DEFAULT NULL,
  `bank_holder_name_snapshot` varchar(150) DEFAULT NULL,
  `status` enum('requested','processing','completed','rejected') NOT NULL DEFAULT 'requested',
  `transfer_proof_path` varchar(255) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payout_requests_author_id_foreign` (`author_id`),
  KEY `payout_requests_processed_by_foreign` (`processed_by`),
  CONSTRAINT `payout_requests_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`),
  CONSTRAINT `payout_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payout_requests`
--

LOCK TABLES `payout_requests` WRITE;
/*!40000 ALTER TABLE `payout_requests` DISABLE KEYS */;
INSERT INTO `payout_requests` VALUES ('01M1NN7WA9PDD2PZQQ7S2TQKYC',1,100000.00,'Bank Central Asia (BCA)','8830123456','Hendra Wijaya','completed',NULL,'TRF-BCA-20260901-09881',NULL,1,'2026-08-30 00:30:58','2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `payout_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `book_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reviews_user_id_book_id_unique` (`user_id`,`book_id`),
  KEY `reviews_book_id_foreign` (`book_id`),
  CONSTRAINT `reviews_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `royalty_ledgers`
--

DROP TABLE IF EXISTS `royalty_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `royalty_ledgers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `book_id` bigint(20) unsigned NOT NULL,
  `payout_request_id` varchar(26) DEFAULT NULL,
  `gross_sale` decimal(12,2) NOT NULL,
  `author_percentage` decimal(5,2) NOT NULL DEFAULT 70.00,
  `author_earning` decimal(12,2) NOT NULL,
  `platform_earning` decimal(12,2) NOT NULL,
  `status` enum('pending','available','withdrawn') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `royalty_ledgers_order_item_id_unique` (`order_item_id`),
  KEY `royalty_ledgers_book_id_foreign` (`book_id`),
  KEY `royalty_ledgers_author_id_status_index` (`author_id`,`status`),
  KEY `royalty_ledgers_payout_request_id_foreign` (`payout_request_id`),
  CONSTRAINT `royalty_ledgers_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`),
  CONSTRAINT `royalty_ledgers_book_id_foreign` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  CONSTRAINT `royalty_ledgers_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`),
  CONSTRAINT `royalty_ledgers_payout_request_id_foreign` FOREIGN KEY (`payout_request_id`) REFERENCES `payout_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `royalty_ledgers`
--

LOCK TABLES `royalty_ledgers` WRITE;
/*!40000 ALTER TABLE `royalty_ledgers` DISABLE KEYS */;
INSERT INTO `royalty_ledgers` VALUES (1,1,1,3,NULL,120000.00,70.00,84000.00,36000.00,'available','2026-09-04 00:30:58','2026-09-04 00:30:58'),(2,1,2,4,NULL,95000.00,70.00,66500.00,28500.00,'available','2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `royalty_ledgers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
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
INSERT INTO `sessions` VALUES ('wlk5kkaa67ggJQAM6Zr177hMnyRIGkdoIks0NTXH',5,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWUhKZTBsc2M0U3hkanBlN29DcFhFbmZpSTRlYXNuMWNJcno1SXhFQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hdXRob3IvcGF5b3V0cyI7czo1OiJyb3V0ZSI7czoyMDoiYXV0aG9yLnBheW91dHMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O30=',1788513008),('Xxf0e2KDEqRpRJe5CGBMDdNLBJnPteib7uLLZeXg',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiU3N1TDVsc1lYYTFWUnplNU1WTXl1WTV1ZlJIMTZvUGUzNGszdkJDVyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNjoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2F1dGhvci9wYXlvdXRzIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1788512621),('ydTrG3AcaetCjdZGghhVMFJAKrVMO8eZmOcJgKWQ',5,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTXNSb0dOblRFMEtwUTFVbnVwdzNoMktMQXJPM0lxVmxheTA0MUFkUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hdXRob3IvcGF5b3V0cyI7czo1OiJyb3V0ZSI7czoyMDoiYXV0aG9yLnBheW91dHMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O30=',1788509036);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submission_reviews`
--

DROP TABLE IF EXISTS `submission_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submission_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint(20) unsigned NOT NULL,
  `reviewer_id` bigint(20) unsigned NOT NULL,
  `feedback` text NOT NULL,
  `action` enum('request_revision','approve','reject') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `submission_reviews_submission_id_foreign` (`submission_id`),
  KEY `submission_reviews_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `submission_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submission_reviews_submission_id_foreign` FOREIGN KEY (`submission_id`) REFERENCES `book_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submission_reviews`
--

LOCK TABLES `submission_reviews` WRITE;
/*!40000 ALTER TABLE `submission_reviews` DISABLE KEYS */;
INSERT INTO `submission_reviews` VALUES (1,2,2,'Mohon tambahkan bab komparasi index B-Tree dan Hash Index pada Bab 4 sebelum naskah diterbitkan.','request_revision','2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `submission_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Utama P4I','admin@p4i.org',NULL,'$2y$12$zLNB7RGNSGPa9eKJRFeYVuPnpVI2QPcSPKUi0fWlhsFByjGjtrHVi',NULL,1,1,'2026-09-04 00:30:56','2026-09-04 00:30:56'),(2,'Kurator Naskah','kurator@p4i.org',NULL,'$2y$12$bI/ffFFy1PaKYwAWSUhtqeVEjIkkGzyU..9lw8uTUc2pIH.xTvx8q',NULL,1,1,'2026-09-04 00:30:57','2026-09-04 00:30:57'),(3,'Rian Mahasiswa','rian@gmail.com',NULL,'$2y$12$Qugp.DDvOuorztM.QPi0t.Pytuv5ZeDw.zDidotUSNLAPycE2ykLe',NULL,0,1,'2026-09-04 00:30:57','2026-09-04 00:30:57'),(4,'Siti Pembaca','siti@gmail.com',NULL,'$2y$12$t8Bs602jPgdYrarshDaAremAwNbBC10ms1wmAXlM.AiWsJbk1LUIi',NULL,0,1,'2026-09-04 00:30:57','2026-09-04 00:30:57'),(5,'Dr. Hendra Wijaya','hendra@author.p4i.org',NULL,'$2y$12$HgujhKjEi4ACkwq47e.FLOj2EfxI1w3fkTre9b1qkUfmp/By3TUqO',NULL,0,1,'2026-09-04 00:30:58','2026-09-04 00:30:58'),(6,'Ahmad Penulis Baru','ahmad@author.p4i.org',NULL,'$2y$12$qILwQpeVd7gS2053nx4B6.jSeFILfShlhsthB3VHe0NCTxsOqHTha',NULL,0,1,'2026-09-04 00:30:58','2026-09-04 00:30:58');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'p4i_publisher'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-10 21:39:04
