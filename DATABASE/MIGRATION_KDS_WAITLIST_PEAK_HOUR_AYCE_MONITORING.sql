-- ========================================================
-- COMPREHENSIVE MIGRATION: KDS, Waitlist, Peak Hour, Course Firing, AYCE, Load Balancing, Monitoring
-- ========================================================
USE ebp_restaurant_db;
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================================
-- KITCHEN DISPLAY SYSTEM (KDS) TABLES
-- ========================================================

-- KDS Screens - Screen assignments for kitchen stations
CREATE TABLE IF NOT EXISTS `kds_screens` (
  `screen_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `station_id` bigint(20) NOT NULL,
  `screen_name` varchar(100) NOT NULL,
  `screen_type` enum('PREP_STATION','EXPEDITER','OVERVIEW') DEFAULT 'PREP_STATION',
  `display_order` int(11) DEFAULT 0,
  `max_tickets_display` int(11) DEFAULT 20,
  `auto_refresh_seconds` int(11) DEFAULT 10,
  `show_completed_tickets` tinyint(1) DEFAULT 0,
  `color_scheme` varchar(50) DEFAULT 'DEFAULT',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`screen_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_station` (`station_id`),
  CONSTRAINT `kds_screens_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_screens_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_screens_ibfk_3` FOREIGN KEY (`station_id`) REFERENCES `kitchen_stations` (`station_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- KDS Routing Rules - Automatic order routing configuration
CREATE TABLE IF NOT EXISTS `kds_routing_rules` (
  `rule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `rule_name` varchar(100) NOT NULL,
  `rule_type` enum('MENU_CATEGORY','DINING_OPTION','ITEM_MODIFIER','ORDER_SOURCE','CUSTOM') DEFAULT 'MENU_CATEGORY',
  `condition_value` varchar(255) DEFAULT NULL,
  `target_station_id` bigint(20) NOT NULL,
  `priority` int(11) DEFAULT 0,
  `is_reroute` tinyint(1) DEFAULT 0,
  `also_send_to_station_id` bigint(20) DEFAULT NULL,
  `apply_to_takeout` tinyint(1) DEFAULT 0,
  `apply_to_delivery` tinyint(1) DEFAULT 0,
  `apply_to_dinein` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`rule_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_target_station` (`target_station_id`),
  KEY `idx_also_station` (`also_send_to_station_id`),
  CONSTRAINT `kds_routing_rules_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_routing_rules_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_routing_rules_ibfk_3` FOREIGN KEY (`target_station_id`) REFERENCES `kitchen_stations` (`station_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_routing_rules_ibfk_4` FOREIGN KEY (`also_send_to_station_id`) REFERENCES `kitchen_stations` (`station_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- KDS Tickets - Active kitchen display tickets
CREATE TABLE IF NOT EXISTS `kds_tickets` (
  `ticket_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `station_id` bigint(20) NOT NULL,
  `screen_id` bigint(20) unsigned NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `table_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `dining_option` enum('DINE_IN','TAKE_OUT','DELIVERY','CURBSIDE') DEFAULT 'DINE_IN',
  `ticket_status` enum('NEW','IN_PROGRESS','READY','FULFILLED','CANCELLED') DEFAULT 'NEW',
  `urgency_level` enum('NORMAL','HIGH','URGENT','OVERDUE') DEFAULT 'NORMAL',
  `estimated_prep_time` int(11) DEFAULT NULL,
  `actual_prep_time` int(11) DEFAULT NULL,
  `prep_started_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `course_number` int(11) DEFAULT 1,
  `is_rerouted` tinyint(1) DEFAULT 0,
  `also_at_stations` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ticket_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_station` (`station_id`),
  KEY `idx_screen` (`screen_id`),
  KEY `idx_status` (`ticket_status`),
  KEY `idx_urgency` (`urgency_level`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `kds_tickets_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_tickets_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_tickets_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_tickets_ibfk_4` FOREIGN KEY (`station_id`) REFERENCES `kitchen_stations` (`station_id`) ON DELETE CASCADE,
  CONSTRAINT `kds_tickets_ibfk_5` FOREIGN KEY (`screen_id`) REFERENCES `kds_screens` (`screen_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- KDS Ticket Items - Individual items on KDS tickets
CREATE TABLE IF NOT EXISTS `kds_ticket_items` (
  `ticket_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `modifiers` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `item_status` enum('PENDING','IN_PROGRESS','READY','FULFILLED','CANCELLED') DEFAULT 'PENDING',
  `prep_started_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`ticket_item_id`),
  KEY `idx_ticket` (`ticket_id`),
  KEY `idx_order_item` (`order_item_id`),
  KEY `idx_status` (`item_status`),
  CONSTRAINT `kds_ticket_items_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `kds_tickets` (`ticket_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- WAITLIST MANAGEMENT TABLES
-- ========================================================

-- Waitlist Entries - Virtual queue entries
CREATE TABLE IF NOT EXISTS `waitlist_entries` (
  `entry_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `party_size` int(11) NOT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `arrival_status` enum('ARRIVING_LATER','HERE_NOW','SEATED','CANCELLED','NO_SHOW') DEFAULT 'ARRIVING_LATER',
  `queue_position` int(11) NOT NULL,
  `estimated_wait_time` int(11) DEFAULT NULL,
  `actual_wait_time` int(11) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `estimated_seating_time` timestamp NULL DEFAULT NULL,
  `seated_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `no_show_at` timestamp NULL DEFAULT NULL,
  `table_id` bigint(20) unsigned DEFAULT NULL,
  `zone_id` bigint(20) unsigned DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `customer_notes` text DEFAULT NULL,
  `staff_notes` text DEFAULT NULL,
  `source` enum('WALK_IN','ONLINE','APP','PHONE') DEFAULT 'WALK_IN',
  `is_vip` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`entry_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_status` (`arrival_status`),
  KEY `idx_position` (`queue_position`),
  KEY `idx_joined` (`joined_at`),
  KEY `idx_phone` (`phone`),
  KEY `idx_table` (`table_id`),
  KEY `idx_zone` (`zone_id`),
  CONSTRAINT `waitlist_entries_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `waitlist_entries_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `waitlist_entries_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE SET NULL,
  CONSTRAINT `waitlist_entries_ibfk_4` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Waitlist Notifications - SMS/push notification logs
CREATE TABLE IF NOT EXISTS `waitlist_notifications` (
  `notification_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `notification_type` enum('POSITION_UPDATE','READY_TO_SEAT','REMINDER','CUSTOM') DEFAULT 'POSITION_UPDATE',
  `message` text NOT NULL,
  `sent_via` enum('SMS','PUSH','EMAIL','WHATSAPP') DEFAULT 'SMS',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `status` enum('PENDING','SENT','DELIVERED','READ','FAILED') DEFAULT 'PENDING',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `idx_entry` (`entry_id`),
  KEY `idx_status` (`status`),
  KEY `idx_sent` (`sent_at`),
  CONSTRAINT `waitlist_notifications_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `waitlist_entries` (`entry_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- PEAK HOUR MANAGEMENT TABLES
-- ========================================================

-- Peak Hour Schedules - Peak hour definitions
CREATE TABLE IF NOT EXISTS `peak_hour_schedules` (
  `schedule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `day_of_week` enum('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `peak_level` enum('LOW','MEDIUM','HIGH','EXTREME') DEFAULT 'HIGH',
  `expected_volume_multiplier` decimal(3,2) DEFAULT 1.50,
  `staff_multiplier` decimal(3,2) DEFAULT 1.25,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_day` (`day_of_week`),
  KEY `idx_time` (`start_time`,`end_time`),
  CONSTRAINT `peak_hour_schedules_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `peak_hour_schedules_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table Turnover Metrics - Table turnover tracking
CREATE TABLE IF NOT EXISTS `table_turnover_metrics` (
  `metric_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `table_id` bigint(20) unsigned NOT NULL,
  `zone_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `hour` int(11) DEFAULT NULL,
  `turnover_count` int(11) DEFAULT 0,
  `avg_seat_time` int(11) DEFAULT NULL,
  `avg_turnover_time` int(11) DEFAULT NULL,
  `revenue_per_turnover` decimal(10,2) DEFAULT NULL,
  `peak_hour_flag` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`metric_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_table` (`table_id`),
  KEY `idx_zone` (`zone_id`),
  KEY `idx_date` (`date`),
  KEY `idx_hour` (`hour`),
  CONSTRAINT `table_turnover_metrics_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `table_turnover_metrics_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `table_turnover_metrics_ibfk_3` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE CASCADE,
  CONSTRAINT `table_turnover_metrics_ibfk_4` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- COURSE FIRING & SEQUENCING TABLES
-- ========================================================

-- Course Sequences - Course definitions
CREATE TABLE IF NOT EXISTS `course_sequences` (
  `course_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `course_number` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_type` enum('APPETIZER','SOUP','SALAD','MAIN','DESSERT','BEVERAGE','CUSTOM') DEFAULT 'CUSTOM',
  `auto_fire_delay_minutes` int(11) DEFAULT 0,
  `manual_fire_only` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `unique_course` (`tenant_id`, `branch_id`, `course_number`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  CONSTRAINT `course_sequences_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `course_sequences_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Order Courses - Order-course assignments
CREATE TABLE IF NOT EXISTS `order_courses` (
  `order_course_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `course_number` int(11) NOT NULL,
  `fire_status` enum('PENDING','FIRED','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  `fired_at` timestamp NULL DEFAULT NULL,
  `fired_by` bigint(20) unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_course_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_status` (`fire_status`),
  CONSTRAINT `order_courses_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course_sequences` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- AYCE (ALL YOU CAN EAT) TABLES
-- ========================================================

-- AYCE Sessions - AYCE session tracking
CREATE TABLE IF NOT EXISTS `ayce_sessions` (
  `session_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `table_id` bigint(20) unsigned NOT NULL,
  `session_type` enum('TIME_LIMITED','UNLIMITED') DEFAULT 'TIME_LIMITED',
  `duration_minutes` int(11) DEFAULT 120,
  `session_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `session_end` timestamp NULL DEFAULT NULL,
  `max_reorders` int(11) DEFAULT NULL,
  `current_reorder_count` int(11) DEFAULT 0,
  `session_status` enum('ACTIVE','PAUSED','COMPLETED','CANCELLED') DEFAULT 'ACTIVE',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`session_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_table` (`table_id`),
  KEY `idx_status` (`session_status`),
  KEY `idx_session_start` (`session_start`),
  CONSTRAINT `ayce_sessions_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `ayce_sessions_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `ayce_sessions_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `ayce_sessions_ibfk_4` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- AYCE Reorders - AYCE re-order tracking
CREATE TABLE IF NOT EXISTS `ayce_reorders` (
  `reorder_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `reorder_number` int(11) NOT NULL,
  `reorder_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `item_count` int(11) DEFAULT 0,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('PENDING','SENT_TO_KITCHEN','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  `kds_ticket_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`reorder_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_reorder_number` (`reorder_number`),
  KEY `idx_status` (`status`),
  CONSTRAINT `ayce_reorders_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `ayce_sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `ayce_reorders_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- LOAD BALANCING TABLES
-- ========================================================

-- Station Load Metrics - Load balancing data
CREATE TABLE IF NOT EXISTS `station_load_metrics` (
  `metric_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `station_id` bigint(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `active_tickets` int(11) DEFAULT 0,
  `pending_items` int(11) DEFAULT 0,
  `avg_prep_time` int(11) DEFAULT NULL,
  `capacity_utilization` decimal(5,2) DEFAULT 0.00,
  `load_level` enum('LOW','MEDIUM','HIGH','OVERLOADED') DEFAULT 'LOW',
  `staff_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`metric_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_station` (`station_id`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_load_level` (`load_level`),
  CONSTRAINT `station_load_metrics_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `station_load_metrics_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `station_load_metrics_ibfk_3` FOREIGN KEY (`station_id`) REFERENCES `kitchen_stations` (`station_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- PERFORMANCE MONITORING TABLES
-- ========================================================

-- Performance Metrics - Kitchen performance tracking
CREATE TABLE IF NOT EXISTS `performance_metrics` (
  `metric_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `station_id` bigint(20) DEFAULT NULL,
  `date` date NOT NULL,
  `hour` int(11) DEFAULT NULL,
  `total_orders` int(11) DEFAULT 0,
  `completed_orders` int(11) DEFAULT 0,
  `cancelled_orders` int(11) DEFAULT 0,
  `avg_order_time` int(11) DEFAULT NULL,
  `avg_prep_time` int(11) DEFAULT NULL,
  `error_count` int(11) DEFAULT 0,
  `error_rate` decimal(5,2) DEFAULT 0.00,
  `on_time_rate` decimal(5,2) DEFAULT 0.00,
  `bottleneck_flag` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`metric_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_station` (`station_id`),
  KEY `idx_date` (`date`),
  KEY `idx_hour` (`hour`),
  KEY `idx_bottleneck` (`bottleneck_flag`),
  CONSTRAINT `performance_metrics_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `performance_metrics_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE,
  CONSTRAINT `performance_metrics_ibfk_3` FOREIGN KEY (`station_id`) REFERENCES `kitchen_stations` (`station_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Order Timing Metrics - Order timing data
CREATE TABLE IF NOT EXISTS `order_timing_metrics` (
  `timing_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `order_placed_at` timestamp NOT NULL,
  `sent_to_kitchen_at` timestamp NULL DEFAULT NULL,
  `prep_started_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `total_prep_time` int(11) DEFAULT NULL,
  `total_service_time` int(11) DEFAULT NULL,
  `estimated_time` int(11) DEFAULT NULL,
  `time_variance` int(11) DEFAULT NULL,
  `is_on_time` tinyint(1) DEFAULT 1,
  `delay_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`timing_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_placed` (`order_placed_at`),
  KEY `idx_on_time` (`is_on_time`),
  CONSTRAINT `order_timing_metrics_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_timing_metrics_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `order_timing_metrics_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Comprehensive migration completed successfully: KDS, Waitlist, Peak Hour, Course Firing, AYCE, Load Balancing, Monitoring tables added' AS message;
