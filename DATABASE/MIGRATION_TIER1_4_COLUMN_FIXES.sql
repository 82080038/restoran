-- ============================================================
-- MIGRATION: Fix Tier 1-4 Table Columns to Match Service Layer
-- Date: 2026-07-19
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE_DEPOSITS - Add missing columns per NightclubAdvancedService
-- ============================================================
ALTER TABLE table_deposits
  ADD COLUMN IF NOT EXISTS reservation_id BIGINT NULL AFTER event_id,
  ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(50) NULL AFTER customer_name,
  ADD COLUMN IF NOT EXISTS event_date DATE NULL AFTER customer_phone,
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS payment_ref VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS minimum_spend DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS no_show_cutoff TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS deposit_status VARCHAR(30) DEFAULT 'PENDING';

-- ============================================================
-- BOTTLE_SERVICE_INVENTORY - Fix columns per NightclubAdvancedService
-- ============================================================
ALTER TABLE bottle_service_inventory
  ADD COLUMN IF NOT EXISTS bottle_size VARCHAR(50) NULL AFTER bottle_name,
  ADD COLUMN IF NOT EXISTS quantity_on_hand INT DEFAULT 0 AFTER stock_qty,
  ADD COLUMN IF NOT EXISTS quantity_reserved INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS quantity_sold INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS unit_cost DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS storage_location VARCHAR(200) NULL;

-- ============================================================
-- BOTTLE_SERVICE_ASSIGNMENTS - Fix columns per NightclubAdvancedService
-- ============================================================
ALTER TABLE bottle_service_assignments
  ADD COLUMN IF NOT EXISTS bottle_inv_id BIGINT NULL AFTER bottle_inventory_id,
  ADD COLUMN IF NOT EXISTS assigned_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS tenant_id BIGINT NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS branch_id BIGINT NULL;

-- ============================================================
-- PROMOTERS - Fix columns per NightclubAdvancedService
-- ============================================================
ALTER TABLE promoters
  ADD COLUMN IF NOT EXISTS promoter_name VARCHAR(200) NULL AFTER name,
  ADD COLUMN IF NOT EXISTS promoter_code VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS commission_type VARCHAR(30) DEFAULT 'PER_HEAD',
  ADD COLUMN IF NOT EXISTS guest_list_limit INT NULL,
  ADD COLUMN IF NOT EXISTS total_guests_brought INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS total_commission_earned DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS notes TEXT;

-- ============================================================
-- PROMOTER_GUEST_LISTS - Fix columns per NightclubAdvancedService
-- ============================================================
ALTER TABLE promoter_guest_lists
  ADD COLUMN IF NOT EXISTS guest_phone VARCHAR(50) NULL AFTER guest_name,
  ADD COLUMN IF NOT EXISTS guest_id BIGINT NULL FIRST,
  ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS commission_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS check_in_status VARCHAR(30) DEFAULT 'PENDING';

-- ============================================================
-- KARAOKE_SONG_CATALOG - Add missing columns per KaraokeAdvancedService
-- ============================================================
ALTER TABLE karaoke_song_catalog
  ADD COLUMN IF NOT EXISTS song_code VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS year INT NULL,
  ADD COLUMN IF NOT EXISTS file_path VARCHAR(500) NULL,
  ADD COLUMN IF NOT EXISTS lyrics_available TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS date_added DATE NULL,
  ADD COLUMN IF NOT EXISTS play_count INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS last_played_at TIMESTAMP NULL;

-- ============================================================
-- KARAOKE_SONG_REQUESTS - Add missing columns per KaraokeAdvancedService
-- ============================================================
ALTER TABLE karaoke_song_requests
  ADD COLUMN IF NOT EXISTS reservation_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS request_source VARCHAR(50) DEFAULT 'QR_APP';

-- ============================================================
-- KARAOKE_ROOM_ORDERS - Fix columns per KaraokeAdvancedService
-- ============================================================
ALTER TABLE karaoke_room_orders
  ADD COLUMN IF NOT EXISTS reservation_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS order_type VARCHAR(30) DEFAULT 'FNB',
  ADD COLUMN IF NOT EXISTS items_json TEXT,
  ADD COLUMN IF NOT EXISTS total_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS ordered_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  ADD COLUMN IF NOT EXISTS room_order_id BIGINT NULL FIRST,
  ADD COLUMN IF NOT EXISTS served_at TIMESTAMP NULL;

