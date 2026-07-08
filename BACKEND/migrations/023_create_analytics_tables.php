<?php

/**
 * Migration 023: Create Analytics Tables
 * 
 * Creates tables for business intelligence and analytics
 * including KPIs, dashboards, and reports
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create kpi_metrics table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS kpi_metrics (
                metric_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(18,2),
                metric_type VARCHAR(50),
                period_start DATE NOT NULL,
                period_end DATE NOT NULL,
                comparison_value DECIMAL(18,2),
                comparison_percentage DECIMAL(5,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_name (metric_name),
                INDEX idx_period (period_start, period_end)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create dashboards table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS dashboards (
                dashboard_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                dashboard_name VARCHAR(255) NOT NULL,
                dashboard_config JSON,
                is_public BOOLEAN DEFAULT FALSE,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_public (is_public)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create saved_reports table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS saved_reports (
                report_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                report_name VARCHAR(255) NOT NULL,
                report_type VARCHAR(50) NOT NULL,
                report_config JSON,
                schedule VARCHAR(50),
                last_run_at TIMESTAMP NULL,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (report_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS saved_reports");
        $pdo->exec("DROP TABLE IF EXISTS dashboards");
        $pdo->exec("DROP TABLE IF EXISTS kpi_metrics");
    }
];
