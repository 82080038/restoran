-- Advanced Franchise Tables
-- Phase 3.4: Franchise Management

-- Brand Compliance Checklists Table
CREATE TABLE IF NOT EXISTS brand_compliance_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    checklist_name VARCHAR(255) NOT NULL,
    checklist_type ENUM('OPERATIONAL', 'MARKETING', 'FOOD_SAFETY', 'BRAND_STANDARDS', 'FINANCIAL') NOT NULL,
    frequency ENUM('DAILY', 'WEEKLY', 'MONTHLY', 'QUARTERLY', 'ANNUALLY', 'ON_DEMAND') NOT NULL,
    status ENUM('ACTIVE', 'INACTIVE', 'ARCHIVED') DEFAULT 'ACTIVE',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_checklist_type (checklist_type),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Brand Compliance Items Table
CREATE TABLE IF NOT EXISTS brand_compliance_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_description TEXT,
    required BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL,
    INDEX idx_checklist (checklist_id),
    INDEX idx_required (required),
    FOREIGN KEY (checklist_id) REFERENCES brand_compliance_checklists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Brand Compliance Audits Table
CREATE TABLE IF NOT EXISTS brand_compliance_audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT,
    franchisee_id INT,
    checklist_id INT NOT NULL,
    audit_date DATE NOT NULL,
    auditor_id INT NOT NULL,
    overall_score DECIMAL(5, 2),
    compliance_status ENUM('COMPLIANT', 'PARTIALLY_COMPLIANT', 'NON_COMPLIANT') NOT NULL,
    findings JSON,
    recommendations JSON,
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_franchisee (franchisee_id),
    INDEX idx_checklist (checklist_id),
    INDEX idx_audit_date (audit_date),
    INDEX idx_compliance_status (compliance_status),
    INDEX idx_follow_up (follow_up_required, follow_up_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (franchisee_id) REFERENCES franchisees(id) ON DELETE SET NULL,
    FOREIGN KEY (checklist_id) REFERENCES brand_compliance_checklists(id) ON DELETE CASCADE,
    FOREIGN KEY (auditor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Brand Compliance Audit Results Table
CREATE TABLE IF NOT EXISTS brand_compliance_audit_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_id INT NOT NULL,
    checklist_item_id INT NOT NULL,
    passed BOOLEAN NOT NULL,
    notes TEXT,
    evidence_url VARCHAR(500),
    created_at DATETIME NOT NULL,
    INDEX idx_audit (audit_id),
    INDEX idx_checklist_item (checklist_item_id),
    INDEX idx_passed (passed),
    FOREIGN KEY (audit_id) REFERENCES brand_compliance_audits(id) ON DELETE CASCADE,
    FOREIGN KEY (checklist_item_id) REFERENCES brand_compliance_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Franchise Performance Tracking Table
CREATE TABLE IF NOT EXISTS franchise_performance_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    franchisee_id INT NOT NULL,
    reporting_period_start DATE NOT NULL,
    reporting_period_end DATE NOT NULL,
    sales_growth DECIMAL(10, 2),
    customer_satisfaction DECIMAL(5, 2),
    operational_efficiency DECIMAL(5, 2),
    brand_compliance_score DECIMAL(5, 2),
    revenue_per_sq_ft DECIMAL(10, 2),
    labor_cost_percentage DECIMAL(5, 2),
    food_cost_percentage DECIMAL(5, 2),
    net_profit_margin DECIMAL(5, 2),
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_franchisee (tenant_id, franchisee_id),
    INDEX idx_reporting_period (reporting_period_start, reporting_period_end),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (franchisee_id) REFERENCES franchisees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
