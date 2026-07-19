<?php

/**
 * Migration 045: Create Beverage Variance Reporting tables
 *
 * Tables:
 * - bar_counts (shift-by-shift inventory counts)
 * - variance_reports (consolidated variance report per period)
 * - variance_items (per-item variance between POS recorded vs actual)
 * - keg_tracking (keg inventory by tap handle)
 */

return [
    'up' => function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bar_counts (
                count_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                count_type ENUM('OPENING','CLOSING','MIDSHIFT') NOT NULL,
                count_date DATE NOT NULL,
                shift_name VARCHAR(50),
                zone VARCHAR(50) DEFAULT 'MAIN_BAR',
                counted_by BIGINT NOT NULL,
                verified_by BIGINT NULL,
                status ENUM('DRAFT','SUBMITTED','VERIFIED','LOCKED') DEFAULT 'DRAFT',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (count_date),
                INDEX idx_type (count_type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bar_count_items (
                item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                count_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                product_name VARCHAR(255),
                unit VARCHAR(20) DEFAULT 'bottle',
                opening_bottles INT NOT NULL DEFAULT 0,
                opening_partial DECIMAL(5,2) NOT NULL DEFAULT 0,
                received_bottles INT NOT NULL DEFAULT 0,
                sold_pos DECIMAL(10,2) NOT NULL DEFAULT 0,
                expected_bottles DECIMAL(10,2) NOT NULL DEFAULT 0,
                counted_bottles DECIMAL(10,2) NOT NULL DEFAULT 0,
                counted_partial DECIMAL(5,2) NOT NULL DEFAULT 0,
                variance_bottles DECIMAL(10,2) NOT NULL DEFAULT 0,
                variance_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                variance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
                notes VARCHAR(500),
                INDEX idx_count (count_id),
                INDEX idx_product (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS variance_reports (
                report_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                report_date DATE NOT NULL,
                period_start DATE NOT NULL,
                period_end DATE NOT NULL,
                total_expected_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_actual_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_variance_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_variance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
                pour_cost_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
                items_with_variance INT NOT NULL DEFAULT 0,
                status ENUM('DRAFT','FINALIZED','REVIEWED') DEFAULT 'DRAFT',
                generated_by BIGINT,
                reviewed_by BIGINT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (report_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS keg_tracking (
                keg_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                keg_number VARCHAR(50),
                tap_handle VARCHAR(50),
                size_liters DECIMAL(10,2) NOT NULL DEFAULT 50.00,
                received_date DATE NOT NULL,
                tapped_date DATE,
                emptied_date DATE,
                full_weight_kg DECIMAL(10,2),
                empty_weight_kg DECIMAL(10,2),
                current_weight_kg DECIMAL(10,2),
                theoretical_remaining_liters DECIMAL(10,2),
                pos_pours_liters DECIMAL(10,2) NOT NULL DEFAULT 0,
                actual_pours_liters DECIMAL(10,2) NOT NULL DEFAULT 0,
                variance_liters DECIMAL(10,2) NOT NULL DEFAULT 0,
                variance_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('INVENTORY','TAPPED','EMPTY','RETURNED') DEFAULT 'INVENTORY',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_product (product_id),
                INDEX idx_status (status),
                INDEX idx_tap (tap_handle)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS keg_tracking");
        $pdo->exec("DROP TABLE IF EXISTS variance_reports");
        $pdo->exec("DROP TABLE IF EXISTS bar_count_items");
        $pdo->exec("DROP TABLE IF EXISTS bar_counts");
    }
];
