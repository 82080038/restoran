<?php

/**
 * Migration 005: Create Menu Management Tables
 * 
 * Creates tables for menu management including categories,
 * products, product prices, and recipes
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create categories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                parent_category_id BIGINT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                image_url VARCHAR(500),
                display_order INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_parent (parent_category_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create products table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                product_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                category_id BIGINT,
                product_code VARCHAR(50) UNIQUE,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                image_url VARCHAR(500),
                price DECIMAL(18,2) NOT NULL,
                cost_price DECIMAL(18,2),
                sku VARCHAR(100),
                barcode VARCHAR(100),
                unit VARCHAR(20) DEFAULT 'PCS',
                is_available BOOLEAN DEFAULT TRUE,
                is_featured BOOLEAN DEFAULT FALSE,
                preparation_time INT DEFAULT 15,
                calories INT,
                allergens TEXT,
                dietary_info TEXT,
                display_order INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_category (category_id),
                INDEX idx_status (status),
                INDEX idx_sku (sku),
                INDEX idx_barcode (barcode)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create product_prices table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_prices (
                price_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                product_id BIGINT NOT NULL,
                price_type VARCHAR(20) DEFAULT 'REGULAR',
                price DECIMAL(18,2) NOT NULL,
                start_date DATE,
                end_date DATE,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_product (product_id),
                INDEX idx_type (price_type),
                INDEX idx_dates (start_date, end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create recipes table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS recipes (
                recipe_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                instructions TEXT,
                preparation_time INT,
                cooking_time INT,
                yield_percentage DECIMAL(5,2) DEFAULT 100.00,
                portions INT DEFAULT 1,
                difficulty_level VARCHAR(20) DEFAULT 'MEDIUM',
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_product (product_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create recipe_ingredients table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS recipe_ingredients (
                recipe_ingredient_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                recipe_id BIGINT NOT NULL,
                ingredient_id BIGINT NOT NULL,
                quantity DECIMAL(10,4) NOT NULL,
                unit VARCHAR(20) NOT NULL,
                is_optional BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE,
                INDEX idx_recipe (recipe_id),
                INDEX idx_ingredient (ingredient_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS recipe_ingredients");
        $pdo->exec("DROP TABLE IF EXISTS recipes");
        $pdo->exec("DROP TABLE IF EXISTS product_prices");
        $pdo->exec("DROP TABLE IF EXISTS products");
        $pdo->exec("DROP TABLE IF EXISTS categories");
    }
];
