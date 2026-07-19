<?php

/**
 * Migration 047: Create Batch & Expiry Date Tracking tables
 *
 * Tables:
 * - inventory_batches (track production/purchase batches with manufacture & expiry dates)
 * - batch_status_logs (track batch status changes: fresh, near-expiry, discounted, expired, discarded)
 */

return [
    'up' => function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_batches (
                batch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                inventory_item_id BIGINT NULL,
                batch_number VARCHAR(100) NOT NULL,
                manufacture_date DATE,
                expiry_date DATE NOT NULL,
                quantity_received DECIMAL(10,2) NOT NULL,
                quantity_remaining DECIMAL(10,2) NOT NULL,
                unit VARCHAR(20),
                unit_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                original_price DECIMAL(18,2),
                discounted_price DECIMAL(18,2),
                discount_applied TINYINT(1) DEFAULT 0,
                discount_percentage DECIMAL(5,2) DEFAULT 0,
                source VARCHAR(50) DEFAULT 'PURCHASE',
                supplier_id BIGINT NULL,
                status ENUM('FRESH','NEAR_EXPIRY','DISCOUNTED','EXPIRED','DISCARDED','SOLD_OUT') DEFAULT 'FRESH',
                days_until_expiry INT DEFAULT 0,
                received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_product (product_id),
                INDEX idx_batch (batch_number),
                INDEX idx_expiry (expiry_date),
                INDEX idx_status (status),
                INDEX idx_days (days_until_expiry)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS batch_status_logs (
                log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                batch_id BIGINT NOT NULL,
                old_status VARCHAR(50),
                new_status VARCHAR(50) NOT NULL,
                changed_by BIGINT,
                reason VARCHAR(500),
                discount_percentage DECIMAL(5,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_batch (batch_id),
                INDEX idx_new_status (new_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS batch_status_logs");
        $pdo->exec("DROP TABLE IF EXISTS inventory_batches");
    }
];
