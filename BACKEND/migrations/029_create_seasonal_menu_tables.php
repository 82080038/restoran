<?php

/**
 * Migration 029: Create Seasonal Menu Tables
 * 
 * Creates tables for seasonal menu planning
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create seasonal_menus table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS seasonal_menus (
                menu_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                menu_name VARCHAR(255) NOT NULL,
                season VARCHAR(20) NOT NULL,
                year INT NOT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                start_date DATE,
                end_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_season (season),
                INDEX idx_year (year),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create seasonal_menu_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS seasonal_menu_items (
                menu_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                menu_id BIGINT NOT NULL,
                recipe_id BIGINT NOT NULL,
                priority INT DEFAULT 0,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (menu_id) REFERENCES seasonal_menus(menu_id) ON DELETE CASCADE,
                INDEX idx_menu (menu_id),
                INDEX idx_recipe (recipe_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS seasonal_menu_items");
        $pdo->exec("DROP TABLE IF EXISTS seasonal_menus");
    }
];
