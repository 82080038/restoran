<?php

/**
 * Migration 049: Create Per-Event Profitability tables
 *
 * Tables:
 * - event_profitability (tracks income vs costs per event/show for any business type)
 * - event_cost_items (detailed cost breakdown per event)
 */

return [
    'up' => function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS event_profitability (
                profitability_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                event_type ENUM('NIGHTCLUB_EVENT','LIVE_MUSIC_CONCERT','CATERING_EVENT','KARAOKE_SESSION','BEACH_CLUB_EVENT','GENERAL') NOT NULL,
                event_id BIGINT NOT NULL,
                event_name VARCHAR(255) NOT NULL,
                event_date DATE NOT NULL,
                ticket_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                fnb_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                bar_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                merch_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                other_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_revenue DECIMAL(18,2) NOT NULL DEFAULT 0,
                cogs DECIMAL(18,2) NOT NULL DEFAULT 0,
                labor_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                artist_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                production_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                marketing_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                overhead_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                other_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                gross_profit DECIMAL(18,2) NOT NULL DEFAULT 0,
                gross_margin_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
                net_profit DECIMAL(18,2) NOT NULL DEFAULT 0,
                net_margin_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
                attendance INT NOT NULL DEFAULT 0,
                revenue_per_head DECIMAL(18,2) NOT NULL DEFAULT 0,
                cost_per_head DECIMAL(18,2) NOT NULL DEFAULT 0,
                profit_per_head DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('DRAFT','FINALIZED','REVIEWED') DEFAULT 'DRAFT',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_event (event_id, event_type),
                INDEX idx_date (event_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS event_cost_items (
                item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                profitability_id BIGINT NOT NULL,
                cost_category ENUM('COGS','LABOR','ARTIST','PRODUCTION','MARKETING','OVERHEAD','OTHER') NOT NULL,
                description VARCHAR(255) NOT NULL,
                amount DECIMAL(18,2) NOT NULL,
                vendor VARCHAR(255),
                invoice_number VARCHAR(100),
                is_confirmed TINYINT(1) DEFAULT 0,
                INDEX idx_profitability (profitability_id),
                INDEX idx_category (cost_category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS event_cost_items");
        $pdo->exec("DROP TABLE IF EXISTS event_profitability");
    }
];
