<?php

/**
 * Migration 016: Create Production Tables
 * 
 * Creates tables for production including production batches,
 * production items, and ingredient substitutes
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create production_batches table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS production_batches (
                batch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                recipe_id BIGINT NOT NULL,
                batch_number VARCHAR(50) UNIQUE,
                quantity INT NOT NULL,
                status VARCHAR(20) DEFAULT 'PENDING',
                yield_percentage DECIMAL(5,2) DEFAULT 100.00,
                production_date DATE,
                completed_at TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_recipe (recipe_id),
                INDEX idx_status (status),
                INDEX idx_date (production_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create ingredient_substitutes table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ingredient_substitutes (
                substitute_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                ingredient_id BIGINT NOT NULL,
                substitute_ingredient_id BIGINT NOT NULL,
                compatibility_score INT DEFAULT 70,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_ingredient_substitute (ingredient_id, substitute_ingredient_id),
                INDEX idx_ingredient (ingredient_id),
                INDEX idx_substitute (substitute_ingredient_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create competitor_prices table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS competitor_prices (
                competitor_price_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                competitor_name VARCHAR(255) NOT NULL,
                price DECIMAL(18,2) NOT NULL,
                recorded_date DATE NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_product (product_id),
                INDEX idx_active (is_active),
                INDEX idx_date (recorded_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create price_history table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS price_history (
                history_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                product_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                old_price DECIMAL(18,2) NOT NULL,
                new_price DECIMAL(18,2) NOT NULL,
                change_percentage DECIMAL(5,2),
                change_reason VARCHAR(255),
                changed_by BIGINT,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_product (product_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (changed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS price_history");
        $pdo->exec("DROP TABLE IF EXISTS competitor_prices");
        $pdo->exec("DROP TABLE IF EXISTS ingredient_substitutes");
        $pdo->exec("DROP TABLE IF EXISTS production_batches");
    }
];