-- ============================================================
-- BEACH_SEAT_MAP - Fix columns per BeachClubAdvancedService
-- ============================================================
ALTER TABLE beach_seat_map
  ADD COLUMN IF NOT EXISTS zone_name VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS capacity INT DEFAULT 2,
  ADD COLUMN IF NOT EXISTS width DECIMAL(10,2) DEFAULT 80,
  ADD COLUMN IF NOT EXISTS height DECIMAL(10,2) DEFAULT 80,
  ADD COLUMN IF NOT EXISTS base_price DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS premium_multiplier DECIMAL(5,2) DEFAULT 1.00,
  ADD COLUMN IF NOT EXISTS minimum_spend DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_bookable TINYINT(1) DEFAULT 1;

-- Create beach_reservations table (referenced by getSeatAvailability)
CREATE TABLE IF NOT EXISTS beach_reservations (
  booking_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  seat_id BIGINT,
  cabana_id BIGINT,
  customer_name VARCHAR(200),
  phone VARCHAR(50),
  reservation_date DATE,
  status VARCHAR(30) DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
);

-- ============================================================
-- WEATHER_RAIN_CHECKS - Fix columns per BeachClubAdvancedService
-- ============================================================
ALTER TABLE weather_rain_checks
  ADD COLUMN IF NOT EXISTS booking_id BIGINT NULL AFTER reservation_id,
  ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS customer_email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS weather_condition VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS refund_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS issued_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS expiry_date DATE NULL,
  ADD COLUMN IF NOT EXISTS rescheduled_date DATE NULL,
  ADD COLUMN IF NOT EXISTS rescheduled_to VARCHAR(200) NULL;

