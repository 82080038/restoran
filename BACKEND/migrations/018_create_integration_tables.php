<?php

/**
 * Migration 018: Create Integration Tables
 * 
 * Creates tables for external system integrations including
 * API connections, webhooks, and sync status
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create integrations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS integrations (
                integration_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                integration_type VARCHAR(50) NOT NULL,
                integration_name VARCHAR(255) NOT NULL,
                config JSON,
                api_key VARCHAR(255),
                api_secret VARCHAR(255),
                webhook_url VARCHAR(500),
                is_active BOOLEAN DEFAULT TRUE,
                last_sync_at TIMESTAMP NULL,
                sync_status VARCHAR(20) DEFAULT 'IDLE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (integration_type),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create integration_logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS integration_logs (
                log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                integration_id BIGINT NOT NULL,
                log_type VARCHAR(50) NOT NULL,
                request_data TEXT,
                response_data TEXT,
                status_code INT,
                error_message TEXT,
                execution_time_ms INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_integration (integration_id),
                INDEX idx_type (log_type),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create webhooks table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS webhooks (
                webhook_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                event_type VARCHAR(50) NOT NULL,
                target_url VARCHAR(500) NOT NULL,
                secret_key VARCHAR(255),
                is_active BOOLEAN DEFAULT TRUE,
                retry_count INT DEFAULT 0,
                last_triggered_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_event (event_type),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create webhook_deliveries table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS webhook_deliveries (
                delivery_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                webhook_id BIGINT NOT NULL,
                payload JSON,
                response_status INT,
                response_body TEXT,
                delivered_at TIMESTAMP NULL,
                retry_count INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_webhook (webhook_id),
                INDEX idx_status (status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS webhook_deliveries");
        $pdo->exec("DROP TABLE IF EXISTS webhooks");
        $pdo->exec("DROP TABLE IF EXISTS integration_logs");
        $pdo->exec("DROP TABLE IF EXISTS integrations");
    }
];
