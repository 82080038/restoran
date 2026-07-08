<?php

/**
 * Migration 030: Create Referral and Achievement Tables
 * 
 * Creates tables for referral program and gamification elements
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create referrals table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS referrals (
                referral_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                referrer_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                referral_code VARCHAR(20) UNIQUE NOT NULL,
                referred_customer_id BIGINT NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                completed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_referrer (referrer_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_code (referral_code),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create customer_achievements table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_achievements (
                achievement_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                customer_id BIGINT NOT NULL,
                achievement_type VARCHAR(50) NOT NULL,
                achievement_data JSON,
                points_awarded INT DEFAULT 0,
                earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_customer (customer_id),
                INDEX idx_type (achievement_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS customer_achievements");
        $pdo->exec("DROP TABLE IF EXISTS referrals");
    }
];
