<?php

/**
 * Migration 009: Create Loyalty Program Tables
 * 
 * Creates tables for loyalty program including loyalty members,
 * loyalty transactions, loyalty rewards, and loyalty promotions
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create loyalty_members table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS loyalty_members (
                member_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                membership_number VARCHAR(50) UNIQUE,
                tier_level VARCHAR(20) DEFAULT 'BRONZE',
                points_balance INT DEFAULT 0,
                total_points_earned INT DEFAULT 0,
                tier_upgraded_at TIMESTAMP NULL,
                last_activity_at TIMESTAMP NULL,
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_customer_tenant (customer_id, tenant_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_customer (customer_id),
                INDEX idx_tier (tier_level)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create loyalty_transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS loyalty_transactions (
                transaction_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                order_id BIGINT,
                points_earned INT DEFAULT 0,
                points_used INT DEFAULT 0,
                transaction_type VARCHAR(20) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_customer (customer_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_order (order_id),
                INDEX idx_type (transaction_type),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create loyalty_rewards table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS loyalty_rewards (
                reward_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                reward_type VARCHAR(20) DEFAULT 'DISCOUNT',
                points_required INT NOT NULL,
                discount_percentage DECIMAL(5,2),
                discount_amount DECIMAL(18,2),
                free_product_id BIGINT,
                quantity_available INT DEFAULT -1,
                total_redeemed INT DEFAULT 0,
                image_url VARCHAR(500),
                terms_conditions TEXT,
                start_date DATE,
                end_date DATE,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_status (status),
                INDEX idx_dates (start_date, end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create loyalty_redemptions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS loyalty_redemptions (
                redemption_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                reward_id BIGINT NOT NULL,
                redemption_code VARCHAR(50) UNIQUE,
                points_used INT NOT NULL,
                order_id BIGINT,
                status VARCHAR(20) DEFAULT 'REDEEMED',
                redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                INDEX idx_customer (customer_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_reward (reward_id),
                INDEX idx_code (redemption_code),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create loyalty_promotions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS loyalty_promotions (
                promotion_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                bonus_points INT DEFAULT 0,
                points_multiplier DECIMAL(3,2) DEFAULT 1.0,
                conditions JSON,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_status (status),
                INDEX idx_dates (start_date, end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS loyalty_promotions");
        $pdo->exec("DROP TABLE IF EXISTS loyalty_redemptions");
        $pdo->exec("DROP TABLE IF EXISTS loyalty_rewards");
        $pdo->exec("DROP TABLE IF EXISTS loyalty_transactions");
        $pdo->exec("DROP TABLE IF EXISTS loyalty_members");
    }
];
