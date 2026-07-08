-- Advanced Marketing Tables
-- Phase 2.5: Marketing Automation

-- Customer Segments Table
CREATE TABLE IF NOT EXISTS customer_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    segment_name VARCHAR(255) NOT NULL,
    segment_description TEXT,
    segment_type ENUM('SPENDING', 'FREQUENCY', 'LOYALTY', 'BEHAVIORAL', 'CUSTOM') NOT NULL,
    criteria JSON,
    status ENUM('ACTIVE', 'INACTIVE', 'ARCHIVED') DEFAULT 'ACTIVE',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    deleted_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_segment_type (segment_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Segment Members Table
CREATE TABLE IF NOT EXISTS segment_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    segment_id INT NOT NULL,
    customer_id INT NOT NULL,
    added_at DATETIME NOT NULL,
    removed_at DATETIME,
    INDEX idx_segment (segment_id),
    INDEX idx_customer (customer_id),
    INDEX idx_added_at (added_at),
    FOREIGN KEY (segment_id) REFERENCES customer_segments(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Campaigns Table
CREATE TABLE IF NOT EXISTS email_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    campaign_name VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255),
    content_html TEXT NOT NULL,
    content_text TEXT,
    segment_id INT,
    scheduled_date DATETIME,
    sent_at DATETIME,
    sent_by INT,
    status ENUM('DRAFT', 'SCHEDULED', 'SENDING', 'SENT', 'FAILED', 'CANCELLED') DEFAULT 'DRAFT',
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_by INT,
    updated_at DATETIME,
    INDEX idx_tenant (tenant_id),
    INDEX idx_segment (segment_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (segment_id) REFERENCES customer_segments(id) ON DELETE SET NULL,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Logs Table
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    customer_id INT,
    email VARCHAR(255) NOT NULL,
    to_name VARCHAR(255),
    status ENUM('SENT', 'DELIVERED', 'OPENED', 'CLICKED', 'BOUNCED', 'FAILED') DEFAULT 'SENT',
    opened_at DATETIME,
    clicked_at DATETIME,
    sent_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_campaign (campaign_id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at),
    FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
