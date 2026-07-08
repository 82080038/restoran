<?php

/**
 * Migration 019: Create Reservation Tables
 * 
 * Creates tables for reservation management including
 * reservations, waitlist, and guest preferences
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create reservations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reservations (
                reservation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                customer_id BIGINT,
                reservation_number VARCHAR(50) UNIQUE,
                party_size INT NOT NULL,
                reservation_date DATE NOT NULL,
                reservation_time TIME NOT NULL,
                status VARCHAR(20) DEFAULT 'CONFIRMED',
                special_requests TEXT,
                table_id BIGINT,
                arrived_at TIMESTAMP NULL,
                seated_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                cancelled_at TIMESTAMP NULL,
                no_show BOOLEAN DEFAULT FALSE,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_customer (customer_id),
                INDEX idx_date (reservation_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create waitlist table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS waitlist (
                waitlist_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                customer_id BIGINT,
                party_size INT NOT NULL,
                estimated_wait_minutes INT,
                actual_wait_minutes INT,
                status VARCHAR(20) DEFAULT 'WAITING',
                phone_number VARCHAR(20),
                notified_at TIMESTAMP NULL,
                seated_at TIMESTAMP NULL,
                cancelled_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create guest_preferences table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS guest_preferences (
                preference_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                customer_id BIGINT,
                preference_type VARCHAR(50) NOT NULL,
                preference_value TEXT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_customer (customer_id),
                INDEX idx_type (preference_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS guest_preferences");
        $pdo->exec("DROP TABLE IF EXISTS waitlist");
        $pdo->exec("DROP TABLE IF EXISTS reservations");
    }
];
