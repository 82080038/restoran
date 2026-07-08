<?php

/**
 * Migration 015: Create Risk Management Tables
 * 
 * Creates tables for risk management including risk register,
 * mitigation plans, and risk assessments
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create risk_register table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS risk_register (
                risk_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                risk_type VARCHAR(50) NOT NULL,
                risk_category VARCHAR(50) NOT NULL,
                description TEXT,
                risk_level VARCHAR(20) DEFAULT 'MEDIUM',
                score INT DEFAULT 50,
                impact VARCHAR(20),
                likelihood VARCHAR(20),
                detectability VARCHAR(20),
                status VARCHAR(20) DEFAULT 'ACTIVE',
                identified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (risk_type),
                INDEX idx_category (risk_category),
                INDEX idx_level (risk_level),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create risk_mitigation_plans table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS risk_mitigation_plans (
                plan_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                risk_id BIGINT NOT NULL,
                plan_name VARCHAR(255) NOT NULL,
                description TEXT,
                actions JSON,
                responsible_person VARCHAR(255),
                target_date DATE,
                status VARCHAR(20) DEFAULT 'PENDING',
                progress_percentage INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (risk_id) REFERENCES risk_register(risk_id) ON DELETE CASCADE,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_risk (risk_id),
                INDEX idx_status (status),
                INDEX idx_target (target_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create equipment table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS equipment (
                equipment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                equipment_code VARCHAR(50) UNIQUE,
                equipment_name VARCHAR(255) NOT NULL,
                equipment_type VARCHAR(50),
                manufacturer VARCHAR(255),
                model VARCHAR(100),
                serial_number VARCHAR(100),
                purchase_date DATE,
                purchase_price DECIMAL(18,2),
                warranty_expiry DATE,
                last_maintenance_date DATE,
                next_maintenance_date DATE,
                status VARCHAR(20) DEFAULT 'OPERATIONAL',
                location VARCHAR(100),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (equipment_type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create system_incidents table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_incidents (
                incident_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                incident_type VARCHAR(50) NOT NULL,
                description TEXT,
                severity VARCHAR(20) DEFAULT 'MEDIUM',
                duration_minutes INT,
                resolved_at TIMESTAMP NULL,
                resolution_notes TEXT,
                incident_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (incident_type),
                INDEX idx_severity (severity),
                INDEX idx_date (incident_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create system_backups table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_backups (
                backup_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                backup_type VARCHAR(50) DEFAULT 'FULL',
                backup_status VARCHAR(20) DEFAULT 'SUCCESS',
                file_path VARCHAR(500),
                file_size BIGINT,
                last_backup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                next_backup_date TIMESTAMP NULL,
                notes TEXT,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_type (backup_type),
                INDEX idx_status (backup_status),
                INDEX idx_date (last_backup_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS system_backups");
        $pdo->exec("DROP TABLE IF EXISTS system_incidents");
        $pdo->exec("DROP TABLE IF EXISTS equipment");
        $pdo->exec("DROP TABLE IF EXISTS risk_mitigation_plans");
        $pdo->exec("DROP TABLE IF EXISTS risk_register");
    }
];
