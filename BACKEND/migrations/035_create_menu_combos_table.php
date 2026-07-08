<?php

/**
 * Migration 035: Create Menu Combos Table
 * 
 * Creates tables for combo/bundle pricing to support套餐 with special pricing
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create menu_combos table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS menu_combos (
                combo_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                combo_code VARCHAR(50) NOT NULL,
                combo_name VARCHAR(150) NOT NULL,
                description TEXT,
                combo_price DECIMAL(18,2) NOT NULL,
                discount_amount DECIMAL(18,2) DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                start_date DATE,
                end_date DATE,
                image_url VARCHAR(500),
                display_order INT DEFAULT 0,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                UNIQUE KEY uk_tenant_code (tenant_id, combo_code),
                INDEX idx_tenant (tenant_id),
                INDEX idx_active (is_active),
                INDEX idx_dates (start_date, end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create menu_combo_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS menu_combo_items (
                combo_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                combo_id BIGINT NOT NULL,
                menu_id BIGINT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                is_required BOOLEAN DEFAULT TRUE,
                max_quantity INT DEFAULT 1,
                min_quantity INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (combo_id) REFERENCES menu_combos(combo_id) ON DELETE CASCADE,
                INDEX idx_combo (combo_id),
                INDEX idx_menu (menu_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Add combo_id to order_items table
        $pdo->exec("
            ALTER TABLE order_items 
            ADD COLUMN combo_id BIGINT NULL AFTER product_id
        ");

        // Add index for combo_id
        $pdo->exec("
            ALTER TABLE order_items 
            ADD INDEX idx_combo (combo_id)
        ");
    },

    'down' => function($pdo) {
        // Remove combo_id from order_items
        $pdo->exec("ALTER TABLE order_items DROP COLUMN combo_id");
        
        // Drop menu_combo_items table
        $pdo->exec("DROP TABLE IF EXISTS menu_combo_items");
        
        // Drop menu_combos table
        $pdo->exec("DROP TABLE IF EXISTS menu_combos");
    }
];
