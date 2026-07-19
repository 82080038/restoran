-- ============================================================
-- MIGRATION: Fix MiscFeatures & VenueAdvanced Table Columns
-- Date: 2026-07-19
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- COAT_CHECK_ITEMS - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE coat_check_items
  ADD COLUMN IF NOT EXISTS check_number VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS customer_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS item_type VARCHAR(50) DEFAULT 'COAT',
  ADD COLUMN IF NOT EXISTS item_count INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS fee_charged DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS fee_paid TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS handled_by VARCHAR(100) NULL;

-- ============================================================
-- KARAOKE_SCORES - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE karaoke_scores
  ADD COLUMN IF NOT EXISTS song_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS pitch_accuracy DECIMAL(5,2) NULL,
  ADD COLUMN IF NOT EXISTS rhythm_accuracy DECIMAL(5,2) NULL,
  ADD COLUMN IF NOT EXISTS volume_level DECIMAL(5,2) NULL,
  ADD COLUMN IF NOT EXISTS duration_seconds INT NULL,
  ADD COLUMN IF NOT EXISTS applause_rating INT NULL;

-- ============================================================
-- EQUIPMENT_ASSETS - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE equipment_assets
  ADD COLUMN IF NOT EXISTS equipment_name VARCHAR(200) NULL AFTER asset_name,
  ADD COLUMN IF NOT EXISTS equipment_type VARCHAR(50) DEFAULT 'MISC' AFTER category,
  ADD COLUMN IF NOT EXISTS brand VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS model VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS purchase_cost DECIMAL(15,2) NULL,
  ADD COLUMN IF NOT EXISTS condition_status VARCHAR(30) DEFAULT 'GOOD',
  ADD COLUMN IF NOT EXISTS assigned_to VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS assigned_location VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS is_cross_hire TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS cross_hire_from VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS cross_hire_return_date DATE NULL,
  ADD COLUMN IF NOT EXISTS equipment_id BIGINT NULL FIRST;

-- ============================================================
-- EQUIPMENT_ASSIGNMENTS - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE equipment_assignments
  ADD COLUMN IF NOT EXISTS equipment_id BIGINT NULL FIRST,
  ADD COLUMN IF NOT EXISTS event_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS room_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS assigned_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS condition_at_return VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS assignment_id BIGINT NULL FIRST;

