-- ============================================================
-- MIGRATION: Tier 1-4 Feature Tables
-- Generated for RESTAURANT_ERP (ebp_restaurant_db)
-- Date: 2026-07-19
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- POS BANK RECONCILIATION (Tier 1)
-- ============================================================
CREATE TABLE IF NOT EXISTS pos_bank_deposits (
  deposit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  deposit_date DATE NOT NULL,
  pos_sales_total DECIMAL(15,2) DEFAULT 0,
  cash_sales_total DECIMAL(15,2) DEFAULT 0,
  non_cash_sales_total DECIMAL(15,2) DEFAULT 0,
  bank_deposit_amount DECIMAL(15,2) DEFAULT 0,
  cash_drawer_counted DECIMAL(15,2) DEFAULT 0,
  cash_drawer_expected DECIMAL(15,2) DEFAULT 0,
  cash_variance DECIMAL(15,2) DEFAULT 0,
  total_variance DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'PENDING',
  matched_by VARCHAR(100),
  matched_at TIMESTAMP NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS merchant_fees (
  fee_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  order_id BIGINT,
  transaction_date DATE NOT NULL,
  payment_method VARCHAR(50),
  processor_name VARCHAR(100),
  gross_amount DECIMAL(15,2) DEFAULT 0,
  fee_amount DECIMAL(15,2) DEFAULT 0,
  fee_percentage DECIMAL(5,2) DEFAULT 0,
  net_amount DECIMAL(15,2) DEFAULT 0,
  external_transaction_id VARCHAR(200),
  metadata TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS eod_closeouts (
  closeout_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  closeout_date DATE NOT NULL,
  opened_by VARCHAR(100),
  opening_cash DECIMAL(15,2) DEFAULT 0,
  closed_by VARCHAR(100),
  closed_at TIMESTAMP NULL,
  cash_in DECIMAL(15,2) DEFAULT 0,
  cash_out DECIMAL(15,2) DEFAULT 0,
  counted_cash DECIMAL(15,2) DEFAULT 0,
  expected_cash DECIMAL(15,2) DEFAULT 0,
  cash_variance DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'OPEN',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- ============================================================
-- BEVERAGE VARIANCE (Tier 1)
-- ============================================================
CREATE TABLE IF NOT EXISTS bar_counts (
  bar_count_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  count_date DATE NOT NULL,
  count_type VARCHAR(30) DEFAULT 'OPENING',
  status VARCHAR(30) DEFAULT 'DRAFT',
  counted_by VARCHAR(100),
  submitted_by VARCHAR(100),
  submitted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS bar_count_items (
  item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  bar_count_id BIGINT NOT NULL,
  product_id BIGINT,
  bottle_size VARCHAR(50),
  full_bottles INT DEFAULT 0,
  partial_bottles DECIMAL(5,2) DEFAULT 0,
  opening_qty DECIMAL(10,2) DEFAULT 0,
  closing_qty DECIMAL(10,2) DEFAULT 0,
  variance DECIMAL(10,2) DEFAULT 0,
  variance_value DECIMAL(15,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS variance_reports (
  report_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  report_date DATE NOT NULL,
  total_variance_value DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'GENERATED',
  generated_by VARCHAR(100),
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS keg_tracking (
  keg_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  keg_number VARCHAR(50),
  product_id BIGINT,
  received_date DATE,
  tapped_date DATE,
  empty_date DATE,
  initial_weight DECIMAL(10,2) DEFAULT 0,
  current_weight DECIMAL(10,2) DEFAULT 0,
  beer_remaining DECIMAL(10,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'RECEIVED',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- ============================================================
-- RECIPE DEPLETION (Tier 1)
-- ============================================================
CREATE TABLE IF NOT EXISTS recipe_depletion_logs (
  log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  order_id BIGINT,
  recipe_id BIGINT,
  ingredient_id BIGINT,
  quantity_depleted DECIMAL(10,2) DEFAULT 0,
  depletion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- BATCH EXPIRY (Tier 1)
-- ============================================================
CREATE TABLE IF NOT EXISTS inventory_batches (
  batch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  batch_number VARCHAR(100),
  product_id BIGINT,
  received_date DATE,
  expiry_date DATE,
  initial_qty DECIMAL(10,2) DEFAULT 0,
  current_qty DECIMAL(10,2) DEFAULT 0,
  discount_percentage DECIMAL(5,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- ============================================================
-- SETTLEMENT (Tier 1 - Live Music Venue)
-- ============================================================
CREATE TABLE IF NOT EXISTS artist_deals (
  deal_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  deal_name VARCHAR(200),
  artist_name VARCHAR(200),
  deal_type VARCHAR(50),
  guarantee_amount DECIMAL(15,2) DEFAULT 0,
  percentage_door DECIMAL(5,2) DEFAULT 0,
  percentage_bar DECIMAL(5,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'DRAFT',
  signed_date DATE,
  signed_by VARCHAR(100),
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS settlements (
  settlement_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  concert_id BIGINT,
  deal_id BIGINT,
  settlement_date DATE,
  gross_door DECIMAL(15,2) DEFAULT 0,
  gross_bar DECIMAL(15,2) DEFAULT 0,
  artist_share DECIMAL(15,2) DEFAULT 0,
  venue_share DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'DRAFT',
  finalized_by VARCHAR(100),
  finalized_at TIMESTAMP NULL,
  paid_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS settlement_items (
  item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  settlement_id BIGINT NOT NULL,
  item_type VARCHAR(50),
  description TEXT,
  amount DECIMAL(15,2) DEFAULT 0,
  is_deduction TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS advancing_sheets (
  advancing_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  concert_id BIGINT,
  artist_name VARCHAR(200),
  contact_person VARCHAR(200),
  arrival_date DATE,
  soundcheck_time TIME,
  equipment_needed TEXT,
  hospitality_requirements TEXT,
  status VARCHAR(30) DEFAULT 'DRAFT',
  confirmed_by VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- ============================================================
-- EVENT PROFITABILITY (Tier 1)
-- ============================================================
CREATE TABLE IF NOT EXISTS event_profitability (
  profitability_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_name VARCHAR(200),
  event_date DATE,
  revenue DECIMAL(15,2) DEFAULT 0,
  total_costs DECIMAL(15,2) DEFAULT 0,
  net_profit DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'DRAFT',
  finalized_by VARCHAR(100),
  finalized_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS event_cost_items (
  cost_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  profitability_id BIGINT NOT NULL,
  category VARCHAR(100),
  description TEXT,
  amount DECIMAL(15,2) DEFAULT 0,
  vendor VARCHAR(200),
  invoice_number VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- EVENT PROPOSAL / BEO (Tier 1 - Catering)
-- ============================================================
CREATE TABLE IF NOT EXISTS event_proposals (
  proposal_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  client_name VARCHAR(200),
  event_type VARCHAR(100),
  event_date DATE,
  guest_count INT DEFAULT 0,
  proposed_budget DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'DRAFT',
  deposit_amount DECIMAL(15,2) DEFAULT 0,
  deposit_paid TINYINT(1) DEFAULT 0,
  converted_to_beo TINYINT(1) DEFAULT 0,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS proposal_menu_items (
  pmi_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  proposal_id BIGINT NOT NULL,
  menu_item VARCHAR(200),
  quantity INT DEFAULT 0,
  unit_price DECIMAL(15,2) DEFAULT 0,
  total_price DECIMAL(15,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS proposal_addons (
  addon_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  proposal_id BIGINT NOT NULL,
  addon_name VARCHAR(200),
  description TEXT,
  price DECIMAL(15,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS beos (
  beo_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  proposal_id BIGINT,
  beo_number VARCHAR(100),
  event_date DATE,
  status VARCHAR(30) DEFAULT 'DRAFT',
  created_by VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS beo_items (
  beo_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  beo_id BIGINT NOT NULL,
  item_description TEXT,
  category VARCHAR(100),
  quantity INT DEFAULT 0,
  assigned_to VARCHAR(200),
  status VARCHAR(30) DEFAULT 'PENDING',
  completed_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- NIGHTCLUB ADVANCED (Tier 2)
-- ============================================================
CREATE TABLE IF NOT EXISTS table_deposits (
  deposit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  table_id BIGINT,
  zone_id BIGINT,
  customer_name VARCHAR(200),
  deposit_amount DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'PENDING',
  paid_date DATE,
  forfeited_date DATE,
  refunded_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS bottle_service_inventory (
  bottle_inventory_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  product_id BIGINT,
  bottle_name VARCHAR(200),
  brand VARCHAR(200),
  size VARCHAR(50),
  cost_price DECIMAL(15,2) DEFAULT 0,
  selling_price DECIMAL(15,2) DEFAULT 0,
  stock_qty INT DEFAULT 0,
  allocated_qty INT DEFAULT 0,
  served_qty INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS bottle_service_assignments (
  assignment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  bottle_inventory_id BIGINT NOT NULL,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  table_id BIGINT,
  customer_name VARCHAR(200),
  quantity INT DEFAULT 1,
  status VARCHAR(30) DEFAULT 'ALLOCATED',
  served_by VARCHAR(100),
  served_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS promoters (
  promoter_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  name VARCHAR(200),
  phone VARCHAR(50),
  commission_rate DECIMAL(5,2) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS promoter_guest_lists (
  pgl_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  promoter_id BIGINT NOT NULL,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  guest_name VARCHAR(200),
  phone VARCHAR(50),
  party_size INT DEFAULT 1,
  entry_type VARCHAR(50) DEFAULT 'FREE_ENTRY',
  checked_in TINYINT(1) DEFAULT 0,
  checked_in_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- KARAOKE ADVANCED (Tier 2)
-- ============================================================
CREATE TABLE IF NOT EXISTS karaoke_song_catalog (
  song_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  title VARCHAR(300),
  artist VARCHAR(200),
  genre VARCHAR(100),
  language VARCHAR(50),
  duration_seconds INT DEFAULT 0,
  popularity_score INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS karaoke_song_requests (
  request_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  room_id BIGINT,
  song_id BIGINT,
  requested_by VARCHAR(200),
  queue_position INT DEFAULT 0,
  status VARCHAR(30) DEFAULT 'QUEUED',
  played_at TIMESTAMP NULL,
  skipped TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS karaoke_room_orders (
  kro_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  room_id BIGINT,
  order_id BIGINT,
  status VARCHAR(30) DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- ============================================================
-- BEACH CLUB ADVANCED (Tier 2)
-- ============================================================
CREATE TABLE IF NOT EXISTS beach_seat_map (
  seat_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  zone_id BIGINT,
  seat_label VARCHAR(50),
  seat_type VARCHAR(50),
  position_x DECIMAL(10,2) DEFAULT 0,
  position_y DECIMAL(10,2) DEFAULT 0,
  is_available TINYINT(1) DEFAULT 1,
  is_reserved TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS weather_rain_checks (
  rain_check_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  reservation_id BIGINT,
  customer_name VARCHAR(200),
  original_date DATE,
  rain_check_date DATE,
  status VARCHAR(30) DEFAULT 'ISSUED',
  refunded TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS weather_policies (
  policy_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  policy_name VARCHAR(200),
  rain_threshold DECIMAL(5,2) DEFAULT 0,
  refund_percentage DECIMAL(5,2) DEFAULT 0,
  reschedule_days INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- SPORTS BAR ADVANCED (Tier 2)
-- ============================================================
CREATE TABLE IF NOT EXISTS bar_tab_preauths (
  tab_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  customer_name VARCHAR(200),
  card_last4 VARCHAR(4),
  preauth_amount DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'OPEN',
  opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  closed_at TIMESTAMP NULL,
  captured_amount DECIMAL(15,2) DEFAULT 0,
  voided_at TIMESTAMP NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- ============================================================
-- OPERATIONS ADVANCED (Tier 2-3)
-- ============================================================
CREATE TABLE IF NOT EXISTS item_86_status (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  product_id BIGINT,
  is_86ed TINYINT(1) DEFAULT 0,
  86ed_at TIMESTAMP NULL,
  86ed_by VARCHAR(100),
  restock_expected_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS custom_orders (
  custom_order_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  customer_name VARCHAR(200),
  description TEXT,
  special_instructions TEXT,
  status VARCHAR(30) DEFAULT 'PENDING',
  quoted_price DECIMAL(15,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS delivery_routes (
  route_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  driver_name VARCHAR(200),
  route_date DATE,
  status VARCHAR(30) DEFAULT 'PLANNED',
  started_at TIMESTAMP NULL,
  completed_at TIMESTAMP NULL,
  total_stops INT DEFAULT 0,
  completed_stops INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS delivery_route_stops (
  stop_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  route_id BIGINT NOT NULL,
  order_id BIGINT,
  stop_sequence INT DEFAULT 0,
  address TEXT,
  status VARCHAR(30) DEFAULT 'PENDING',
  arrived_at TIMESTAMP NULL,
  departed_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS catering_lead_pipeline (
  lead_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  lead_name VARCHAR(200),
  company VARCHAR(200),
  event_type VARCHAR(100),
  event_date DATE,
  guest_count INT DEFAULT 0,
  estimated_value DECIMAL(15,2) DEFAULT 0,
  stage VARCHAR(50) DEFAULT 'NEW',
  source VARCHAR(100),
  assigned_to VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS allergens (
  allergen_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  allergen_name VARCHAR(100) NOT NULL,
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS allergen_tracking (
  tracking_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  product_id BIGINT,
  allergen_id BIGINT,
  contains TINYINT(1) DEFAULT 0,
  may_contain TINYINT(1) DEFAULT 0,
  dietary_tags VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS ai_sales_predictions (
  prediction_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  prediction_date DATE,
  product_id BIGINT,
  predicted_qty DECIMAL(10,2) DEFAULT 0,
  confidence_level DECIMAL(5,2) DEFAULT 0,
  model_version VARCHAR(50),
  generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS booking_channel_sync (
  sync_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  channel_name VARCHAR(100),
  sync_status VARCHAR(30) DEFAULT 'IDLE',
  last_synced_at TIMESTAMP NULL,
  booking_count INT DEFAULT 0,
  error_message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS order_throttling_config (
  config_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  max_orders_per_slot INT DEFAULT 0,
  slot_duration_minutes INT DEFAULT 15,
  is_paused TINYINT(1) DEFAULT 0,
  paused_until TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS daily_production_plans (
  plan_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  plan_date DATE,
  recipe_id BIGINT,
  planned_qty DECIMAL(10,2) DEFAULT 0,
  actual_qty DECIMAL(10,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'PLANNED',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS service_speed_metrics (
  metric_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  order_id BIGINT,
  kitchen_time INT DEFAULT 0,
  service_time INT DEFAULT 0,
  total_time INT DEFAULT 0,
  measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- VENUE ADVANCED (Tier 2-3)
-- ============================================================
CREATE TABLE IF NOT EXISTS memberships (
  membership_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  customer_name VARCHAR(200),
  membership_type VARCHAR(50),
  points_balance INT DEFAULT 0,
  total_earned INT DEFAULT 0,
  total_redeemed INT DEFAULT 0,
  joined_date DATE,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS entrance_tickets (
  ticket_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  ticket_type VARCHAR(50),
  price DECIMAL(15,2) DEFAULT 0,
  qr_code VARCHAR(500),
  status VARCHAR(30) DEFAULT 'UNSOLD',
  scanned_at TIMESTAMP NULL,
  scanned_by VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS qr_ticket_scans (
  scan_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  ticket_id BIGINT,
  event_id BIGINT,
  scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  gate VARCHAR(50),
  is_valid TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS occupancy_tracking (
  occupancy_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  current_count INT DEFAULT 0,
  max_capacity INT DEFAULT 0,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS comp_guest_lists (
  comp_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  guest_name VARCHAR(200),
  comp_type VARCHAR(50),
  authorized_by VARCHAR(100),
  checked_in TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS event_holds_calendar (
  hold_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  hold_type VARCHAR(50),
  start_time TIMESTAMP,
  end_time TIMESTAMP,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS karaoke_room_calendar (
  krc_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  room_id BIGINT,
  event_date DATE,
  start_time TIME,
  end_time TIME,
  booking_type VARCHAR(50),
  status VARCHAR(30) DEFAULT 'AVAILABLE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS karaoke_overtime_charges (
  overtime_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  room_id BIGINT,
  reservation_id BIGINT,
  overtime_minutes INT DEFAULT 0,
  charge_amount DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- MISC FEATURES (Tier 4)
-- ============================================================
CREATE TABLE IF NOT EXISTS coat_check_items (
  coat_check_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  item_description VARCHAR(200),
  ticket_number VARCHAR(50),
  checked_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  checked_out_at TIMESTAMP NULL,
  fee DECIMAL(10,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'CHECKED_IN'
);

CREATE TABLE IF NOT EXISTS karaoke_scores (
  score_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  room_id BIGINT,
  singer_name VARCHAR(200),
  song_title VARCHAR(300),
  score DECIMAL(5,2) DEFAULT 0,
  scored_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS equipment_assets (
  asset_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  asset_name VARCHAR(200),
  category VARCHAR(100),
  serial_number VARCHAR(100),
  purchase_date DATE,
  value DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'AVAILABLE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS equipment_assignments (
  ea_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  asset_id BIGINT NOT NULL,
  assigned_to VARCHAR(200),
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  returned_at TIMESTAMP NULL,
  `condition` VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS social_group_bookings (
  sgb_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_date DATE,
  organizer_name VARCHAR(200),
  total_amount DECIMAL(15,2) DEFAULT 0,
  per_person_amount DECIMAL(15,2) DEFAULT 0,
  party_size INT DEFAULT 0,
  status VARCHAR(30) DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS social_group_booking_members (
  sgbm_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sgb_id BIGINT NOT NULL,
  member_name VARCHAR(200),
  phone VARCHAR(50),
  share_amount DECIMAL(15,2) DEFAULT 0,
  paid TINYINT(1) DEFAULT 0,
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS wine_list (
  wine_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  wine_name VARCHAR(200),
  vintage INT,
  varietal VARCHAR(100),
  region VARCHAR(100),
  bottle_price DECIMAL(15,2) DEFAULT 0,
  glass_price DECIMAL(15,2) DEFAULT 0,
  stock_qty INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS wine_pairing_suggestions (
  pairing_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  wine_id BIGINT,
  menu_item_id BIGINT,
  pairing_notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS waiter_button_presses (
  press_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  table_id BIGINT,
  pressed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  responded_at TIMESTAMP NULL,
  responded_by VARCHAR(100),
  status VARCHAR(30) DEFAULT 'PENDING'
);

CREATE TABLE IF NOT EXISTS entertainer_rotations (
  rotation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_id BIGINT,
  entertainer_name VARCHAR(200),
  stage VARCHAR(100),
  start_time TIMESTAMP,
  end_time TIMESTAMP NULL,
  performance_type VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- ENTERTAINMENT (Tier 2 - Karaoke, Beach Club, Live Music)
-- ============================================================
CREATE TABLE IF NOT EXISTS karaoke_rooms (
  room_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  room_number VARCHAR(20),
  room_name VARCHAR(100),
  capacity INT DEFAULT 0,
  hourly_rate DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'AVAILABLE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS karaoke_reservations (
  kres_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  room_id BIGINT,
  customer_name VARCHAR(200),
  phone VARCHAR(50),
  party_size INT DEFAULT 0,
  reservation_date DATE,
  start_time TIME,
  end_time TIME,
  status VARCHAR(30) DEFAULT 'BOOKED',
  checked_in_at TIMESTAMP NULL,
  checked_out_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS beach_club_cabanas (
  cabana_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  cabana_number VARCHAR(20),
  cabana_type VARCHAR(50),
  capacity INT DEFAULT 0,
  daily_rate DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'AVAILABLE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS beach_club_reservations (
  bcr_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  cabana_id BIGINT,
  customer_name VARCHAR(200),
  phone VARCHAR(50),
  reservation_date DATE,
  party_size INT DEFAULT 0,
  total_amount DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS beach_club_events (
  bce_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  event_name VARCHAR(200),
  event_date DATE,
  description TEXT,
  ticket_price DECIMAL(15,2) DEFAULT 0,
  capacity INT DEFAULT 0,
  status VARCHAR(30) DEFAULT 'SCHEDULED',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS live_music_concerts (
  concert_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  artist_name VARCHAR(200),
  concert_date DATE,
  venue_capacity INT DEFAULT 0,
  ticket_price DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'SCHEDULED',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS live_music_seating_sections (
  section_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  concert_id BIGINT NOT NULL,
  section_name VARCHAR(100),
  capacity INT DEFAULT 0,
  price_tier VARCHAR(50),
  seats_available INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

CREATE TABLE IF NOT EXISTS live_music_tickets (
  lmt_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  concert_id BIGINT NOT NULL,
  section_id BIGINT,
  seat_number VARCHAR(20),
  price DECIMAL(15,2) DEFAULT 0,
  status VARCHAR(30) DEFAULT 'UNSOLD',
  sold_to VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Tier 1-4 tables created successfully!' AS message;
SELECT CONCAT('Total tables in database: ', COUNT(*)) AS table_count FROM information_schema.tables WHERE table_schema = 'ebp_restaurant_db';
