<?php

/**
 * Migration 001: Create Tenant Tables
 * 
 * Creates the foundation tables for multi-tenant architecture
 * - tenants
 * - companies
 * - branches
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create tenants table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tenants (
                tenant_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_code VARCHAR(50) NOT NULL UNIQUE,
                tenant_name VARCHAR(255) NOT NULL,
                tenant_type ENUM('HOME_BASED', 'SMALL_RESTAURANT', 'REGIONAL_CHAIN', 'NATIONAL_CORPORATION', 'INTERNATIONAL_CORPORATION') DEFAULT 'SMALL_RESTAURANT',
                business_registration_number VARCHAR(100),
                tax_id VARCHAR(100),
                contact_email VARCHAR(255),
                contact_phone VARCHAR(50),
                address TEXT,
                city VARCHAR(100),
                state VARCHAR(100),
                country VARCHAR(100) DEFAULT 'Indonesia',
                postal_code VARCHAR(20),
                status ENUM('ACTIVE', 'SUSPENDED', 'TERMINATED') DEFAULT 'ACTIVE',
                subscription_plan ENUM('FREE', 'BASIC', 'PROFESSIONAL', 'ENTERPRISE') DEFAULT 'BASIC',
                subscription_start_date DATE,
                subscription_end_date DATE,
                max_branches INT DEFAULT 1,
                max_users INT DEFAULT 5,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_tenant_code (tenant_code),
                INDEX idx_tenant_status (status),
                INDEX idx_subscription_plan (subscription_plan)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create companies table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS companies (
                company_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                company_name VARCHAR(255) NOT NULL,
                company_type ENUM('SINGLE_OWNER', 'PARTNERSHIP', 'CORPORATION', 'FRANCHISE') DEFAULT 'SINGLE_OWNER',
                registration_number VARCHAR(100),
                tax_id VARCHAR(100),
                legal_address TEXT,
                billing_address TEXT,
                contact_person VARCHAR(255),
                contact_email VARCHAR(255),
                contact_phone VARCHAR(50),
                website VARCHAR(255),
                established_date DATE,
                status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_company_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create branches table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS branches (
                branch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                company_id BIGINT,
                branch_code VARCHAR(50) NOT NULL,
                branch_name VARCHAR(255) NOT NULL,
                branch_type ENUM('HEADQUARTERS', 'OUTLET', 'KIOSK', 'GHOST_KITCHEN') DEFAULT 'OUTLET',
                address TEXT NOT NULL,
                city VARCHAR(100) NOT NULL,
                state VARCHAR(100),
                country VARCHAR(100) DEFAULT 'Indonesia',
                postal_code VARCHAR(20),
                latitude DECIMAL(10, 8),
                longitude DECIMAL(11, 8),
                phone VARCHAR(50),
                email VARCHAR(255),
                opening_hours JSON,
                business_hours_start TIME DEFAULT '08:00:00',
                business_hours_end TIME DEFAULT '22:00:00',
                is_24_hours BOOLEAN DEFAULT FALSE,
                status ENUM('ACTIVE', 'INACTIVE', 'CLOSED_TEMPORARILY', 'PERMANENTLY_CLOSED') DEFAULT 'ACTIVE',
                opening_date DATE,
                closing_date DATE,
                floor_area_sqm DECIMAL(10, 2),
                seating_capacity INT,
                manager_id BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
                FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE SET NULL,
                UNIQUE KEY uk_branch_code_tenant (branch_code, tenant_id),
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_company_id (company_id),
                INDEX idx_branch_status (status),
                INDEX idx_branch_type (branch_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS branches");
        $pdo->exec("DROP TABLE IF EXISTS companies");
        $pdo->exec("DROP TABLE IF EXISTS tenants");
    }
];
