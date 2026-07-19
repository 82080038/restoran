<?php

/**
 * Migration 048: Create Settlement System tables (Live Music Venue)
 *
 * Tables:
 * - artist_deals (deal types: versus, flat guarantee, door deal, percentage)
 * - settlements (internal vs external settlement, estimated vs actual)
 * - settlement_items (line items: ticket revenue, bar revenue, merch, costs)
 * - advancing_sheets (day-of-show coordination: riders, tech specs, hospitality)
 */

return [
    'up' => function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS artist_deals (
                deal_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                concert_id BIGINT NULL,
                artist_name VARCHAR(255) NOT NULL,
                deal_type ENUM('VERSUS','FLAT_GUARANTEE','DOOR_DEAL','PERCENTAGE','PLUS_DEAL') NOT NULL,
                guarantee_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                percentage_artist DECIMAL(5,2) NOT NULL DEFAULT 0,
                percentage_venue DECIMAL(5,2) NOT NULL DEFAULT 0,
                ticket_price_range_min DECIMAL(18,2),
                ticket_price_range_max DECIMAL(18,2),
                radius_clause_km INT,
                radius_clause_days INT,
                merch_split_artist DECIMAL(5,2) DEFAULT 100,
                merch_split_venue DECIMAL(5,2) DEFAULT 0,
                bar_revenue_included TINYINT(1) DEFAULT 0,
                contract_status ENUM('PENDING','SIGNED','CANCELLED','COMPLETED') DEFAULT 'PENDING',
                contract_signed_at TIMESTAMP NULL,
                contract_pdf_path VARCHAR(500),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_concert (concert_id),
                INDEX idx_deal_type (deal_type),
                INDEX idx_status (contract_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settlements (
                settlement_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                concert_id BIGINT NULL,
                deal_id BIGINT NULL,
                settlement_type ENUM('INTERNAL','EXTERNAL') NOT NULL,
                settlement_date DATE NOT NULL,
                estimated_ticket_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                actual_ticket_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                ticket_count_sold INT NOT NULL DEFAULT 0,
                ticket_count_comp INT NOT NULL DEFAULT 0,
                bar_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                merch_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                artist_guarantee DECIMAL(18,2) NOT NULL DEFAULT 0,
                artist_payout DECIMAL(18,2) NOT NULL DEFAULT 0,
                venue_production_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                venue_profit DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('DRAFT','ESTIMATED','FINALIZED','PAID','DISPUTED') DEFAULT 'DRAFT',
                finalized_by BIGINT NULL,
                finalized_at TIMESTAMP NULL,
                pdf_path VARCHAR(500),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_concert (concert_id),
                INDEX idx_deal (deal_id),
                INDEX idx_type (settlement_type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settlement_items (
                item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                settlement_id BIGINT NOT NULL,
                item_type ENUM('TICKET_REVENUE','BAR_REVENUE','MERCH_REVENUE','ARTIST_GUARANTEE','PRODUCTION_COST','STAFF_COST','MARKETING_COST','OTHER_COST','CO_PROMOTION_SPLIT') NOT NULL,
                description VARCHAR(255),
                amount DECIMAL(18,2) NOT NULL,
                is_revenue TINYINT(1) DEFAULT 0,
                metadata JSON,
                INDEX idx_settlement (settlement_id),
                INDEX idx_type (item_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS advancing_sheets (
                sheet_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                concert_id BIGINT NOT NULL,
                deal_id BIGINT NULL,
                load_in_time TIME,
                soundcheck_time TIME,
                doors_time TIME,
                set_times JSON,
                stage_plot_path VARCHAR(500),
                input_list JSON,
                hospitality_rider JSON,
                tech_requirements JSON,
                ground_transport JSON,
                security_plan TEXT,
                contact_phone VARCHAR(50),
                contact_email VARCHAR(255),
                status ENUM('PENDING','CONFIRMED','COMPLETED') DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_concert (concert_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS advancing_sheets");
        $pdo->exec("DROP TABLE IF EXISTS settlement_items");
        $pdo->exec("DROP TABLE IF EXISTS settlements");
        $pdo->exec("DROP TABLE IF EXISTS artist_deals");
    }
];
