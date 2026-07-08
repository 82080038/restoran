<?php

/**
 * Migration 025: Create Inventory Reorder Tables
 * 
 * Creates tables for automated reorder point management
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create inventory_reorder table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_reorder (
                reorder_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                inventory_id BIGINT NOT NULL,
                reorder_point DECIMAL(10,4) NOT NULL,
                max_stock DECIMAL(10,4),
                lead_time_days INT DEFAULT 3,
                safety_stock DECIMAL(10,4) DEFAULT 0,
                auto_reorder BOOLEAN DEFAULT FALSE,
                last_reorder_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_tenant_branch_inventory (tenant_id, branch_id, inventory_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_inventory (inventory_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create reorder_alerts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reorder_alerts (
                alert_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                inventory_id BIGINT NOT NULL,
                alert_type VARCHAR(50) DEFAULT 'LOW_STOCK',
                current_stock DECIMAL(10,4),
                reorder_point DECIMAL(10,4),
                quantity_needed DECIMAL(10,4),
                status VARCHAR(20) DEFAULT 'NEW',
                resolved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS reorder_alerts");
        $pdo->exec("DROP TABLE IF EXISTS inventory_reorder");
    }
];
