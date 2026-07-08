<?php

/**
 * Migration 014: Create Sustainability Tables
 * 
 * Creates tables for sustainability including food waste,
 * energy consumption, and carbon footprint tracking
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create food_waste table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS food_waste (
                waste_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                waste_type VARCHAR(50) NOT NULL,
                quantity_kg DECIMAL(10,4) NOT NULL,
                reason VARCHAR(255),
                waste_date DATE NOT NULL,
                recorded_by BIGINT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (waste_type),
                INDEX idx_date (waste_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create energy_consumption table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS energy_consumption (
                consumption_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                energy_type VARCHAR(50) NOT NULL,
                consumption_kwh DECIMAL(10,4) NOT NULL,
                consumption_date DATE NOT NULL,
                meter_reading DECIMAL(18,2),
                recorded_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (energy_type),
                INDEX idx_date (consumption_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create transportation_logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS transportation_logs (
                transport_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                transport_type VARCHAR(50) NOT NULL,
                distance_km DECIMAL(10,2) NOT NULL,
                vehicle_id VARCHAR(100),
                transport_date DATE NOT NULL,
                purpose VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (transport_type),
                INDEX idx_date (transport_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create sustainability_reports table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS sustainability_reports (
                report_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                report_period_start DATE NOT NULL,
                report_period_end DATE NOT NULL,
                carbon_footprint_kg DECIMAL(18,2),
                waste_total_kg DECIMAL(18,2),
                energy_total_kwh DECIMAL(18,2),
                sustainability_score INT,
                generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_period (report_period_start, report_period_end)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS sustainability_reports");
        $pdo->exec("DROP TABLE IF EXISTS transportation_logs");
        $pdo->exec("DROP TABLE IF EXISTS energy_consumption");
        $pdo->exec("DROP TABLE IF EXISTS food_waste");
    }
];
