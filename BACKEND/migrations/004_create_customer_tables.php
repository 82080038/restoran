<?php

/**
 * Migration 004: Create Customer Management Tables
 * 
 * Creates tables for customer management including customers,
 * customer addresses, and customer preferences
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create customers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customers (
                customer_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                customer_code VARCHAR(50) UNIQUE,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                email VARCHAR(255),
                date_of_birth DATE,
                gender VARCHAR(10),
                membership_level VARCHAR(20) DEFAULT 'REGULAR',
                total_visits INT DEFAULT 0,
                total_spent DECIMAL(18,2) DEFAULT 0,
                last_visit_date DATE,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status),
                INDEX idx_phone (phone),
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create customer_addresses table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_addresses (
                address_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT NOT NULL,
                address_type VARCHAR(20) DEFAULT 'HOME',
                address_line1 VARCHAR(255) NOT NULL,
                address_line2 VARCHAR(255),
                city VARCHAR(100),
                state VARCHAR(100),
                postal_code VARCHAR(20),
                country VARCHAR(100) DEFAULT 'Indonesia',
                is_default BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
                INDEX idx_customer (customer_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create customer_preferences table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_preferences (
                preference_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT NOT NULL,
                preference_key VARCHAR(100) NOT NULL,
                preference_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
                UNIQUE KEY uk_customer_preference (customer_id, preference_key),
                INDEX idx_customer (customer_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS customer_preferences");
        $pdo->exec("DROP TABLE IF EXISTS customer_addresses");
        $pdo->exec("DROP TABLE IF EXISTS customers");
    }
];
