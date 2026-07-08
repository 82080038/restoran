<?php

/**
 * Migration 010: Create Staff Management Tables
 * 
 * Creates tables for staff management including employees,
 * employee skills, attendance, and schedules
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create employees table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS employees (
                employee_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                employee_code VARCHAR(50) UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100),
                email VARCHAR(255),
                phone VARCHAR(20),
                date_of_birth DATE,
                gender VARCHAR(10),
                address TEXT,
                position VARCHAR(100),
                department VARCHAR(100),
                hire_date DATE NOT NULL,
                termination_date DATE,
                employment_type VARCHAR(20) DEFAULT 'FULL_TIME',
                hourly_rate DECIMAL(18,2),
                salary DECIMAL(18,2),
                max_hours_per_week INT DEFAULT 40,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                profile_image_url VARCHAR(500),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status),
                INDEX idx_position (position)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create skills table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS skills (
                skill_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                skill_name VARCHAR(100) NOT NULL,
                description TEXT,
                category VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create employee_skills table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS employee_skills (
                employee_skill_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                employee_id BIGINT NOT NULL,
                skill_id BIGINT NOT NULL,
                proficiency_level VARCHAR(20) DEFAULT 'INTERMEDIATE',
                certified BOOLEAN DEFAULT FALSE,
                certification_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
                FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE,
                UNIQUE KEY uk_employee_skill (employee_id, skill_id),
                INDEX idx_employee (employee_id),
                INDEX idx_skill (skill_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create attendance table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS attendance (
                attendance_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                employee_id BIGINT NOT NULL,
                work_date DATE NOT NULL,
                check_in_time TIME,
                check_out_time TIME,
                hours_worked DECIMAL(5,2),
                overtime_hours DECIMAL(5,2) DEFAULT 0,
                break_minutes INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'PRESENT',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_employee_date (employee_id, work_date),
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_employee (employee_id),
                INDEX idx_date (work_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create schedules table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS schedules (
                schedule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                employee_id BIGINT NOT NULL,
                shift_date DATE NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                shift_name VARCHAR(50),
                status VARCHAR(20) DEFAULT 'SCHEDULED',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_employee (employee_id),
                INDEX idx_date (shift_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create employee_availability table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS employee_availability (
                availability_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                employee_id BIGINT NOT NULL,
                day_of_week INT NOT NULL,
                is_available BOOLEAN DEFAULT TRUE,
                preferred_start_time TIME,
                preferred_end_time TIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_employee_day (employee_id, day_of_week),
                INDEX idx_employee (employee_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS employee_availability");
        $pdo->exec("DROP TABLE IF EXISTS schedules");
        $pdo->exec("DROP TABLE IF EXISTS attendance");
        $pdo->exec("DROP TABLE IF EXISTS employee_skills");
        $pdo->exec("DROP TABLE IF EXISTS skills");
        $pdo->exec("DROP TABLE IF EXISTS employees");
    }
];
