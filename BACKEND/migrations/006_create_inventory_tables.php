<?php

/**
 * Migration 006: Create Inventory Management Tables
 * 
 * Creates tables for inventory management including inventory items,
 * stock balances, stock transactions, and suppliers
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create inventory_categories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory_categories (
                category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                parent_category_id BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_category_id) REFERENCES inventory_categories(category_id) ON DELETE SET NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_parent (parent_category_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create inventory table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS inventory (
                inventory_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                category_id BIGINT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                sku VARCHAR(100) UNIQUE,
                barcode VARCHAR(100),
                unit VARCHAR(20) DEFAULT 'PCS',
                unit_cost DECIMAL(18,2),
                selling_price DECIMAL(18,2),
                reorder_level DECIMAL(10,4) DEFAULT 0,
                max_stock_level DECIMAL(10,4),
                lead_time_days INT DEFAULT 7,
                shelf_life_days INT,
                expiration_date DATE,
                carbon_footprint_per_kg DECIMAL(10,4) DEFAULT 2.5,
                is_perishable BOOLEAN DEFAULT FALSE,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (category_id) REFERENCES inventory_categories(category_id) ON DELETE SET NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_category (category_id),
                INDEX idx_sku (sku),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create stock_balances table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS stock_balances (
                stock_balance_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                branch_id BIGINT NOT NULL,
                inventory_id BIGINT NOT NULL,
                quantity DECIMAL(18,4) DEFAULT 0,
                average_cost DECIMAL(18,2),
                last_transaction_date TIMESTAMP NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_branch_inventory (branch_id, inventory_id),
                INDEX idx_branch (branch_id),
                INDEX idx_inventory (inventory_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create stock_transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS stock_transactions (
                transaction_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                inventory_id BIGINT NOT NULL,
                transaction_type VARCHAR(20) NOT NULL,
                quantity DECIMAL(18,4) NOT NULL,
                unit_cost DECIMAL(18,2),
                reference_type VARCHAR(50),
                reference_id BIGINT,
                notes TEXT,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_inventory (inventory_id),
                INDEX idx_type (transaction_type),
                INDEX idx_reference (reference_type, reference_id),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create suppliers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS suppliers (
                supplier_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                supplier_code VARCHAR(50) UNIQUE,
                name VARCHAR(255) NOT NULL,
                contact_person VARCHAR(255),
                phone VARCHAR(20),
                email VARCHAR(255),
                address TEXT,
                city VARCHAR(100),
                state VARCHAR(100),
                postal_code VARCHAR(20),
                country VARCHAR(100) DEFAULT 'Indonesia',
                tax_id VARCHAR(50),
                payment_terms VARCHAR(50),
                lead_time_days INT DEFAULT 7,
                credit_limit DECIMAL(18,2),
                rating DECIMAL(3,2) DEFAULT 3.00,
                is_active BOOLEAN DEFAULT TRUE,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_code (supplier_code),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create supplier_products table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS supplier_products (
                supplier_product_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                supplier_id BIGINT NOT NULL,
                inventory_id BIGINT NOT NULL,
                supplier_sku VARCHAR(100),
                supplier_price DECIMAL(18,2),
                minimum_order_quantity DECIMAL(10,4),
                is_preferred BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_supplier_inventory (supplier_id, inventory_id),
                INDEX idx_supplier (supplier_id),
                INDEX idx_inventory (inventory_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS supplier_products");
        $pdo->exec("DROP TABLE IF EXISTS suppliers");
        $pdo->exec("DROP TABLE IF EXISTS stock_transactions");
        $pdo->exec("DROP TABLE IF EXISTS stock_balances");
        $pdo->exec("DROP TABLE IF EXISTS inventory");
        $pdo->exec("DROP TABLE IF EXISTS inventory_categories");
    }
];
