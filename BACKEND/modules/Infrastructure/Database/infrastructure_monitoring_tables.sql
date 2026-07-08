-- Infrastructure Monitoring Tables
-- Phase 3.6: Infrastructure Scaling

-- Performance Metrics Table
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    server_id VARCHAR(100) DEFAULT 'default',
    cpu_usage_percent DECIMAL(5, 2),
    memory_usage_percent DECIMAL(5, 2),
    disk_usage_percent DECIMAL(5, 2),
    network_in_bytes BIGINT DEFAULT 0,
    network_out_bytes BIGINT DEFAULT 0,
    active_connections INT DEFAULT 0,
    request_rate_per_second DECIMAL(10, 2) DEFAULT 0,
    response_time_avg_ms DECIMAL(10, 2),
    error_rate_percent DECIMAL(5, 2) DEFAULT 0,
    cache_hit_rate_percent DECIMAL(5, 2) DEFAULT 0,
    database_connections INT DEFAULT 0,
    database_query_time_avg_ms DECIMAL(10, 2),
    recorded_at DATETIME NOT NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_server (server_id),
    INDEX idx_recorded_at (recorded_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Infrastructure Alerts Table
CREATE TABLE IF NOT EXISTS infrastructure_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    alert_type VARCHAR(50) NOT NULL,
    severity ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
    message TEXT NOT NULL,
    status ENUM('OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN',
    resolved_by INT,
    resolved_at DATETIME,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cache Configuration Table
CREATE TABLE IF NOT EXISTS cache_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    cache_type ENUM('REDIS', 'MEMCACHED', 'APC', 'OPCACHE', 'FILE') NOT NULL,
    cache_key_prefix VARCHAR(50),
    ttl_seconds INT DEFAULT 3600,
    enabled BOOLEAN DEFAULT TRUE,
    config JSON,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_cache_type (cache_type),
    INDEX idx_enabled (enabled),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CDN Configuration Table
CREATE TABLE IF NOT EXISTS cdn_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    distribution_domain VARCHAR(255),
    enabled BOOLEAN DEFAULT TRUE,
    cache_rules JSON,
    ssl_certificate BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_provider (provider),
    INDEX idx_enabled (enabled),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Load Balancer Configuration Table
CREATE TABLE IF NOT EXISTS load_balancer_configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    algorithm ENUM('ROUND_ROBIN', 'LEAST_CONNECTIONS', 'IP_HASH', 'WEIGHTED') DEFAULT 'ROUND_ROBIN',
    health_check_enabled BOOLEAN DEFAULT TRUE,
    health_check_interval INT DEFAULT 30,
    health_check_path VARCHAR(255) DEFAULT '/health',
    sticky_session BOOLEAN DEFAULT FALSE,
    session_ttl INT DEFAULT 3600,
    backend_servers JSON,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_algorithm (algorithm),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
