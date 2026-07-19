-- ============================================================
-- MIGRATION: Gap Feature Tables (7 new features)
-- Date: 2026-07-19
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- SCAN ID / VERIFIKASI USAIA (Nightclub)
-- ============================================================
CREATE TABLE IF NOT EXISTS id_scans (
  scan_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  guest_name VARCHAR(200),
  id_type VARCHAR(30) DEFAULT 'KTP',
  id_number VARCHAR(100),
  date_of_birth DATE,
  age_calculated INT,
  is_over_21 TINYINT(1) DEFAULT 0,
  is_over_18 TINYINT(1) DEFAULT 0,
  scan_result VARCHAR(20) DEFAULT 'APPROVED',
  rejection_reason VARCHAR(200),
  scanned_by VARCHAR(100),
  scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  photo_path VARCHAR(500)
);

-- ============================================================
-- COGS MINUMAN KONSOLIDASI (Sports Bar)
-- ============================================================
CREATE TABLE IF NOT EXISTS beverage_cogs (
  cogs_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  report_date DATE NOT NULL,
  beverage_category VARCHAR(50),
  product_id BIGINT,
  product_name VARCHAR(200),
  unit_type VARCHAR(30),
  opening_qty DECIMAL(10,2) DEFAULT 0,
  received_qty DECIMAL(10,2) DEFAULT 0,
  sold_qty DECIMAL(10,2) DEFAULT 0,
  closing_qty DECIMAL(10,2) DEFAULT 0,
  unit_cost DECIMAL(15,2) DEFAULT 0,
  total_cost DECIMAL(15,2) DEFAULT 0,
  revenue DECIMAL(15,2) DEFAULT 0,
  pour_cost_pct DECIMAL(5,2) DEFAULT 0,
  variance_qty DECIMAL(10,2) DEFAULT 0,
  variance_value DECIMAL(15,2) DEFAULT 0
);

-- ============================================================
-- KONTRAK E-SIGNATURE (Catering)
-- ============================================================
CREATE TABLE IF NOT EXISTS e_signatures (
  signature_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  contract_id BIGINT,
  document_type VARCHAR(50) DEFAULT 'CATERING_CONTRACT',
  document_title VARCHAR(200),
  document_content LONGTEXT,
  document_hash VARCHAR(64),
  signer_name VARCHAR(200),
  signer_email VARCHAR(100),
  signer_role VARCHAR(50),
  signature_data TEXT,
  signature_ip VARCHAR(45),
  signed_at TIMESTAMP NULL,
  status VARCHAR(20) DEFAULT 'PENDING',
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- LANGGANAN MAKAN KORPORAT BERULANG (Catering)
-- ============================================================
CREATE TABLE IF NOT EXISTS corporate_meal_subscriptions (
  subscription_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  company_name VARCHAR(200),
  contact_person VARCHAR(200),
  contact_phone VARCHAR(50),
  contact_email VARCHAR(100),
  billing_address TEXT,
  meal_plan VARCHAR(50) DEFAULT 'DAILY_LUNCH',
  head_count INT DEFAULT 10,
  delivery_address TEXT,
  delivery_time TIME,
  frequency VARCHAR(20) DEFAULT 'WEEKLY',
  days_of_week VARCHAR(20),
  price_per_head DECIMAL(15,2) DEFAULT 0,
  monthly_total DECIMAL(15,2) DEFAULT 0,
  start_date DATE,
  end_date DATE,
  auto_renew TINYINT(1) DEFAULT 1,
  status VARCHAR(20) DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS corporate_meal_deliveries (
  delivery_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  subscription_id BIGINT NOT NULL,
  tenant_id BIGINT NOT NULL,
  delivery_date DATE NOT NULL,
  head_count_served INT,
  total_amount DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(20) DEFAULT 'SCHEDULED',
  delivered_at TIMESTAMP NULL,
  notes TEXT
);

-- ============================================================
-- DRIVE-THRU INTEGRATION (Fast Food)
-- ============================================================
CREATE TABLE IF NOT EXISTS drive_thru_sessions (
  session_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  lane_number INT DEFAULT 1,
  vehicle_description VARCHAR(200),
  order_id BIGINT,
  detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  greeted_at TIMESTAMP NULL,
  order_taken_at TIMESTAMP NULL,
  payment_at TIMESTAMP NULL,
  pickup_at TIMESTAMP NULL,
  total_wait_seconds INT,
  status VARCHAR(20) DEFAULT 'DETECTED',
  order_total DECIMAL(15,2) DEFAULT 0
);

-- ============================================================
-- TASTING MENU (Fine Dining)
-- ============================================================
CREATE TABLE IF NOT EXISTS tasting_menus (
  tasting_menu_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  menu_name VARCHAR(200),
  description TEXT,
  price_per_cover DECIMAL(15,2) DEFAULT 0,
  course_count INT DEFAULT 5,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS tasting_menu_courses (
  course_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tasting_menu_id BIGINT NOT NULL,
  course_number INT NOT NULL,
  course_name VARCHAR(200),
  course_description TEXT,
  product_id BIGINT,
  pairing_beverage VARCHAR(200),
  prep_time_minutes INT DEFAULT 10,
  is_optional TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS tasting_menu_reservations (
  reservation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  tasting_menu_id BIGINT NOT NULL,
  customer_name VARCHAR(200),
  phone VARCHAR(50),
  party_size INT DEFAULT 2,
  reservation_date DATE,
  reservation_time TIME,
  table_id BIGINT,
  total_amount DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(20) DEFAULT 'PENDING',
  special_requests TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- DEPOSIT RESERVASI (Fine Dining)
-- ============================================================
CREATE TABLE IF NOT EXISTS reservation_deposits (
  deposit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  reservation_id BIGINT,
  customer_name VARCHAR(200),
  phone VARCHAR(50),
  party_size INT,
  reservation_date DATE,
  reservation_time TIME,
  deposit_amount DECIMAL(15,2) DEFAULT 0,
  deposit_type VARCHAR(30) DEFAULT 'PER_PERSON',
  payment_method VARCHAR(50),
  payment_ref VARCHAR(200),
  paid_at TIMESTAMP NULL,
  forfeited_at TIMESTAMP NULL,
  refunded_at TIMESTAMP NULL,
  no_show_cutoff TIMESTAMP NULL,
  status VARCHAR(20) DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Gap feature tables created successfully!' AS message;
