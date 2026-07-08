-- API Marketplace Tables
-- Phase 3.5: API Marketplace

-- API Keys Table
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    key_name VARCHAR(255) NOT NULL,
    api_key_hash VARCHAR(255) NOT NULL,
    api_key_prefix VARCHAR(20) NOT NULL,
    permissions JSON,
    rate_limit_per_minute INT DEFAULT 60,
    rate_limit_per_hour INT DEFAULT 1000,
    ip_whitelist JSON,
    expiry_date DATETIME,
    status ENUM('ACTIVE', 'INACTIVE', 'REVOKED', 'EXPIRED') DEFAULT 'ACTIVE',
    last_used_at DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    revoked_by INT,
    revoked_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_api_key_prefix (api_key_prefix),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Usage Logs Table
CREATE TABLE IF NOT EXISTS api_usage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    api_key_id INT NOT NULL,
    endpoint VARCHAR(500) NOT NULL,
    http_method VARCHAR(10) NOT NULL,
    status_code INT NOT NULL,
    response_time_ms INT,
    request_size INT,
    response_size INT,
    user_agent VARCHAR(500),
    ip_address VARCHAR(45),
    created_at DATETIME NOT NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_api_key (api_key_id),
    INDEX idx_endpoint (endpoint),
    INDEX idx_status_code (status_code),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhooks Table
CREATE TABLE IF NOT EXISTS webhooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    webhook_name VARCHAR(255) NOT NULL,
    webhook_url VARCHAR(500) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    headers JSON,
    secret VARCHAR(255) NOT NULL,
    retry_on_failure BOOLEAN DEFAULT TRUE,
    max_retries INT DEFAULT 3,
    status ENUM('ACTIVE', 'INACTIVE', 'DISABLED') DEFAULT 'ACTIVE',
    last_triggered_at DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_event_type (event_type),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Logs Table
CREATE TABLE IF NOT EXISTS webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    webhook_id INT NOT NULL,
    event_data JSON,
    response_code INT,
    response_body TEXT,
    attempt_number INT DEFAULT 1,
    status ENUM('PENDING', 'SUCCESS', 'FAILED', 'RETRYING') DEFAULT 'PENDING',
    error_message TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_webhook (webhook_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Third-Party Integrations Table
CREATE TABLE IF NOT EXISTS third_party_integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    integration_name VARCHAR(255) NOT NULL,
    integration_type ENUM('PAYMENT', 'DELIVERY', 'MARKETING', 'ANALYTICS', 'ACCOUNTING', 'INVENTORY', 'CUSTOM') NOT NULL,
    provider VARCHAR(100) NOT NULL,
    config JSON,
    status ENUM('ACTIVE', 'INACTIVE', 'ERROR', 'MAINTENANCE') DEFAULT 'ACTIVE',
    last_sync_at DATETIME,
    sync_frequency VARCHAR(50),
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_integration_type (integration_type),
    INDEX idx_provider (provider),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
