<?php

/**
 * Migration 052: Create Tier 3 Feature tables
 *
 * Covers:
 * - Kiosk Self-Ordering enhancements
 * - AI Sales Prediction
 * - Dynamic Pricing (beach club/live music)
 * - Membership Management
 * - QR Ticket Scanning
 * - Real-Time Occupancy Tracking
 * - Visual Room Calendar (Karaoke)
 * - Overtime Billing (Karaoke)
 * - Multi-Channel Booking Sync
 * - Holds vs Confirms Calendar (Live Music)
 * - Comp/Guest List Management
 * - Order Throttling
 * - Auto Purchase Order
 * - Daily Production Planning (Bakery)
 * - Service Speed Metrics (Fast Food)
 */

return [
    'up' => function($pdo) {
        // ==================== AI SALES PREDICTION ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ai_sales_predictions (
                prediction_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                prediction_date DATE NOT NULL,
                predicted_hour TINYINT,
                predicted_revenue DECIMAL(18,2),
                predicted_orders INT,
                confidence_score DECIMAL(5,2),
                weather_factor VARCHAR(50),
                event_factor VARCHAR(255),
                holiday_factor VARCHAR(100),
                model_version VARCHAR(20),
                actual_revenue DECIMAL(18,2) NULL,
                actual_orders INT NULL,
                accuracy_pct DECIMAL(5,2) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (prediction_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== DYNAMIC PRICING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS dynamic_pricing_rules (
                rule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                rule_name VARCHAR(255) NOT NULL,
                product_id BIGINT NULL,
                category_id BIGINT NULL,
                trigger_type ENUM('OCCUPANCY','TIME_OF_DAY','DAY_OF_WEEK','WEATHER','DEMAND','SEASON','EVENT') NOT NULL,
                trigger_condition JSON,
                price_modifier_type ENUM('PERCENTAGE','FLAT','MULTIPLIER') NOT NULL,
                price_modifier_value DECIMAL(10,2) NOT NULL,
                min_price DECIMAL(18,2),
                max_price DECIMAL(18,2),
                priority INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                valid_from DATE,
                valid_to DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_product (product_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS dynamic_price_history (
                history_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                original_price DECIMAL(18,2) NOT NULL,
                adjusted_price DECIMAL(18,2) NOT NULL,
                rule_id BIGINT,
                adjustment_reason VARCHAR(255),
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_product (product_id),
                INDEX idx_applied (applied_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== MEMBERSHIP MANAGEMENT ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS memberships (
                membership_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                member_name VARCHAR(255) NOT NULL,
                member_email VARCHAR(255),
                member_phone VARCHAR(50),
                tier ENUM('BRONZE','SILVER','GOLD','PLATINUM','VIP') DEFAULT 'BRONZE',
                points_balance INT NOT NULL DEFAULT 0,
                total_spent DECIMAL(18,2) NOT NULL DEFAULT 0,
                join_date DATE NOT NULL,
                expiry_date DATE,
                status ENUM('ACTIVE','EXPIRED','SUSPENDED','CANCELLED') DEFAULT 'ACTIVE',
                family_account TINYINT(1) DEFAULT 0,
                linked_members JSON,
                guest_passes_remaining INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_tier (tier),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS membership_transactions (
                txn_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                membership_id BIGINT NOT NULL,
                transaction_type ENUM('EARN','REDEEM','BONUS','ADJUSTMENT','EXPIRE') NOT NULL,
                points INT NOT NULL,
                order_id BIGINT,
                description VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_membership (membership_id),
                INDEX idx_type (transaction_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== QR TICKET SCANNING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS qr_ticket_scans (
                scan_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                event_id BIGINT,
                ticket_id BIGINT,
                qr_code VARCHAR(500) NOT NULL,
                scan_result ENUM('VALID','INVALID','DUPLICATE','EXPIRED','NOT_FOUND') NOT NULL,
                scanned_by BIGINT,
                scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                device_id VARCHAR(100),
                INDEX idx_tenant (tenant_id),
                INDEX idx_event (event_id),
                INDEX idx_result (scan_result),
                INDEX idx_scanned_at (scanned_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== REAL-TIME OCCUPANCY ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS occupancy_tracking (
                occupancy_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                tracking_date DATE NOT NULL,
                current_occupancy INT NOT NULL DEFAULT 0,
                max_capacity INT NOT NULL,
                entry_count INT NOT NULL DEFAULT 0,
                exit_count INT NOT NULL DEFAULT 0,
                status ENUM('OPEN','AT_CAPACITY','WAITLIST','CLOSED') DEFAULT 'OPEN',
                last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (tracking_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS occupancy_events (
                event_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                occupancy_id BIGINT NOT NULL,
                event_type ENUM('ENTRY','EXIT') NOT NULL,
                person_count INT NOT NULL DEFAULT 1,
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_occupancy (occupancy_id),
                INDEX idx_type (event_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== KARAOKE ROOM CALENDAR ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS karaoke_room_calendar (
                calendar_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                room_id BIGINT NOT NULL,
                reservation_id BIGINT,
                start_time DATETIME NOT NULL,
                end_time DATETIME NOT NULL,
                status ENUM('AVAILABLE','HELD','BOOKED','MAINTENANCE','BLOCKED') DEFAULT 'AVAILABLE',
                customer_name VARCHAR(255),
                notes VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_room (room_id),
                INDEX idx_start (start_time),
                INDEX idx_end (end_time),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== KARAOKE OVERTIME BILLING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS karaoke_overtime_charges (
                overtime_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                room_id BIGINT NOT NULL,
                reservation_id BIGINT,
                booked_end_time DATETIME NOT NULL,
                actual_end_time DATETIME,
                overtime_minutes INT NOT NULL DEFAULT 0,
                overtime_rate_per_hour DECIMAL(18,2) NOT NULL DEFAULT 0,
                overtime_charge DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('PENDING','INVOICED','PAID','WAIVED') DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_room (room_id),
                INDEX idx_reservation (reservation_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== MULTI-CHANNEL BOOKING SYNC ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS booking_channel_sync (
                sync_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                channel_name VARCHAR(100) NOT NULL,
                channel_type ENUM('WEBSITE','OTA','AGENT','WALK_IN','PHONE','APP') NOT NULL,
                external_booking_id VARCHAR(255),
                internal_booking_id BIGINT,
                sync_status ENUM('PENDING','SYNCED','CONFLICT','FAILED') DEFAULT 'PENDING',
                synced_at TIMESTAMP NULL,
                error_message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_channel (channel_name),
                INDEX idx_status (sync_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== HOLDS vs CONFIRMS CALENDAR ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS event_holds_calendar (
                hold_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                event_date DATE NOT NULL,
                artist_name VARCHAR(255),
                hold_type ENUM('FIRST_HOLD','SECOND_HOLD','SOFT_HOLD','CONFIRMED','RELEASED') DEFAULT 'FIRST_HOLD',
                priority_rank INT DEFAULT 1,
                promoter_name VARCHAR(255),
                hold_expires_at DATETIME,
                released_at TIMESTAMP NULL,
                rolled_to_date DATE,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_date (event_date),
                INDEX idx_hold_type (hold_type),
                INDEX idx_rank (priority_rank)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== COMP / GUEST LIST ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS comp_guest_lists (
                comp_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                event_id BIGINT NOT NULL,
                list_type ENUM('COMP','GUEST','PRESS','VIP','STAFF') DEFAULT 'GUEST',
                guest_name VARCHAR(255) NOT NULL,
                guest_phone VARCHAR(50),
                party_size INT NOT NULL DEFAULT 1,
                comp_type ENUM('FULL','DISCOUNTED','ENTRY_ONLY','FNB_VOUCHER') DEFAULT 'FULL',
                comp_value DECIMAL(18,2) NOT NULL DEFAULT 0,
                check_in_status ENUM('EXPECTED','CHECKED_IN','NO_SHOW') DEFAULT 'EXPECTED',
                checked_in_at TIMESTAMP NULL,
                added_by BIGINT,
                notes VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_event (event_id),
                INDEX idx_list_type (list_type),
                INDEX idx_status (check_in_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== ORDER THROTTLING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_throttling_config (
                config_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                channel ENUM('ONLINE','KIOSK','MOBILE','ALL') DEFAULT 'ALL',
                max_orders_per_slot INT NOT NULL DEFAULT 10,
                slot_duration_minutes INT NOT NULL DEFAULT 15,
                current_orders_in_slot INT NOT NULL DEFAULT 0,
                current_slot_start DATETIME,
                is_active TINYINT(1) DEFAULT 1,
                auto_pause_threshold INT DEFAULT 20,
                is_paused TINYINT(1) DEFAULT 0,
                estimated_wait_minutes INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_channel (channel),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== AUTO PURCHASE ORDER ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS auto_po_rules (
                rule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                inventory_id BIGINT NOT NULL,
                reorder_point DECIMAL(18,2) NOT NULL,
                reorder_quantity DECIMAL(18,2) NOT NULL,
                preferred_supplier_id BIGINT,
                fallback_supplier_id BIGINT,
                auto_generate TINYINT(1) DEFAULT 0,
                requires_approval TINYINT(1) DEFAULT 1,
                last_po_generated_at TIMESTAMP NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_inventory (inventory_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== DAILY PRODUCTION PLANNING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS daily_production_plans (
                plan_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                plan_date DATE NOT NULL,
                product_id BIGINT NOT NULL,
                product_name VARCHAR(255),
                planned_quantity DECIMAL(18,2) NOT NULL,
                produced_quantity DECIMAL(18,2) NOT NULL DEFAULT 0,
                sold_quantity DECIMAL(18,2) NOT NULL DEFAULT 0,
                wasted_quantity DECIMAL(18,2) NOT NULL DEFAULT 0,
                remaining_quantity DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('PLANNED','IN_PRODUCTION','COMPLETED','CANCELLED') DEFAULT 'PLANNED',
                production_start TIMESTAMP NULL,
                production_end TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_date (plan_date),
                INDEX idx_product (product_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== SERVICE SPEED METRICS ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_speed_metrics (
                metric_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                order_id BIGINT,
                metric_date DATE NOT NULL,
                metric_hour TINYINT,
                order_received_at DATETIME NOT NULL,
                order_started_at DATETIME,
                order_ready_at DATETIME,
                order_served_at DATETIME,
                total_prep_seconds INT,
                total_service_seconds INT,
                order_type ENUM('DINE_IN','TAKEAWAY','DRIVE_THRU','DELIVERY','KIOSK') DEFAULT 'DINE_IN',
                items_count INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_date (metric_date),
                INDEX idx_hour (metric_hour),
                INDEX idx_type (order_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS service_speed_metrics");
        $pdo->exec("DROP TABLE IF EXISTS daily_production_plans");
        $pdo->exec("DROP TABLE IF EXISTS auto_po_rules");
        $pdo->exec("DROP TABLE IF EXISTS order_throttling_config");
        $pdo->exec("DROP TABLE IF EXISTS comp_guest_lists");
        $pdo->exec("DROP TABLE IF EXISTS event_holds_calendar");
        $pdo->exec("DROP TABLE IF EXISTS booking_channel_sync");
        $pdo->exec("DROP TABLE IF EXISTS karaoke_overtime_charges");
        $pdo->exec("DROP TABLE IF EXISTS karaoke_room_calendar");
        $pdo->exec("DROP TABLE IF EXISTS occupancy_events");
        $pdo->exec("DROP TABLE IF EXISTS occupancy_tracking");
        $pdo->exec("DROP TABLE IF EXISTS qr_ticket_scans");
        $pdo->exec("DROP TABLE IF EXISTS membership_transactions");
        $pdo->exec("DROP TABLE IF EXISTS memberships");
        $pdo->exec("DROP TABLE IF EXISTS dynamic_price_history");
        $pdo->exec("DROP TABLE IF EXISTS dynamic_pricing_rules");
        $pdo->exec("DROP TABLE IF EXISTS ai_sales_predictions");
    }
];
