-- Multi-Currency Tables
-- Phase 3.1: Multi-currency Support

-- Exchange Rates Table
CREATE TABLE IF NOT EXISTS exchange_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    currency_code VARCHAR(3) NOT NULL,
    exchange_rate DECIMAL(15, 8) NOT NULL,
    base_currency VARCHAR(3) DEFAULT 'USD',
    rate_source ENUM('MANUAL', 'AUTO_API', 'CENTRAL_BANK', 'FIXER', 'XE') DEFAULT 'MANUAL',
    effective_date DATE NOT NULL,
    updated_by INT NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_currency (tenant_id, currency_code),
    INDEX idx_effective_date (effective_date),
    INDEX idx_base_currency (base_currency),
    UNIQUE KEY uk_tenant_currency_date (tenant_id, currency_code, effective_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Prices Table (Multi-currency)
CREATE TABLE IF NOT EXISTS product_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    product_id INT NOT NULL,
    currency_code VARCHAR(3) NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    effective_date DATE NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_product (tenant_id, product_id),
    INDEX idx_currency (currency_code),
    INDEX idx_effective_date (effective_date),
    UNIQUE KEY uk_product_currency_date (tenant_id, product_id, currency_code, effective_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currency Conversion Log Table
CREATE TABLE IF NOT EXISTS currency_conversion_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    converted_amount DECIMAL(15, 2) NOT NULL,
    exchange_rate_used DECIMAL(15, 8) NOT NULL,
    conversion_date DATE NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_conversion_date (conversion_date),
    INDEX idx_from_to_currency (from_currency, to_currency),
    INDEX idx_reference (reference_type, reference_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Branch Currency Settings Table
CREATE TABLE IF NOT EXISTS branch_currency_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    branch_id INT NOT NULL,
    default_currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    accepted_currencies JSON,
    auto_convert_to_default BOOLEAN DEFAULT FALSE,
    rounding_rule ENUM('ROUND_HALF_UP', 'ROUND_HALF_DOWN', 'ROUND_UP', 'ROUND_DOWN', 'ROUND_ZERO', 'ROUND_BANKERS') DEFAULT 'ROUND_HALF_UP',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_default_currency (default_currency),
    UNIQUE KEY uk_tenant_branch (tenant_id, branch_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
