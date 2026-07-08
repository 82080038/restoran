<?php

/**
 * Migration 007: Create Order Management Tables
 * 
 * Creates tables for order management including orders,
 * order items, order payments, and restaurant tables
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create restaurant_tables table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS restaurant_tables (
                table_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                table_number VARCHAR(20) NOT NULL,
                table_name VARCHAR(100),
                capacity INT DEFAULT 4,
                table_type VARCHAR(20) DEFAULT 'STANDARD',
                location VARCHAR(50),
                is_available BOOLEAN DEFAULT TRUE,
                status VARCHAR(20) DEFAULT 'AVAILABLE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_branch_table (branch_id, table_number),
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create orders table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                order_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                order_number VARCHAR(50) UNIQUE,
                customer_id BIGINT,
                table_id BIGINT,
                order_type VARCHAR(20) DEFAULT 'DINE_IN',
                order_channel VARCHAR(20) DEFAULT 'POS',
                subtotal DECIMAL(18,2) DEFAULT 0,
                tax_amount DECIMAL(18,2) DEFAULT 0,
                discount_amount DECIMAL(18,2) DEFAULT 0,
                service_charge DECIMAL(18,2) DEFAULT 0,
                total_amount DECIMAL(18,2) DEFAULT 0,
                currency VARCHAR(3) DEFAULT 'IDR',
                status VARCHAR(20) DEFAULT 'PENDING',
                payment_status VARCHAR(20) DEFAULT 'UNPAID',
                order_date DATE,
                order_time TIME,
                estimated_preparation_time INT,
                actual_preparation_time INT,
                notes TEXT,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_customer (customer_id),
                INDEX idx_table (table_id),
                INDEX idx_status (status),
                INDEX idx_payment_status (payment_status),
                INDEX idx_date (order_date),
                INDEX idx_number (order_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create order_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                order_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                order_id BIGINT NOT NULL,
                product_id BIGINT NOT NULL,
                quantity INT NOT NULL,
                unit_price DECIMAL(18,2) NOT NULL,
                discount_amount DECIMAL(18,2) DEFAULT 0,
                tax_amount DECIMAL(18,2) DEFAULT 0,
                subtotal DECIMAL(18,2) NOT NULL,
                special_instructions TEXT,
                status VARCHAR(20) DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_order (order_id),
                INDEX idx_product (product_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create payments table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                payment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                order_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                payment_number VARCHAR(50) UNIQUE,
                amount DECIMAL(18,2) NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'PENDING',
                reference_number VARCHAR(100),
                card_last_four VARCHAR(4),
                transaction_id VARCHAR(100),
                source VARCHAR(50) DEFAULT 'POS',
                notes TEXT,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_order (order_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_method (payment_method),
                INDEX idx_status (status),
                INDEX idx_date (payment_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create invoices table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                invoice_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                order_id BIGINT,
                invoice_number VARCHAR(50) UNIQUE,
                customer_id BIGINT,
                invoice_date DATE NOT NULL,
                due_date DATE,
                subtotal DECIMAL(18,2) DEFAULT 0,
                tax_amount DECIMAL(18,2) DEFAULT 0,
                discount_amount DECIMAL(18,2) DEFAULT 0,
                total_amount DECIMAL(18,2) NOT NULL,
                paid_amount DECIMAL(18,2) DEFAULT 0,
                balance_amount DECIMAL(18,2) NOT NULL,
                status VARCHAR(20) DEFAULT 'DRAFT',
                notes TEXT,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_order (order_id),
                INDEX idx_customer (customer_id),
                INDEX idx_status (status),
                INDEX idx_due_date (due_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS invoices");
        $pdo->exec("DROP TABLE IF EXISTS payments");
        $pdo->exec("DROP TABLE IF EXISTS order_items");
        $pdo->exec("DROP TABLE IF EXISTS orders");
        $pdo->exec("DROP TABLE IF EXISTS restaurant_tables");
    }
];
