-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: ebp_restaurant_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `account_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `account_code` varchar(50) DEFAULT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `parent_account_id` bigint(20) DEFAULT NULL,
  `balance_type` varchar(10) DEFAULT 'DEBIT',
  `opening_balance` decimal(18,2) DEFAULT 0.00,
  `current_balance` decimal(18,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `account_code` (`account_code`),
  KEY `parent_account_id` (`parent_account_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_code` (`account_code`),
  KEY `idx_type` (`account_type`),
  CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`parent_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_analytics`
--

DROP TABLE IF EXISTS `ad_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_campaign_id` int(11) NOT NULL,
  `analytics_date` date NOT NULL,
  `impressions` int(11) DEFAULT 0,
  `clicks` int(11) DEFAULT 0,
  `conversions` int(11) DEFAULT 0,
  `spend` decimal(10,2) DEFAULT 0.00,
  `ctr` decimal(5,2) DEFAULT NULL,
  `conversion_rate` decimal(5,2) DEFAULT NULL,
  `cpa` decimal(10,2) DEFAULT NULL,
  `additional_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_metrics`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_campaign_date` (`ad_campaign_id`,`analytics_date`),
  KEY `idx_analytics_date` (`analytics_date`),
  CONSTRAINT `ad_analytics_ibfk_1` FOREIGN KEY (`ad_campaign_id`) REFERENCES `ad_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_analytics`
--

