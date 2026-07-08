<?php

/**
 * Migration 008: Create Kitchen Management Tables
 * 
 * Creates tables for kitchen management including kitchen orders,
 * kitchen order items, and kitchen stations
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create kitchen_stations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS kitchen_stations (
                station_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                station_name VARCHAR(100) NOT NULL,
                station_type VARCHAR(50) DEFAULT 'PREPARATION',
                description TEXT,
                display_order INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create kitchen_orders table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS kitchen_orders (
                kitchen_order_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                order_id BIGINT NOT NULL,
                kitchen_order_number VARCHAR(50) UNIQUE,
                station_id BIGINT,
                status VARCHAR(20) DEFAULT 'PENDING',
                priority VARCHAR(20) DEFAULT 'NORMAL',
                estimated_completion_time TIMESTAMP NULL,
                actual_completion_time TIMESTAMP NULL,
                started_at TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_order (order_id),
                INDEX idx_station (station_id),
                INDEX idx_status (status),
                INDEX idx_priority (priority)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create kitchen_order_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS kitchen_order_items (
                kitchen_order_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                kitchen_order_id BIGINT NOT NULL,
                order_item_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                quantity INT NOT NULL,
                special_instructions TEXT,
                status VARCHAR(20) DEFAULT 'PENDING',
                started_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_kitchen_order (kitchen_order_id),
                INDEX idx_order_item (order_item_id),
                INDEX idx_product (product_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS kitchen_order_items");
        $pdo->exec("DROP TABLE IF EXISTS kitchen_orders");
        $pdo->exec("DROP TABLE IF EXISTS kitchen_stations");
    }
];
