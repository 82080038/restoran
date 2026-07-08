-- Quality Control Tables
-- Phase 3.3: Quality Control

-- Quality Checks Table
CREATE TABLE IF NOT EXISTS quality_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    check_type ENUM('INCOMING', 'IN_PROCESS', 'OUTGOING', 'ROUTINE', 'SPECIAL') NOT NULL,
    check_category ENUM('FOOD_SAFETY', 'HYGIENE', 'TEMPERATURE', 'APPEARANCE', 'TASTE', 'PACKAGING', 'LABELING') NOT NULL,
    item_id INT,
    item_name VARCHAR(255),
    inspection_criteria JSON,
    result ENUM('PASSED', 'FAILED', 'CONDITIONAL') NOT NULL,
    notes TEXT,
    inspector_id INT NOT NULL,
    inspection_date DATETIME NOT NULL,
    status ENUM('SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'COMPLETED',
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_check_type (check_type),
    INDEX idx_check_category (check_category),
    INDEX idx_inspection_date (inspection_date),
    INDEX idx_result (result),
    INDEX idx_item (item_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (inspector_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quality Check Details Table
CREATE TABLE IF NOT EXISTS quality_check_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quality_check_id INT NOT NULL,
    criterion_name VARCHAR(255) NOT NULL,
    expected_value VARCHAR(255),
    actual_value VARCHAR(255),
    passed BOOLEAN NOT NULL,
    notes TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_quality_check (quality_check_id),
    INDEX idx_passed (passed),
    FOREIGN KEY (quality_check_id) REFERENCES quality_checks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Non-Conformances Table
CREATE TABLE IF NOT EXISTS non_conformances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    quality_check_id INT,
    severity ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
    description TEXT NOT NULL,
    root_cause TEXT,
    corrective_action TEXT,
    preventive_action TEXT,
    responsible_person VARCHAR(255),
    target_date DATE,
    status ENUM('OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED', 'ESCALATED') DEFAULT 'OPEN',
    reported_by INT NOT NULL,
    resolved_by INT,
    resolved_at DATETIME,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_quality_check (quality_check_id),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_target_date (target_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (quality_check_id) REFERENCES quality_checks(id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quality Metrics Table
CREATE TABLE IF NOT EXISTS quality_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    metric_date DATE NOT NULL,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(10, 2) NOT NULL,
    target_value DECIMAL(10, 2),
    variance DECIMAL(10, 2),
    notes TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    UNIQUE KEY uk_tenant_branch_date_type (tenant_id, branch_id, metric_date, metric_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
