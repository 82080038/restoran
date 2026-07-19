<?php

/**
 * Migration 046: Create Recipe-Level Inventory Depletion tables
 *
 * Tables:
 * - recipe_depletion_logs (tracks raw material deduction when products sold)
 * - production_batches (tracks production output and ingredient consumption)
 */

return [
    'up' => function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS recipe_depletion_logs (
                log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                order_id BIGINT NULL,
                product_id BIGINT NOT NULL,
                recipe_id BIGINT NULL,
                quantity_sold DECIMAL(10,2) NOT NULL,
                unit VARCHAR(20) DEFAULT 'portion',
                ingredient_inventory_item_id BIGINT NOT NULL,
                ingredient_name VARCHAR(255),
                depletion_quantity DECIMAL(10,4) NOT NULL,
                depletion_unit VARCHAR(20),
                depletion_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                depletion_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_order (order_id),
                INDEX idx_product (product_id),
                INDEX idx_ingredient (ingredient_inventory_item_id),
                INDEX idx_date (depletion_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS production_batches (
                batch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                recipe_id BIGINT NOT NULL,
                product_id BIGINT NULL,
                batch_code VARCHAR(100),
                planned_quantity DECIMAL(10,2) NOT NULL,
                actual_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
                unit VARCHAR(20) DEFAULT 'portion',
                ingredients_consumed JSON,
                total_ingredient_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                labor_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                overhead_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                unit_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('PLANNED','IN_PROGRESS','COMPLETED','CANCELLED') DEFAULT 'PLANNED',
                produced_by BIGINT,
                produced_at TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_recipe (recipe_id),
                INDEX idx_product (product_id),
                INDEX idx_status (status),
                INDEX idx_date (produced_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS production_batches");
        $pdo->exec("DROP TABLE IF EXISTS recipe_depletion_logs");
    }
];