LOCK TABLES `ad_analytics` WRITE;
/*!40000 ALTER TABLE `ad_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_campaigns`
--

DROP TABLE IF EXISTS `ad_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(100) NOT NULL,
  `campaign_type` enum('supplier','equipment','service','restaurant','brand') NOT NULL,
  `advertiser_type` enum('supplier','restaurant','brand') NOT NULL,
  `advertiser_id` int(11) NOT NULL,
  `ad_format` enum('banner','sponsored_listing','product_listing','sponsored_content','push_notification') NOT NULL,
  `targeting_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`targeting_criteria`)),
  `budget` decimal(15,2) NOT NULL,
  `actual_spend` decimal(15,2) DEFAULT 0.00,
  `impressions` int(11) DEFAULT 0,
  `clicks` int(11) DEFAULT 0,
  `conversions` int(11) DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','active','paused','completed','cancelled') DEFAULT 'draft',
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_campaign_type` (`campaign_type`),
  KEY `idx_advertiser_id` (`advertiser_id`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_campaigns`
--

LOCK TABLES `ad_campaigns` WRITE;
/*!40000 ALTER TABLE `ad_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_clicks`
--

DROP TABLE IF EXISTS `ad_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_campaign_id` int(11) NOT NULL,
  `ad_impression_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `click_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`click_context`)),
  `device_type` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_impression_id` (`ad_impression_id`),
  KEY `idx_ad_campaign_id` (`ad_campaign_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ad_clicks_ibfk_1` FOREIGN KEY (`ad_campaign_id`) REFERENCES `ad_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_clicks_ibfk_2` FOREIGN KEY (`ad_impression_id`) REFERENCES `ad_impressions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_clicks`
--

LOCK TABLES `ad_clicks` WRITE;
/*!40000 ALTER TABLE `ad_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_conversions`
--

DROP TABLE IF EXISTS `ad_conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_conversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_campaign_id` int(11) NOT NULL,
  `ad_click_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `conversion_type` varchar(50) NOT NULL,
  `conversion_value` decimal(10,2) DEFAULT NULL,
  `conversion_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conversion_context`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ad_click_id` (`ad_click_id`),
  KEY `idx_ad_campaign_id` (`ad_campaign_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_conversion_type` (`conversion_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ad_conversions_ibfk_1` FOREIGN KEY (`ad_campaign_id`) REFERENCES `ad_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_conversions_ibfk_2` FOREIGN KEY (`ad_click_id`) REFERENCES `ad_clicks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_conversions`
--

LOCK TABLES `ad_conversions` WRITE;
/*!40000 ALTER TABLE `ad_conversions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_conversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad_impressions`
--

DROP TABLE IF EXISTS `ad_impressions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_impressions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_campaign_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `impression_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`impression_context`)),
  `device_type` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ad_campaign_id` (`ad_campaign_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ad_impressions_ibfk_1` FOREIGN KEY (`ad_campaign_id`) REFERENCES `ad_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad_impressions`
--

LOCK TABLES `ad_impressions` WRITE;
/*!40000 ALTER TABLE `ad_impressions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad_impressions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_decision_logs`
--

DROP TABLE IF EXISTS `ai_decision_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_decision_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_model_id` int(11) NOT NULL,
  `decision_type` varchar(100) NOT NULL,
  `decision_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`decision_context`)),
  `decision_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`decision_result`)),
  `autonomy_level` enum('recommendation','auto_approve_bounds','full_autonomy') NOT NULL,
  `human_override` tinyint(1) DEFAULT 0,
  `override_reason` text DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_model_id` (`ai_model_id`),
  KEY `idx_decision_type` (`decision_type`),
  KEY `idx_autonomy_level` (`autonomy_level`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  CONSTRAINT `ai_decision_logs_ibfk_1` FOREIGN KEY (`ai_model_id`) REFERENCES `ai_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_decision_logs`
--

LOCK TABLES `ai_decision_logs` WRITE;
/*!40000 ALTER TABLE `ai_decision_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_decision_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_governance_logs`
--

DROP TABLE IF EXISTS `ai_governance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_governance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_model_id` int(11) NOT NULL,
  `governance_type` enum('ethics_review','compliance_check','risk_assessment','audit') NOT NULL,
  `review_date` date NOT NULL,
  `reviewer` varchar(100) DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `action_required` tinyint(1) DEFAULT 0,
  `action_taken` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed','overdue') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_model_id` (`ai_model_id`),
  KEY `idx_governance_type` (`governance_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `ai_governance_logs_ibfk_1` FOREIGN KEY (`ai_model_id`) REFERENCES `ai_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_governance_logs`
--

LOCK TABLES `ai_governance_logs` WRITE;
/*!40000 ALTER TABLE `ai_governance_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_governance_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_model_feedback`
--

DROP TABLE IF EXISTS `ai_model_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_model_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_prediction_id` int(11) NOT NULL,
  `feedback_type` enum('positive','negative','neutral') NOT NULL,
  `feedback_text` text DEFAULT NULL,
  `actual_outcome` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`actual_outcome`)),
  `feedback_provider` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_prediction_id` (`ai_prediction_id`),
  KEY `idx_feedback_type` (`feedback_type`),
  CONSTRAINT `ai_model_feedback_ibfk_1` FOREIGN KEY (`ai_prediction_id`) REFERENCES `ai_predictions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_model_feedback`
--

LOCK TABLES `ai_model_feedback` WRITE;
/*!40000 ALTER TABLE `ai_model_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_model_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_models`
--

DROP TABLE IF EXISTS `ai_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `model_type` enum('predictive','decision_support','operational','customer_experience','financial') NOT NULL,
  `model_category` varchar(50) NOT NULL,
  `model_version` varchar(50) NOT NULL,
  `model_description` text DEFAULT NULL,
  `training_data_source` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`training_data_source`)),
  `model_file_path` varchar(255) DEFAULT NULL,
  `model_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`model_parameters`)),
  `performance_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`performance_metrics`)),
  `status` enum('development','training','testing','production','deprecated') DEFAULT 'development',
  `deployed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_model_type` (`model_type`),
  KEY `idx_model_category` (`model_category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_models`
--

LOCK TABLES `ai_models` WRITE;
/*!40000 ALTER TABLE `ai_models` DISABLE KEYS */;
INSERT INTO `ai_models` VALUES (1,'Demand Forecasting Model','predictive','demand_forecasting','1.0.0','Predicts daily/weekly demand based on historical data',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(2,'Inventory Optimization Model','predictive','inventory','1.0.0','Optimizes inventory levels and reorder points',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(3,'Staff Scheduling Model','predictive','staffing','1.0.0','Recommends optimal staff schedules',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(4,'Menu Engineering Model','decision_support','menu','1.0.0','Provides menu engineering recommendations',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(5,'Dynamic Pricing Model','decision_support','pricing','1.0.0','Suggests dynamic pricing adjustments',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(6,'Supplier Selection Model','decision_support','procurement','1.0.0','Recommends optimal suppliers',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(7,'Kitchen Operations AI','operational','kitchen','1.0.0','Optimizes kitchen operations and workflow',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(8,'Table Management AI','operational','front_of_house','1.0.0','Optimizes table assignment and turnover',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(9,'Delivery Optimization AI','operational','delivery','1.0.0','Optimizes delivery routes and timing',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(10,'Customer Personalization AI','customer_experience','personalization','1.0.0','Personalizes customer experience',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(11,'Sentiment Analysis AI','customer_experience','feedback','1.0.0','Analyzes customer sentiment from reviews',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(12,'Churn Prediction AI','customer_experience','retention','1.0.0','Predicts customer churn risk',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(13,'Revenue Forecasting AI','financial','forecasting','1.0.0','Forecasts revenue and financial metrics',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(14,'Cost Optimization AI','financial','cost_analysis','1.0.0','Identifies cost optimization opportunities',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09'),(15,'Fraud Detection AI','financial','security','1.0.0','Detects fraudulent transactions',NULL,NULL,NULL,NULL,'development',NULL,'2026-07-05 03:27:09','2026-07-05 03:27:09');
/*!40000 ALTER TABLE `ai_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_predictions`
--

DROP TABLE IF EXISTS `ai_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ai_model_id` int(11) NOT NULL,
  `prediction_type` varchar(50) NOT NULL,
  `input_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`input_data`)),
  `prediction_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prediction_result`)),
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `prediction_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prediction_context`)),
  `restaurant_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_model_id` (`ai_model_id`),
  KEY `idx_prediction_type` (`prediction_type`),
  KEY `idx_restaurant_id` (`restaurant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ai_predictions_ibfk_1` FOREIGN KEY (`ai_model_id`) REFERENCES `ai_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_predictions`
--

LOCK TABLES `ai_predictions` WRITE;
/*!40000 ALTER TABLE `ai_predictions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_predictions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_logs`
--

DROP TABLE IF EXISTS `api_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) DEFAULT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `request_method` varchar(10) NOT NULL,
  `request_path` varchar(500) NOT NULL,
  `request_params` text DEFAULT NULL,
  `response_status` int(11) DEFAULT NULL,
  `response_time_ms` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_method` (`request_method`),
  KEY `idx_path` (`request_path`),
  KEY `idx_status` (`response_status`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_logs`
--

LOCK TABLES `api_logs` WRITE;
/*!40000 ALTER TABLE `api_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_rate_limits`
--

DROP TABLE IF EXISTS `api_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_rate_limits` (
  `limit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `request_count` int(11) DEFAULT 0,
  `window_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `window_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_blocked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`limit_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_blocked` (`is_blocked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_rate_limits`
--

LOCK TABLES `api_rate_limits` WRITE;
/*!40000 ALTER TABLE `api_rate_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `attendance_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `work_date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `break_minutes` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'PRESENT',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `uk_employee_date` (`employee_id`,`work_date`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_date` (`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `audit_log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `record_id` bigint(20) unsigned DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_log_id`),
  KEY `idx_audit_logs_tenant_id` (`tenant_id`),
  KEY `idx_audit_logs_user_id` (`user_id`),
  KEY `idx_audit_logs_module` (`module`),
  KEY `idx_audit_logs_action` (`action`),
  KEY `idx_audit_logs_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_type` enum('full','incremental','differential') NOT NULL,
  `backup_source` varchar(100) NOT NULL,
  `backup_location` varchar(255) NOT NULL,
  `backup_size_bytes` bigint(20) DEFAULT NULL,
  `status` enum('started','completed','failed','cancelled') NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_backup_type` (`backup_type`),
  KEY `idx_status` (`status`),
  KEY `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beta_participants`
--

DROP TABLE IF EXISTS `beta_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beta_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `beta_program_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `participant_type` enum('early_adopter','industry_expert','partner','invited') NOT NULL,
  `status` enum('invited','accepted','active','completed','declined','removed') DEFAULT 'invited',
  `joined_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `feedback_score` int(11) DEFAULT NULL,
  `feedback_text` text DEFAULT NULL,
  `incentives_claimed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`incentives_claimed`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_beta_program_id` (`beta_program_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beta_participants`
--

LOCK TABLES `beta_participants` WRITE;
/*!40000 ALTER TABLE `beta_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `beta_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beta_programs`
--

DROP TABLE IF EXISTS `beta_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beta_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `program_description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `current_participants` int(11) DEFAULT 0,
  `status` enum('planning','recruiting','active','completed','cancelled') DEFAULT 'planning',
  `incentives` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`incentives`)),
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beta_programs`
--

LOCK TABLES `beta_programs` WRITE;
/*!40000 ALTER TABLE `beta_programs` DISABLE KEYS */;
/*!40000 ALTER TABLE `beta_programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `branch_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `branch_code` varchar(50) NOT NULL,
  `branch_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `delivery_radius_km` decimal(5,2) DEFAULT 5.00,
  `is_main` tinyint(1) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `display_workflow_config_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`branch_id`),
  UNIQUE KEY `idx_branches_tenant_code` (`tenant_id`,`branch_code`),
  KEY `idx_branches_tenant_id` (`tenant_id`),
  KEY `idx_branches_company_id` (`company_id`),
  KEY `idx_branches_status` (`status`),
  KEY `idx_branches_location` (`latitude`,`longitude`),
  KEY `idx_display_workflow` (`display_workflow_config_id`),
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `branches_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (2,1,2,'MAIN','Main Branch','123 Main Street',NULL,NULL,NULL,NULL,5.00,0,'ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL,NULL);
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_type_pricing`
--

DROP TABLE IF EXISTS `business_type_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_type_pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_type` enum('home_based','small_restaurant','regional_chain','national_corporation','international_corporation') NOT NULL,
  `pricing_tier` varchar(50) NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `features_included` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_included`)),
  `max_locations` int(11) DEFAULT NULL,
  `max_users` int(11) DEFAULT NULL,
  `max_inventory_items` int(11) DEFAULT NULL,
  `api_access` tinyint(1) DEFAULT 0,
  `priority_support` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_business_tier` (`business_type`,`pricing_tier`),
  KEY `idx_business_type` (`business_type`),
  KEY `idx_pricing_tier` (`pricing_tier`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_type_pricing`
--

LOCK TABLES `business_type_pricing` WRITE;
/*!40000 ALTER TABLE `business_type_pricing` DISABLE KEYS */;
INSERT INTO `business_type_pricing` VALUES (1,'home_based','free',0.00,'[\"pos\", \"inventory\", \"menu\"]',1,2,50,0,0,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(2,'home_based','starter',29.00,'[\"pos\", \"inventory\", \"menu\", \"staff\"]',1,5,100,0,0,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(3,'small_restaurant','starter',49.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\"]',1,10,200,0,0,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(4,'small_restaurant','standard',99.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\", \"loyalty\", \"delivery\", \"analytics\"]',3,25,500,0,1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(5,'small_restaurant','professional',249.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\", \"loyalty\", \"delivery\", \"analytics\", \"kitchen_display\", \"table_management\", \"procurement\"]',10,50,1000,1,1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(6,'regional_chain','standard',149.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\", \"loyalty\", \"delivery\", \"analytics\", \"multi_location\"]',5,50,1000,0,1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(7,'regional_chain','professional',349.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\", \"loyalty\", \"delivery\", \"analytics\", \"multi_location\", \"kitchen_display\", \"table_management\", \"procurement\", \"api_access\"]',15,100,2000,1,1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(8,'national_corporation','professional',499.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\", \"loyalty\", \"delivery\", \"analytics\", \"multi_location\", \"kitchen_display\", \"table_management\", \"procurement\", \"api_access\", \"franchise\", \"sustainability\"]',50,200,5000,1,1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(9,'national_corporation','enterprise',999.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\", \"loyalty\", \"delivery\", \"analytics\", \"multi_location\", \"kitchen_display\", \"table_management\", \"procurement\", \"api_access\", \"franchise\", \"sustainability\", \"international\", \"ai_analytics\"]',100,500,10000,1,1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(10,'international_corporation','enterprise',1499.00,'[\"pos\", \"inventory\", \"menu\", \"staff\", \"reservations\", \"loyalty\", \"delivery\", \"analytics\", \"multi_location\", \"kitchen_display\", \"table_management\", \"procurement\", \"api_access\", \"franchise\", \"sustainability\", \"international\", \"ai_analytics\", \"automation\"]',999,1000,50000,1,1,'2026-07-05 03:24:53','2026-07-05 03:24:53');
/*!40000 ALTER TABLE `business_type_pricing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `category_code` varchar(50) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `idx_categories_tenant_code` (`tenant_id`,`category_code`),
  KEY `idx_categories_tenant_id` (`tenant_id`),
  KEY `idx_categories_parent_id` (`parent_id`),
  KEY `idx_categories_status` (`status`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certifications`
--

DROP TABLE IF EXISTS `certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certifications` (
  `certification_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `certification_type` varchar(50) NOT NULL,
  `certification_number` varchar(100) DEFAULT NULL,
  `issuing_authority` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `document_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`certification_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`certification_type`),
  KEY `idx_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certifications`
--

LOCK TABLES `certifications` WRITE;
/*!40000 ALTER TABLE `certifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chart_of_accounts` (
  `account_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `account_code` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE') NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `idx_chart_of_accounts_tenant_code` (`tenant_id`,`account_code`),
  KEY `idx_chart_of_accounts_tenant_id` (`tenant_id`),
  KEY `idx_chart_of_accounts_type` (`account_type`),
  KEY `idx_chart_of_accounts_parent_id` (`parent_id`),
  CONSTRAINT `chart_of_accounts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chart_of_accounts_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `chart_of_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `company_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `company_code` varchar(50) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`company_id`),
  UNIQUE KEY `idx_companies_tenant_code` (`tenant_id`,`company_code`),
  KEY `idx_companies_tenant_id` (`tenant_id`),
  KEY `idx_companies_status` (`status`),
  CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (2,1,'DEFAULT','Default Restaurant',NULL,NULL,NULL,NULL,'ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competitor_prices`
--

DROP TABLE IF EXISTS `competitor_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `competitor_prices` (
  `competitor_price_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `competitor_name` varchar(255) NOT NULL,
  `price` decimal(18,2) NOT NULL,
  `recorded_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`competitor_price_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_date` (`recorded_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competitor_prices`
--

LOCK TABLES `competitor_prices` WRITE;
/*!40000 ALTER TABLE `competitor_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `competitor_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compliance_alerts`
--

DROP TABLE IF EXISTS `compliance_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_alerts` (
  `alert_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `alert_type` varchar(50) DEFAULT 'WARNING',
  `message` varchar(500) NOT NULL,
  `discrepancies_json` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`alert_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`alert_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compliance_alerts`
--

LOCK TABLES `compliance_alerts` WRITE;
/*!40000 ALTER TABLE `compliance_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `compliance_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compliance_checks`
--

DROP TABLE IF EXISTS `compliance_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_checks` (
  `check_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `check_type` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'PENDING',
  `violations_json` text DEFAULT NULL,
  `warnings_json` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`check_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`check_type`),
  KEY `idx_date` (`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compliance_checks`
--

LOCK TABLES `compliance_checks` WRITE;
/*!40000 ALTER TABLE `compliance_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `compliance_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_achievements`
--

DROP TABLE IF EXISTS `customer_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_achievements` (
  `achievement_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `achievement_type` varchar(50) NOT NULL,
  `achievement_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`achievement_data`)),
  `points_awarded` int(11) DEFAULT 0,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`achievement_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_type` (`achievement_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_achievements`
--

LOCK TABLES `customer_achievements` WRITE;
/*!40000 ALTER TABLE `customer_achievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_achievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_addresses` (
  `address_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `address_type` varchar(20) DEFAULT 'HOME',
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Indonesia',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`address_id`),
  KEY `idx_customer` (`customer_id`),
  CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_loyalty`
--

DROP TABLE IF EXISTS `customer_loyalty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_loyalty` (
  `customer_loyalty_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `current_tier` enum('BRONZE','SILVER','GOLD','PLATINUM') DEFAULT 'BRONZE',
  `tier_progress` int(11) DEFAULT 0,
  `tier_points_required` int(11) DEFAULT 100,
  `points_earned_lifetime` int(11) DEFAULT 0,
  `points_redeemed_lifetime` int(11) DEFAULT 0,
  `last_tier_upgrade` date DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_loyalty_id`),
  UNIQUE KEY `uk_customer_loyalty` (`tenant_id`,`user_id`),
  KEY `idx_customer_loyalty_tenant` (`tenant_id`),
  KEY `idx_customer_loyalty_user` (`user_id`),
  KEY `idx_customer_loyalty_tier` (`current_tier`),
  CONSTRAINT `fk_customer_loyalty_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_customer_loyalty_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer loyalty status and tier tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_loyalty`
--

LOCK TABLES `customer_loyalty` WRITE;
/*!40000 ALTER TABLE `customer_loyalty` DISABLE KEYS */;
INSERT INTO `customer_loyalty` VALUES (1,1,2,7620,'PLATINUM',0,1000,8500,880,'2026-07-05',2,'2026-07-05 13:55:25',2,'2026-07-05 14:32:46',NULL);
/*!40000 ALTER TABLE `customer_loyalty` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_preferences`
--

DROP TABLE IF EXISTS `customer_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_preferences` (
  `preference_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`preference_id`),
  UNIQUE KEY `uk_customer_preference` (`customer_id`,`preference_key`),
  KEY `idx_customer` (`customer_id`),
  CONSTRAINT `customer_preferences_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_preferences`
--

LOCK TABLES `customer_preferences` WRITE;
/*!40000 ALTER TABLE `customer_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_pricing`
--

DROP TABLE IF EXISTS `customer_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_pricing` (
  `pricing_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `customer_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `special_price` decimal(18,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `is_complimentary` tinyint(1) DEFAULT 0 COMMENT 'Flag for complimentary items (free for customer)',
  `complimentary_reason` varchar(255) DEFAULT NULL COMMENT 'Reason for complimentary item (birthday, VIP, etc.)',
  `complimentary_code` varchar(50) DEFAULT NULL COMMENT 'Code for complimentary tracking and reporting',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`pricing_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_complimentary` (`is_complimentary`),
  KEY `idx_validity` (`valid_from`,`valid_until`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_pricing`
--

LOCK TABLES `customer_pricing` WRITE;
/*!40000 ALTER TABLE `customer_pricing` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_pricing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `customer_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `membership_level` varchar(20) DEFAULT 'REGULAR',
  `total_visits` int(11) DEFAULT 0,
  `total_spent` decimal(18,2) DEFAULT 0.00,
  `last_visit_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `customer_code` (`customer_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboards`
--

DROP TABLE IF EXISTS `dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboards` (
  `dashboard_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `dashboard_name` varchar(255) NOT NULL,
  `dashboard_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dashboard_config`)),
  `is_public` tinyint(1) DEFAULT 0,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`dashboard_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboards`
--

LOCK TABLES `dashboards` WRITE;
/*!40000 ALTER TABLE `dashboards` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashboards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_product_subscriptions`
--

DROP TABLE IF EXISTS `data_product_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_product_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_product_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `subscriber_type` enum('restaurant','supplier','brand','other') NOT NULL,
  `subscription_start_date` date NOT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `access_level` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`access_level`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_data_product_id` (`data_product_id`),
  KEY `idx_subscriber_id` (`subscriber_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `data_product_subscriptions_ibfk_1` FOREIGN KEY (`data_product_id`) REFERENCES `data_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_product_subscriptions`
--

LOCK TABLES `data_product_subscriptions` WRITE;
/*!40000 ALTER TABLE `data_product_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_product_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_products`
--

DROP TABLE IF EXISTS `data_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `product_type` enum('aggregated_insights','lead_generation','market_report','custom_analytics') NOT NULL,
  `description` text DEFAULT NULL,
  `data_source` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_source`)),
  `pricing_model` enum('subscription','one_time','usage_based') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `availability` enum('public','private','custom') DEFAULT 'public',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_availability` (`availability`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_products`
--

LOCK TABLES `data_products` WRITE;
/*!40000 ALTER TABLE `data_products` DISABLE KEYS */;
INSERT INTO `data_products` VALUES (1,'Restaurant Industry Insights','aggregated_insights','Aggregated data on restaurant trends, pricing, and performance','[\"orders\", \"menus\", \"reviews\"]','subscription',99.00,'public',1,'2026-07-05 03:26:37','2026-07-05 03:26:37'),(2,'Supplier Lead Generation','lead_generation','Qualified leads for suppliers based on restaurant demand','[\"inventory\", \"procurement\"]','usage_based',5.00,'public',1,'2026-07-05 03:26:37','2026-07-05 03:26:37'),(3,'Market Trend Reports','market_report','Monthly reports on F&B market trends and forecasts','[\"orders\", \"searches\", \"reviews\"]','subscription',199.00,'public',1,'2026-07-05 03:26:37','2026-07-05 03:26:37'),(4,'Custom Analytics Dashboard','custom_analytics','Tailored analytics dashboard for specific business needs','[\"all\"]','subscription',299.00,'public',1,'2026-07-05 03:26:37','2026-07-05 03:26:37');
/*!40000 ALTER TABLE `data_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `demand_forecasts`
--

DROP TABLE IF EXISTS `demand_forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `demand_forecasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `forecast_date` date NOT NULL,
  `forecast_type` enum('daily','weekly','monthly') NOT NULL,
  `predicted_orders` int(11) DEFAULT NULL,
  `predicted_revenue` decimal(15,2) DEFAULT NULL,
  `confidence_level` decimal(5,2) DEFAULT NULL,
  `factors_considered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factors_considered`)),
  `actual_orders` int(11) DEFAULT NULL,
  `actual_revenue` decimal(15,2) DEFAULT NULL,
  `forecast_accuracy` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_date_type` (`tenant_id`,`forecast_date`,`forecast_type`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_forecast_date` (`forecast_date`),
  KEY `idx_forecast_type` (`forecast_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `demand_forecasts`
--

LOCK TABLES `demand_forecasts` WRITE;
/*!40000 ALTER TABLE `demand_forecasts` DISABLE KEYS */;
/*!40000 ALTER TABLE `demand_forecasts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disaster_recovery_plans`
--

DROP TABLE IF EXISTS `disaster_recovery_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disaster_recovery_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `plan_type` enum('data_loss','system_outage','security_breach','natural_disaster') NOT NULL,
  `recovery_objectives` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recovery_objectives`)),
  `recovery_procedures` text DEFAULT NULL,
  `contact_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contact_information`)),
  `last_tested` date DEFAULT NULL,
  `next_test_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_plan_type` (`plan_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disaster_recovery_plans`
--

LOCK TABLES `disaster_recovery_plans` WRITE;
/*!40000 ALTER TABLE `disaster_recovery_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `disaster_recovery_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `display_workflow_configurations`
--

DROP TABLE IF EXISTS `display_workflow_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `display_workflow_configurations` (
  `config_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `workflow_type` enum('STANDARD','PADANG_DISPLAY','BUFFET','CAFETERIA','FOOD_COURT','COUNTER_SERVICE','TABLE_SERVICE','SELF_SERVICE') NOT NULL DEFAULT 'STANDARD',
  `config_name` varchar(100) NOT NULL,
  `display_mode` enum('INDIVIDUAL_ITEMS','GROUPED_DISPLAY','COMBO_DISPLAY','CATEGORY_DISPLAY','PRICE_BASED_DISPLAY') DEFAULT 'INDIVIDUAL_ITEMS',
  `show_out_of_stock` tinyint(1) DEFAULT 0,
  `show_low_stock` tinyint(1) DEFAULT 1,
  `auto_hide_out_of_stock` tinyint(1) DEFAULT 0,
  `display_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of category IDs to display' CHECK (json_valid(`display_categories`)),
  `display_order` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Category display order' CHECK (json_valid(`display_order`)),
  `price_display_mode` enum('SHOW_ALL','HIDE_PRICES','SHOW_ON_REQUEST','SHOW_RANGE') DEFAULT 'SHOW_ALL',
  `allow_customer_selection` tinyint(1) DEFAULT 1,
  `require_table_assignment` tinyint(1) DEFAULT 0,
  `kitchen_notification_mode` enum('AUTO','MANUAL','BATCH') DEFAULT 'AUTO',
  `serving_mode` enum('SELF_SERVE','STAFF_SERVE','HYBRID') DEFAULT 'STAFF_SERVE',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`config_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_workflow_type` (`workflow_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `display_workflow_configurations`
--

LOCK TABLES `display_workflow_configurations` WRITE;
/*!40000 ALTER TABLE `display_workflow_configurations` DISABLE KEYS */;
INSERT INTO `display_workflow_configurations` VALUES (1,1,NULL,'STANDARD','Standard Display','INDIVIDUAL_ITEMS',0,1,0,NULL,NULL,'SHOW_ALL',1,0,'AUTO','STAFF_SERVE',1,'2026-07-08 10:56:31','2026-07-08 10:56:31',NULL,NULL),(2,1,NULL,'PADANG_DISPLAY','Padang Style Display','GROUPED_DISPLAY',0,1,0,NULL,NULL,'SHOW_ALL',1,0,'AUTO','SELF_SERVE',0,'2026-07-08 10:56:31','2026-07-08 10:56:31',NULL,NULL),(3,1,NULL,'BUFFET','Buffet Display','CATEGORY_DISPLAY',0,1,0,NULL,NULL,'SHOW_ALL',1,0,'AUTO','SELF_SERVE',0,'2026-07-08 10:56:31','2026-07-08 10:56:31',NULL,NULL);
/*!40000 ALTER TABLE `display_workflow_configurations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamic_pricing_rules`
--

DROP TABLE IF EXISTS `dynamic_pricing_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dynamic_pricing_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rule_name` varchar(100) NOT NULL,
  `rule_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rule_conditions`)),
  `price_adjustment_type` enum('percentage','fixed') NOT NULL,
  `price_adjustment_value` decimal(10,2) NOT NULL,
  `min_price` decimal(10,2) DEFAULT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `autonomy_level` enum('recommendation','auto_approve_bounds','full_autonomy') DEFAULT 'recommendation',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamic_pricing_rules`
--

LOCK TABLES `dynamic_pricing_rules` WRITE;
/*!40000 ALTER TABLE `dynamic_pricing_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamic_pricing_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_availability`
--

DROP TABLE IF EXISTS `employee_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_availability` (
  `availability_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) NOT NULL,
  `day_of_week` int(11) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `preferred_start_time` time DEFAULT NULL,
  `preferred_end_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`availability_id`),
  UNIQUE KEY `uk_employee_day` (`employee_id`,`day_of_week`),
  KEY `idx_employee` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_availability`
--

LOCK TABLES `employee_availability` WRITE;
/*!40000 ALTER TABLE `employee_availability` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_skills`
--

DROP TABLE IF EXISTS `employee_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_skills` (
  `employee_skill_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) NOT NULL,
  `skill_id` bigint(20) NOT NULL,
  `proficiency_level` varchar(20) DEFAULT 'INTERMEDIATE',
  `certified` tinyint(1) DEFAULT 0,
  `certification_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`employee_skill_id`),
  UNIQUE KEY `uk_employee_skill` (`employee_id`,`skill_id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_skill` (`skill_id`),
  CONSTRAINT `employee_skills_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  CONSTRAINT `employee_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_skills`
--

LOCK TABLES `employee_skills` WRITE;
/*!40000 ALTER TABLE `employee_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `employee_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `termination_date` date DEFAULT NULL,
  `employment_type` varchar(20) DEFAULT 'FULL_TIME',
  `hourly_rate` decimal(18,2) DEFAULT NULL,
  `salary` decimal(18,2) DEFAULT NULL,
  `max_hours_per_week` int(11) DEFAULT 40,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `profile_image_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `energy_consumption`
--

DROP TABLE IF EXISTS `energy_consumption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `energy_consumption` (
  `consumption_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `energy_type` varchar(50) NOT NULL,
  `consumption_kwh` decimal(10,4) NOT NULL,
  `consumption_date` date NOT NULL,
  `meter_reading` decimal(18,2) DEFAULT NULL,
  `recorded_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`consumption_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`energy_type`),
  KEY `idx_date` (`consumption_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `energy_consumption`
--

LOCK TABLES `energy_consumption` WRITE;
/*!40000 ALTER TABLE `energy_consumption` DISABLE KEYS */;
/*!40000 ALTER TABLE `energy_consumption` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment` (
  `equipment_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `equipment_code` varchar(50) DEFAULT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `equipment_type` varchar(50) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(18,2) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'OPERATIONAL',
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`equipment_id`),
  UNIQUE KEY `equipment_code` (`equipment_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`equipment_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feature_modules`
--

DROP TABLE IF EXISTS `feature_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feature_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_code` varchar(50) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `dependencies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dependencies`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_code` (`module_code`),
  KEY `idx_module_code` (`module_code`),
  KEY `idx_module_category` (`module_category`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feature_modules`
--

LOCK TABLES `feature_modules` WRITE;
/*!40000 ALTER TABLE `feature_modules` DISABLE KEYS */;
INSERT INTO `feature_modules` VALUES (1,'pos','Point of Sale','core','Basic POS functionality','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(2,'inventory','Inventory Management','core','Inventory tracking and management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(3,'menu','Menu Management','core','Menu item and category management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(4,'staff','Staff Management','core','Staff scheduling and management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(5,'reservations','Reservation System','customer','Table reservation management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(6,'loyalty','Loyalty Program','customer','Customer loyalty and rewards','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(7,'delivery','Delivery Integration','operations','Delivery platform integration','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(8,'analytics','Analytics Dashboard','reporting','Business analytics and reporting','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(9,'multi_location','Multi-Location','enterprise','Multi-location management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(10,'api_access','API Access','enterprise','API access for integrations','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(11,'kitchen_display','Kitchen Display System','operations','KDS for kitchen operations','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(12,'table_management','Table Management','operations','Table and floor management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(13,'procurement','Procurement','supply_chain','Purchase order and supplier management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(14,'franchise','Franchise Management','enterprise','Franchise operations management','[\"multi_location\"]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(15,'ghost_kitchen','Ghost Kitchen','operations','Virtual brand and ghost kitchen management','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(16,'sustainability','Sustainability','reporting','Environmental impact tracking','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(17,'international','International','enterprise','Multi-currency and multi-language support','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(18,'ai_analytics','AI Analytics','advanced','AI-powered analytics and predictions','[\"analytics\"]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(19,'automation','Automation','advanced','Workflow automation and triggers','[]',1,'2026-07-05 03:24:53','2026-07-05 03:24:53');
/*!40000 ALTER TABLE `feature_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `featured_restaurant_requests`
--

DROP TABLE IF EXISTS `featured_restaurant_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `featured_restaurant_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `request_type` enum('featured_placement','sponsored_recommendation','boost') NOT NULL,
  `request_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_details`)),
  `budget` decimal(10,2) DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_request_type` (`request_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `featured_restaurant_requests`
--

LOCK TABLES `featured_restaurant_requests` WRITE;
/*!40000 ALTER TABLE `featured_restaurant_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `featured_restaurant_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `feedback_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `feedback_type` varchar(50) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `sentiment_score` decimal(3,2) DEFAULT NULL,
  `sentiment_label` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'NEW',
  `responded_at` timestamp NULL DEFAULT NULL,
  `responded_by` bigint(20) DEFAULT NULL,
  `response_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`feedback_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_type` (`feedback_type`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_discovery_app`
--

DROP TABLE IF EXISTS `food_discovery_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_discovery_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `discovery_type` enum('halal_finder','food_waste_reduction','local_specialties') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `cuisine_type` varchar(100) DEFAULT NULL,
  `price_range` varchar(10) DEFAULT NULL,
  `halal_certified` tinyint(1) DEFAULT 0,
  `sustainability_score` int(11) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `operating_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operating_hours`)),
  `contact_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contact_info`)),
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_discovery_type` (`discovery_type`),
  KEY `idx_city` (`city`),
  KEY `idx_halal_certified` (`halal_certified`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_discovery_app`
--

LOCK TABLES `food_discovery_app` WRITE;
/*!40000 ALTER TABLE `food_discovery_app` DISABLE KEYS */;
/*!40000 ALTER TABLE `food_discovery_app` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_waste`
--

DROP TABLE IF EXISTS `food_waste`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_waste` (
  `waste_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `waste_type` varchar(50) NOT NULL,
  `quantity_kg` decimal(10,4) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `waste_date` date NOT NULL,
  `recorded_by` bigint(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`waste_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`waste_type`),
  KEY `idx_date` (`waste_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_waste`
--

LOCK TABLES `food_waste` WRITE;
/*!40000 ALTER TABLE `food_waste` DISABLE KEYS */;
/*!40000 ALTER TABLE `food_waste` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geographic_expansions`
--

DROP TABLE IF EXISTS `geographic_expansions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geographic_expansions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expansion_name` varchar(100) NOT NULL,
  `target_country` varchar(100) NOT NULL,
  `target_city` varchar(100) NOT NULL,
  `target_region` varchar(100) DEFAULT NULL,
  `expansion_stage` enum('research','planning','preparation','launch','growth','mature') DEFAULT 'research',
  `target_customers` int(11) DEFAULT NULL,
  `current_customers` int(11) DEFAULT 0,
  `launch_date` date DEFAULT NULL,
  `investment_budget` decimal(15,2) DEFAULT NULL,
  `actual_spend` decimal(15,2) DEFAULT NULL,
  `roi` decimal(5,2) DEFAULT NULL,
  `status` enum('planned','in_progress','completed','paused','cancelled') DEFAULT 'planned',
  `challenges` text DEFAULT NULL,
  `lessons_learned` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_target_country` (`target_country`),
  KEY `idx_target_city` (`target_city`),
  KEY `idx_expansion_stage` (`expansion_stage`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geographic_expansions`
--

LOCK TABLES `geographic_expansions` WRITE;
/*!40000 ALTER TABLE `geographic_expansions` DISABLE KEYS */;
/*!40000 ALTER TABLE `geographic_expansions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `growth_metrics`
--

DROP TABLE IF EXISTS `growth_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `growth_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_date` date NOT NULL,
  `metric_type` enum('acquisition','activation','engagement','retention','revenue') NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(15,2) NOT NULL,
  `target_value` decimal(15,2) DEFAULT NULL,
  `segment` varchar(50) DEFAULT NULL,
  `channel` varchar(50) DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_metric_date` (`metric_date`),
  KEY `idx_metric_type` (`metric_type`),
  KEY `idx_metric_name` (`metric_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `growth_metrics`
--

LOCK TABLES `growth_metrics` WRITE;
/*!40000 ALTER TABLE `growth_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `growth_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_preferences`
--

DROP TABLE IF EXISTS `guest_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_preferences` (
  `preference_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `preference_type` varchar(50) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`preference_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_type` (`preference_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_preferences`
--

LOCK TABLES `guest_preferences` WRITE;
/*!40000 ALTER TABLE `guest_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `guest_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `haccp_checkpoints`
--

DROP TABLE IF EXISTS `haccp_checkpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `haccp_checkpoints` (
  `checkpoint_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `checkpoint_name` varchar(255) NOT NULL,
  `checkpoint_type` varchar(50) DEFAULT NULL,
  `last_check_date` date DEFAULT NULL,
  `frequency` varchar(20) DEFAULT 'DAILY',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`checkpoint_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `haccp_checkpoints`
--

LOCK TABLES `haccp_checkpoints` WRITE;
/*!40000 ALTER TABLE `haccp_checkpoints` DISABLE KEYS */;
/*!40000 ALTER TABLE `haccp_checkpoints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `halal_certifications`
--

DROP TABLE IF EXISTS `halal_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `halal_certifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `certification_number` varchar(100) NOT NULL,
  `certifying_body` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_certification_number` (`certification_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `halal_certifications`
--

LOCK TABLES `halal_certifications` WRITE;
/*!40000 ALTER TABLE `halal_certifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `halal_certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredient_substitutes`
--

DROP TABLE IF EXISTS `ingredient_substitutes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredient_substitutes` (
  `substitute_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ingredient_id` bigint(20) NOT NULL,
  `substitute_ingredient_id` bigint(20) NOT NULL,
  `compatibility_score` int(11) DEFAULT 70,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`substitute_id`),
  UNIQUE KEY `uk_ingredient_substitute` (`ingredient_id`,`substitute_ingredient_id`),
  KEY `idx_ingredient` (`ingredient_id`),
  KEY `idx_substitute` (`substitute_ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredient_substitutes`
--

LOCK TABLES `ingredient_substitutes` WRITE;
/*!40000 ALTER TABLE `ingredient_substitutes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingredient_substitutes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integration_logs`
--

DROP TABLE IF EXISTS `integration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `integration_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `integration_id` bigint(20) NOT NULL,
  `log_type` varchar(50) NOT NULL,
  `request_data` text DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `status_code` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `execution_time_ms` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_integration` (`integration_id`),
  KEY `idx_type` (`log_type`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration_logs`
--

LOCK TABLES `integration_logs` WRITE;
/*!40000 ALTER TABLE `integration_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `integration_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integrations`
--

DROP TABLE IF EXISTS `integrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `integrations` (
  `integration_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `integration_type` varchar(50) NOT NULL,
  `integration_name` varchar(255) NOT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `sync_status` varchar(20) DEFAULT 'IDLE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`integration_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`integration_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integrations`
--

LOCK TABLES `integrations` WRITE;
/*!40000 ALTER TABLE `integrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `integrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `inventory_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'unit',
  `minimum_stock` decimal(10,2) DEFAULT 0.00,
  `maximum_stock` decimal(10,2) DEFAULT 0.00,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `supplier_contract_id` int(11) DEFAULT NULL,
  `halal_certified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`inventory_id`),
  UNIQUE KEY `idx_inventory_branch_product` (`branch_id`,`product_id`),
  KEY `idx_inventory_tenant_id` (`tenant_id`),
  KEY `idx_inventory_branch_id` (`branch_id`),
  KEY `idx_inventory_product_id` (`product_id`),
  KEY `idx_batch_number` (`batch_number`),
  KEY `idx_expiry_date` (`expiry_date`),
  KEY `idx_supplier_contract_id` (`supplier_contract_id`),
  KEY `idx_halal_certified` (`halal_certified`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_categories`
--

DROP TABLE IF EXISTS `inventory_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_categories` (
  `category_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_category_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`category_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_parent` (`parent_category_id`),
  CONSTRAINT `inventory_categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `inventory_categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_categories`
--

LOCK TABLES `inventory_categories` WRITE;
/*!40000 ALTER TABLE `inventory_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `item_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `weight` decimal(10,3) NOT NULL,
  `unit_cost` decimal(18,2) DEFAULT NULL,
  `calculated_cost` decimal(18,2) DEFAULT NULL,
  `status` enum('AVAILABLE','RESERVED','SOLD','DISCARDED') DEFAULT 'AVAILABLE',
  `received_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `idx_inventory` (`inventory_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_code` (`item_code`),
  KEY `idx_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_optimizations`
--

DROP TABLE IF EXISTS `inventory_optimizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_optimizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `optimization_date` date NOT NULL,
  `recommended_order_quantity` decimal(10,2) DEFAULT NULL,
  `current_stock` decimal(10,2) DEFAULT NULL,
  `predicted_demand` decimal(10,2) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT NULL,
  `safety_stock_level` decimal(10,2) DEFAULT NULL,
  `reorder_point` decimal(10,2) DEFAULT NULL,
  `cost_savings_estimate` decimal(10,2) DEFAULT NULL,
  `implemented` tinyint(1) DEFAULT 0,
  `implemented_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_optimization_date` (`optimization_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_optimizations`
--

LOCK TABLES `inventory_optimizations` WRITE;
/*!40000 ALTER TABLE `inventory_optimizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_optimizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_reorder`
--

DROP TABLE IF EXISTS `inventory_reorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_reorder` (
  `reorder_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `inventory_id` bigint(20) NOT NULL,
  `reorder_point` decimal(10,4) NOT NULL,
  `max_stock` decimal(10,4) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT 3,
  `safety_stock` decimal(10,4) DEFAULT 0.0000,
  `auto_reorder` tinyint(1) DEFAULT 0,
  `last_reorder_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`reorder_id`),
  UNIQUE KEY `uk_tenant_branch_inventory` (`tenant_id`,`branch_id`,`inventory_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_inventory` (`inventory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_reorder`
--

LOCK TABLES `inventory_reorder` WRITE;
/*!40000 ALTER TABLE `inventory_reorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_reorder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `invoice_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(18,2) DEFAULT 0.00,
  `tax_amount` decimal(18,2) DEFAULT 0.00,
  `discount_amount` decimal(18,2) DEFAULT 0.00,
  `total_amount` decimal(18,2) NOT NULL,
  `paid_amount` decimal(18,2) DEFAULT 0.00,
  `balance_amount` decimal(18,2) NOT NULL,
  `status` varchar(20) DEFAULT 'DRAFT',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_entries` (
  `journal_entry_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `journal_number` varchar(50) NOT NULL,
  `journal_date` date NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('DRAFT','POSTED','CANCELLED') DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`journal_entry_id`),
  UNIQUE KEY `idx_journal_entries_tenant_number` (`tenant_id`,`journal_number`),
  KEY `idx_journal_entries_tenant_id` (`tenant_id`),
  KEY `idx_journal_entries_branch_id` (`branch_id`),
  KEY `idx_journal_entries_date` (`journal_date`),
  KEY `idx_journal_entries_status` (`status`),
  CONSTRAINT `journal_entries_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `journal_entries_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_lines` (
  `journal_line_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`journal_line_id`),
  KEY `idx_journal_lines_journal_entry_id` (`journal_entry_id`),
  KEY `idx_journal_lines_account_id` (`account_id`),
  CONSTRAINT `journal_lines_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`journal_entry_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `journal_lines_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_lines`
--

LOCK TABLES `journal_lines` WRITE;
/*!40000 ALTER TABLE `journal_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `journal_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kitchen_order_items`
--

DROP TABLE IF EXISTS `kitchen_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kitchen_order_items` (
  `kitchen_order_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kitchen_order_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('PENDING','PREPARING','READY','SERVED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`kitchen_order_item_id`),
  KEY `idx_kitchen_order_items_kitchen_order_id` (`kitchen_order_id`),
  KEY `idx_kitchen_order_items_order_item_id` (`order_item_id`),
  KEY `idx_kitchen_order_items_product_id` (`product_id`),
  KEY `idx_kitchen_order_items_status` (`status`),
  CONSTRAINT `kitchen_order_items_ibfk_1` FOREIGN KEY (`kitchen_order_id`) REFERENCES `kitchen_orders` (`kitchen_order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_order_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_order_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kitchen_order_items`
--

LOCK TABLES `kitchen_order_items` WRITE;
/*!40000 ALTER TABLE `kitchen_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `kitchen_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kitchen_orders`
--

DROP TABLE IF EXISTS `kitchen_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kitchen_orders` (
  `kitchen_order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `kitchen_order_number` varchar(50) NOT NULL,
  `status` enum('PENDING','IN_PROGRESS','READY','SERVED','CANCELLED') DEFAULT 'PENDING',
  `priority` enum('LOW','NORMAL','HIGH','URGENT') DEFAULT 'NORMAL',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`kitchen_order_id`),
  UNIQUE KEY `idx_kitchen_orders_tenant_number` (`tenant_id`,`kitchen_order_number`),
  KEY `idx_kitchen_orders_tenant_id` (`tenant_id`),
  KEY `idx_kitchen_orders_branch_id` (`branch_id`),
  KEY `idx_kitchen_orders_order_id` (`order_id`),
  KEY `idx_kitchen_orders_status` (`status`),
  CONSTRAINT `kitchen_orders_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_orders_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kitchen_orders_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kitchen_orders`
--

LOCK TABLES `kitchen_orders` WRITE;
/*!40000 ALTER TABLE `kitchen_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `kitchen_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kitchen_stations`
--

DROP TABLE IF EXISTS `kitchen_stations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kitchen_stations` (
  `station_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `station_name` varchar(100) NOT NULL,
  `station_type` varchar(50) DEFAULT 'PREPARATION',
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`station_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kitchen_stations`
--

LOCK TABLES `kitchen_stations` WRITE;
/*!40000 ALTER TABLE `kitchen_stations` DISABLE KEYS */;
/*!40000 ALTER TABLE `kitchen_stations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_metrics`
--

DROP TABLE IF EXISTS `kpi_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kpi_metrics` (
  `metric_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(18,2) DEFAULT NULL,
  `metric_type` varchar(50) DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `comparison_value` decimal(18,2) DEFAULT NULL,
  `comparison_percentage` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`metric_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_name` (`metric_name`),
  KEY `idx_period` (`period_start`,`period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_metrics`
--

LOCK TABLES `kpi_metrics` WRITE;
/*!40000 ALTER TABLE `kpi_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `kpi_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` varchar(10) DEFAULT NULL,
  `language_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `language_code` (`language_code`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_members`
--

DROP TABLE IF EXISTS `loyalty_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_members` (
  `member_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `tenant_id` bigint(20) NOT NULL,
  `membership_number` varchar(50) DEFAULT NULL,
  `tier_level` varchar(20) DEFAULT 'BRONZE',
  `points_balance` int(11) DEFAULT 0,
  `total_points_earned` int(11) DEFAULT 0,
  `tier_upgraded_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`member_id`),
  UNIQUE KEY `uk_customer_tenant` (`customer_id`,`tenant_id`),
  UNIQUE KEY `membership_number` (`membership_number`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_tier` (`tier_level`),
  CONSTRAINT `loyalty_members_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_members`
--

LOCK TABLES `loyalty_members` WRITE;
/*!40000 ALTER TABLE `loyalty_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_points`
--

DROP TABLE IF EXISTS `loyalty_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_points` (
  `loyalty_point_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_redeemed` int(11) DEFAULT 0,
  `transaction_type` enum('EARNED','REDEEMED','ADJUSTED') NOT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`loyalty_point_id`),
  KEY `idx_loyalty_points_tenant` (`tenant_id`),
  KEY `idx_loyalty_points_user` (`user_id`),
  KEY `idx_loyalty_points_type` (`transaction_type`),
  KEY `idx_loyalty_points_created_at` (`created_at`),
  CONSTRAINT `fk_loyalty_points_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loyalty_points_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer loyalty point transactions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_points`
--

LOCK TABLES `loyalty_points` WRITE;
/*!40000 ALTER TABLE `loyalty_points` DISABLE KEYS */;
INSERT INTO `loyalty_points` VALUES (2,1,2,100,0,'EARNED',NULL,'WELCOME_BONUS','Welcome bonus points',2,'2026-07-05 13:55:25'),(8,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 13:59:05'),(9,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 13:59:08'),(10,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 13:59:23'),(11,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 13:59:23'),(12,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 13:59:23'),(13,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:00:12'),(14,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:00:15'),(15,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:00:31'),(16,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:00:31'),(17,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:00:31'),(18,1,2,100,0,'EARNED',NULL,NULL,'Test',2,'2026-07-05 14:00:47'),(19,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:02:09'),(20,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:02:10'),(21,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:02:22'),(22,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:02:22'),(23,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:02:23'),(24,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:03:37'),(25,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:03:38'),(26,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:03:49'),(27,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:03:49'),(28,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:03:49'),(29,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:04:45'),(30,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:04:45'),(31,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:04:57'),(32,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:04:57'),(33,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:04:58'),(34,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:06:30'),(35,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:06:30'),(36,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:06:33'),(37,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:06:37'),(38,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:06:37'),(39,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:06:38'),(40,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:07:25'),(41,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:07:25'),(42,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:07:29'),(43,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:07:30'),(44,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:07:30'),(45,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:07:30'),(46,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:07:52'),(47,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:07:52'),(48,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:07:56'),(49,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:07:58'),(50,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:07:58'),(51,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:07:58'),(52,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:08:18'),(53,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:08:18'),(54,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:08:19'),(55,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:08:21'),(56,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:08:21'),(57,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:08:21'),(58,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:10:33'),(59,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:10:33'),(60,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:10:34'),(61,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:10:35'),(62,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:10:35'),(63,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:10:35'),(64,1,2,100,0,'EARNED',NULL,NULL,'Test award points',2,'2026-07-05 14:32:43'),(65,1,2,0,50,'REDEEMED',NULL,NULL,'Test redeem points',2,'2026-07-05 14:32:43'),(66,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:32:44'),(67,1,2,100,0,'EARNED',NULL,NULL,NULL,2,'2026-07-05 14:32:46'),(68,1,2,0,30,'REDEEMED',NULL,NULL,NULL,2,'2026-07-05 14:32:46'),(69,1,2,500,0,'EARNED',NULL,NULL,'Tier upgrade test',2,'2026-07-05 14:32:46');
/*!40000 ALTER TABLE `loyalty_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_promotions`
--

DROP TABLE IF EXISTS `loyalty_promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_promotions` (
  `promotion_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `bonus_points` int(11) DEFAULT 0,
  `points_multiplier` decimal(3,2) DEFAULT 1.00,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`promotion_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_promotions`
--

LOCK TABLES `loyalty_promotions` WRITE;
/*!40000 ALTER TABLE `loyalty_promotions` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_redemptions`
--

DROP TABLE IF EXISTS `loyalty_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_redemptions` (
  `redemption_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `tenant_id` bigint(20) NOT NULL,
  `reward_id` bigint(20) NOT NULL,
  `redemption_code` varchar(50) DEFAULT NULL,
  `points_used` int(11) NOT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'REDEEMED',
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`redemption_id`),
  UNIQUE KEY `redemption_code` (`redemption_code`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_reward` (`reward_id`),
  KEY `idx_code` (`redemption_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_redemptions`
--

LOCK TABLES `loyalty_redemptions` WRITE;
/*!40000 ALTER TABLE `loyalty_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_rewards`
--

DROP TABLE IF EXISTS `loyalty_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_rewards` (
  `reward_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `reward_code` varchar(50) NOT NULL,
  `reward_name` varchar(150) NOT NULL,
  `reward_name_en` varchar(150) DEFAULT NULL,
  `reward_description` text DEFAULT NULL,
  `points_required` int(11) NOT NULL,
  `reward_type` enum('DISCOUNT','FREE_ITEM','UPGRADE','EXPERIENCE') NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE','EXPIRED') DEFAULT 'ACTIVE',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`reward_id`),
  UNIQUE KEY `reward_code` (`reward_code`),
  KEY `idx_loyalty_rewards_tenant` (`tenant_id`),
  KEY `idx_loyalty_rewards_status` (`status`),
  KEY `idx_loyalty_rewards_code` (`reward_code`),
  KEY `idx_loyalty_rewards_validity` (`valid_from`,`valid_until`),
  CONSTRAINT `fk_loyalty_rewards_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available loyalty rewards';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_rewards`
--

LOCK TABLES `loyalty_rewards` WRITE;
/*!40000 ALTER TABLE `loyalty_rewards` DISABLE KEYS */;
INSERT INTO `loyalty_rewards` VALUES (1,1,'WELCOME_BONUS','Bonus Selamat Datang','Welcome Bonus','Poin bonus untuk pelanggan baru',100,'DISCOUNT',NULL,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 13:45:24',NULL,'2026-07-05 13:45:24',NULL),(2,1,'BIRTHDAY_BONUS','Bonus Ulang Tahun','Birthday Bonus','Poin bonus spesial ulang tahun',200,'FREE_ITEM',NULL,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 13:45:24',NULL,'2026-07-05 13:45:24',NULL),(3,1,'REFERRAL_BONUS','Bonus Referral','Referral Bonus','Poin bonus untuk referral pelanggan baru',150,'DISCOUNT',NULL,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 13:45:24',NULL,'2026-07-05 13:45:24',NULL),(4,1,'TEST001','Test Reward',NULL,NULL,50,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:02:57',NULL,'2026-07-05 14:02:57',NULL),(5,1,'TEST_REWARD_1783260218508','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:03:38',NULL,'2026-07-05 14:03:38',NULL),(6,1,'TEST_REWARD_1783260285775','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:04:45',NULL,'2026-07-05 14:04:45',NULL),(7,1,'TEST_REWARD_1783260390833','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:06:30',NULL,'2026-07-05 14:06:30',NULL),(8,1,'REDEEM_TEST_1783260393771','Redeem Test Reward',NULL,NULL,50,'DISCOUNT',5.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:06:33',NULL,'2026-07-05 14:06:33',NULL),(9,1,'TEST_REWARD_1783260446132','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:07:26',NULL,'2026-07-05 14:07:26',NULL),(10,1,'REDEEM_TEST_1783260449049','Redeem Test Reward',NULL,NULL,50,'DISCOUNT',5.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:07:29',NULL,'2026-07-05 14:07:29',NULL),(11,1,'TEST_REWARD_1783260473043','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:07:53',NULL,'2026-07-05 14:07:53',NULL),(12,1,'REDEEM_TEST_1783260476452','Redeem Test Reward',NULL,NULL,50,'DISCOUNT',5.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:07:56',NULL,'2026-07-05 14:07:56',NULL),(13,1,'TEST_REWARD_1783260499261','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:08:19',NULL,'2026-07-05 14:08:19',NULL),(14,1,'REDEEM_TEST_1783260499689','Redeem Test Reward',NULL,NULL,50,'DISCOUNT',5.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:08:19',NULL,'2026-07-05 14:08:19',NULL),(15,1,'TEST_REWARD_1783260633734','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:10:33',NULL,'2026-07-05 14:10:33',NULL),(16,1,'REDEEM_TEST_1783260634220','Redeem Test Reward',NULL,NULL,50,'DISCOUNT',5.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:10:34',NULL,'2026-07-05 14:10:34',NULL),(17,1,'TEST_REWARD_1783261963698','Test Reward','Test Reward','Test reward for Playwright',100,'DISCOUNT',10.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:32:43',NULL,'2026-07-05 14:32:43',NULL),(18,1,'REDEEM_TEST_1783261964487','Redeem Test Reward',NULL,NULL,50,'DISCOUNT',5.00,NULL,'ACTIVE',NULL,NULL,NULL,'2026-07-05 14:32:44',NULL,'2026-07-05 14:32:44',NULL);
/*!40000 ALTER TABLE `loyalty_rewards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_transactions`
--

DROP TABLE IF EXISTS `loyalty_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_transactions` (
  `transaction_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `tenant_id` bigint(20) NOT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_used` int(11) DEFAULT 0,
  `transaction_type` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_transactions`
--

LOCK TABLES `loyalty_transactions` WRITE;
/*!40000 ALTER TABLE `loyalty_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketplace_orders`
--

DROP TABLE IF EXISTS `marketplace_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `total_amount` decimal(15,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `order_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`order_items`)),
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketplace_orders`
--

LOCK TABLES `marketplace_orders` WRITE;
/*!40000 ALTER TABLE `marketplace_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `marketplace_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_ab_test_variants`
--

DROP TABLE IF EXISTS `menu_ab_test_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_ab_test_variants` (
  `variant_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `test_id` bigint(20) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `variant_price` decimal(18,2) NOT NULL,
  `variant_description` text DEFAULT NULL,
  `allocation_percentage` decimal(5,2) NOT NULL,
  `impressions` int(11) DEFAULT 0,
  `conversions` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`variant_id`),
  KEY `idx_test` (`test_id`),
  CONSTRAINT `menu_ab_test_variants_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `menu_ab_tests` (`test_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_ab_test_variants`
--

LOCK TABLES `menu_ab_test_variants` WRITE;
/*!40000 ALTER TABLE `menu_ab_test_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_ab_test_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_ab_tests`
--

DROP TABLE IF EXISTS `menu_ab_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_ab_tests` (
  `test_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `test_name` varchar(255) NOT NULL,
  `baseline_price` decimal(18,2) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `winning_variant_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`test_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_ab_tests`
--

LOCK TABLES `menu_ab_tests` WRITE;
/*!40000 ALTER TABLE `menu_ab_tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_ab_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_engineering_recommendations`
--

DROP TABLE IF EXISTS `menu_engineering_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_engineering_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `recommendation_date` date NOT NULL,
  `recommendation_type` enum('price_adjustment','promotion','remove','feature') NOT NULL,
  `current_price` decimal(10,2) DEFAULT NULL,
  `recommended_price` decimal(10,2) DEFAULT NULL,
  `expected_impact` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`expected_impact`)),
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `implemented` tinyint(1) DEFAULT 0,
  `implemented_at` timestamp NULL DEFAULT NULL,
  `result_measured` tinyint(1) DEFAULT 0,
  `actual_impact` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`actual_impact`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_recommendation_date` (`recommendation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_engineering_recommendations`
--

LOCK TABLES `menu_engineering_recommendations` WRITE;
/*!40000 ALTER TABLE `menu_engineering_recommendations` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_engineering_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'001_create_tenant_tables.php','2026-07-08 08:29:27'),(2,'002_create_user_tables.php','2026-07-08 08:29:45'),(3,'003_create_reconciliation_tables.php','2026-07-08 08:31:07'),(4,'004_create_customer_tables.php','2026-07-08 08:31:07'),(5,'005_create_menu_tables.php','2026-07-08 08:48:35'),(6,'006_create_inventory_tables.php','2026-07-08 08:49:50'),(7,'007_create_order_tables.php','2026-07-08 08:52:36'),(8,'008_create_kitchen_tables.php','2026-07-08 08:53:58'),(9,'009_create_loyalty_tables.php','2026-07-08 08:54:55'),(10,'010_create_staff_tables.php','2026-07-08 08:56:08'),(11,'011_create_accounting_tables.php','2026-07-08 08:56:08'),(12,'012_create_reporting_tables.php','2026-07-08 08:56:38'),(13,'013_create_compliance_tables.php','2026-07-08 08:56:38'),(14,'014_create_sustainability_tables.php','2026-07-08 08:56:38'),(15,'015_create_risk_tables.php','2026-07-08 08:56:58'),(16,'016_create_production_tables.php','2026-07-08 08:58:14'),(17,'017_create_settings_tables.php','2026-07-08 08:58:14'),(18,'018_create_integration_tables.php','2026-07-08 08:58:14'),(19,'019_create_reservation_tables.php','2026-07-08 08:59:13'),(20,'020_create_feedback_tables.php','2026-07-08 08:59:13'),(21,'021_create_language_tables.php','2026-07-08 08:59:45'),(22,'022_create_security_tables.php','2026-07-08 08:59:45'),(23,'023_create_analytics_tables.php','2026-07-08 08:59:46'),(24,'024_create_procurement_tables.php','2026-07-08 09:00:19'),(25,'025_create_inventory_reorder_tables.php','2026-07-08 09:00:44'),(26,'026_create_split_order_tables.php','2026-07-08 09:00:44'),(27,'027_create_payroll_tables.php','2026-07-08 09:00:44'),(28,'028_create_ab_test_tables.php','2026-07-08 09:00:44'),(29,'029_create_seasonal_menu_tables.php','2026-07-08 09:00:45'),(30,'030_create_referral_achievement_tables.php','2026-07-08 09:00:45'),(31,'031_create_staff_messages_tables.php','2026-07-08 09:00:45'),(32,'032_add_weight_based_pricing.php','2026-07-08 09:09:31'),(33,'033_create_inventory_items_table.php','2026-07-08 09:09:31'),(34,'034_add_availability_check_function.php','2026-07-08 09:09:56');
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_effects`
--

DROP TABLE IF EXISTS `network_effects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_effects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `effect_type` enum('restaurant_to_restaurant','consumer_to_restaurant','restaurant_to_consumer','consumer_to_consumer') NOT NULL,
  `effect_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `activation_threshold` int(11) DEFAULT NULL,
  `current_value` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `measurement_frequency_days` int(11) DEFAULT 7,
  `last_measured` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_effect_type` (`effect_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_effects`
--

LOCK TABLES `network_effects` WRITE;
/*!40000 ALTER TABLE `network_effects` DISABLE KEYS */;
INSERT INTO `network_effects` VALUES (1,'restaurant_to_restaurant','Supplier Marketplace','Restaurants sharing supplier information improves marketplace value',50,0,1,7,NULL,'2026-07-05 03:25:53','2026-07-05 03:25:53'),(2,'consumer_to_restaurant','Restaurant Discovery','More consumers using app increases restaurant visibility',100,0,1,7,NULL,'2026-07-05 03:25:53','2026-07-05 03:25:53'),(3,'restaurant_to_consumer','Menu Recommendations','More restaurants provide better menu recommendations',20,0,1,7,NULL,'2026-07-05 03:25:53','2026-07-05 03:25:53'),(4,'consumer_to_consumer','Social Features','More consumers enable social sharing and reviews',200,0,1,7,NULL,'2026-07-05 03:25:53','2026-07-05 03:25:53');
/*!40000 ALTER TABLE `network_effects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `notification_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`notification_type`),
  KEY `idx_read` (`is_read`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `onboarding_templates`
--

DROP TABLE IF EXISTS `onboarding_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `onboarding_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_type` enum('home_based','small_restaurant','regional_chain','national_corporation','international_corporation') NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`steps`)),
  `estimated_duration_minutes` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_business_type` (`business_type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `onboarding_templates`
--

LOCK TABLES `onboarding_templates` WRITE;
/*!40000 ALTER TABLE `onboarding_templates` DISABLE KEYS */;
INSERT INTO `onboarding_templates` VALUES (1,'home_based','Home-Based Quick Start','[{\"step\": 1, \"title\": \"Account Setup\", \"duration\": 5}, {\"step\": 2, \"title\": \"Business Info\", \"duration\": 3}, {\"step\": 3, \"title\": \"Menu Setup\", \"duration\": 10}, {\"step\": 4, \"title\": \"Inventory Setup\", \"duration\": 5}, {\"step\": 5, \"title\": \"Staff Setup\", \"duration\": 2}]',25,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(2,'small_restaurant','Restaurant Standard Onboarding','[{\"step\": 1, \"title\": \"Account Setup\", \"duration\": 5}, {\"step\": 2, \"title\": \"Business Info\", \"duration\": 5}, {\"step\": 3, \"title\": \"Menu Setup\", \"duration\": 15}, {\"step\": 4, \"title\": \"Inventory Setup\", \"duration\": 10}, {\"step\": 5, \"title\": \"Staff Setup\", \"duration\": 10}, {\"step\": 6, \"title\": \"Table Setup\", \"duration\": 5}, {\"step\": 7, \"title\": \"Payment Setup\", \"duration\": 5}]',55,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(3,'regional_chain','Chain Restaurant Onboarding','[{\"step\": 1, \"title\": \"Account Setup\", \"duration\": 10}, {\"step\": 2, \"title\": \"Business Info\", \"duration\": 10}, {\"step\": 3, \"title\": \"Location Setup\", \"duration\": 15}, {\"step\": 4, \"title\": \"Menu Setup\", \"duration\": 20}, {\"step\": 5, \"title\": \"Inventory Setup\", \"duration\": 15}, {\"step\": 6, \"title\": \"Staff Setup\", \"duration\": 15}, {\"step\": 7, \"title\": \"Table Setup\", \"duration\": 10}, {\"step\": 8, \"title\": \"Payment Setup\", \"duration\": 10}, {\"step\": 9, \"title\": \"Multi-Location Config\", \"duration\": 15}]',120,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(4,'national_corporation','Enterprise Onboarding','[{\"step\": 1, \"title\": \"Account Setup\", \"duration\": 15}, {\"step\": 2, \"title\": \"Business Info\", \"duration\": 15}, {\"step\": 3, \"title\": \"Location Setup\", \"duration\": 20}, {\"step\": 4, \"title\": \"Menu Setup\", \"duration\": 30}, {\"step\": 5, \"title\": \"Inventory Setup\", \"duration\": 20}, {\"step\": 6, \"title\": \"Staff Setup\", \"duration\": 20}, {\"step\": 7, \"title\": \"Table Setup\", \"duration\": 15}, {\"step\": 8, \"title\": \"Payment Setup\", \"duration\": 15}, {\"step\": 9, \"title\": \"Multi-Location Config\", \"duration\": 20}, {\"step\": 10, \"title\": \"API Integration\", \"duration\": 30}, {\"step\": 11, \"title\": \"Custom Configuration\", \"duration\": 30}]',240,'2026-07-05 03:24:53','2026-07-05 03:24:53'),(5,'international_corporation','International Enterprise Onboarding','[{\"step\": 1, \"title\": \"Account Setup\", \"duration\": 20}, {\"step\": 2, \"title\": \"Business Info\", \"duration\": 20}, {\"step\": 3, \"title\": \"Location Setup\", \"duration\": 30}, {\"step\": 4, \"title\": \"Menu Setup\", \"duration\": 40}, {\"step\": 5, \"title\": \"Inventory Setup\", \"duration\": 30}, {\"step\": 6, \"title\": \"Staff Setup\", \"duration\": 30}, {\"step\": 7, \"title\": \"Table Setup\", \"duration\": 20}, {\"step\": 8, \"title\": \"Payment Setup\", \"duration\": 20}, {\"step\": 9, \"title\": \"Multi-Location Config\", \"duration\": 30}, {\"step\": 10, \"title\": \"API Integration\", \"duration\": 40}, {\"step\": 11, \"title\": \"International Config\", \"duration\": 30}, {\"step\": 12, \"title\": \"Custom Configuration\", \"duration\": 40}]',360,'2026-07-05 03:24:53','2026-07-05 03:24:53');
/*!40000 ALTER TABLE `onboarding_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_modifiers`
--

DROP TABLE IF EXISTS `order_item_modifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_item_modifiers` (
  `order_item_modifier_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `modifier_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_item_modifier_id`),
  KEY `idx_order_item_modifiers_order_item_id` (`order_item_id`),
  KEY `idx_order_item_modifiers_modifier_id` (`modifier_id`),
  CONSTRAINT `order_item_modifiers_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_modifiers`
--

LOCK TABLES `order_item_modifiers` WRITE;
/*!40000 ALTER TABLE `order_item_modifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item_modifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `actual_weight` decimal(10,3) DEFAULT NULL,
  `actual_unit_id` bigint(20) DEFAULT NULL,
  `calculated_price` decimal(18,2) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('PENDING','PREPARING','READY','SERVED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_item_id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `idx_order_items_product_id` (`product_id`),
  KEY `idx_order_items_status` (`status`),
  KEY `idx_actual_unit` (`actual_unit_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `table_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('PENDING','CONFIRMED','PREPARING','READY','SERVED','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  `order_type` enum('DINE_IN','TAKE_AWAY','DELIVERY','PRE_ORDER') DEFAULT 'DINE_IN',
  `is_open_order` tinyint(1) DEFAULT 1,
  `is_priority` tinyint(1) DEFAULT 0,
  `is_held` tinyint(1) DEFAULT 0,
  `hold_reason` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `delivery_time` datetime DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('UNPAID','PARTIAL','PAID','REFUNDED') DEFAULT 'UNPAID',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `parent_order_id` bigint(20) DEFAULT NULL,
  `split_identifier` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `idx_orders_tenant_number` (`tenant_id`,`order_number`),
  KEY `idx_orders_tenant_id` (`tenant_id`),
  KEY `idx_orders_branch_id` (`branch_id`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_table_id` (`table_id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_created_at` (`created_at`),
  KEY `idx_orders_order_type` (`order_type`),
  KEY `idx_orders_is_open_order` (`is_open_order`),
  KEY `idx_orders_is_priority` (`is_priority`),
  KEY `idx_parent_order` (`parent_order_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `split_bill_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` enum('CASH','QRIS','DEBIT','CREDIT','E_WALLET','TRANSFER','VOUCHER') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('PENDING','COMPLETED','FAILED','REFUNDED') DEFAULT 'COMPLETED',
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_order_id` (`order_id`),
  KEY `idx_payments_split_bill_id` (`split_bill_id`),
  KEY `idx_payments_payment_method` (`payment_method`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`split_bill_id`) REFERENCES `split_bills` (`split_bill_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_entries`
--

DROP TABLE IF EXISTS `payroll_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_entries` (
  `entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `period_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `regular_hours` decimal(10,2) DEFAULT 0.00,
  `overtime_hours` decimal(10,2) DEFAULT 0.00,
  `hourly_rate` decimal(18,2) NOT NULL,
  `regular_pay` decimal(18,2) DEFAULT 0.00,
  `overtime_pay` decimal(18,2) DEFAULT 0.00,
  `bonuses` decimal(18,2) DEFAULT 0.00,
  `deductions` decimal(18,2) DEFAULT 0.00,
  `net_pay` decimal(18,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`entry_id`),
  KEY `idx_period` (`period_id`),
  KEY `idx_employee` (`employee_id`),
  CONSTRAINT `payroll_entries_ibfk_1` FOREIGN KEY (`period_id`) REFERENCES `payroll_periods` (`period_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_entries`
--

LOCK TABLES `payroll_entries` WRITE;
/*!40000 ALTER TABLE `payroll_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_periods`
--

DROP TABLE IF EXISTS `payroll_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_periods` (
  `period_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'OPEN',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`period_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_periods`
--

LOCK TABLES `payroll_periods` WRITE;
/*!40000 ALTER TABLE `payroll_periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permission_code` varchar(100) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_code` (`permission_code`),
  KEY `idx_permissions_module` (`module`),
  KEY `idx_permissions_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'MENU_MANAGE','MENU_MANAGE',NULL,NULL,'MENU MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(2,'TABLE_MANAGE','TABLE_MANAGE',NULL,NULL,'TABLE MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(3,'RESERVATION_MANAGE','RESERVATION_MANAGE',NULL,NULL,'RESERVATION MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(4,'INVENTORY_MANAGE','INVENTORY_MANAGE',NULL,NULL,'INVENTORY MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(5,'KITCHEN_VIEW','KITCHEN_VIEW',NULL,NULL,'KITCHEN VIEW','2026-07-02 07:41:03','2026-07-02 07:41:03'),(6,'USER_MANAGE','USER_MANAGE',NULL,NULL,'USER MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(7,'SETTINGS_MANAGE','SETTINGS_MANAGE',NULL,NULL,'SETTINGS MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03'),(8,'REPORT_VIEW','REPORT_VIEW',NULL,NULL,'REPORT VIEW','2026-07-02 07:41:03','2026-07-02 07:41:03'),(9,'SALES_MANAGE','SALES_MANAGE',NULL,NULL,'SALES MANAGE','2026-07-02 07:41:03','2026-07-02 07:41:03');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_history`
--

DROP TABLE IF EXISTS `price_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_history` (
  `history_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `old_price` decimal(18,2) NOT NULL,
  `new_price` decimal(18,2) NOT NULL,
  `change_percentage` decimal(5,2) DEFAULT NULL,
  `change_reason` varchar(255) DEFAULT NULL,
  `changed_by` bigint(20) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_date` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_history`
--

LOCK TABLES `price_history` WRITE;
/*!40000 ALTER TABLE `price_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifier_assignments`
--

DROP TABLE IF EXISTS `product_modifier_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_modifier_assignments` (
  `assignment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `modifier_group_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `idx_product_modifier_assignments_product_group` (`product_id`,`modifier_group_id`),
  KEY `idx_product_modifier_assignments_product_id` (`product_id`),
  KEY `idx_product_modifier_assignments_modifier_group_id` (`modifier_group_id`),
  CONSTRAINT `product_modifier_assignments_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_modifier_assignments_ibfk_2` FOREIGN KEY (`modifier_group_id`) REFERENCES `product_modifier_groups` (`modifier_group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifier_assignments`
--

LOCK TABLES `product_modifier_assignments` WRITE;
/*!40000 ALTER TABLE `product_modifier_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_modifier_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifier_groups`
--

DROP TABLE IF EXISTS `product_modifier_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_modifier_groups` (
  `modifier_group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `group_code` varchar(50) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `min_selections` int(11) DEFAULT 0,
  `max_selections` int(11) DEFAULT 1,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`modifier_group_id`),
  UNIQUE KEY `idx_modifier_groups_tenant_code` (`tenant_id`,`group_code`),
  KEY `idx_modifier_groups_tenant_id` (`tenant_id`),
  CONSTRAINT `product_modifier_groups_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifier_groups`
--

LOCK TABLES `product_modifier_groups` WRITE;
/*!40000 ALTER TABLE `product_modifier_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_modifier_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_modifiers`
--

DROP TABLE IF EXISTS `product_modifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_modifiers` (
  `modifier_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `modifier_group_id` bigint(20) unsigned NOT NULL,
  `modifier_code` varchar(50) NOT NULL,
  `modifier_name` varchar(100) NOT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_available` tinyint(1) DEFAULT 1,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`modifier_id`),
  UNIQUE KEY `idx_modifiers_group_code` (`modifier_group_id`,`modifier_code`),
  KEY `idx_modifiers_modifier_group_id` (`modifier_group_id`),
  CONSTRAINT `product_modifiers_ibfk_1` FOREIGN KEY (`modifier_group_id`) REFERENCES `product_modifier_groups` (`modifier_group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_modifiers`
--

LOCK TABLES `product_modifiers` WRITE;
/*!40000 ALTER TABLE `product_modifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_modifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_prices`
--

DROP TABLE IF EXISTS `product_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_prices` (
  `price_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `price_type` enum('REGULAR','REFRIGERATED','WITH_ICE','HOT','ROOM_TEMPERATURE','FROZEN','TAKEAWAY','DINE_IN','DELIVERY','PROMOTIONAL','BULK','WHOLESALE') DEFAULT 'REGULAR' COMMENT 'Product condition/service type pricing',
  `price` decimal(18,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`price_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_type` (`price_type`),
  KEY `idx_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_prices`
--

LOCK TABLES `product_prices` WRITE;
/*!40000 ALTER TABLE `product_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `variant_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `variant_code` varchar(50) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_default` tinyint(1) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`variant_id`),
  UNIQUE KEY `idx_product_variants_product_code` (`product_id`,`variant_code`),
  KEY `idx_product_variants_product_id` (`product_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `production_batches`
--

DROP TABLE IF EXISTS `production_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `production_batches` (
  `batch_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `recipe_id` bigint(20) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'PENDING',
  `yield_percentage` decimal(5,2) DEFAULT 100.00,
  `production_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`batch_id`),
  UNIQUE KEY `batch_number` (`batch_number`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_recipe` (`recipe_id`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`production_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `production_batches`
--

LOCK TABLES `production_batches` WRITE;
/*!40000 ALTER TABLE `production_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `production_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `preparation_time` int(11) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `pricing_type` enum('FIXED','WEIGHT_BASED','UNIT_BASED') DEFAULT 'FIXED',
  `unit_price_per_kg` decimal(18,2) DEFAULT NULL,
  `unit_price_per_unit` decimal(18,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `idx_products_tenant_code` (`tenant_id`,`product_code`),
  KEY `idx_products_tenant_id` (`tenant_id`),
  KEY `idx_products_category_id` (`category_id`),
  KEY `idx_products_name` (`product_name`),
  KEY `idx_products_status` (`status`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_items` (
  `poi_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `po_id` bigint(20) NOT NULL,
  `inventory_id` bigint(20) NOT NULL,
  `quantity` decimal(10,4) NOT NULL,
  `unit_price` decimal(18,2) NOT NULL,
  `line_total` decimal(18,2) DEFAULT NULL,
  `received_quantity` decimal(10,4) DEFAULT 0.0000,
  PRIMARY KEY (`poi_id`),
  KEY `idx_po` (`po_id`),
  KEY `idx_inventory` (`inventory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `po_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `po_number` varchar(50) DEFAULT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'DRAFT',
  `subtotal` decimal(18,2) DEFAULT NULL,
  `tax_amount` decimal(18,2) DEFAULT NULL,
  `total_amount` decimal(18,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `approved_by` bigint(20) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`po_id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`order_date`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_ingredients`
--

DROP TABLE IF EXISTS `recipe_ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_ingredients` (
  `recipe_ingredient_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `recipe_id` bigint(20) unsigned NOT NULL,
  `ingredient_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `allergen_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allergen_info`)),
  `dietary_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dietary_info`)),
  PRIMARY KEY (`recipe_ingredient_id`),
  KEY `idx_recipe_ingredients_recipe_id` (`recipe_id`),
  KEY `idx_recipe_ingredients_ingredient_id` (`ingredient_id`),
  CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_ingredients`
--

LOCK TABLES `recipe_ingredients` WRITE;
/*!40000 ALTER TABLE `recipe_ingredients` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipes`
--

DROP TABLE IF EXISTS `recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipes` (
  `recipe_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `recipe_code` varchar(50) NOT NULL,
  `recipe_name` varchar(100) NOT NULL,
  `instructions` text DEFAULT NULL,
  `yield_quantity` decimal(10,2) DEFAULT 1.00,
  `yield_unit` varchar(20) DEFAULT 'portion',
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sourcing_type` enum('self_produced','outsourced','supplier_sourced','mixed') DEFAULT 'supplier_sourced',
  `halal_certified` tinyint(1) DEFAULT 0,
  `halal_certification_id` int(11) DEFAULT NULL,
  `production_cost_labor` decimal(10,2) DEFAULT 0.00,
  `production_cost_equipment` decimal(10,2) DEFAULT 0.00,
  `production_cost_overhead` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`recipe_id`),
  UNIQUE KEY `idx_recipes_tenant_code` (`tenant_id`,`recipe_code`),
  KEY `idx_recipes_tenant_id` (`tenant_id`),
  KEY `idx_recipes_product_id` (`product_id`),
  KEY `idx_recipes_status` (`status`),
  KEY `idx_sourcing_type` (`sourcing_type`),
  KEY `idx_halal_certified` (`halal_certified`),
  CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipes`
--

LOCK TABLES `recipes` WRITE;
/*!40000 ALTER TABLE `recipes` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconciliation_alerts`
--

DROP TABLE IF EXISTS `reconciliation_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconciliation_alerts` (
  `alert_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `alert_type` enum('CRITICAL','WARNING','INFO') DEFAULT 'WARNING',
  `message` varchar(500) NOT NULL,
  `discrepancies_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`discrepancies_json`)),
  `status` enum('ACTIVE','ACKNOWLEDGED','RESOLVED','DISMISSED') DEFAULT 'ACTIVE',
  `acknowledged_by` bigint(20) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`alert_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconciliation_alerts`
--

LOCK TABLES `reconciliation_alerts` WRITE;
/*!40000 ALTER TABLE `reconciliation_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconciliation_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconciliation_batch_jobs`
--

DROP TABLE IF EXISTS `reconciliation_batch_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconciliation_batch_jobs` (
  `batch_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `job_type` enum('FULL_RECONCILIATION','PARTIAL_RECONCILIATION','SOURCE_SYNC','ALERT_CHECK') NOT NULL,
  `status` enum('PENDING','RUNNING','COMPLETED','FAILED','CANCELLED') DEFAULT 'PENDING',
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `total_orders` int(11) DEFAULT 0,
  `processed_orders` int(11) DEFAULT 0,
  `successful_orders` int(11) DEFAULT 0,
  `failed_orders` int(11) DEFAULT 0,
  `discrepancies_found` int(11) DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`batch_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_job_type` (`job_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconciliation_batch_jobs`
--

LOCK TABLES `reconciliation_batch_jobs` WRITE;
/*!40000 ALTER TABLE `reconciliation_batch_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconciliation_batch_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconciliation_logs`
--

DROP TABLE IF EXISTS `reconciliation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconciliation_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `status` enum('RECONCILED','DISCREPANCY_HIGH','DISCREPANCY_LOW','PENDING','MANUALLY_OVERRIDDEN') DEFAULT 'PENDING',
  `expected_total` decimal(18,2) NOT NULL,
  `actual_total` decimal(18,2) NOT NULL,
  `difference` decimal(18,2) DEFAULT NULL,
  `discrepancies_count` int(11) DEFAULT 0,
  `discrepancies_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`discrepancies_json`)),
  `reconciled_at` timestamp NULL DEFAULT NULL,
  `reconciled_by` varchar(100) DEFAULT 'SYSTEM',
  `override_reason` text DEFAULT NULL,
  `overridden_by` bigint(20) DEFAULT NULL,
  `overridden_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_reconciled_at` (`reconciled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconciliation_logs`
--

LOCK TABLES `reconciliation_logs` WRITE;
/*!40000 ALTER TABLE `reconciliation_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconciliation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconciliation_rules`
--

DROP TABLE IF EXISTS `reconciliation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconciliation_rules` (
  `rule_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `rule_code` varchar(100) NOT NULL,
  `rule_type` enum('MATCHING','TOLERANCE','ALERTING','AUTO_CORRECTION') NOT NULL,
  `source_type` enum('POS','PAYMENT_PROCESSOR','DELIVERY_PLATFORM','BANK','CASH_REGISTER') DEFAULT NULL,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`conditions`)),
  `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`actions`)),
  `priority` int(11) DEFAULT 100,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`rule_id`),
  UNIQUE KEY `uk_rule_code_tenant` (`rule_code`,`tenant_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_rule_type` (`rule_type`),
  KEY `idx_source_type` (`source_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconciliation_rules`
--

LOCK TABLES `reconciliation_rules` WRITE;
/*!40000 ALTER TABLE `reconciliation_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconciliation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconciliation_sources`
--

DROP TABLE IF EXISTS `reconciliation_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconciliation_sources` (
  `source_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `source_type` enum('POS','PAYMENT_PROCESSOR','DELIVERY_PLATFORM','BANK','CASH_REGISTER') NOT NULL,
  `source_name` varchar(255) NOT NULL,
  `source_code` varchar(100) NOT NULL,
  `api_endpoint` varchar(500) DEFAULT NULL,
  `api_key_encrypted` text DEFAULT NULL,
  `api_secret_encrypted` text DEFAULT NULL,
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `is_active` tinyint(1) DEFAULT 1,
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `sync_frequency_minutes` int(11) DEFAULT 60,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`source_id`),
  UNIQUE KEY `uk_source_code_tenant` (`source_code`,`tenant_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_source_type` (`source_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconciliation_sources`
--

LOCK TABLES `reconciliation_sources` WRITE;
/*!40000 ALTER TABLE `reconciliation_sources` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconciliation_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reconciliation_transactions`
--

DROP TABLE IF EXISTS `reconciliation_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reconciliation_transactions` (
  `transaction_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_id` bigint(20) NOT NULL,
  `source_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `external_transaction_id` varchar(255) DEFAULT NULL,
  `transaction_type` enum('PAYMENT','REFUND','VOID','ADJUSTMENT') DEFAULT 'PAYMENT',
  `amount` decimal(18,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('MATCHED','UNMATCHED','DISPUTED') DEFAULT 'UNMATCHED',
  `matching_score` decimal(5,2) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `idx_log_id` (`log_id`),
  KEY `idx_source_id` (`source_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_external_transaction_id` (`external_transaction_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reconciliation_transactions`
--

LOCK TABLES `reconciliation_transactions` WRITE;
/*!40000 ALTER TABLE `reconciliation_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `reconciliation_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_programs`
--

DROP TABLE IF EXISTS `referral_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referral_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `program_type` enum('restaurant_referral','consumer_referral','staff_referral') NOT NULL,
  `reward_type` enum('credit','discount','cash','points') NOT NULL,
  `reward_value` decimal(10,2) NOT NULL,
  `referrer_reward_value` decimal(10,2) DEFAULT NULL,
  `max_rewards_per_user` int(11) DEFAULT NULL,
  `program_start_date` date NOT NULL,
  `program_end_date` date DEFAULT NULL,
  `status` enum('active','paused','ended') DEFAULT 'active',
  `terms` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_program_type` (`program_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_programs`
--

LOCK TABLES `referral_programs` WRITE;
/*!40000 ALTER TABLE `referral_programs` DISABLE KEYS */;
INSERT INTO `referral_programs` VALUES (1,'Restaurant Referral Program','restaurant_referral','credit',500.00,500.00,10,'2026-07-05',NULL,'active','Refer a restaurant and both get $500 credit when they sign up for a paid plan.','2026-07-05 03:25:53','2026-07-05 03:25:53'),(2,'Consumer Referral Program','consumer_referral','discount',10.00,5.00,50,'2026-07-05',NULL,'active','Refer a friend and get $10 discount, they get $5 discount on first order.','2026-07-05 03:25:53','2026-07-05 03:25:53');
/*!40000 ALTER TABLE `referral_programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referral_program_id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referee_id` int(11) DEFAULT NULL,
  `referral_code` varchar(50) NOT NULL,
  `status` enum('pending','completed','rewarded','expired') DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `reward_claimed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_referral_program_id` (`referral_program_id`),
  KEY `idx_referrer_id` (`referrer_id`),
  KEY `idx_referee_id` (`referee_id`),
  KEY `idx_referral_code` (`referral_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

LOCK TABLES `referrals` WRITE;
/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reorder_alerts`
--

DROP TABLE IF EXISTS `reorder_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reorder_alerts` (
  `alert_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `inventory_id` bigint(20) NOT NULL,
  `alert_type` varchar(50) DEFAULT 'LOW_STOCK',
  `current_stock` decimal(10,4) DEFAULT NULL,
  `reorder_point` decimal(10,4) DEFAULT NULL,
  `quantity_needed` decimal(10,4) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'NEW',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`alert_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reorder_alerts`
--

LOCK TABLES `reorder_alerts` WRITE;
/*!40000 ALTER TABLE `reorder_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `reorder_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_schedules`
--

DROP TABLE IF EXISTS `report_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_schedules` (
  `schedule_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `schedule_type` varchar(20) DEFAULT 'DAILY',
  `schedule_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`schedule_config`)),
  `next_run_at` timestamp NULL DEFAULT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_next_run` (`next_run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_schedules`
--

LOCK TABLES `report_schedules` WRITE;
/*!40000 ALTER TABLE `report_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `report_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `report_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_config`)),
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` bigint(20) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'COMPLETED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`report_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`report_type`),
  KEY `idx_date` (`generated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `reservation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `reservation_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `table_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `party_size` int(11) NOT NULL,
  `status` enum('PENDING','CONFIRMED','SEATED','COMPLETED','CANCELLED','NO_SHOW') DEFAULT 'PENDING',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`reservation_id`),
  UNIQUE KEY `idx_reservations_tenant_number` (`tenant_id`,`reservation_number`),
  KEY `idx_reservations_tenant_id` (`tenant_id`),
  KEY `idx_reservations_branch_id` (`branch_id`),
  KEY `idx_reservations_table_id` (`table_id`),
  KEY `idx_reservations_date` (`reservation_date`),
  KEY `idx_reservations_status` (`status`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_tables`
--

DROP TABLE IF EXISTS `restaurant_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restaurant_tables` (
  `table_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `table_number` varchar(20) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT 4,
  `table_type` varchar(20) DEFAULT 'STANDARD',
  `location` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `status` varchar(20) DEFAULT 'AVAILABLE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `uk_branch_table` (`branch_id`,`table_number`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_tables`
--

LOCK TABLES `restaurant_tables` WRITE;
/*!40000 ALTER TABLE `restaurant_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_assessments`
--

DROP TABLE IF EXISTS `risk_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `risk_category` enum('technical','business','operational','external') NOT NULL,
  `risk_type` varchar(100) NOT NULL,
  `risk_description` text DEFAULT NULL,
  `probability` enum('very_low','low','medium','high','very_high') NOT NULL,
  `impact` enum('very_low','low','medium','high','very_high') NOT NULL,
  `risk_score` int(11) NOT NULL,
  `mitigation_strategy` text DEFAULT NULL,
  `mitigation_status` enum('not_started','in_progress','completed','on_hold') DEFAULT 'not_started',
  `owner` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_risk_category` (`risk_category`),
  KEY `idx_risk_score` (`risk_score`),
  KEY `idx_mitigation_status` (`mitigation_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_assessments`
--

LOCK TABLES `risk_assessments` WRITE;
/*!40000 ALTER TABLE `risk_assessments` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_incidents`
--

DROP TABLE IF EXISTS `risk_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `risk_assessment_id` int(11) DEFAULT NULL,
  `incident_type` varchar(100) NOT NULL,
  `incident_description` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL,
  `status` enum('open','investigating','resolved','closed') DEFAULT 'open',
  `impact_assessment` text DEFAULT NULL,
  `resolution_actions` text DEFAULT NULL,
  `lessons_learned` text DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_risk_assessment_id` (`risk_assessment_id`),
  KEY `idx_incident_type` (`incident_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_incidents`
--

LOCK TABLES `risk_incidents` WRITE;
/*!40000 ALTER TABLE `risk_incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_mitigation_plans`
--

DROP TABLE IF EXISTS `risk_mitigation_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_mitigation_plans` (
  `plan_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `risk_id` bigint(20) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`actions`)),
  `responsible_person` varchar(255) DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'PENDING',
  `progress_percentage` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`plan_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_risk` (`risk_id`),
  KEY `idx_status` (`status`),
  KEY `idx_target` (`target_date`),
  CONSTRAINT `risk_mitigation_plans_ibfk_1` FOREIGN KEY (`risk_id`) REFERENCES `risk_register` (`risk_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_mitigation_plans`
--

LOCK TABLES `risk_mitigation_plans` WRITE;
/*!40000 ALTER TABLE `risk_mitigation_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_mitigation_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `risk_register`
--

DROP TABLE IF EXISTS `risk_register`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_register` (
  `risk_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `risk_type` varchar(50) NOT NULL,
  `risk_category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `risk_level` varchar(20) DEFAULT 'MEDIUM',
  `score` int(11) DEFAULT 50,
  `impact` varchar(20) DEFAULT NULL,
  `likelihood` varchar(20) DEFAULT NULL,
  `detectability` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `identified_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`risk_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`risk_type`),
  KEY `idx_category` (`risk_category`),
  KEY `idx_level` (`risk_level`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `risk_register`
--

LOCK TABLES `risk_register` WRITE;
/*!40000 ALTER TABLE `risk_register` DISABLE KEYS */;
/*!40000 ALTER TABLE `risk_register` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `idx_role_permissions_role_permission` (`role_id`,`permission_id`),
  KEY `idx_role_permissions_role_id` (`role_id`),
  KEY `idx_role_permissions_permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,2,1,'2026-07-02 07:41:03'),(2,2,2,'2026-07-02 07:41:03'),(3,2,3,'2026-07-02 07:41:03'),(4,2,4,'2026-07-02 07:41:03'),(5,2,5,'2026-07-02 07:41:03'),(6,2,6,'2026-07-02 07:41:03'),(7,2,7,'2026-07-02 07:41:03'),(8,2,8,'2026-07-02 07:41:03'),(9,2,9,'2026-07-02 07:41:03');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `role_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `role_code` varchar(50) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `idx_roles_tenant_code` (`tenant_id`,`role_code`),
  KEY `idx_roles_tenant_id` (`tenant_id`),
  KEY `idx_roles_status` (`status`),
  CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (2,1,'ADMIN','Administrator','Full system access',0,'ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_reports`
--

DROP TABLE IF EXISTS `saved_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saved_reports` (
  `report_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `report_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_config`)),
  `schedule` varchar(50) DEFAULT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`report_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_reports`
--

LOCK TABLES `saved_reports` WRITE;
/*!40000 ALTER TABLE `saved_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `schedule_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `shift_name` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'SCHEDULED',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_date` (`shift_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seasonal_menu_items`
--

DROP TABLE IF EXISTS `seasonal_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasonal_menu_items` (
  `menu_item_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `menu_id` bigint(20) NOT NULL,
  `recipe_id` bigint(20) NOT NULL,
  `priority` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`menu_item_id`),
  KEY `idx_menu` (`menu_id`),
  KEY `idx_recipe` (`recipe_id`),
  CONSTRAINT `seasonal_menu_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `seasonal_menus` (`menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seasonal_menu_items`
--

LOCK TABLES `seasonal_menu_items` WRITE;
/*!40000 ALTER TABLE `seasonal_menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `seasonal_menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seasonal_menus`
--

DROP TABLE IF EXISTS `seasonal_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasonal_menus` (
  `menu_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `season` varchar(20) NOT NULL,
  `year` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`menu_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_season` (`season`),
  KEY `idx_year` (`year`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seasonal_menus`
--

LOCK TABLES `seasonal_menus` WRITE;
/*!40000 ALTER TABLE `seasonal_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `seasonal_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_audit_logs`
--

DROP TABLE IF EXISTS `security_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_data`)),
  `response_status` int(11) DEFAULT NULL,
  `severity` enum('info','warning','critical') DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_audit_logs`
--

LOCK TABLES `security_audit_logs` WRITE;
/*!40000 ALTER TABLE `security_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_events`
--

DROP TABLE IF EXISTS `security_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_events` (
  `event_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_severity` varchar(20) DEFAULT 'INFO',
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`event_type`),
  KEY `idx_severity` (`event_severity`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_events`
--

LOCK TABLES `security_events` WRITE;
/*!40000 ALTER TABLE `security_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_incidents`
--

DROP TABLE IF EXISTS `security_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_incidents` (
  `incident_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `incident_type` varchar(50) NOT NULL,
  `severity` varchar(20) DEFAULT 'MEDIUM',
  `description` text DEFAULT NULL,
  `affected_users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`affected_users`)),
  `status` varchar(20) DEFAULT 'OPEN',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`incident_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`incident_type`),
  KEY `idx_status` (`status`),
  KEY `idx_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_incidents`
--

LOCK TABLES `security_incidents` WRITE;
/*!40000 ALTER TABLE `security_incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('STRING','NUMBER','BOOLEAN','JSON') DEFAULT 'STRING',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `idx_settings_tenant_key` (`tenant_id`,`setting_key`),
  KEY `idx_settings_tenant_id` (`tenant_id`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `skill_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`skill_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sla_monitoring`
--

DROP TABLE IF EXISTS `sla_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sla_monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `sla_type` varchar(50) NOT NULL,
  `sla_target` decimal(5,2) NOT NULL,
  `sla_actual` decimal(5,2) DEFAULT NULL,
  `measurement_period` varchar(20) NOT NULL,
  `status` enum('met','breached','warning') NOT NULL,
  `breach_reason` text DEFAULT NULL,
  `measured_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_sla_type` (`sla_type`),
  KEY `idx_status` (`status`),
  KEY `idx_measured_at` (`measured_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sla_monitoring`
--

LOCK TABLES `sla_monitoring` WRITE;
/*!40000 ALTER TABLE `sla_monitoring` DISABLE KEYS */;
/*!40000 ALTER TABLE `sla_monitoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spinoff_analytics`
--

DROP TABLE IF EXISTS `spinoff_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spinoff_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spinoff_app_id` int(11) NOT NULL,
  `analytics_date` date NOT NULL,
  `metric_type` enum('users','revenue','engagement','retention','acquisition') NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(15,2) NOT NULL,
  `segment` varchar(50) DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_spinoff_app_id` (`spinoff_app_id`),
  KEY `idx_analytics_date` (`analytics_date`),
  KEY `idx_metric_type` (`metric_type`),
  CONSTRAINT `spinoff_analytics_ibfk_1` FOREIGN KEY (`spinoff_app_id`) REFERENCES `spinoff_apps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spinoff_analytics`
--

LOCK TABLES `spinoff_analytics` WRITE;
/*!40000 ALTER TABLE `spinoff_analytics` DISABLE KEYS */;
/*!40000 ALTER TABLE `spinoff_analytics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spinoff_apps`
--

DROP TABLE IF EXISTS `spinoff_apps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spinoff_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_name` varchar(100) NOT NULL,
  `app_code` varchar(50) NOT NULL,
  `app_category` enum('consumer_facing','supplier_facing','staff_facing','analytics','niche','international') NOT NULL,
  `app_description` text DEFAULT NULL,
  `target_audience` varchar(100) DEFAULT NULL,
  `problem_solved` text DEFAULT NULL,
  `market_potential` enum('high','medium','low') NOT NULL,
  `strategic_fit` enum('high','medium','low') NOT NULL,
  `feasibility` enum('high','medium','low') NOT NULL,
  `risk_level` enum('high','medium','low') NOT NULL,
  `estimated_development_months` int(11) DEFAULT NULL,
  `estimated_budget` decimal(15,2) DEFAULT NULL,
  `monetization_model` enum('subscription','transaction','freemium','advertising','marketplace') NOT NULL,
  `status` enum('idea','validation','development','beta','launched','paused','cancelled') DEFAULT 'idea',
  `launch_date` date DEFAULT NULL,
  `parent_restaurant_erp_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_code` (`app_code`),
  KEY `idx_app_category` (`app_category`),
  KEY `idx_status` (`status`),
  KEY `idx_market_potential` (`market_potential`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spinoff_apps`
--

LOCK TABLES `spinoff_apps` WRITE;
/*!40000 ALTER TABLE `spinoff_apps` DISABLE KEYS */;
INSERT INTO `spinoff_apps` VALUES (1,'Supplier Marketplace','SUPP_MKT','supplier_facing','B2B marketplace connecting restaurants with suppliers','Restaurants and Suppliers','Inefficient supplier discovery and ordering','high','high','high','medium',6,150000.00,'marketplace','idea',NULL,NULL,'2026-07-05 03:27:33','2026-07-05 03:27:33'),(2,'Halal Food Finder','HALAL_FOOD','consumer_facing','App for finding halal-certified restaurants','Muslim consumers','Difficulty finding halal food options','high','high','high','low',4,80000.00,'freemium','idea',NULL,NULL,'2026-07-05 03:27:33','2026-07-05 03:27:33'),(3,'Food Waste Reduction','FOOD_WASTE','consumer_facing','App connecting restaurants with consumers for discounted near-expiry food','Eco-conscious consumers','Food waste in restaurants','medium','medium','high','medium',5,100000.00,'transaction','idea',NULL,NULL,'2026-07-05 03:27:33','2026-07-05 03:27:33'),(4,'Staff Marketplace','STAFF_MKT','staff_facing','Gig economy platform for restaurant staff','Restaurant workers and restaurants','Staff shortage and flexible staffing needs','high','high','high','medium',6,120000.00,'marketplace','idea',NULL,NULL,'2026-07-05 03:27:33','2026-07-05 03:27:33'),(5,'Food Traceability','FOOD_TRACE','supplier_facing','Blockchain-based food traceability system','Food industry','Food safety and transparency concerns','medium','medium','medium','high',8,200000.00,'subscription','idea',NULL,NULL,'2026-07-05 03:27:33','2026-07-05 03:27:33'),(6,'Indonesian Food Discovery','INDO_FOOD','international','App for discovering Indonesian food globally','International foodies','Lack of Indonesian food visibility abroad','medium','high','medium','medium',5,90000.00,'freemium','idea',NULL,NULL,'2026-07-05 03:27:33','2026-07-05 03:27:33');
/*!40000 ALTER TABLE `spinoff_apps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spinoff_milestones`
--

DROP TABLE IF EXISTS `spinoff_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spinoff_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spinoff_app_id` int(11) NOT NULL,
  `milestone_name` varchar(100) NOT NULL,
  `milestone_description` text DEFAULT NULL,
  `target_date` date NOT NULL,
  `actual_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','delayed') DEFAULT 'pending',
  `progress_percentage` int(11) DEFAULT 0,
  `owner` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_spinoff_app_id` (`spinoff_app_id`),
  KEY `idx_target_date` (`target_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `spinoff_milestones_ibfk_1` FOREIGN KEY (`spinoff_app_id`) REFERENCES `spinoff_apps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spinoff_milestones`
--

LOCK TABLES `spinoff_milestones` WRITE;
/*!40000 ALTER TABLE `spinoff_milestones` DISABLE KEYS */;
/*!40000 ALTER TABLE `spinoff_milestones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `split_bill_items`
--

DROP TABLE IF EXISTS `split_bill_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `split_bill_items` (
  `split_bill_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `split_bill_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`split_bill_item_id`),
  KEY `idx_split_bill_items_split_bill_id` (`split_bill_id`),
  KEY `idx_split_bill_items_order_item_id` (`order_item_id`),
  CONSTRAINT `split_bill_items_ibfk_1` FOREIGN KEY (`split_bill_id`) REFERENCES `split_bills` (`split_bill_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `split_bill_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `split_bill_items`
--

LOCK TABLES `split_bill_items` WRITE;
/*!40000 ALTER TABLE `split_bill_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `split_bill_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `split_bills`
--

DROP TABLE IF EXISTS `split_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `split_bills` (
  `split_bill_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `split_type` enum('PER_PERSON','PER_ITEM','CUSTOM') NOT NULL,
  `total_splits` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`split_bill_id`),
  KEY `idx_split_bills_order_id` (`order_id`),
  CONSTRAINT `split_bills_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `split_bills`
--

LOCK TABLES `split_bills` WRITE;
/*!40000 ALTER TABLE `split_bills` DISABLE KEYS */;
/*!40000 ALTER TABLE `split_bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_gig_bookings`
--

DROP TABLE IF EXISTS `staff_gig_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_gig_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `shift_start` time NOT NULL,
  `shift_end` time NOT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `booking_status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `rating_given_by_staff` int(11) DEFAULT NULL,
  `rating_given_by_restaurant` int(11) DEFAULT NULL,
  `feedback_by_staff` text DEFAULT NULL,
  `feedback_by_restaurant` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_booking_date` (`booking_date`),
  KEY `idx_booking_status` (`booking_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_gig_bookings`
--

LOCK TABLES `staff_gig_bookings` WRITE;
/*!40000 ALTER TABLE `staff_gig_bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_gig_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_marketplace`
--

DROP TABLE IF EXISTS `staff_marketplace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_marketplace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `job_category` enum('chef','waiter','bartender','manager','cleaner','other') NOT NULL,
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills`)),
  `experience_years` int(11) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `availability` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`availability`)),
  `preferred_locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_locations`)),
  `certifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`certifications`)),
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_job_category` (`job_category`),
  KEY `idx_is_available` (`is_available`),
  KEY `idx_is_verified` (`is_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_marketplace`
--

LOCK TABLES `staff_marketplace` WRITE;
/*!40000 ALTER TABLE `staff_marketplace` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_marketplace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_messages`
--

DROP TABLE IF EXISTS `staff_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_messages` (
  `message_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `sender_id` bigint(20) NOT NULL,
  `recipient_id` bigint(20) NOT NULL,
  `message` text NOT NULL,
  `message_type` varchar(50) DEFAULT 'GENERAL',
  `status` varchar(20) DEFAULT 'SENT',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_recipient` (`recipient_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_messages`
--

LOCK TABLES `staff_messages` WRITE;
/*!40000 ALTER TABLE `staff_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_schedule_recommendations`
--

DROP TABLE IF EXISTS `staff_schedule_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_schedule_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `shift_type` varchar(50) NOT NULL,
  `recommended_staff_count` int(11) DEFAULT NULL,
  `predicted_demand` int(11) DEFAULT NULL,
  `required_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_skills`)),
  `cost_estimate` decimal(10,2) DEFAULT NULL,
  `implemented` tinyint(1) DEFAULT 0,
  `actual_staff_count` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_schedule_date` (`schedule_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_schedule_recommendations`
--

LOCK TABLES `staff_schedule_recommendations` WRITE;
/*!40000 ALTER TABLE `staff_schedule_recommendations` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_schedule_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_balances`
--

DROP TABLE IF EXISTS `stock_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_balances` (
  `stock_balance_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) NOT NULL,
  `inventory_id` bigint(20) NOT NULL,
  `quantity` decimal(18,4) DEFAULT 0.0000,
  `average_cost` decimal(18,2) DEFAULT NULL,
  `last_transaction_date` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`stock_balance_id`),
  UNIQUE KEY `uk_branch_inventory` (`branch_id`,`inventory_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_inventory` (`inventory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_balances`
--

LOCK TABLES `stock_balances` WRITE;
/*!40000 ALTER TABLE `stock_balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transactions`
--

DROP TABLE IF EXISTS `stock_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_transactions` (
  `stock_transaction_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `transaction_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`stock_transaction_id`),
  KEY `idx_stock_transactions_tenant_id` (`tenant_id`),
  KEY `idx_stock_transactions_branch_id` (`branch_id`),
  KEY `idx_stock_transactions_product_id` (`product_id`),
  KEY `idx_stock_transactions_type` (`transaction_type`),
  KEY `idx_stock_transactions_created_at` (`created_at`),
  CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stock_transactions_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stock_transactions_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transactions`
--

LOCK TABLES `stock_transactions` WRITE;
/*!40000 ALTER TABLE `stock_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_ad_placements`
--

DROP TABLE IF EXISTS `supplier_ad_placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_ad_placements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `ad_campaign_id` int(11) NOT NULL,
  `placement_type` enum('banner','sponsored_product','featured_supplier') NOT NULL,
  `placement_position` varchar(50) DEFAULT NULL,
  `target_audience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_audience`)),
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','paused','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ad_campaign_id` (`ad_campaign_id`),
  KEY `idx_placement_type` (`placement_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_ad_placements`
--

LOCK TABLES `supplier_ad_placements` WRITE;
/*!40000 ALTER TABLE `supplier_ad_placements` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_ad_placements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_contracts`
--

DROP TABLE IF EXISTS `supplier_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contract_number` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `pricing_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pricing_json`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_contract_number` (`contract_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_contracts`
--

LOCK TABLES `supplier_contracts` WRITE;
/*!40000 ALTER TABLE `supplier_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_marketplace`
--

DROP TABLE IF EXISTS `supplier_marketplace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_marketplace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_category` varchar(100) NOT NULL,
  `product_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `minimum_order_quantity` decimal(10,2) DEFAULT NULL,
  `available_stock` decimal(10,2) DEFAULT NULL,
  `product_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`product_images`)),
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `certifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`certifications`)),
  `delivery_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`delivery_options`)),
  `rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product_category` (`product_category`),
  KEY `idx_is_featured` (`is_featured`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_marketplace`
--

LOCK TABLES `supplier_marketplace` WRITE;
/*!40000 ALTER TABLE `supplier_marketplace` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_marketplace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_products`
--

DROP TABLE IF EXISTS `supplier_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_products` (
  `supplier_product_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) NOT NULL,
  `inventory_id` bigint(20) NOT NULL,
  `supplier_sku` varchar(100) DEFAULT NULL,
  `supplier_price` decimal(18,2) DEFAULT NULL,
  `minimum_order_quantity` decimal(10,4) DEFAULT NULL,
  `is_preferred` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`supplier_product_id`),
  UNIQUE KEY `uk_supplier_inventory` (`supplier_id`,`inventory_id`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_inventory` (`inventory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_products`
--

LOCK TABLES `supplier_products` WRITE;
/*!40000 ALTER TABLE `supplier_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `supplier_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `supplier_code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Indonesia',
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `lead_time_days` int(11) DEFAULT 7,
  `credit_limit` decimal(18,2) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 3.00,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`supplier_id`),
  UNIQUE KEY `supplier_code` (`supplier_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_code` (`supplier_code`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_responses`
--

DROP TABLE IF EXISTS `survey_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_responses` (
  `response_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `survey_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`response_id`),
  KEY `idx_survey` (`survey_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_date` (`completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_responses`
--

LOCK TABLES `survey_responses` WRITE;
/*!40000 ALTER TABLE `survey_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surveys`
--

DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surveys` (
  `survey_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `survey_name` varchar(255) NOT NULL,
  `survey_type` varchar(50) DEFAULT NULL,
  `questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`questions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`survey_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surveys`
--

LOCK TABLES `surveys` WRITE;
/*!40000 ALTER TABLE `surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sustainability_reports`
--

DROP TABLE IF EXISTS `sustainability_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sustainability_reports` (
  `report_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `report_period_start` date NOT NULL,
  `report_period_end` date NOT NULL,
  `carbon_footprint_kg` decimal(18,2) DEFAULT NULL,
  `waste_total_kg` decimal(18,2) DEFAULT NULL,
  `energy_total_kwh` decimal(18,2) DEFAULT NULL,
  `sustainability_score` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`report_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_period` (`report_period_start`,`report_period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sustainability_reports`
--

LOCK TABLES `sustainability_reports` WRITE;
/*!40000 ALTER TABLE `sustainability_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `sustainability_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_backups`
--

DROP TABLE IF EXISTS `system_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_backups` (
  `backup_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `backup_type` varchar(50) DEFAULT 'FULL',
  `backup_status` varchar(20) DEFAULT 'SUCCESS',
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `last_backup_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `next_backup_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`backup_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`backup_type`),
  KEY `idx_status` (`backup_status`),
  KEY `idx_date` (`last_backup_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_backups`
--

LOCK TABLES `system_backups` WRITE;
/*!40000 ALTER TABLE `system_backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_health_checks`
--

DROP TABLE IF EXISTS `system_health_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_health_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `check_type` varchar(50) NOT NULL,
  `check_name` varchar(100) NOT NULL,
  `status` enum('healthy','warning','critical','unknown') NOT NULL,
  `message` text DEFAULT NULL,
  `metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metrics`)),
  `last_checked` timestamp NOT NULL DEFAULT current_timestamp(),
  `check_frequency_minutes` int(11) DEFAULT 5,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_check_type` (`check_type`),
  KEY `idx_status` (`status`),
  KEY `idx_last_checked` (`last_checked`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_health_checks`
--

LOCK TABLES `system_health_checks` WRITE;
/*!40000 ALTER TABLE `system_health_checks` DISABLE KEYS */;
INSERT INTO `system_health_checks` VALUES (1,'database','Database Connectivity','healthy',NULL,NULL,'2026-07-05 03:25:26',1,1),(2,'database','Database Replication Lag','healthy',NULL,NULL,'2026-07-05 03:25:26',5,1),(3,'api','API Response Time','healthy',NULL,NULL,'2026-07-05 03:25:26',1,1),(4,'api','API Error Rate','healthy',NULL,NULL,'2026-07-05 03:25:26',5,1),(5,'storage','Disk Space','healthy',NULL,NULL,'2026-07-05 03:25:26',5,1),(6,'storage','Backup Integrity','healthy',NULL,NULL,'2026-07-05 03:25:26',60,1),(7,'security','SSL Certificate','healthy',NULL,NULL,'2026-07-05 03:25:26',60,1),(8,'security','Failed Login Attempts','healthy',NULL,NULL,'2026-07-05 03:25:26',5,1),(9,'performance','Memory Usage','healthy',NULL,NULL,'2026-07-05 03:25:26',1,1),(10,'performance','CPU Usage','healthy',NULL,NULL,'2026-07-05 03:25:26',1,1),(11,'external','Payment Gateway','healthy',NULL,NULL,'2026-07-05 03:25:26',5,1),(12,'external','Delivery API','healthy',NULL,NULL,'2026-07-05 03:25:26',5,1);
/*!40000 ALTER TABLE `system_health_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_incidents`
--

DROP TABLE IF EXISTS `system_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_incidents` (
  `incident_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `incident_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `severity` varchar(20) DEFAULT 'MEDIUM',
  `duration_minutes` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `incident_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`incident_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`incident_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_date` (`incident_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_incidents`
--

LOCK TABLES `system_incidents` WRITE;
/*!40000 ALTER TABLE `system_incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `table_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `table_number` varchar(20) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT 4,
  `area` varchar(50) DEFAULT NULL,
  `status` enum('AVAILABLE','OCCUPIED','RESERVED','CLEANING') DEFAULT 'AVAILABLE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `idx_tables_branch_number` (`branch_id`,`table_number`),
  KEY `idx_tables_tenant_id` (`tenant_id`),
  KEY `idx_tables_branch_id` (`branch_id`),
  KEY `idx_tables_status` (`status`),
  CONSTRAINT `tables_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tables_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temperature_logs`
--

DROP TABLE IF EXISTS `temperature_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temperature_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `storage_area` varchar(100) NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `logged_by` bigint(20) DEFAULT NULL,
  `log_date` date NOT NULL,
  `log_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_date` (`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temperature_logs`
--

LOCK TABLES `temperature_logs` WRITE;
/*!40000 ALTER TABLE `temperature_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `temperature_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_configurations`
--

DROP TABLE IF EXISTS `tenant_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `business_type` enum('home_based','small_restaurant','regional_chain','national_corporation','international_corporation') DEFAULT 'small_restaurant',
  `physical_presence` enum('no_building','home_kitchen','food_truck','stall','cafe','restaurant','hotel','international_facility') DEFAULT 'restaurant',
  `cuisine_type` enum('traditional','international','fusion') DEFAULT 'traditional',
  `halal_type` enum('halal_only','non_halal','mixed') DEFAULT 'halal_only',
  `target_market` enum('mass_market','niche','premium','luxury') DEFAULT 'mass_market',
  `menu_complexity` enum('single_item','limited','moderate','extensive') DEFAULT 'moderate',
  `product_mix` enum('food_only','beverage_only','food_beverage','food_non_food') DEFAULT 'food_beverage',
  `enabled_features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`enabled_features`)),
  `pricing_tier` varchar(50) DEFAULT 'starter',
  `onboarding_completed` tinyint(1) DEFAULT 0,
  `onboarding_step` int(11) DEFAULT 0,
  `configuration_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_business_type` (`business_type`),
  KEY `idx_pricing_tier` (`pricing_tier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_configurations`
--

LOCK TABLES `tenant_configurations` WRITE;
/*!40000 ALTER TABLE `tenant_configurations` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_configurations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_feature_modules`
--

DROP TABLE IF EXISTS `tenant_feature_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_feature_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `enabled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_module` (`tenant_id`,`module_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_feature_modules`
--

LOCK TABLES `tenant_feature_modules` WRITE;
/*!40000 ALTER TABLE `tenant_feature_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_feature_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `tenant_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_code` varchar(50) NOT NULL,
  `tenant_name` varchar(150) NOT NULL,
  `business_type` varchar(50) DEFAULT 'RESTAURANT',
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`tenant_id`),
  UNIQUE KEY `tenant_code` (`tenant_code`),
  KEY `idx_tenants_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES (1,'DEFAULT','Default Tenant','RESTAURANT','ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL);
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translations` (
  `translation_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `translation_key` varchar(255) NOT NULL,
  `translation_value` text NOT NULL,
  `context` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`translation_id`),
  UNIQUE KEY `uk_language_key` (`language_id`,`translation_key`),
  KEY `idx_key` (`translation_key`),
  KEY `idx_context` (`context`),
  CONSTRAINT `translations_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `languages` (`language_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transportation_logs`
--

DROP TABLE IF EXISTS `transportation_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transportation_logs` (
  `transport_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `transport_type` varchar(50) NOT NULL,
  `distance_km` decimal(10,2) NOT NULL,
  `vehicle_id` varchar(100) DEFAULT NULL,
  `transport_date` date NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transport_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_type` (`transport_type`),
  KEY `idx_date` (`transport_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transportation_logs`
--

LOCK TABLES `transportation_logs` WRITE;
/*!40000 ALTER TABLE `transportation_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `transportation_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_ad_preferences`
--

DROP TABLE IF EXISTS `user_ad_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_ad_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ad_personalization_enabled` tinyint(1) DEFAULT 1,
  `ad_categories_opted_in` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ad_categories_opted_in`)),
  `ad_categories_opted_out` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ad_categories_opted_out`)),
  `data_sharing_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_ad_preferences`
--

LOCK TABLES `user_ad_preferences` WRITE;
/*!40000 ALTER TABLE `user_ad_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_ad_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_language_preferences`
--

DROP TABLE IF EXISTS `user_language_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_language_preferences` (
  `preference_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `language_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`preference_id`),
  UNIQUE KEY `uk_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_language_preferences`
--

LOCK TABLES `user_language_preferences` WRITE;
/*!40000 ALTER TABLE `user_language_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_language_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_role_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_role_id`),
  UNIQUE KEY `idx_user_roles_user_role` (`user_id`,`role_id`),
  KEY `idx_user_roles_user_id` (`user_id`),
  KEY `idx_user_roles_role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (2,2,2,'2026-07-02 07:41:04');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE','SUSPENDED') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `idx_users_tenant_username` (`tenant_id`,`username`),
  UNIQUE KEY `idx_users_tenant_email` (`tenant_id`,`email`),
  KEY `idx_users_tenant_id` (`tenant_id`),
  KEY `idx_users_branch_id` (`branch_id`),
  KEY `idx_users_status` (`status`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,1,2,'admin','admin@restaurant.com','$2y$10$kQgQqm06RpPVrBqACkLNneJkQVRdb1SOMgKz.1pnwnFHi/6yErbHm','System Administrator',NULL,'ACTIVE','2026-07-02 07:41:03','2026-07-02 07:41:03',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `viral_campaigns`
--

DROP TABLE IF EXISTS `viral_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `viral_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(100) NOT NULL,
  `campaign_type` enum('social_share','challenge','contest','giveaway') NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `target_metric` varchar(50) NOT NULL,
  `target_value` decimal(15,2) DEFAULT NULL,
  `current_value` decimal(15,2) DEFAULT 0.00,
  `status` enum('planned','active','completed','cancelled') DEFAULT 'planned',
  `budget` decimal(15,2) DEFAULT NULL,
  `spend` decimal(15,2) DEFAULT NULL,
  `roi` decimal(5,2) DEFAULT NULL,
  `viral_coefficient` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_campaign_type` (`campaign_type`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `viral_campaigns`
--

LOCK TABLES `viral_campaigns` WRITE;
/*!40000 ALTER TABLE `viral_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `viral_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `waitlist`
--

DROP TABLE IF EXISTS `waitlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `waitlist` (
  `waitlist_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `party_size` int(11) NOT NULL,
  `estimated_wait_minutes` int(11) DEFAULT NULL,
  `actual_wait_minutes` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'WAITING',
  `phone_number` varchar(20) DEFAULT NULL,
  `notified_at` timestamp NULL DEFAULT NULL,
  `seated_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`waitlist_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `waitlist`
--

LOCK TABLES `waitlist` WRITE;
/*!40000 ALTER TABLE `waitlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `waitlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhook_deliveries`
--

DROP TABLE IF EXISTS `webhook_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_deliveries` (
  `delivery_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `webhook_id` bigint(20) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `response_status` int(11) DEFAULT NULL,
  `response_body` text DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`delivery_id`),
  KEY `idx_webhook` (`webhook_id`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhook_deliveries`
--

LOCK TABLES `webhook_deliveries` WRITE;
/*!40000 ALTER TABLE `webhook_deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhook_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhooks` (
  `webhook_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `target_url` varchar(500) NOT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `retry_count` int(11) DEFAULT 0,
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`webhook_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_event` (`event_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhooks`
--

LOCK TABLES `webhooks` WRITE;
/*!40000 ALTER TABLE `webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhooks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-08 19:08:33
