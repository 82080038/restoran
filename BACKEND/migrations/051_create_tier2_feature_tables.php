<?php

/**
 * Migration 051: Create Tier 2 Feature tables
 *
 * Covers:
 * - Nightclub: table deposits, bottle service inventory, promoter management
 * - Karaoke: song catalog, in-room F&B ordering
 * - Beach Club: visual seat map, weather/rain check
 * - Sports Bar: pre-authorization bar tab
 * - Cross-cutting: 86-ing, custom orders, delivery routing, lead pipeline, allergen tracking
 */

return [
    'up' => function($pdo) {
        // ==================== NIGHTCLUB ====================

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS table_deposits (
                deposit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                reservation_id BIGINT NULL,
                table_id BIGINT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(50),
                event_date DATE NOT NULL,
                deposit_amount DECIMAL(18,2) NOT NULL,
                deposit_status ENUM('PENDING','PAID','FORFEITED','REFUNDED','APPLIED') DEFAULT 'PENDING',
                payment_method VARCHAR(50),
                payment_ref VARCHAR(255),
                minimum_spend DECIMAL(18,2) NOT NULL DEFAULT 0,
                no_show_cutoff TIME,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_event_date (event_date),
                INDEX idx_status (deposit_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bottle_service_inventory (
                bottle_inv_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                bottle_name VARCHAR(255) NOT NULL,
                bottle_size VARCHAR(50) DEFAULT '750ml',
                quantity_on_hand INT NOT NULL DEFAULT 0,
                quantity_reserved INT NOT NULL DEFAULT 0,
                quantity_sold INT NOT NULL DEFAULT 0,
                unit_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                selling_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                storage_location VARCHAR(100),
                last_counted_at TIMESTAMP NULL,
                status ENUM('IN_STOCK','LOW_STOCK','OUT_OF_STOCK') DEFAULT 'IN_STOCK',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_product (product_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bottle_service_assignments (
                assignment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                event_id BIGINT NULL,
                table_id BIGINT NULL,
                bottle_inv_id BIGINT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                assigned_by BIGINT,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('ASSIGNED','SERVED','RETURNED','CONSUMED') DEFAULT 'ASSIGNED',
                notes VARCHAR(500),
                INDEX idx_tenant (tenant_id),
                INDEX idx_event (event_id),
                INDEX idx_table (table_id),
                INDEX idx_bottle (bottle_inv_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS promoters (
                promoter_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                promoter_name VARCHAR(255) NOT NULL,
                promoter_code VARCHAR(50),
                phone VARCHAR(50),
                email VARCHAR(255),
                commission_type ENUM('PER_HEAD','PERCENTAGE','FLAT','TIERED') DEFAULT 'PER_HEAD',
                commission_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
                guest_list_limit INT,
                is_active TINYINT(1) DEFAULT 1,
                total_guests_brought INT NOT NULL DEFAULT 0,
                total_commission_earned DECIMAL(18,2) NOT NULL DEFAULT 0,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_code (promoter_code),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS promoter_guest_lists (
                guest_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                promoter_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                event_id BIGINT NOT NULL,
                guest_name VARCHAR(255) NOT NULL,
                guest_phone VARCHAR(50),
                party_size INT NOT NULL DEFAULT 1,
                check_in_status ENUM('EXPECTED','CHECKED_IN','NO_SHOW') DEFAULT 'EXPECTED',
                checked_in_at TIMESTAMP NULL,
                entry_type ENUM('FREE','DISCOUNTED','FULL_PRICE') DEFAULT 'FREE',
                discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                commission_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_promoter (promoter_id),
                INDEX idx_event (event_id),
                INDEX idx_status (check_in_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== KARAOKE ====================

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS karaoke_song_catalog (
                song_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                song_code VARCHAR(50),
                title VARCHAR(500) NOT NULL,
                artist VARCHAR(255),
                genre VARCHAR(100),
                language VARCHAR(50),
                year INT,
                duration_seconds INT,
                file_path VARCHAR(500),
                lyrics_available TINYINT(1) DEFAULT 0,
                play_count INT NOT NULL DEFAULT 0,
                last_played_at TIMESTAMP NULL,
                date_added DATE,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_song_code (song_code),
                INDEX idx_title (title),
                INDEX idx_artist (artist),
                INDEX idx_genre (genre),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS karaoke_song_requests (
                request_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                room_id BIGINT NOT NULL,
                reservation_id BIGINT NULL,
                song_id BIGINT NOT NULL,
                requested_by VARCHAR(255),
                request_source ENUM('QR_APP','STAFF','KIOSK') DEFAULT 'QR_APP',
                queue_position INT NOT NULL DEFAULT 0,
                status ENUM('QUEUED','PLAYING','PLAYED','SKIPPED') DEFAULT 'QUEUED',
                requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                played_at TIMESTAMP NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_room (room_id),
                INDEX idx_song (song_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS karaoke_room_orders (
                room_order_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                room_id BIGINT NOT NULL,
                reservation_id BIGINT NULL,
                order_id BIGINT NULL,
                order_type ENUM('FNB','DRINK','SERVICE') DEFAULT 'FNB',
                items_json JSON,
                total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('PENDING','PREPARING','SERVED','CANCELLED') DEFAULT 'PENDING',
                ordered_by VARCHAR(255),
                ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                served_at TIMESTAMP NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_room (room_id),
                INDEX idx_reservation (reservation_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== BEACH CLUB ====================

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS beach_seat_map (
                seat_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                zone_name VARCHAR(100) NOT NULL,
                seat_label VARCHAR(50) NOT NULL,
                seat_type ENUM('CABANA','LOUNGER','DAY_BED','UMBRELLA','TABLE') NOT NULL,
                capacity INT NOT NULL DEFAULT 2,
                position_x INT NOT NULL DEFAULT 0,
                position_y INT NOT NULL DEFAULT 0,
                width INT DEFAULT 80,
                height INT DEFAULT 80,
                base_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                premium_multiplier DECIMAL(5,2) NOT NULL DEFAULT 1.00,
                minimum_spend DECIMAL(18,2) NOT NULL DEFAULT 0,
                is_bookable TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_zone (zone_name),
                INDEX idx_type (seat_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS weather_rain_checks (
                rain_check_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                booking_id BIGINT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(50),
                customer_email VARCHAR(255),
                original_date DATE NOT NULL,
                weather_condition VARCHAR(50),
                rescheduled_date DATE,
                rescheduled_to BIGINT NULL,
                refund_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('ISSUED','RESCHEDULED','REFUNDED','EXPIRED') DEFAULT 'ISSUED',
                issued_by BIGINT,
                issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expiry_date DATE,
                notes TEXT,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_original_date (original_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS weather_policies (
                policy_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                policy_name VARCHAR(255) NOT NULL,
                rain_threshold_mm DECIMAL(5,2) DEFAULT 5.00,
                auto_issue_rain_check TINYINT(1) DEFAULT 0,
                reschedule_window_days INT DEFAULT 30,
                refund_policy ENUM('FULL','PARTIAL','NO_REFUND','CREDIT') DEFAULT 'CREDIT',
                partial_refund_pct DECIMAL(5,2) DEFAULT 0,
                notification_template TEXT,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== SPORTS BAR ====================

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bar_tab_preauths (
                tab_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                tab_number VARCHAR(50),
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(50),
                card_last_four VARCHAR(4),
                card_brand VARCHAR(20),
                preauth_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                consumed_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                remaining_hold DECIMAL(18,2) NOT NULL DEFAULT 0,
                tip_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                final_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('OPEN','CLOSED','CAPTURED','VOIDED','EXPIRED') DEFAULT 'OPEN',
                opened_by BIGINT,
                opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                closed_at TIMESTAMP NULL,
                external_ref VARCHAR(255),
                items_json JSON,
                notes TEXT,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_tab_number (tab_number),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== CROSS-CUTTING ====================

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS item_86_status (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                is_86ed TINYINT(1) NOT NULL DEFAULT 0,
                reason VARCHAR(255),
                86ed_by BIGINT,
                86ed_at TIMESTAMP NULL,
                expected_restock_date DATE,
                restocked_by BIGINT,
                restocked_at TIMESTAMP NULL,
                auto_restock TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_product_branch (product_id, branch_id, tenant_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_86ed (is_86ed)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS custom_orders (
                custom_order_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                order_number VARCHAR(50),
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(50),
                customer_email VARCHAR(255),
                product_name VARCHAR(255) NOT NULL,
                product_description TEXT,
                specifications JSON,
                quantity INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                deposit_required DECIMAL(18,2) NOT NULL DEFAULT 0,
                deposit_paid DECIMAL(18,2) NOT NULL DEFAULT 0,
                pickup_date DATE,
                pickup_time TIME,
                delivery_address TEXT,
                delivery_fee DECIMAL(18,2) NOT NULL DEFAULT 0,
                fulfillment_type ENUM('PICKUP','DELIVERY') DEFAULT 'PICKUP',
                status ENUM('PENDING','CONFIRMED','IN_PRODUCTION','READY','COMPLETED','CANCELLED') DEFAULT 'PENDING',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_order_number (order_number),
                INDEX idx_status (status),
                INDEX idx_pickup_date (pickup_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS delivery_routes (
                route_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                route_date DATE NOT NULL,
                driver_name VARCHAR(255),
                driver_phone VARCHAR(50),
                vehicle VARCHAR(100),
                total_stops INT NOT NULL DEFAULT 0,
                total_distance_km DECIMAL(10,2) NOT NULL DEFAULT 0,
                estimated_duration_minutes INT,
                actual_start_time TIMESTAMP NULL,
                actual_end_time TIMESTAMP NULL,
                status ENUM('PLANNED','IN_PROGRESS','COMPLETED','CANCELLED') DEFAULT 'PLANNED',
                optimization_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_route_date (route_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS delivery_route_stops (
                stop_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                route_id BIGINT NOT NULL,
                order_id BIGINT NULL,
                stop_sequence INT NOT NULL DEFAULT 0,
                customer_name VARCHAR(255),
                delivery_address TEXT NOT NULL,
                contact_phone VARCHAR(50),
                items_summary TEXT,
                amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('PENDING','REACHED','DELIVERED','FAILED','SKIPPED') DEFAULT 'PENDING',
                arrived_at TIMESTAMP NULL,
                delivered_at TIMESTAMP NULL,
                proof_photo_path VARCHAR(500),
                signature_path VARCHAR(500),
                failure_reason VARCHAR(500),
                INDEX idx_route (route_id),
                INDEX idx_sequence (stop_sequence),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS catering_lead_pipeline (
                lead_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                lead_number VARCHAR(50),
                lead_source VARCHAR(100),
                client_name VARCHAR(255) NOT NULL,
                client_company VARCHAR(255),
                client_phone VARCHAR(50),
                client_email VARCHAR(255),
                event_type VARCHAR(100),
                event_date DATE,
                guest_count INT,
                estimated_value DECIMAL(18,2) NOT NULL DEFAULT 0,
                stage ENUM('INQUIRY','QUALIFIED','PROPOSAL_SENT','NEGOTIATION','BOOKED','COMPLETED','LOST') DEFAULT 'INQUIRY',
                stage_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                assigned_to BIGINT,
                probability_pct INT DEFAULT 10,
                notes TEXT,
                next_follow_up DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_stage (stage),
                INDEX idx_event_date (event_date),
                INDEX idx_assigned (assigned_to)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS allergen_tracking (
                allergen_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                allergens JSON,
                dietary_tags JSON,
                contains_gluten TINYINT(1) DEFAULT 0,
                contains_dairy TINYINT(1) DEFAULT 0,
                contains_nuts TINYINT(1) DEFAULT 0,
                contains_eggs TINYINT(1) DEFAULT 0,
                contains_soy TINYINT(1) DEFAULT 0,
                contains_shellfish TINYINT(1) DEFAULT 0,
                contains_fish TINYINT(1) DEFAULT 0,
                contains_sesame TINYINT(1) DEFAULT 0,
                is_vegetarian TINYINT(1) DEFAULT 0,
                is_vegan TINYINT(1) DEFAULT 0,
                is_halal TINYINT(1) DEFAULT 0,
                is_kosher TINYINT(1) DEFAULT 0,
                certification_body VARCHAR(255),
                certification_number VARCHAR(255),
                certification_expiry DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_product_tenant (product_id, tenant_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_product (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS allergen_tracking");
        $pdo->exec("DROP TABLE IF EXISTS catering_lead_pipeline");
        $pdo->exec("DROP TABLE IF EXISTS delivery_route_stops");
        $pdo->exec("DROP TABLE IF EXISTS delivery_routes");
        $pdo->exec("DROP TABLE IF EXISTS custom_orders");
        $pdo->exec("DROP TABLE IF EXISTS item_86_status");
        $pdo->exec("DROP TABLE IF EXISTS bar_tab_preauths");
        $pdo->exec("DROP TABLE IF EXISTS weather_policies");
        $pdo->exec("DROP TABLE IF EXISTS weather_rain_checks");
        $pdo->exec("DROP TABLE IF EXISTS beach_seat_map");
        $pdo->exec("DROP TABLE IF EXISTS karaoke_room_orders");
        $pdo->exec("DROP TABLE IF EXISTS karaoke_song_requests");
        $pdo->exec("DROP TABLE IF EXISTS karaoke_song_catalog");
        $pdo->exec("DROP TABLE IF EXISTS promoter_guest_lists");
        $pdo->exec("DROP TABLE IF EXISTS promoters");
        $pdo->exec("DROP TABLE IF EXISTS bottle_service_assignments");
        $pdo->exec("DROP TABLE IF EXISTS bottle_service_inventory");
        $pdo->exec("DROP TABLE IF EXISTS table_deposits");
    }
];
