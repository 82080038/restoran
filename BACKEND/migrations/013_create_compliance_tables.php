<?php

/**
 * Migration 013: Create Compliance Tables
 * 
 * Creates tables for compliance including compliance checks,
 * compliance alerts, and certifications
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create compliance_checks table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS compliance_checks (
                check_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                check_type VARCHAR(50) NOT NULL,
                status VARCHAR(20) DEFAULT 'PENDING',
                violations_json TEXT,
                warnings_json TEXT,
                start_date DATE,
                end_date DATE,
                checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (check_type),
                INDEX idx_date (checked_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create compliance_alerts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS compliance_alerts (
                alert_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                alert_type VARCHAR(50) DEFAULT 'WARNING',
                message VARCHAR(500) NOT NULL,
                discrepancies_json TEXT,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                resolved_at TIMESTAMP NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status),
                INDEX idx_type (alert_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create certifications table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS certifications (
                certification_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                certification_type VARCHAR(50) NOT NULL,
                certification_number VARCHAR(100),
                issuing_authority VARCHAR(255),
                issue_date DATE,
                expiry_date DATE,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                document_url VARCHAR(500),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (certification_type),
                INDEX idx_expiry (expiry_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create temperature_logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS temperature_logs (
                log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                storage_area VARCHAR(100) NOT NULL,
                temperature DECIMAL(5,2) NOT NULL,
                logged_by BIGINT,
                log_date DATE NOT NULL,
                log_time TIME NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (log_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create haccp_checkpoints table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS haccp_checkpoints (
                checkpoint_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                checkpoint_name VARCHAR(255) NOT NULL,
                checkpoint_type VARCHAR(50),
                last_check_date DATE,
                frequency VARCHAR(20) DEFAULT 'DAILY',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS haccp_checkpoints");
        $pdo->exec("DROP TABLE IF EXISTS temperature_logs");
        $pdo->exec("DROP TABLE IF EXISTS certifications");
        $pdo->exec("DROP TABLE IF EXISTS compliance_alerts");
        $pdo->exec("DROP TABLE IF EXISTS compliance_checks");
    }
];
