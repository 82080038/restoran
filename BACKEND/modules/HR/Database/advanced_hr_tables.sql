-- Advanced HR Tables
-- Phase 2.4: Advanced HR

-- Multi-Location Schedules Table
CREATE TABLE IF NOT EXISTS multi_location_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    schedule_name VARCHAR(255) NOT NULL,
    schedule_type ENUM('SINGLE_LOCATION', 'MULTI_LOCATION', 'ROTATION') DEFAULT 'MULTI_LOCATION',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('DRAFT', 'PUBLISHED', 'ACTIVE', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_schedule_dates (start_date, end_date),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schedule Assignments Table
CREATE TABLE IF NOT EXISTS schedule_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    employee_id INT NOT NULL,
    shift_id INT,
    assigned_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration INT DEFAULT 0,
    total_hours DECIMAL(5, 2) GENERATED ALWAYS AS (TIMESTAMPDIFF(MINUTE, start_time, end_time) - break_duration) / 60 STORED,
    overtime_hours DECIMAL(5, 2) DEFAULT 0,
    hourly_rate DECIMAL(10, 2),
    regular_cost DECIMAL(10, 2) GENERATED ALWAYS AS (total_hours * hourly_rate) STORED,
    overtime_cost DECIMAL(10, 2) GENERATED ALWAYS AS (overtime_hours * hourly_rate * 1.5) STORED,
    total_cost DECIMAL(10, 2) GENERATED ALWAYS AS (regular_cost + overtime_cost) STORED,
    status ENUM('SCHEDULED', 'CHECKED_IN', 'CHECKED_OUT', 'ABSENT', 'LATE') DEFAULT 'SCHEDULED',
    notes TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_schedule (schedule_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_employee (employee_id),
    INDEX idx_assigned_date (assigned_date),
    INDEX idx_status (status),
    FOREIGN KEY (schedule_id) REFERENCES multi_location_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training Programs Table
CREATE TABLE IF NOT EXISTS training_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    training_name VARCHAR(255) NOT NULL,
    training_description TEXT,
    training_type ENUM('ONBOARDING', 'SKILL_DEVELOPMENT', 'SAFETY', 'COMPLIANCE', 'LEADERSHIP', 'CUSTOMER_SERVICE') NOT NULL,
    category VARCHAR(100),
    duration_hours DECIMAL(5, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    instructor VARCHAR(255),
    location VARCHAR(255),
    max_participants INT,
    status ENUM('PLANNED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'PLANNED',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_training_dates (start_date, end_date),
    INDEX idx_status (status),
    INDEX idx_category (category),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training Participants Table
CREATE TABLE IF NOT EXISTS training_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    employee_id INT NOT NULL,
    enrollment_status ENUM('ENROLLED', 'IN_PROGRESS', 'COMPLETED', 'DROPPED', 'FAILED') DEFAULT 'ENROLLED',
    enrollment_date DATE NOT NULL,
    completion_date DATE,
    completion_status ENUM('PASSED', 'FAILED', 'PENDING') DEFAULT 'PENDING',
    score DECIMAL(5, 2),
    feedback TEXT,
    certificate_issued BOOLEAN DEFAULT FALSE,
    certificate_url VARCHAR(255),
    completed_by INT,
    created_at DATETIME NOT NULL,
    INDEX idx_training (training_id),
    INDEX idx_employee (employee_id),
    INDEX idx_enrollment_status (enrollment_status),
    INDEX idx_completion_status (completion_status),
    FOREIGN KEY (training_id) REFERENCES training_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
