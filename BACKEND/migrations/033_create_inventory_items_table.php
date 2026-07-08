<?php

/**
 * Migration 033: Create Inventory Items Table
 * 
 * Creates table for tracking individual inventory items with specific weights
 * for made-to-order products like fish and roasted pork
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create inventory_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_items (
                item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                inventory_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                item_code VARCHAR(50) UNIQUE,
                weight DECIMAL(10,3) NOT NULL,
                unit_cost DECIMAL(18,2),
                calculated_cost DECIMAL(18,2),
                status ENUM('AVAILABLE', 'RESERVED', 'SOLD', 'DISCARDED') DEFAULT 'AVAILABLE',
                received_date DATE,
                expiry_date DATE,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_inventory (inventory_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status),
                INDEX idx_code (item_code),
                INDEX idx_expiry (expiry_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS inventory_items");
    }
];
