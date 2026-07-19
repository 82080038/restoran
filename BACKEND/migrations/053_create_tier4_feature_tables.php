<?php

/**
 * Migration 053: Create Tier 4 Feature tables
 *
 * Covers:
 * - Coat Check Management (Nightclub)
 * - Karaoke Score System
 * - Equipment Tracking (Live Music/Karaoke)
 * - Radius Clause Tracking (Live Music)
 * - Social Group Booking (Beach Club)
 * - Wine Pairing & Sommelier Module (Fine Dining)
 * - Waiter Button Response Tracking (Karaoke)
 * - Entertainer/Performer Rotation (Nightclub)
 */

return [
    'up' => function($pdo) {
        // ==================== COAT CHECK ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS coat_check_items (
                coat_check_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                event_id BIGINT,
                check_number VARCHAR(50),
                customer_name VARCHAR(255),
                item_type ENUM('COAT','JACKET','BAG','UMBRELLA','HELMET','OTHER') DEFAULT 'COAT',
                item_description VARCHAR(255),
                item_count INT NOT NULL DEFAULT 1,
                fee_charged DECIMAL(18,2) NOT NULL DEFAULT 0,
                fee_paid TINYINT(1) DEFAULT 0,
                checked_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                checked_out_at TIMESTAMP NULL,
                status ENUM('CHECKED_IN','CHECKED_OUT','LOST','UNCLAIMED') DEFAULT 'CHECKED_IN',
                handled_by BIGINT,
                notes VARCHAR(500),
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_event (event_id),
                INDEX idx_check_number (check_number),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== KARAOKE SCORE ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS karaoke_scores (
                score_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                room_id BIGINT NOT NULL,
                song_id BIGINT,
                singer_name VARCHAR(255),
                score DECIMAL(5,2) NOT NULL DEFAULT 0,
                pitch_accuracy DECIMAL(5,2),
                rhythm_accuracy DECIMAL(5,2),
                volume_level DECIMAL(5,2),
                duration_seconds INT,
                applause_rating INT,
                scored_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_room (room_id),
                INDEX idx_score (score DESC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== EQUIPMENT TRACKING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS equipment_assets (
                equipment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                equipment_name VARCHAR(255) NOT NULL,
                equipment_type ENUM('SOUND','LIGHTING','VIDEO','INSTRUMENT','MIC','MISC') DEFAULT 'MISC',
                brand VARCHAR(100),
                model VARCHAR(100),
                serial_number VARCHAR(100),
                purchase_date DATE,
                purchase_cost DECIMAL(18,2),
                current_value DECIMAL(18,2),
                condition_status ENUM('EXCELLENT','GOOD','FAIR','NEEDS_REPAIR','BROKEN') DEFAULT 'GOOD',
                assigned_to VARCHAR(255),
                assigned_location VARCHAR(255),
                is_cross_hire TINYINT(1) DEFAULT 0,
                cross_hire_from VARCHAR(255),
                cross_hire_return_date DATE,
                last_maintenance_date DATE,
                next_maintenance_date DATE,
                status ENUM('IN_USE','IN_STORAGE','OUT_FOR_REPAIR','CROSSED_OUT','LOST') DEFAULT 'IN_STORAGE',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_type (equipment_type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS equipment_assignments (
                assignment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                equipment_id BIGINT NOT NULL,
                event_id BIGINT,
                room_id BIGINT,
                assigned_by BIGINT,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                returned_at TIMESTAMP NULL,
                condition_at_return VARCHAR(50),
                notes VARCHAR(500),
                INDEX idx_equipment (equipment_id),
                INDEX idx_event (event_id),
                INDEX idx_room (room_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== RADIUS CLAUSE TRACKING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS radius_clause_checks (
                check_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                deal_id BIGINT NOT NULL,
                artist_name VARCHAR(255) NOT NULL,
                clause_radius_km DECIMAL(10,2) NOT NULL,
                clause_days INT NOT NULL,
                event_date DATE NOT NULL,
                conflicting_venue VARCHAR(255),
                conflicting_venue_distance_km DECIMAL(10,2),
                conflicting_event_date DATE,
                check_result ENUM('CLEAR','VIOLATION','WARNING','REVIEW_NEEDED') DEFAULT 'CLEAR',
                checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                notes TEXT,
                INDEX idx_tenant (tenant_id),
                INDEX idx_deal (deal_id),
                INDEX idx_result (check_result)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== SOCIAL GROUP BOOKING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS social_group_bookings (
                group_booking_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                organizer_name VARCHAR(255) NOT NULL,
                organizer_phone VARCHAR(50),
                organizer_email VARCHAR(255),
                event_date DATE NOT NULL,
                event_name VARCHAR(255),
                total_party_size INT NOT NULL DEFAULT 1,
                total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                deposit_collected DECIMAL(18,2) NOT NULL DEFAULT 0,
                split_type ENUM('EVEN','BY_ITEM','CUSTOM','ORGANIZER_PAYS') DEFAULT 'EVEN',
                status ENUM('PENDING','CONFIRMED','COMPLETED','CANCELLED') DEFAULT 'PENDING',
                invite_link VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_event_date (event_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS social_group_booking_members (
                member_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                group_booking_id BIGINT NOT NULL,
                member_name VARCHAR(255) NOT NULL,
                member_phone VARCHAR(50),
                member_email VARCHAR(255),
                share_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                share_paid TINYINT(1) DEFAULT 0,
                paid_at TIMESTAMP NULL,
                rsvp_status ENUM('INVITED','ACCEPTED','DECLINED','MAYBE') DEFAULT 'INVITED',
                INDEX idx_group_booking (group_booking_id),
                INDEX idx_rsvp (rsvp_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== WINE PAIRING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wine_list (
                wine_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                wine_name VARCHAR(255) NOT NULL,
                vintage INT,
                varietal VARCHAR(100),
                region VARCHAR(100),
                country VARCHAR(50),
                wine_type ENUM('RED','WHITE','ROSE','SPARKLING','DESSERT','FORTIFIED') DEFAULT 'RED',
                bottle_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                glass_price DECIMAL(18,2),
                cost_per_bottle DECIMAL(18,2),
                inventory_bottles INT NOT NULL DEFAULT 0,
                pairings TEXT,
                tasting_notes TEXT,
                alcohol_pct DECIMAL(5,2),
                rating DECIMAL(3,1),
                is_available TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_type (wine_type),
                INDEX idx_available (is_available)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wine_pairing_suggestions (
                pairing_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                wine_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                pairing_strength ENUM('CLASSIC','EXCELLENT','GOOD','EXPERIMENTAL') DEFAULT 'GOOD',
                pairing_reason VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_wine_product (wine_id, product_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_wine (wine_id),
                INDEX idx_product (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== WAITER BUTTON TRACKING ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS waiter_button_presses (
                press_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                room_id BIGINT NOT NULL,
                pressed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                responded_at TIMESTAMP NULL,
                response_seconds INT,
                responded_by BIGINT,
                response_type ENUM('ACKNOWLEDGED','SERVED','IGNORED','AUTO_TIMEOUT') DEFAULT 'ACKNOWLEDGED',
                INDEX idx_tenant (tenant_id),
                INDEX idx_room (room_id),
                INDEX idx_pressed_at (pressed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ==================== ENTERTAINER ROTATION ====================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS entertainer_rotations (
                rotation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                event_id BIGINT,
                entertainer_name VARCHAR(255) NOT NULL,
                entertainer_type ENUM('DJ','LIVE_BAND','SOLO','MC','PERFORMER') DEFAULT 'DJ',
                set_number INT NOT NULL DEFAULT 1,
                set_start_time DATETIME,
                set_end_time DATETIME,
                set_duration_minutes INT,
                status ENUM('SCHEDULED','PLAYING','COMPLETED','CANCELLED','NO_SHOW') DEFAULT 'SCHEDULED',
                notes VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_event (event_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS entertainer_rotations");
        $pdo->exec("DROP TABLE IF EXISTS waiter_button_presses");
        $pdo->exec("DROP TABLE IF EXISTS wine_pairing_suggestions");
        $pdo->exec("DROP TABLE IF EXISTS wine_list");
        $pdo->exec("DROP TABLE IF EXISTS social_group_booking_members");
        $pdo->exec("DROP TABLE IF EXISTS social_group_bookings");
        $pdo->exec("DROP TABLE IF EXISTS radius_clause_checks");
        $pdo->exec("DROP TABLE IF EXISTS equipment_assignments");
        $pdo->exec("DROP TABLE IF EXISTS equipment_assets");
        $pdo->exec("DROP TABLE IF EXISTS karaoke_scores");
        $pdo->exec("DROP TABLE IF EXISTS coat_check_items");
    }
];
