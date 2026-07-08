<?php

/**
 * Migration 028: Create A/B Test Tables
 * 
 * Creates tables for menu A/B testing functionality
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create menu_ab_tests table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS menu_ab_tests (
                test_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                test_name VARCHAR(255) NOT NULL,
                baseline_price DECIMAL(18,2) NOT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                start_date DATE NOT NULL,
                end_date DATE,
                winning_variant_id BIGINT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_product (product_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create menu_ab_test_variants table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS menu_ab_test_variants (
                variant_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                test_id BIGINT NOT NULL,
                variant_name VARCHAR(100) NOT NULL,
                variant_price DECIMAL(18,2) NOT NULL,
                variant_description TEXT,
                allocation_percentage DECIMAL(5,2) NOT NULL,
                impressions INT DEFAULT 0,
                conversions INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (test_id) REFERENCES menu_ab_tests(test_id) ON DELETE CASCADE,
                INDEX idx_test (test_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS menu_ab_test_variants");
        $pdo->exec("DROP TABLE IF EXISTS menu_ab_tests");
    }
];
