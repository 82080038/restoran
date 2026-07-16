-- Migration: Add Floors, Zones, and Kitchen Stations for Multi-Floor Multi-Zone Operations
-- Date: 2026-07-16
-- Description: Support complex F&B operations with multiple floors, dining zones, and kitchen stations

USE ebp_restaurant_db;

-- ========================================================
-- FLOORS TABLE
-- ========================================================
CREATE TABLE IF NOT EXISTS `floors` (
  `floor_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `floor_code` varchar(50) NOT NULL,
  `floor_name` varchar(100) NOT NULL,
  `floor_level` int(11) NOT NULL DEFAULT 0,
  `floor_type` varchar(50) DEFAULT 'DINING',
  `description` text,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`floor_id`),
  UNIQUE KEY `unique_floor_code` (`tenant_id`, `branch_id`, `floor_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_level` (`floor_level`),
  CONSTRAINT `floors_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `floors_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- ZONES TABLE
-- ========================================================
CREATE TABLE IF NOT EXISTS `zones` (
  `zone_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `floor_id` bigint(20) unsigned NOT NULL,
  `zone_code` varchar(50) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `zone_type` varchar(50) DEFAULT 'DINING',
  `service_type` varchar(50) DEFAULT 'TABLE_SERVICE',
  `description` text,
  `capacity` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`zone_id`),
  UNIQUE KEY `unique_zone_code` (`tenant_id`, `branch_id`, `floor_id`, `zone_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_floor` (`floor_id`),
  KEY `idx_type` (`zone_type`),
  CONSTRAINT `zones_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `zones_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `zones_ibfk_3` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`floor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- UPDATE EXISTING KITCHEN_STATIONS TABLE
-- ========================================================
-- Columns already added from previous migration attempt
-- ALTER TABLE `kitchen_stations`
-- ADD COLUMN `floor_id` bigint(20) unsigned DEFAULT NULL AFTER `branch_id`,
-- ADD COLUMN `kitchen_code` varchar(50) DEFAULT NULL AFTER `station_name`,
-- ADD COLUMN `kitchen_category` varchar(50) DEFAULT 'HOT_KITCHEN' AFTER `station_type`,
-- ADD COLUMN `capacity` int(11) DEFAULT 0 AFTER `description`,
-- ADD COLUMN `is_central` tinyint(1) DEFAULT 0 AFTER `capacity`,
-- ADD KEY `idx_floor_id` (`floor_id`),
-- ADD KEY `idx_kitchen_code` (`kitchen_code`),
-- ADD KEY `idx_kitchen_category` (`kitchen_category`),
-- ADD CONSTRAINT `kitchen_stations_ibfk_3` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`floor_id`) ON DELETE SET NULL;

-- ========================================================
-- UPDATE TABLES TABLE TO SUPPORT ZONE ASSIGNMENT
-- ========================================================
-- Columns already added from previous migration attempt
-- ALTER TABLE `tables`
-- ADD COLUMN `floor_id` bigint(20) unsigned DEFAULT NULL AFTER `branch_id`,
-- ADD COLUMN `zone_id` bigint(20) unsigned DEFAULT NULL AFTER `floor_id`,
-- ADD KEY `idx_floor_id` (`floor_id`),
-- ADD KEY `idx_zone_id` (`zone_id`),
-- ADD CONSTRAINT `tables_ibfk_3` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`floor_id`) ON DELETE SET NULL,
-- ADD CONSTRAINT `tables_ibfk_4` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE SET NULL;

-- ========================================================
-- STAFF_ZONE_ASSIGNMENT TABLE
-- ========================================================
CREATE TABLE IF NOT EXISTS `staff_zone_assignment` (
  `assignment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `floor_id` bigint(20) unsigned DEFAULT NULL,
  `zone_id` bigint(20) unsigned DEFAULT NULL,
  `kitchen_id` bigint(20) unsigned DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `assignment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`assignment_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_floor` (`floor_id`),
  KEY `idx_zone` (`zone_id`),
  KEY `idx_kitchen` (`kitchen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- ORDER_ROUTING TABLE
-- ========================================================
CREATE TABLE IF NOT EXISTS `order_routing` (
  `routing_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned DEFAULT NULL,
  `source_zone_id` bigint(20) unsigned NOT NULL,
  `target_kitchen_id` bigint(20) unsigned NOT NULL,
  `routing_status` varchar(50) DEFAULT 'PENDING',
  `priority` int(11) DEFAULT 0,
  `estimated_time` int(11) DEFAULT NULL,
  `actual_time` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`routing_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_order_item` (`order_item_id`),
  KEY `idx_source_zone` (`source_zone_id`),
  KEY `idx_target_kitchen` (`target_kitchen_id`),
  KEY `idx_status` (`routing_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- CROSS_ZONE_TABS TABLE
-- ========================================================
CREATE TABLE IF NOT EXISTS `cross_zone_tabs` (
  `tab_transfer_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tab_id` bigint(20) unsigned NOT NULL,
  `from_zone_id` bigint(20) unsigned NOT NULL,
  `to_zone_id` bigint(20) unsigned NOT NULL,
  `from_table_id` bigint(20) unsigned DEFAULT NULL,
  `to_table_id` bigint(20) unsigned DEFAULT NULL,
  `transfer_reason` varchar(255) DEFAULT NULL,
  `transferred_by` bigint(20) unsigned DEFAULT NULL,
  `transfer_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tab_transfer_id`),
  KEY `idx_tab` (`tab_id`),
  KEY `idx_from_zone` (`from_zone_id`),
  KEY `idx_to_zone` (`to_zone_id`),
  KEY `idx_from_table` (`from_table_id`),
  KEY `idx_to_table` (`to_table_id`),
  KEY `idx_transferred_by` (`transferred_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- SUCCESS MESSAGE
-- ========================================================
SELECT 'Migration completed successfully: Floors, Zones, Kitchen Stations tables added' AS message;
