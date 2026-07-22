-- Migration: Offline Mode Tables
-- Creates tables for offline sync functionality: device_registrations, sync_queue,
-- offline_transactions, offline_conflicts, offline_data_snapshots, offline_settings

-- Device Registrations
CREATE TABLE IF NOT EXISTS `device_registrations` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `restaurant_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned DEFAULT NULL,
    `device_id` varchar(100) NOT NULL,
    `device_name` varchar(255) DEFAULT NULL,
    `device_type` enum('TABLET','PHONE','POS_TERMINAL','KIOSK','DESKTOP') DEFAULT 'TABLET',
    `device_os` varchar(50) DEFAULT NULL,
    `device_os_version` varchar(50) DEFAULT NULL,
    `app_version` varchar(20) DEFAULT NULL,
    `storage_capacity_mb` int(11) DEFAULT NULL,
    `available_storage_mb` int(11) DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `last_seen_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_device_reg_restaurant_device` (`restaurant_id`, `device_id`),
    KEY `idx_device_reg_user_id` (`user_id`),
    KEY `idx_device_reg_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sync Queue
CREATE TABLE IF NOT EXISTS `sync_queue` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `restaurant_id` bigint(20) unsigned NOT NULL,
    `device_id` varchar(100) DEFAULT NULL,
    `queue_type` enum('ORDER','PAYMENT','INVENTORY','RESERVATION','CUSTOMER','MENU_UPDATE') NOT NULL,
    `priority` int(11) DEFAULT 5,
    `payload` json NOT NULL,
    `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
    `processing_attempts` int(11) DEFAULT 0,
    `max_attempts` int(11) DEFAULT 3,
    `started_at` timestamp NULL DEFAULT NULL,
    `completed_at` timestamp NULL DEFAULT NULL,
    `error_message` text DEFAULT NULL,
    `error_details` json DEFAULT NULL,
    `depends_on_id` bigint(20) unsigned DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_sync_queue_restaurant_id` (`restaurant_id`),
    KEY `idx_sync_queue_device_id` (`device_id`),
    KEY `idx_sync_queue_status` (`status`),
    KEY `idx_sync_queue_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline Transactions
CREATE TABLE IF NOT EXISTS `offline_transactions` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `restaurant_id` bigint(20) unsigned NOT NULL,
    `device_id` varchar(100) NOT NULL,
    `transaction_type` enum('order','payment','inventory','reservation','customer') NOT NULL,
    `transaction_data` json NOT NULL,
    `status` enum('pending','synced','conflict','failed') DEFAULT 'pending',
    `synced_at` timestamp NULL DEFAULT NULL,
    `conflict_resolution` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_offline_txn_restaurant_id` (`restaurant_id`),
    KEY `idx_offline_txn_device_id` (`device_id`),
    KEY `idx_offline_txn_status` (`status`),
    KEY `idx_offline_txn_type` (`transaction_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline Conflicts
CREATE TABLE IF NOT EXISTS `offline_conflicts` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `restaurant_id` bigint(20) unsigned NOT NULL,
    `device_id` varchar(100) NOT NULL,
    `transaction_id` bigint(20) unsigned NOT NULL,
    `entity_type` varchar(50) NOT NULL,
    `entity_id` bigint(20) unsigned DEFAULT NULL,
    `local_data` json NOT NULL,
    `server_data` json DEFAULT NULL,
    `conflict_type` enum('version_mismatch','deleted_on_server','modified_on_server','duplicate') NOT NULL,
    `resolution` enum('pending','use_local','use_server','merge','discard') DEFAULT 'pending',
    `resolved_by` bigint(20) unsigned DEFAULT NULL,
    `resolved_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_offline_conflict_restaurant_id` (`restaurant_id`),
    KEY `idx_offline_conflict_device_id` (`device_id`),
    KEY `idx_offline_conflict_transaction_id` (`transaction_id`),
    KEY `idx_offline_conflict_resolution` (`resolution`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline Data Snapshots
CREATE TABLE IF NOT EXISTS `offline_data_snapshots` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `restaurant_id` bigint(20) unsigned NOT NULL,
    `device_id` varchar(100) NOT NULL,
    `data_type` enum('menu','inventory','prices','customers','reservations','settings','all') NOT NULL,
    `snapshot_data` longtext NOT NULL,
    `version` int(11) DEFAULT 1,
    `size_bytes` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_offline_snapshot_restaurant_device` (`restaurant_id`, `device_id`),
    KEY `idx_offline_snapshot_data_type` (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline Settings
CREATE TABLE IF NOT EXISTS `offline_settings` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `restaurant_id` bigint(20) unsigned NOT NULL,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_offline_settings_restaurant_key` (`restaurant_id`, `setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Offline mode tables created successfully!' AS message;
