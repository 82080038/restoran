<?php

/**
 * Migration 027: Create Payroll Tables
 * 
 * Creates tables for payroll management
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create payroll_periods table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payroll_periods (
                period_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                period_name VARCHAR(100) NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                status VARCHAR(20) DEFAULT 'OPEN',
                processed_at TIMESTAMP NULL,
                processed_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status),
                INDEX idx_dates (start_date, end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create payroll_entries table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payroll_entries (
                entry_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                period_id BIGINT NOT NULL,
                employee_id BIGINT NOT NULL,
                regular_hours DECIMAL(10,2) DEFAULT 0,
                overtime_hours DECIMAL(10,2) DEFAULT 0,
                hourly_rate DECIMAL(18,2) NOT NULL,
                regular_pay DECIMAL(18,2) DEFAULT 0,
                overtime_pay DECIMAL(18,2) DEFAULT 0,
                bonuses DECIMAL(18,2) DEFAULT 0,
                deductions DECIMAL(18,2) DEFAULT 0,
                net_pay DECIMAL(18,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (period_id) REFERENCES payroll_periods(period_id) ON DELETE CASCADE,
                INDEX idx_period (period_id),
                INDEX idx_employee (employee_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS payroll_entries");
        $pdo->exec("DROP TABLE IF EXISTS payroll_periods");
    }
];
