-- Multi-Branch Operations Tables
-- Phase 2.3: Multi-Branch Operations

-- Purchase Branch Allocations Table
CREATE TABLE IF NOT EXISTS purchase_branch_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id INT NOT NULL,
    item_id INT NOT NULL,
    branch_id INT NOT NULL,
    allocated_quantity DECIMAL(10, 2) NOT NULL,
    received_quantity DECIMAL(10, 2) DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_purchase_order (purchase_order_id),
    INDEX idx_item (item_id),
    INDEX idx_branch (branch_id),
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Price History Table
CREATE TABLE IF NOT EXISTS price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    product_id INT NOT NULL,
    old_price DECIMAL(10, 2) NOT NULL,
    new_price DECIMAL(10, 2) NOT NULL,
    effective_date DATE NOT NULL,
    change_reason VARCHAR(255),
    changed_by INT NOT NULL,
    changed_at DATETIME NOT NULL,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_product (product_id),
    INDEX idx_effective_date (effective_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Branch Performance Cache Table
CREATE TABLE IF NOT EXISTS branch_performance_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    report_date DATE NOT NULL,
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(15, 2) DEFAULT 0,
    average_order_value DECIMAL(10, 2) DEFAULT 0,
    unique_customers INT DEFAULT 0,
    total_customers INT DEFAULT 0,
    staff_count INT DEFAULT 0,
    inventory_turnover DECIMAL(5, 2) DEFAULT 0,
    food_cost_percentage DECIMAL(5, 2) DEFAULT 0,
    labor_cost_percentage DECIMAL(5, 2) DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_branch_date (tenant_id, branch_id, report_date),
    INDEX idx_report_date (report_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    UNIQUE KEY uk_branch_date (tenant_id, branch_id, report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
