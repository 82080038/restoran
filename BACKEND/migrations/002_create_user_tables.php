<?php

/**
 * Migration 002: Create User Management Tables
 * 
 * Creates tables for user management, roles, and permissions
 * - users
 * - roles
 * - permissions
 * - role_permissions
 * - user_roles
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                user_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100),
                display_name VARCHAR(255),
                phone VARCHAR(50),
                profile_image VARCHAR(500),
                date_of_birth DATE,
                gender ENUM('MALE', 'FEMALE', 'OTHER', 'PREFER_NOT_TO_SAY'),
                nationality VARCHAR(100),
                language_preference VARCHAR(10) DEFAULT 'id',
                timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
                status ENUM('ACTIVE', 'INACTIVE', 'SUSPENDED', 'PENDING_ACTIVATION') DEFAULT 'PENDING_ACTIVATION',
                email_verified BOOLEAN DEFAULT FALSE,
                email_verified_at TIMESTAMP NULL,
                last_login_at TIMESTAMP NULL,
                last_login_ip VARCHAR(45),
                failed_login_attempts INT DEFAULT 0,
                locked_until TIMESTAMP NULL,
                password_changed_at TIMESTAMP NULL,
                must_change_password BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
                FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL,
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_branch_id (branch_id),
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create roles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                role_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                role_name VARCHAR(100) NOT NULL,
                role_code VARCHAR(50) NOT NULL,
                description TEXT,
                is_system_role BOOLEAN DEFAULT FALSE,
                is_custom BOOLEAN DEFAULT FALSE,
                permissions JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                UNIQUE KEY uk_role_code_tenant (role_code, tenant_id),
                FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE,
                INDEX idx_tenant_id (tenant_id),
                INDEX idx_role_code (role_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create permissions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS permissions (
                permission_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                permission_code VARCHAR(100) NOT NULL UNIQUE,
                permission_name VARCHAR(255) NOT NULL,
                module VARCHAR(100),
                description TEXT,
                is_system_permission BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_permission_code (permission_code),
                INDEX idx_module (module)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create role_permissions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS role_permissions (
                role_permission_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                role_id BIGINT NOT NULL,
                permission_id BIGINT NOT NULL,
                granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                granted_by BIGINT,
                FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE,
                FOREIGN KEY (granted_by) REFERENCES users(user_id) ON DELETE SET NULL,
                UNIQUE KEY uk_role_permission (role_id, permission_id),
                INDEX idx_role_id (role_id),
                INDEX idx_permission_id (permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create user_roles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_roles (
                user_role_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                role_id BIGINT NOT NULL,
                branch_id BIGINT,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                assigned_by BIGINT,
                expires_at TIMESTAMP NULL,
                is_primary BOOLEAN DEFAULT FALSE,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
                FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL,
                FOREIGN KEY (assigned_by) REFERENCES users(user_id) ON DELETE SET NULL,
                UNIQUE KEY uk_user_role_branch (user_id, role_id, branch_id),
                INDEX idx_user_id (user_id),
                INDEX idx_role_id (role_id),
                INDEX idx_branch_id (branch_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS user_roles");
        $pdo->exec("DROP TABLE IF EXISTS role_permissions");
        $pdo->exec("DROP TABLE IF EXISTS permissions");
        $pdo->exec("DROP TABLE IF EXISTS roles");
        $pdo->exec("DROP TABLE IF EXISTS users");
    }
];
