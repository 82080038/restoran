-- HACCP Compliance Tables
-- Phase 3.2: HACCP Compliance

-- HACCP Critical Control Points Table
CREATE TABLE IF NOT EXISTS haccp_ccps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    ccp_name VARCHAR(255) NOT NULL,
    process_step VARCHAR(255) NOT NULL,
    hazard_type ENUM('BIOLOGICAL', 'CHEMICAL', 'PHYSICAL', 'ALLERGEN') NOT NULL,
    hazard_description TEXT,
    critical_limit VARCHAR(500) NOT NULL,
    monitoring_procedure TEXT NOT NULL,
    corrective_action TEXT NOT NULL,
    monitoring_frequency ENUM('HOURLY', 'SHIFT', 'DAILY', 'WEEKLY', 'MONTHLY', 'PER_BATCH') NOT NULL,
    responsible_person VARCHAR(255),
    status ENUM('ACTIVE', 'INACTIVE', 'REVIEW_REQUIRED') DEFAULT 'ACTIVE',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_process_step (process_step),
    INDEX idx_hazard_type (hazard_type),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HACCP Monitoring Table
CREATE TABLE IF NOT EXISTS haccp_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    ccp_id INT NOT NULL,
    monitoring_date DATETIME NOT NULL,
    monitoring_time TIME NOT NULL,
    actual_value VARCHAR(255) NOT NULL,
    within_limits BOOLEAN NOT NULL,
    monitoring_result TEXT,
    corrective_action_taken TEXT,
    monitoring_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_ccp (ccp_id),
    INDEX idx_monitoring_date (monitoring_date),
    INDEX idx_within_limits (within_limits),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (ccp_id) REFERENCES haccp_ccps(id) ON DELETE CASCADE,
    FOREIGN KEY (monitoring_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HACCP Alerts Table
CREATE TABLE IF NOT EXISTS haccp_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    ccp_id INT NOT NULL,
    monitoring_id INT,
    alert_type ENUM('LIMIT_VIOLATION', 'MONITORING_OVERDUE', 'CORRECTIVE_ACTION_REQUIRED', 'DOCUMENTATION_MISSING') NOT NULL,
    alert_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
    alert_message TEXT,
    status ENUM('OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN',
    resolved_by INT,
    resolved_at DATETIME,
    resolution_notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_ccp (ccp_id),
    INDEX idx_status (status),
    INDEX idx_alert_level (alert_level),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (ccp_id) REFERENCES haccp_ccps(id) ON DELETE CASCADE,
    FOREIGN KEY (monitoring_id) REFERENCES haccp_monitoring(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HACCP Documents Table
CREATE TABLE IF NOT EXISTS haccp_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    document_type ENUM('PLAN', 'PROCEDURE', 'RECORD', 'TRAINING', 'CERTIFICATION', 'AUDIT_REPORT') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_description TEXT,
    file_path VARCHAR(500),
    file_size INT,
    file_type VARCHAR(50),
    expiry_date DATE,
    status ENUM('DRAFT', 'APPROVED', 'EXPIRED', 'ARCHIVED') DEFAULT 'DRAFT',
    approved_by INT,
    approved_at DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_document_type (document_type),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
