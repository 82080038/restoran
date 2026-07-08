-- Central Kitchen Management Tables
-- Phase 2.1: Central Kitchen Management

-- Production Plans Table
CREATE TABLE IF NOT EXISTS production_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    plan_name VARCHAR(255) NOT NULL,
    plan_description TEXT,
    production_date DATE NOT NULL,
    status ENUM('DRAFT', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_production_date (production_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Production Plan Items Table
CREATE TABLE IF NOT EXISTS production_plan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    production_plan_id INT NOT NULL,
    recipe_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_production_plan (production_plan_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Production Yields Table
CREATE TABLE IF NOT EXISTS production_yields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    production_plan_id INT,
    recipe_id INT NOT NULL,
    planned_quantity DECIMAL(10, 2) NOT NULL,
    actual_quantity DECIMAL(10, 2) NOT NULL,
    yield_percentage DECIMAL(5, 2) NOT NULL,
    waste_quantity DECIMAL(10, 2) DEFAULT 0,
    variance_reason TEXT,
    recorded_by INT NOT NULL,
    recorded_at DATETIME NOT NULL,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_production_plan (production_plan_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_recorded_at (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Distribution Orders Table
CREATE TABLE IF NOT EXISTS distribution_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    source_branch_id INT NOT NULL,
    destination_branch_id INT NOT NULL,
    distribution_date DATE NOT NULL,
    status ENUM('PENDING', 'CONFIRMED', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED') DEFAULT 'PENDING',
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    shipped_at DATETIME,
    delivered_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_source_branch (source_branch_id),
    INDEX idx_destination_branch (destination_branch_id),
    INDEX idx_distribution_date (distribution_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Distribution Items Table
CREATE TABLE IF NOT EXISTS distribution_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    distribution_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_distribution_order (distribution_order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