-- ============================================================
-- WEATHER_POLICIES - Fix columns per BeachClubAdvancedService
-- ============================================================
ALTER TABLE weather_policies
  ADD COLUMN IF NOT EXISTS rain_threshold_mm DECIMAL(5,2) DEFAULT 5.00,
  ADD COLUMN IF NOT EXISTS auto_issue_rain_check TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS reschedule_window_days INT DEFAULT 30,
  ADD COLUMN IF NOT EXISTS refund_policy VARCHAR(30) DEFAULT 'CREDIT',
  ADD COLUMN IF NOT EXISTS partial_refund_pct DECIMAL(5,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS notification_template TEXT;

-- ============================================================
-- ITEM_86_STATUS - Fix columns per OperationsAdvancedService
-- ============================================================
ALTER TABLE item_86_status
  ADD COLUMN IF NOT EXISTS reason TEXT NULL,
  ADD COLUMN IF NOT EXISTS 86ed_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS expected_restock_date DATE NULL,
  ADD COLUMN IF NOT EXISTS restocked_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS restocked_at TIMESTAMP NULL;

-- ============================================================
-- CUSTOM_ORDERS - Fix columns per OperationsAdvancedService
-- ============================================================
ALTER TABLE custom_orders
  ADD COLUMN IF NOT EXISTS order_number VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS customer_email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS product_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS product_description TEXT,
  ADD COLUMN IF NOT EXISTS specifications TEXT,
  ADD COLUMN IF NOT EXISTS quantity INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS unit_price DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS total_price DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS deposit_required DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS pickup_date DATE NULL,
  ADD COLUMN IF NOT EXISTS pickup_time TIME NULL,
  ADD COLUMN IF NOT EXISTS delivery_address TEXT,
  ADD COLUMN IF NOT EXISTS delivery_fee DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS fulfillment_type VARCHAR(30) DEFAULT 'PICKUP';

-- ============================================================
-- DELIVERY_ROUTES - Fix columns per OperationsAdvancedService
-- ============================================================
ALTER TABLE delivery_routes
  ADD COLUMN IF NOT EXISTS driver_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS vehicle VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS estimated_duration_minutes INT NULL,
  ADD COLUMN IF NOT EXISTS actual_start_time TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS actual_end_time TIMESTAMP NULL;

-- ============================================================
-- DELIVERY_ROUTE_STOPS - Fix columns per OperationsAdvancedService
-- ============================================================
ALTER TABLE delivery_route_stops
  ADD COLUMN IF NOT EXISTS customer_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS items_summary TEXT,
  ADD COLUMN IF NOT EXISTS amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS proof_photo_path VARCHAR(500) NULL,
  ADD COLUMN IF NOT EXISTS signature_path VARCHAR(500) NULL,
  ADD COLUMN IF NOT EXISTS failure_reason TEXT,
  ADD COLUMN IF NOT EXISTS delivered_at TIMESTAMP NULL;

-- ============================================================
-- CATERING_LEAD_PIPELINE - Fix columns per OperationsAdvancedService
-- ============================================================
ALTER TABLE catering_lead_pipeline
  ADD COLUMN IF NOT EXISTS lead_number VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS lead_source VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS client_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS client_company VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS client_phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS client_email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS assigned_to VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS notes TEXT,
  ADD COLUMN IF NOT EXISTS next_follow_up DATE NULL,
  ADD COLUMN IF NOT EXISTS stage_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  ADD COLUMN IF NOT EXISTS probability_pct DECIMAL(5,2) DEFAULT 0;

-- ============================================================
-- ALLERGEN_TRACKING - Fix columns per OperationsAdvancedService
-- ============================================================
ALTER TABLE allergen_tracking
  ADD COLUMN IF NOT EXISTS allergens TEXT,
  ADD COLUMN IF NOT EXISTS dietary_tags TEXT,
  ADD COLUMN IF NOT EXISTS contains_gluten TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS contains_dairy TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS contains_nuts TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS contains_eggs TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS contains_soy TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS contains_shellfish TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS contains_fish TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS contains_sesame TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_vegetarian TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_vegan TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_halal TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_kosher TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS certification_body VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS certification_number VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS certification_expiry DATE NULL;

-- ============================================================
-- AI_SALES_PREDICTIONS - Fix columns per Tier3OperationsService
-- ============================================================
ALTER TABLE ai_sales_predictions
  ADD COLUMN IF NOT EXISTS predicted_hour INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS predicted_revenue DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS predicted_orders INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS confidence_score DECIMAL(5,2) DEFAULT 0;

-- ============================================================
-- BOOKING_CHANNEL_SYNC - Fix columns per Tier3OperationsService
-- ============================================================
ALTER TABLE booking_channel_sync
  ADD COLUMN IF NOT EXISTS channel_type VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS external_booking_id VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS internal_booking_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS synced_at TIMESTAMP NULL;

-- ============================================================
-- ORDER_THROTTLING_CONFIG - Fix columns per Tier3OperationsService
-- ============================================================
ALTER TABLE order_throttling_config
  ADD COLUMN IF NOT EXISTS channel VARCHAR(50) DEFAULT 'ALL',
  ADD COLUMN IF NOT EXISTS auto_pause_threshold INT DEFAULT 20,
  ADD COLUMN IF NOT EXISTS current_orders_in_slot INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS current_slot_start TIMESTAMP NULL;

-- ============================================================
-- AUTO_PO_RULES - Create table per Tier3OperationsService
-- ============================================================
CREATE TABLE IF NOT EXISTS auto_po_rules (
  rule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_id BIGINT,
  inventory_id BIGINT,
  reorder_point DECIMAL(10,2) DEFAULT 0,
  reorder_quantity DECIMAL(10,2) DEFAULT 0,
  preferred_supplier_id BIGINT,
  fallback_supplier_id BIGINT,
  auto_generate TINYINT(1) DEFAULT 0,
  requires_approval TINYINT(1) DEFAULT 1,
  is_active TINYINT(1) DEFAULT 1,
  last_po_generated_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- ============================================================
-- DAILY_PRODUCTION_PLANS - Fix columns per Tier3OperationsService
-- ============================================================
ALTER TABLE daily_production_plans
  ADD COLUMN IF NOT EXISTS product_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS product_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS planned_quantity DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS produced_quantity DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS sold_quantity DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS wasted_quantity DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS remaining_quantity DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS production_start TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS production_end TIMESTAMP NULL;

-- ============================================================
-- SERVICE_SPEED_METRICS - Fix columns per Tier3OperationsService
-- ============================================================
ALTER TABLE service_speed_metrics
  ADD COLUMN IF NOT EXISTS metric_date DATE NULL,
  ADD COLUMN IF NOT EXISTS metric_hour INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS order_received_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS order_started_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS order_ready_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS order_served_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS total_prep_seconds INT NULL,
  ADD COLUMN IF NOT EXISTS total_service_seconds INT NULL,
  ADD COLUMN IF NOT EXISTS order_type VARCHAR(30) DEFAULT 'DINE_IN',
  ADD COLUMN IF NOT EXISTS items_count INT NULL;

-- ============================================================
-- KARAOKE_ROOMS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE karaoke_rooms
  ADD COLUMN IF NOT EXISTS room_code VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS room_type VARCHAR(50) DEFAULT 'STANDARD',
  ADD COLUMN IF NOT EXISTS minimum_spend DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS has_private_bathroom TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS has_waiter_button TINYINT(1) DEFAULT 1,
  ADD COLUMN IF NOT EXISTS equipment_status VARCHAR(30) DEFAULT 'ACTIVE',
  ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- ============================================================
-- KARAOKE_RESERVATIONS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE karaoke_reservations
  ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS hourly_rate DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS room_charge DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS minimum_spend DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS total_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS payment_status VARCHAR(30) DEFAULT 'PENDING',
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS special_requests TEXT,
  ADD COLUMN IF NOT EXISTS actual_end_time TIME NULL,
  ADD COLUMN IF NOT EXISTS checked_out_at TIMESTAMP NULL;

-- ============================================================
-- BEACH_CLUB_CABANAS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE beach_club_cabanas
  ADD COLUMN IF NOT EXISTS cabana_code VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS cabana_name VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS minimum_spend DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS location VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS has_butler TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS has_private_pool TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- ============================================================
-- BEACH_CLUB_RESERVATIONS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE beach_club_reservations
  ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS arrival_time TIME NULL,
  ADD COLUMN IF NOT EXISTS daily_rate DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS minimum_spend DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS total_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS payment_status VARCHAR(30) DEFAULT 'PENDING',
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS includes_pool_access TINYINT(1) DEFAULT 1,
  ADD COLUMN IF NOT EXISTS includes_towel TINYINT(1) DEFAULT 1,
  ADD COLUMN IF NOT EXISTS special_requests TEXT;

-- ============================================================
-- BEACH_CLUB_EVENTS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE beach_club_events
  ADD COLUMN IF NOT EXISTS start_time TIME NULL,
  ADD COLUMN IF NOT EXISTS end_time TIME NULL,
  ADD COLUMN IF NOT EXISTS theme VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS dj_name VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS music_genre VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS entrance_fee DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- ============================================================
-- LIVE_MUSIC_CONCERTS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE live_music_concerts
  ADD COLUMN IF NOT EXISTS concert_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS genre VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS doors_open_time TIME NULL,
  ADD COLUMN IF NOT EXISTS show_time TIME NULL,
  ADD COLUMN IF NOT EXISTS end_time TIME NULL,
  ADD COLUMN IF NOT EXISTS poster_url VARCHAR(500) NULL,
  ADD COLUMN IF NOT EXISTS description TEXT,
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- ============================================================
-- LIVE_MUSIC_SEATING_SECTIONS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE live_music_seating_sections
  ADD COLUMN IF NOT EXISTS tenant_id BIGINT NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS branch_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS section_type VARCHAR(50) DEFAULT 'GA_STANDING',
  ADD COLUMN IF NOT EXISTS price DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_numbered TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- ============================================================
-- LIVE_MUSIC_TICKETS - Fix columns per EntertainmentService
-- ============================================================
ALTER TABLE live_music_tickets
  ADD COLUMN IF NOT EXISTS tenant_id BIGINT NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS branch_id BIGINT NULL,
  ADD COLUMN IF NOT EXISTS customer_name VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS phone VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS ticket_type VARCHAR(50) DEFAULT 'GA',
  ADD COLUMN IF NOT EXISTS unit_price DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS quantity INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS total_amount DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS ticket_code VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS payment_status VARCHAR(30) DEFAULT 'PENDING',
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS sold_by VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS sold_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  ADD COLUMN IF NOT EXISTS check_in_status TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS check_in_at TIMESTAMP NULL;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Tier 1-4 column fixes applied successfully!' AS message;
