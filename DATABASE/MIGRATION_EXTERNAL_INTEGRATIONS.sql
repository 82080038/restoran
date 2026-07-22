-- ============================================================
-- MIGRATION: External Integration Tables
-- Date: 2026-07-22
-- Replaces placeholder ExternalIntegrationService with real DB-backed implementations
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- E-WALLET / QRIS PAYMENT PROVIDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS ewallet_providers (
  provider_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  code VARCHAR(30) NOT NULL,
  name VARCHAR(100) NOT NULL,
  status VARCHAR(20) DEFAULT 'ACTIVE',
  fee_pct DECIMAL(5,2) DEFAULT 0,
  api_key VARCHAR(500),
  api_secret VARCHAR(500),
  merchant_id VARCHAR(200),
  callback_url VARCHAR(500),
  is_sandbox TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  UNIQUE KEY uk_tenant_code (tenant_id, code)
);

-- ============================================================
-- QRIS / E-WALLET PAYMENT TRANSACTIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS ewallet_payments (
  payment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  order_id BIGINT,
  provider VARCHAR(30) NOT NULL,
  provider_ref VARCHAR(200),
  amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  fee_amount DECIMAL(15,2) DEFAULT 0,
  net_amount DECIMAL(15,2) DEFAULT 0,
  qr_string TEXT,
  status VARCHAR(20) DEFAULT 'PENDING',
  expires_at TIMESTAMP NULL,
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  INDEX idx_tenant_status (tenant_id, status),
  INDEX idx_provider_ref (provider_ref)
);

-- ============================================================
-- TICKETING PLATFORM SYNC LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS ticketing_sync_log (
  sync_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  platform VARCHAR(50) NOT NULL,
  event_id VARCHAR(200),
  tickets_synced INT DEFAULT 0,
  status VARCHAR(20) DEFAULT 'SYNCED',
  error_message TEXT,
  synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  INDEX idx_tenant_platform (tenant_id, platform)
);

-- ============================================================
-- LINE BUSTING / MOBILE POS SESSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS line_bust_sessions (
  session_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  device_id VARCHAR(200),
  staff_user_id BIGINT,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  ended_at TIMESTAMP NULL,
  orders_taken INT DEFAULT 0,
  status VARCHAR(20) DEFAULT 'ACTIVE',
  INDEX idx_tenant_date (tenant_id, started_at)
);

-- ============================================================
-- OFFLINE SYNC QUEUE (for ExternalIntegrationService offline endpoints)
-- ============================================================
CREATE TABLE IF NOT EXISTS external_offline_queue (
  queue_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  device_id VARCHAR(200),
  transaction_data LONGTEXT,
  transaction_type VARCHAR(50),
  status VARCHAR(20) DEFAULT 'PENDING',
  error_message TEXT,
  synced_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  INDEX idx_tenant_status (tenant_id, status)
);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'External integration tables created successfully!' AS message;
