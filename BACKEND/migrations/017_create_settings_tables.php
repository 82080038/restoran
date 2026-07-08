<?php

/**
 * Migration 017: Create Settings Tables
 * 
 * Creates tables for system settings, configurations,
 * and audit logs
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create settings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                setting_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                setting_key VARCHAR(100) NOT NULL,
                setting_value TEXT,
                setting_type VARCHAR(20) DEFAULT 'STRING',
                description TEXT,
                is_encrypted BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_tenant_branch_key (tenant_id, branch_id, setting_key),
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_key (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create audit_logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                user_id BIGINT,
                action VARCHAR(50) NOT NULL,
                entity_type VARCHAR(50),
                entity_id BIGINT,
                old_values JSON,
                new_values JSON,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_user (user_id),
                INDEX idx_action (action),
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create notifications table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                notification_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                user_id BIGINT,
                notification_type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                data JSON,
                is_read BOOLEAN DEFAULT FALSE,
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_user (user_id),
                INDEX idx_type (notification_type),
                INDEX idx_read (is_read),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create api_logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_logs (
                log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT,
                branch_id BIGINT,
                user_id BIGINT,
                request_method VARCHAR(10) NOT NULL,
                request_path VARCHAR(500) NOT NULL,
                request_params TEXT,
                response_status INT,
                response_time_ms INT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_user (user_id),
                INDEX idx_method (request_method),
                INDEX idx_path (request_path),
                INDEX idx_status (response_status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS api_logs");
        $pdo->exec("DROP TABLE IF EXISTS notifications");
        $pdo->exec("DROP TABLE IF EXISTS audit_logs");
        $pdo->exec("DROP TABLE IF EXISTS settings");
    }
];