-- ============================================================
-- RADIUS_CLAUSE_CHECKS - New table per MiscFeaturesService
-- ============================================================
CREATE TABLE IF NOT EXISTS radius_clause_checks (
  check_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  deal_id BIGINT,
  artist_name VARCHAR(200),
  clause_radius_km DECIMAL(10,2),
  clause_days INT,
  event_date DATE,
  conflicting_venue VARCHAR(200),
  conflicting_venue_distance_km DECIMAL(10,2),
  conflicting_event_date DATE,
  check_result VARCHAR(30) DEFAULT 'CLEAR',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- SOCIAL_GROUP_BOOKINGS - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE social_group_bookings
  ADD COLUMN IF NOT EXISTS organizer_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS organizer_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS organizer_email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS event_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS total_party_size INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS deposit_collected DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS split_type VARCHAR(20) DEFAULT 'EVEN',
  ADD COLUMN IF NOT EXISTS invite_link VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS group_booking_id BIGINT NULL FIRST;

-- ============================================================
-- SOCIAL_GROUP_BOOKING_MEMBERS - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE social_group_booking_members
  ADD COLUMN IF NOT EXISTS group_booking_id BIGINT NULL FIRST,
  ADD COLUMN IF NOT EXISTS member_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS member_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS member_email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS share_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS share_paid TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS member_id BIGINT NULL FIRST;

-- ============================================================
-- WINE_LIST - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE wine_list
  ADD COLUMN IF NOT EXISTS country VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS wine_type VARCHAR(20) DEFAULT 'RED',
  ADD COLUMN IF NOT EXISTS cost_per_bottle DECIMAL(15,2) NULL,
  ADD COLUMN IF NOT EXISTS inventory_bottles INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS pairings TEXT,
  ADD COLUMN IF NOT EXISTS tasting_notes TEXT,
  ADD COLUMN IF NOT EXISTS alcohol_pct DECIMAL(5,2) NULL,
  ADD COLUMN IF NOT EXISTS rating DECIMAL(3,1) NULL,
  ADD COLUMN IF NOT EXISTS is_available TINYINT(1) DEFAULT 1;

-- ============================================================
-- WINE_PAIRING_SUGGESTIONS - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE wine_pairing_suggestions
  ADD COLUMN IF NOT EXISTS tenant_id BIGINT NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS product_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS pairing_strength VARCHAR(30) DEFAULT 'GOOD',
  ADD COLUMN IF NOT EXISTS pairing_reason TEXT;

-- ============================================================
-- WAITER_BUTTON_PRESSES - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE waiter_button_presses
  ADD COLUMN IF NOT EXISTS room_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS response_seconds INT NULL,
  ADD COLUMN IF NOT EXISTS response_type VARCHAR(30) NULL;

-- ============================================================
-- ENTERTAINER_ROTATIONS - Fix per MiscFeaturesService
-- ============================================================
ALTER TABLE entertainer_rotations
  ADD COLUMN IF NOT EXISTS entertainer_type VARCHAR(50) DEFAULT 'DJ',
  ADD COLUMN IF NOT EXISTS set_number INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS set_start_time TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS set_end_time TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS set_duration_minutes INT NULL,
  ADD COLUMN IF NOT EXISTS status VARCHAR(30) DEFAULT 'SCHEDULED';

-- ============================================================
-- DYNAMIC_PRICING_RULES - Add missing columns per VenueAdvancedService
-- ============================================================
ALTER TABLE dynamic_pricing_rules
  ADD COLUMN IF NOT EXISTS branch_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS category_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS trigger_type VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS trigger_condition TEXT,
  ADD COLUMN IF NOT EXISTS price_modifier_type VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS price_modifier_value DECIMAL(15,2) NULL,
  ADD COLUMN IF NOT EXISTS priority INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS valid_from DATE NULL,
  ADD COLUMN IF NOT EXISTS valid_to DATE NULL;

-- ============================================================
-- DYNAMIC_PRICE_HISTORY - New table per VenueAdvancedService
-- ============================================================
CREATE TABLE IF NOT EXISTS dynamic_price_history (
  history_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  product_id BIGINT,
  original_price DECIMAL(15,2),
  adjusted_price DECIMAL(15,2),
  rule_id BIGINT,
  adjustment_reason VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- MEMBERSHIPS - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE memberships
  ADD COLUMN IF NOT EXISTS member_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS member_email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS member_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS tier VARCHAR(30) DEFAULT 'BRONZE',
  ADD COLUMN IF NOT EXISTS join_date DATE NULL,
  ADD COLUMN IF NOT EXISTS expiry_date DATE NULL,
  ADD COLUMN IF NOT EXISTS family_account TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS guest_passes_remaining INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS total_spent DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS status VARCHAR(30) DEFAULT 'ACTIVE';

-- ============================================================
-- MEMBERSHIP_TRANSACTIONS - New table per VenueAdvancedService
-- ============================================================
CREATE TABLE IF NOT EXISTS membership_transactions (
  mt_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  membership_id BIGINT NOT NULL,
  transaction_type VARCHAR(20) NOT NULL,
  points INT DEFAULT 0,
  order_id BIGINT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- ENTRANCE_TICKETS - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE entrance_tickets
  ADD COLUMN IF NOT EXISTS expires_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS used_at TIMESTAMP NULL;

-- ============================================================
-- QR_TICKET_SCANS - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE qr_ticket_scans
  ADD COLUMN IF NOT EXISTS branch_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS qr_code VARCHAR(500) NULL,
  ADD COLUMN IF NOT EXISTS scan_result VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS scanned_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS device_id VARCHAR(100) NULL;

-- ============================================================
-- OCCUPANCY_TRACKING - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE occupancy_tracking
  ADD COLUMN IF NOT EXISTS tracking_date DATE NULL,
  ADD COLUMN IF NOT EXISTS current_occupancy INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS entry_count INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS exit_count INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS status VARCHAR(30) DEFAULT 'OPEN',
  ADD COLUMN IF NOT EXISTS occupancy_id BIGINT NULL FIRST;

-- ============================================================
-- OCCUPANCY_EVENTS - New table per VenueAdvancedService
-- ============================================================
CREATE TABLE IF NOT EXISTS occupancy_events (
  oe_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  occupancy_id BIGINT NOT NULL,
  event_type VARCHAR(20) NOT NULL,
  person_count INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- KARAOKE_ROOM_CALENDAR - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE karaoke_room_calendar
  ADD COLUMN IF NOT EXISTS reservation_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS customer_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS notes TEXT;

-- ============================================================
-- KARAOKE_OVERTIME_CHARGES - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE karaoke_overtime_charges
  ADD COLUMN IF NOT EXISTS booked_end_time TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS actual_end_time TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS overtime_rate_per_hour DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS overtime_charge DECIMAL(15,2) DEFAULT 0;

-- ============================================================
-- EVENT_HOLDS_CALENDAR - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE event_holds_calendar
  ADD COLUMN IF NOT EXISTS event_date DATE NULL,
  ADD COLUMN IF NOT EXISTS artist_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS hold_type VARCHAR(30) DEFAULT 'FIRST_HOLD',
  ADD COLUMN IF NOT EXISTS priority_rank INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS promoter_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS hold_expires_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS released_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS rolled_to_date DATE NULL;

-- ============================================================
-- COMP_GUEST_LISTS - Fix per VenueAdvancedService
-- ============================================================
ALTER TABLE comp_guest_lists
  ADD COLUMN IF NOT EXISTS list_type VARCHAR(30) DEFAULT 'GUEST',
  ADD COLUMN IF NOT EXISTS guest_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS party_size INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS comp_type VARCHAR(30) DEFAULT 'FULL',
  ADD COLUMN IF NOT EXISTS comp_value DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS added_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS check_in_status VARCHAR(30) DEFAULT 'PENDING',
  ADD COLUMN IF NOT EXISTS checked_in_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS comp_id BIGINT NULL FIRST;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'MiscFeatures & VenueAdvanced column fixes applied successfully!' AS message;
