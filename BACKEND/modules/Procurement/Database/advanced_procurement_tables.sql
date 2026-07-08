-- Advanced Procurement Tables
-- Phase 2.2: Advanced Procurement

-- Purchase Plans Table
CREATE TABLE IF NOT EXISTS purchase_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    plan_name VARCHAR(255) NOT NULL,
    plan_type ENUM('MANUAL', 'AUTO_GENERATED', 'AI_OPTIMIZED') DEFAULT 'MANUAL',
    planning_period_start DATE NOT NULL,
    planning_period_end DATE NOT NULL,
    status ENUM('DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'COMPLETED') DEFAULT 'DRAFT',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_planning_period (planning_period_start, planning_period_end),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Plan Items Table
CREATE TABLE IF NOT EXISTS purchase_plan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_plan_id INT NOT NULL,
    inventory_item_id INT NOT NULL,
    supplier_id INT,
    current_stock DECIMAL(10, 2) NOT NULL,
    minimum_stock DECIMAL(10, 2) NOT NULL,
    forecast_demand DECIMAL(10, 2) NOT NULL,
    safety_stock DECIMAL(10, 2) NOT NULL,
    suggested_quantity DECIMAL(10, 2) NOT NULL,
    estimated_cost DECIMAL(15, 2) NOT NULL,
    priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_purchase_plan (purchase_plan_id),
    INDEX idx_inventory_item (inventory_item_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_priority (priority),
    FOREIGN KEY (purchase_plan_id) REFERENCES purchase_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Three-Way Matches Table
CREATE TABLE IF NOT EXISTS three_way_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    purchase_order_id INT NOT NULL,
    goods_receipt_id INT NOT NULL,
    invoice_id INT NOT NULL,
    match_status ENUM('MATCHED', 'VARIANCE_DETECTED', 'REQUIRES_REVIEW', 'REJECTED') DEFAULT 'MATCHED',
    variance_amount DECIMAL(15, 2) DEFAULT 0,
    variance_percentage DECIMAL(5, 2) DEFAULT 0,
    match_details JSON,
    matched_by INT NOT NULL,
    matched_at DATETIME NOT NULL,
    approved_by INT,
    approved_at DATETIME,
    notes TEXT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_purchase_order (purchase_order_id),
    INDEX idx_goods_receipt (goods_receipt_id),
    INDEX idx_invoice (invoice_id),
    INDEX idx_match_status (match_status),
    INDEX idx_matched_at (matched_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES supplier_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (matched_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Supplier Invoices Table (if not exists)
CREATE TABLE IF NOT EXISTS supplier_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    supplier_id INT NOT NULL,
    invoice_number VARCHAR(100) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(15, 2) NOT NULL,
    tax_amount DECIMAL(15, 2) DEFAULT 0,
    discount_amount DECIMAL(15, 2) DEFAULT 0,
    total_amount DECIMAL(15, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    status ENUM('DRAFT', 'PENDING', 'APPROVED', 'PAID', 'PARTIALLY_PAID', 'OVERDUE', 'CANCELLED') DEFAULT 'PENDING',
    payment_terms VARCHAR(255),
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice Items Table
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    inventory_item_id INT NOT NULL,
    description VARCHAR(255),
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    line_total DECIMAL(15, 2) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_invoice (invoice_id),
    INDEX idx_inventory_item (inventory_item_id),
    FOREIGN KEY (invoice_id) REFERENCES supplier_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
