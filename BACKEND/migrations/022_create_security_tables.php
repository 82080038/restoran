<?php

/**
 * Migration 022: Create Security Tables
 * 
 * Creates tables for security including audit logs,
 * security incidents, and access control
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create security_events table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS security_events (
                event_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                user_id BIGINT,
                event_type VARCHAR(50) NOT NULL,
                event_severity VARCHAR(20) DEFAULT 'INFO',
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                metadata JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_user (user_id),
                INDEX idx_type (event_type),
                INDEX idx_severity (event_severity),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create security_incidents table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS security_incidents (
                incident_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                incident_type VARCHAR(50) NOT NULL,
                severity VARCHAR(20) DEFAULT 'MEDIUM',
                description TEXT,
                affected_users JSON,
                status VARCHAR(20) DEFAULT 'OPEN',
                resolved_at TIMESTAMP NULL,
                resolved_by BIGINT,
                resolution_notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (incident_type),
                INDEX idx_status (status),
                INDEX idx_severity (severity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create api_rate_limits table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_rate_limits (
                limit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                user_id BIGINT,
                ip_address VARCHAR(45),
                endpoint VARCHAR(255),
                request_count INT DEFAULT 0,
                window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                window_end TIMESTAMP,
                is_blocked BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_user (user_id),
                INDEX idx_ip (ip_address),
                INDEX idx_blocked (is_blocked)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS api_rate_limits");
        $pdo->exec("DROP TABLE IF EXISTS security_incidents");
        $pdo->exec("DROP TABLE IF EXISTS security_events");
    }
];
