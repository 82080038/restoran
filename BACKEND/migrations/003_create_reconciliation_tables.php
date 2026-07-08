<?php

/**
 * Migration 003: Create Reconciliation Tables
 * 
 * Creates tables for payment reconciliation and discrepancy tracking
 * - reconciliation_logs
 * - reconciliation_alerts
 * - reconciliation_sources
 * - reconciliation_transactions
 * - reconciliation_rules
 * - reconciliation_batch_jobs
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create reconciliation_logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reconciliation_logs (
                log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                order_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                status ENUM('RECONCILED', 'DISCREPANCY_HIGH', 'DISCREPANCY_LOW', 'PENDING', 'MANUALLY_OVERRIDDEN') DEFAULT 'PENDING',
                expected_total DECIMAL(18, 2) NOT NULL,
                actual_total DECIMAL(18, 2) NOT NULL,
                difference DECIMAL(18, 2),
                discrepancies_count INT DEFAULT 0,
                discrepancies_json JSON,
                reconciled_at TIMESTAMP NULL,
                reconciled_by VARCHAR(100) DEFAULT 'SYSTEM',
                override_reason TEXT,
                overridden_by BIGINT,
                overridden_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order_id (order_id),
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_branch_id (branch_id),
                INDEX idx_status (status),
                INDEX idx_reconciled_at (reconciled_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create reconciliation_alerts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reconciliation_alerts (
                alert_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                order_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                alert_type ENUM('CRITICAL', 'WARNING', 'INFO') DEFAULT 'WARNING',
                message VARCHAR(500) NOT NULL,
                discrepancies_json JSON,
                status ENUM('ACTIVE', 'ACKNOWLEDGED', 'RESOLVED', 'DISMISSED') DEFAULT 'ACTIVE',
                acknowledged_by BIGINT,
                acknowledged_at TIMESTAMP NULL,
                resolved_by BIGINT,
                resolved_at TIMESTAMP NULL,
                resolution_notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order_id (order_id),
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_branch_id (branch_id),
                INDEX idx_alert_type (alert_type),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create reconciliation_sources table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reconciliation_sources (
                source_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                source_type ENUM('POS', 'PAYMENT_PROCESSOR', 'DELIVERY_PLATFORM', 'BANK', 'CASH_REGISTER') NOT NULL,
                source_name VARCHAR(255) NOT NULL,
                source_code VARCHAR(100) NOT NULL,
                api_endpoint VARCHAR(500),
                api_key_encrypted TEXT,
                api_secret_encrypted TEXT,
                configuration JSON,
                is_active BOOLEAN DEFAULT TRUE,
                last_sync_at TIMESTAMP NULL,
                sync_frequency_minutes INT DEFAULT 60,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                UNIQUE KEY uk_source_code_tenant (source_code, tenant_id),
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_source_type (source_type),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create reconciliation_transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reconciliation_transactions (
                transaction_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                log_id BIGINT NOT NULL,
                source_id BIGINT NOT NULL,
                order_id BIGINT NOT NULL,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                external_transaction_id VARCHAR(255),
                transaction_type ENUM('PAYMENT', 'REFUND', 'VOID', 'ADJUSTMENT') DEFAULT 'PAYMENT',
                amount DECIMAL(18, 2) NOT NULL,
                currency VARCHAR(3) DEFAULT 'IDR',
                transaction_date TIMESTAMP NOT NULL,
                status ENUM('MATCHED', 'UNMATCHED', 'DISPUTED') DEFAULT 'UNMATCHED',
                matching_score DECIMAL(5, 2),
                metadata JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_log_id (log_id),
                INDEX idx_source_id (source_id),
                INDEX idx_order_id (order_id),
                INDEX idx_external_transaction_id (external_transaction_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create reconciliation_rules table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reconciliation_rules (
                rule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                rule_name VARCHAR(255) NOT NULL,
                rule_code VARCHAR(100) NOT NULL,
                rule_type ENUM('MATCHING', 'TOLERANCE', 'ALERTING', 'AUTO_CORRECTION') NOT NULL,
                source_type ENUM('POS', 'PAYMENT_PROCESSOR', 'DELIVERY_PLATFORM', 'BANK', 'CASH_REGISTER'),
                conditions JSON NOT NULL,
                actions JSON NOT NULL,
                priority INT DEFAULT 100,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                UNIQUE KEY uk_rule_code_tenant (rule_code, tenant_id),
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_rule_type (rule_type),
                INDEX idx_source_type (source_type),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create reconciliation_batch_jobs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reconciliation_batch_jobs (
                batch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                job_type ENUM('FULL_RECONCILIATION', 'PARTIAL_RECONCILIATION', 'SOURCE_SYNC', 'ALERT_CHECK') NOT NULL,
                status ENUM('PENDING', 'RUNNING', 'COMPLETED', 'FAILED', 'CANCELLED') DEFAULT 'PENDING',
                parameters JSON,
                total_orders INT DEFAULT 0,
                processed_orders INT DEFAULT 0,
                successful_orders INT DEFAULT 0,
                failed_orders INT DEFAULT 0,
                discrepancies_found INT DEFAULT 0,
                started_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                error_message TEXT,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_branch_id (branch_id),
                INDEX idx_status (status),
                INDEX idx_job_type (job_type),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS reconciliation_batch_jobs");
        $pdo->exec("DROP TABLE IF EXISTS reconciliation_rules");
        $pdo->exec("DROP TABLE IF EXISTS reconciliation_transactions");
        $pdo->exec("DROP TABLE IF EXISTS reconciliation_sources");
        $pdo->exec("DROP TABLE IF EXISTS reconciliation_alerts");
        $pdo->exec("DROP TABLE IF EXISTS reconciliation_logs");
    }
];
