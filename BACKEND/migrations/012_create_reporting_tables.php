<?php

/**
 * Migration 012: Create Reporting Tables
 * 
 * Creates tables for reporting including reports,
 * report schedules, and report templates
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create reports table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reports (
                report_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                report_name VARCHAR(255) NOT NULL,
                report_type VARCHAR(50) NOT NULL,
                report_config JSON,
                generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                generated_by BIGINT,
                file_path VARCHAR(500),
                file_size BIGINT,
                status VARCHAR(20) DEFAULT 'COMPLETED',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (report_type),
                INDEX idx_date (generated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create report_schedules table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS report_schedules (
                schedule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                report_name VARCHAR(255) NOT NULL,
                report_type VARCHAR(50) NOT NULL,
                schedule_type VARCHAR(20) DEFAULT 'DAILY',
                schedule_config JSON,
                next_run_at TIMESTAMP NULL,
                last_run_at TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_active (is_active),
                INDEX idx_next_run (next_run_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS report_schedules");
        $pdo->exec("DROP TABLE IF EXISTS reports");
    }
];
