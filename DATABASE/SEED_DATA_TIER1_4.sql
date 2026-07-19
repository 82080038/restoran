-- ============================================================
-- SEED DATA TIER 1-4 + GAP FEATURES
-- Appended to existing SEED_DATA.sql
-- Date: 2026-07-19
-- ============================================================

-- ============================================================
-- TIER 1: POS-BANK RECONCILIATION (Tenant 1)
-- ============================================================
INSERT IGNORE INTO pos_bank_deposits (deposit_id, tenant_id, branch_id, deposit_date, pos_sales_total, cash_sales_total, non_cash_sales_total, bank_deposit_amount, cash_drawer_counted, cash_drawer_expected, cash_variance, total_variance, status, matched_by) VALUES
(1, 1, 1, CURDATE() - INTERVAL 1 DAY, 5000000.00, 2000000.00, 3000000.00, 2000000.00, 2000000.00, 2000000.00, 0.00, 0.00, 'MATCHED', 'admin'),
(2, 1, 1, CURDATE() - INTERVAL 2 DAY, 4500000.00, 1800000.00, 2700000.00, 1750000.00, 1800000.00, 1800000.00, -50000.00, -50000.00, 'DISPUTED', NULL),
(3, 1, 1, CURDATE() - INTERVAL 3 DAY, 6200000.00, 2500000.00, 3700000.00, 2500000.00, 2500000.00, 2500000.00, 0.00, 0.00, 'MATCHED', 'admin');

INSERT IGNORE INTO reconciliation_transactions (transaction_id, log_id, source_id, order_id, tenant_id, branch_id, external_transaction_id, transaction_type, amount, currency, transaction_date, status, matching_score) VALUES
(1, 1, 1, 1, 1, 1, 'BCA-TRX-001', 'PAYMENT', 3500000.00, 'IDR', NOW() - INTERVAL 1 DAY, 'MATCHED', 100.00),
(2, 1, 2, 2, 1, 1, 'MANDIRI-TRX-002', 'PAYMENT', 1500000.00, 'IDR', NOW() - INTERVAL 1 DAY, 'MATCHED', 100.00),
(3, 2, 3, 3, 1, 1, 'BNI-TRX-003', 'PAYMENT', 2700000.00, 'IDR', NOW() - INTERVAL 2 DAY, 'UNMATCHED', 0.00);

INSERT IGNORE INTO reconciliation_rules (rule_id, tenant_id, rule_name, rule_code, rule_type, source_type, conditions, actions, priority, is_active) VALUES
(1, 1, 'Exact Amount Match', 'EXACT_MATCH', 'MATCHING', 'POS', '{"fields":["amount","transaction_date"],"tolerance":0}', '{"action":"auto_match"}', 100, 1),
(2, 1, 'Tolerance Match ±50000', 'TOLERANCE_50K', 'TOLERANCE', 'POS', '{"fields":["amount","transaction_date"],"tolerance":50000}', '{"action":"auto_match_with_alert"}', 90, 1);

INSERT IGNORE INTO reconciliation_logs (log_id, order_id, tenant_id, branch_id, status, expected_total, actual_total, difference, discrepancies_count, reconciled_at, reconciled_by) VALUES
(1, 1, 1, 1, 'RECONCILED', 5000000.00, 5000000.00, 0.00, 0, NOW() - INTERVAL 1 DAY, 'admin'),
(2, 2, 1, 1, 'DISPUTED', 4500000.00, 4450000.00, -50000.00, 1, NULL, NULL);

-- ============================================================
-- TIER 1: BEVERAGE VARIANCE (Tenant 3 - Bar)
-- ============================================================
INSERT IGNORE INTO variance_reports (report_id, tenant_id, branch_id, report_date, total_variance_value, status) VALUES
(1, 3, 3, CURDATE() - INTERVAL 1 DAY, -585000.00, 'REVIEWED'),
(2, 3, 3, CURDATE() - INTERVAL 2 DAY, -350000.00, 'FLAGGED');

-- ============================================================
-- TIER 1: RECIPE DEPLETION (Tenant 1 - Restaurant)
-- ============================================================
INSERT IGNORE INTO recipe_depletion_logs (log_id, tenant_id, branch_id, order_id, recipe_id, ingredient_id, quantity_depleted) VALUES
(1, 1, 1, 1, 1, 1, 2.50),
(2, 1, 1, 1, 1, 2, 1.00),
(3, 1, 1, 2, 2, 3, 0.50);

-- ============================================================
-- TIER 1: PRODUCTION BATCHES (Tenant 1 - Restaurant Bakery)
-- ============================================================
INSERT IGNORE INTO production_batches (batch_id, tenant_id, branch_id, recipe_id, batch_number, quantity, status, yield_percentage, production_date) VALUES
(1, 1, 1, 1, 'BATCH-20260718-001', 50, 'COMPLETED', 98.00, CURDATE() - INTERVAL 1 DAY),
(2, 1, 1, 2, 'BATCH-20260719-001', 30, 'IN_PROGRESS', NULL, CURDATE());

-- ============================================================
-- TIER 1: SETTLEMENTS (Tenant 13 - Nightclub/Live Music)
-- ============================================================
INSERT IGNORE INTO settlements (settlement_id, tenant_id, branch_id, settlement_date, gross_door, gross_bar, artist_share, venue_share, status, finalized_by) VALUES
(1, 13, 13, CURDATE() - INTERVAL 7 DAY, 15000000.00, 25000000.00, 12000000.00, 28000000.00, 'FINALIZED', 'admin'),
(2, 13, 13, CURDATE() - INTERVAL 3 DAY, 8000000.00, 18000000.00, 6000000.00, 20000000.00, 'DRAFT', NULL);

-- ============================================================
-- TIER 1: EVENT PROFITABILITY (Tenant 13)
-- ============================================================
INSERT IGNORE INTO event_profitability (profitability_id, tenant_id, branch_id, event_name, event_date, revenue, total_costs, net_profit, status, finalized_by) VALUES
(1, 13, 13, 'Neon Friday Night', CURDATE() - INTERVAL 7 DAY, 40000000.00, 25000000.00, 15000000.00, 'FINALIZED', 'admin');

-- ============================================================
-- TIER 1: EVENT PROPOSALS (Tenant 6 - Catering)
-- ============================================================
INSERT IGNORE INTO event_proposals (proposal_id, tenant_id, branch_id, client_name, event_type, event_date, guest_count, proposed_budget, status, deposit_amount, deposit_paid, notes) VALUES
(1, 6, 6, 'PT Maju Bersama', 'CORPORATE_LUNCH', CURDATE() + INTERVAL 14 DAY, 200, 35000000.00, 'SENT', 5000000.00, 1, 'Annual corporate lunch for 200 pax'),
(2, 6, 6, 'Budi & Ani Wedding', 'WEDDING', CURDATE() + INTERVAL 30 DAY, 500, 150000000.00, 'DRAFT', 30000000.00, 0, 'Wedding reception - Indonesian menu');

-- ============================================================
-- TIER 2: NIGHTCLUB ADVANCED (Tenant 13)
-- ============================================================
INSERT IGNORE INTO table_deposits (deposit_id, tenant_id, branch_id, event_id, customer_name, customer_phone, event_date, deposit_amount, status, paid_date, payment_method, payment_ref, minimum_spend, no_show_cutoff, deposit_status) VALUES
(1, 13, 13, 1, 'Andi Wijaya', '081234500004', CURDATE() + INTERVAL 2 DAY, 2500000.00, 'PAID', CURDATE() - INTERVAL 3 DAY, 'CREDIT_CARD', 'CC-TD-001', 5000000.00, NOW() + INTERVAL 2 DAY, 'CONFIRMED'),
(2, 13, 13, 2, 'Chandra Tan', '081234500006', CURDATE() + INTERVAL 6 DAY, 4000000.00, 'PAID', CURDATE() - INTERVAL 1 DAY, 'BANK_TRANSFER', 'VA-TD-002', 8000000.00, NOW() + INTERVAL 5 DAY, 'CONFIRMED');

INSERT IGNORE INTO bottle_service_inventory (bottle_inventory_id, tenant_id, branch_id, bottle_name, bottle_size, brand, cost_price, selling_price, stock_qty, quantity_on_hand, allocated_qty, served_qty, quantity_reserved, quantity_sold, unit_cost, storage_location) VALUES
(1, 13, 13, 'Johnnie Walker Gold Label', '700ml', 'Johnnie Walker', 1800000.00, 2500000.00, 24, 20, 4, 0, 4, 0, 1800000.00, 'VIP Bar Storage'),
(2, 13, 13, 'Grey Goose Vodka', '750ml', 'Grey Goose', 2200000.00, 3000000.00, 18, 15, 2, 1, 2, 1, 2200000.00, 'VIP Bar Storage'),
(3, 13, 13, 'Moet & Chandon Imperial', '750ml', 'Moet & Chandon', 1500000.00, 2200000.00, 12, 10, 1, 1, 1, 1, 1500000.00, 'Champage Cooler');

INSERT IGNORE INTO promoters (promoter_id, tenant_id, branch_id, name, promoter_name, phone, commission_rate, is_active, promoter_code, email, commission_type, guest_list_limit, total_guests_brought, total_commission_earned) VALUES
(1, 13, 13, 'Rudi Hartono', 'Rudi Hartono', '081234510001', 15.00, 1, 'PROM001', 'rudi@neonclub.com', 'PER_HEAD', 50, 120, 1800000.00),
(2, 13, 13, 'Sara Dewi', 'Sara Dewi', '081234510002', 10.00, 1, 'PROM002', 'sara@neonclub.com', 'PER_HEAD', 30, 85, 850000.00),
(3, 13, 13, 'Joko Susilo', 'Joko Susilo', '081234510003', 12.00, 1, 'PROM003', 'joko@neonclub.com', 'PERCENTAGE', 40, 60, 720000.00);

INSERT IGNORE INTO promoter_guest_lists (pgl_id, tenant_id, branch_id, promoter_id, event_id, guest_name, phone, party_size, check_in_status, commission_amount) VALUES
(1, 13, 13, 1, 1, 'Dewi Asmara', '081234520001', 3, 'CHECKED_IN', 45000.00),
(2, 13, 13, 1, 1, 'Bambang Tri', '081234520002', 2, 'CHECKED_IN', 30000.00),
(3, 13, 13, 2, 2, 'Lina Marlina', '081234520003', 4, 'PENDING', 0.00),
(4, 13, 13, 3, 3, 'Agus Setiawan', '081234520004', 2, 'CHECKED_IN', 24000.00);

-- ============================================================
-- TIER 2: KARAOKE ADVANCED (Tenant 13 has karaoke rooms too)
-- ============================================================
INSERT IGNORE INTO karaoke_song_catalog (song_id, tenant_id, title, artist, genre, language, duration_seconds, popularity_score, is_active, song_code, year, play_count) VALUES
(1, 13, 'Bohemian Rhapsody', 'Queen', 'ROCK', 'EN', 354, 9.8, 1, 'KAR001', 1975, 342),
(2, 13, 'Dangdut Lawas', 'Rhoma Irama', 'DANGDUT', 'ID', 285, 9.5, 1, 'KAR002', 1985, 567),
(3, 13, 'Shape of You', 'Ed Sheeran', 'POP', 'EN', 233, 9.7, 1, 'KAR003', 2017, 891),
(4, 13, 'Bunga', 'Ari Lasso', 'POP', 'ID', 278, 9.2, 1, 'KAR004', 2003, 234),
(5, 13, 'Sweet Caroline', 'Neil Diamond', 'POP', 'EN', 201, 9.6, 1, 'KAR005', 1969, 456);

INSERT IGNORE INTO karaoke_rooms (room_id, tenant_id, branch_id, room_number, room_name, capacity, hourly_rate, status, room_code, room_type, minimum_spend, has_private_bathroom, has_waiter_button, equipment_status, is_active) VALUES
(1, 13, 13, 'K-01', 'VIP Room 1', 8, 500000.00, 'AVAILABLE', 'KR001', 'VIP', 2000000.00, 1, 1, 'GOOD', 1),
(2, 13, 13, 'K-02', 'VIP Room 2', 10, 750000.00, 'AVAILABLE', 'KR002', 'VIP', 3000000.00, 1, 1, 'GOOD', 1),
(3, 13, 13, 'K-03', 'Standard Room 1', 6, 300000.00, 'OCCUPIED', 'KR003', 'STANDARD', 800000.00, 0, 1, 'GOOD', 1),
(4, 13, 13, 'K-04', 'Standard Room 2', 6, 300000.00, 'AVAILABLE', 'KR004', 'STANDARD', 800000.00, 0, 1, 'MAINTENANCE', 0);

INSERT IGNORE INTO karaoke_room_calendar (krc_id, tenant_id, branch_id, room_id, event_date, start_time, end_time, booking_type, status, customer_name) VALUES
(1, 13, 13, 3, CURDATE(), '20:00:00', '23:00:00', 'RESERVATION', 'BOOKED', 'Eka Putra'),
(2, 13, 13, 1, CURDATE() + INTERVAL 1 DAY, '21:00:00', '01:00:00', 'RESERVATION', 'BOOKED', 'Fajar Nugroho');

INSERT IGNORE INTO karaoke_overtime_charges (overtime_id, tenant_id, branch_id, room_id, reservation_id, overtime_minutes, charge_amount, status, overtime_rate_per_hour) VALUES
(1, 13, 13, 3, 1, 30, 37500.00, 'PENDING', 75000.00);

INSERT IGNORE INTO karaoke_scores (score_id, tenant_id, branch_id, room_id, singer_name, song_title, score, scored_at, song_id, pitch_accuracy, rhythm_accuracy, volume_level, duration_seconds, applause_rating) VALUES
(1, 13, 13, 3, 'Eka Putra', 'Bohemian Rhapsody', 92, NOW() - INTERVAL 1 HOUR, 1, 88, 95, 90, 354, 4),
(2, 13, 13, 3, 'Eka Putra', 'Sweet Caroline', 87, NOW() - INTERVAL 30 MINUTE, 5, 82, 90, 85, 201, 3);

-- ============================================================
-- TIER 2: BEACH CLUB (Use tenant 13 for beach seats too)
-- ============================================================
INSERT IGNORE INTO beach_seat_map (seat_id, tenant_id, branch_id, zone_id, seat_label, seat_type, position_x, position_y, is_available, is_reserved, zone_name, capacity, base_price, premium_multiplier, minimum_spend, is_bookable) VALUES
(1, 13, 13, 24, 'CAB-01', 'CABANA', 10, 10, 1, 0, 'Beach Cabanas', 6, 2500000.00, 1.50, 5000000.00, 1),
(2, 13, 13, 24, 'CAB-02', 'CABANA', 30, 10, 1, 0, 'Beach Cabanas', 6, 2500000.00, 1.50, 5000000.00, 1),
(3, 13, 13, 24, 'SUN-01', 'SUN_LOUNGER', 50, 20, 1, 0, 'Sun Loungers', 1, 250000.00, 1.00, 0.00, 1),
(4, 13, 13, 24, 'SUN-02', 'SUN_LOUNGER', 60, 20, 0, 1, 'Sun Loungers', 1, 250000.00, 1.00, 0.00, 1),
(5, 13, 13, 24, 'DAY-01', 'DAY_BED', 70, 30, 1, 0, 'Day Beds', 4, 1500000.00, 1.20, 3000000.00, 1);

INSERT IGNORE INTO weather_policies (policy_id, tenant_id, branch_id, policy_name, rain_threshold, refund_percentage, reschedule_days, is_active, rain_threshold_mm, auto_issue_rain_check, reschedule_window_days, refund_policy, partial_refund_pct) VALUES
(1, 13, 13, 'Standard Rain Policy', 5.0, 100, 7, 1, 5.0, 1, 14, 'FULL_REFUND', 50.00);

INSERT IGNORE INTO weather_rain_checks (rain_check_id, tenant_id, branch_id, booking_id, customer_name, original_date, rain_check_date, rescheduled_date, refund_amount, status, issued_by, expiry_date) VALUES
(1, 13, 13, 1, 'Andi Wijaya', CURDATE() - INTERVAL 3 DAY, CURDATE() - INTERVAL 5 DAY, CURDATE() + INTERVAL 7 DAY, 2500000.00, 'RESCHEDULED', 'manager', CURDATE() + INTERVAL 14 DAY);

-- ============================================================
-- TIER 2: SPORTS BAR ADVANCED (Tenant 3 - Bar)
-- ============================================================
INSERT IGNORE INTO keg_tracking (keg_id, tenant_id, branch_id, keg_number, product_id, received_date, tapped_date, initial_weight, current_weight, beer_remaining, status) VALUES
(1, 3, 3, 'KEG-001', 1, CURDATE() - INTERVAL 10 DAY, CURDATE() - INTERVAL 5 DAY, 30.00, 18.50, 61.67, 'ACTIVE'),
(2, 3, 3, 'KEG-002', 2, CURDATE() - INTERVAL 7 DAY, CURDATE() - INTERVAL 3 DAY, 30.00, 22.00, 73.33, 'ACTIVE'),
(3, 3, 3, 'KEG-003', 1, CURDATE() - INTERVAL 15 DAY, CURDATE() - INTERVAL 12 DAY, 30.00, 2.00, 6.67, 'ALMOST_EMPTY');

-- ============================================================
-- TIER 2: OPERATIONS ADVANCED (Tenant 1)
-- ============================================================
INSERT IGNORE INTO item_86_status (id, tenant_id, branch_id, product_id, is_86ed, 86ed_at, 86ed_by, reason, expected_restock_date) VALUES
(1, 1, 1, 6, 1, NOW() - INTERVAL 2 HOUR, 'admin', 'Out of stock - supply issue', CURDATE() + INTERVAL 1 DAY),
(2, 1, 1, 3, 0, NULL, NULL, NULL, NULL);

INSERT IGNORE INTO custom_orders (custom_order_id, tenant_id, branch_id, customer_name, description, special_instructions, status, quoted_price, order_number, customer_phone, product_name, product_description, quantity, unit_price, total_price, deposit_required, pickup_date, pickup_time, fulfillment_type) VALUES
(1, 1, 1, 'Siti Aminah', 'Birthday cake for 20 people', 'Chocolate with strawberry filling, write "Happy Birthday Sari"', 'IN_PROGRESS', 850000.00, 'CO-2026-001', '08333333333', 'Custom Birthday Cake', '3-tier chocolate strawberry cake', 1, 850000.00, 850000.00, 425000.00, CURDATE() + INTERVAL 3 DAY, '14:00:00', 'PICKUP'),
(2, 1, 1, 'Budi Santoso', 'Catering for office meeting', '10 lunch boxes with nasi goreng and ayam bakar', 'PENDING', 350000.00, 'CO-2026-002', '081234567890', 'Office Lunch Boxes', 'Nasi goreng + ayam bakar combo', 10, 35000.00, 350000.00, 175000.00, CURDATE() + INTERVAL 5 DAY, '12:00:00', 'DELIVERY');

INSERT IGNORE INTO delivery_routes (route_id, tenant_id, branch_id, driver_name, route_date, status, total_stops, completed_stops, driver_phone, vehicle, estimated_duration_minutes) VALUES
(1, 1, 1, 'Joko Driver', CURDATE(), 'IN_PROGRESS', 5, 2, '081234540001', 'Motorcycle - B 1234 ABC', 120),
(2, 1, 1, 'Andi Driver', CURDATE() + INTERVAL 1 DAY, 'SCHEDULED', 3, 0, '081234540002', 'Van - B 5678 XYZ', 90);

INSERT IGNORE INTO catering_lead_pipeline (lead_id, tenant_id, branch_id, lead_name, company, event_type, event_date, guest_count, estimated_value, stage, source, assigned_to, lead_number, lead_source, client_name, client_company, client_phone, client_email, probability_pct) VALUES
(1, 6, 6, 'PT Maju Bersama Annual Meeting', 'PT Maju Bersama', 'CORPORATE_LUNCH', CURDATE() + INTERVAL 14 DAY, 200, 35000000.00, 'PROPOSAL', 'WEBSITE', 'cater_manager', 'LEAD-001', 'WEBSITE', 'Pak Andi', 'PT Maju Bersama', '081234550001', 'andi@majubersama.com', 60.00),
(2, 6, 6, 'Sari Wedding Reception', 'Personal', 'WEDDING', CURDATE() + INTERVAL 30 DAY, 500, 150000000.00, 'QUALIFIED', 'REFERRAL', 'cater_manager', 'LEAD-002', 'REFERRAL', 'Sari', 'Personal', '081234550002', 'sari@email.com', 40.00),
(3, 6, 6, 'Tech Conference Catering', 'PT Tech Solutions', 'CONFERENCE', CURDATE() + INTERVAL 45 DAY, 300, 55000000.00, 'INQUIRY', 'GOOGLE', 'cater_admin', 'LEAD-003', 'GOOGLE', 'Bu Lina', 'PT Tech Solutions', '081234550003', 'lina@techsol.com', 20.00);

-- ============================================================
-- TIER 2: ALLERGENS (Tenant 1)
-- ============================================================
INSERT IGNORE INTO allergens (allergen_id, allergen_name, description, is_active) VALUES
(1, 'Gluten', 'Wheat, barley, rye proteins', 1),
(2, 'Dairy', 'Milk and milk products', 1),
(3, 'Nuts', 'Tree nuts and peanuts', 1),
(4, 'Shellfish', 'Shrimp, crab, lobster', 1),
(5, 'Eggs', 'Egg proteins', 1),
(6, 'Soy', 'Soy-based products', 1),
(7, 'Fish', 'Fin fish', 1),
(8, 'Sesame', 'Sesame seeds', 1);

INSERT IGNORE INTO allergen_tracking (tracking_id, tenant_id, product_id, allergen_id, contains, is_vegetarian, is_vegan, is_halal, is_kosher) VALUES
(1, 1, 1, 1, 1, 0, 0, 1, 0),
(2, 1, 1, 5, 1, 0, 0, 1, 0),
(3, 1, 2, 1, 0, 0, 0, 1, 0),
(4, 1, 3, 1, 1, 1, 0, 1, 0),
(5, 1, 3, 2, 0, 1, 1, 1, 0),
(6, 1, 4, 2, 0, 1, 1, 1, 0);

-- ============================================================
-- TIER 3: DYNAMIC PRICING (Tenant 13)
-- ============================================================
INSERT IGNORE INTO dynamic_pricing_rules (id, tenant_id, branch_id, rule_name, rule_conditions, price_adjustment_type, price_adjustment_value, min_price, max_price, is_active, trigger_type, trigger_condition, price_modifier_type, price_modifier_value, priority) VALUES
(1, 13, 13, 'Weekend Premium 20%', 'day_type=weekend', 'PERCENTAGE', 20.00, 0, 999999999.00, 1, 'TIME_BASED', 'WEEKEND', 'PERCENTAGE', 20.00, 1),
(2, 13, 13, 'Early Bird Discount 15%', 'time_before_event>=7d', 'PERCENTAGE', -15.00, 0, 999999999.00, 1, 'TIME_BASED', 'EARLY_BIRD', 'PERCENTAGE', -15.00, 2),
(3, 13, 13, 'High Occupancy Surge 30%', 'occupancy_pct>80', 'PERCENTAGE', 30.00, 0, 999999999.00, 1, 'OCCUPANCY', 'HIGH', 'PERCENTAGE', 30.00, 3);

-- ============================================================
-- TIER 3: MEMBERSHIPS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO memberships (membership_id, tenant_id, branch_id, customer_name, member_name, member_email, member_phone, membership_type, tier, points_balance, total_earned, total_redeemed, joined_date, join_date, expiry_date, total_spent, status, is_active) VALUES
(1, 13, 13, 'Andi Wijaya', 'Andi Wijaya', 'andi@example.com', '081234500004', 'VIP', 'PLATINUM', 15000, 50000, 35000, CURDATE() - INTERVAL 6 MONTH, CURDATE() - INTERVAL 6 MONTH, CURDATE() + INTERVAL 6 MONTH, 75000000.00, 'ACTIVE', 1),
(2, 13, 13, 'Sara Dewi', 'Sara Dewi', 'sara@example.com', '081234500002', 'STANDARD', 'GOLD', 5000, 20000, 15000, CURDATE() - INTERVAL 3 MONTH, CURDATE() - INTERVAL 3 MONTH, CURDATE() + INTERVAL 9 MONTH, 30000000.00, 'ACTIVE', 1);

-- ============================================================
-- TIER 3: OCCUPANCY TRACKING (Tenant 13)
-- ============================================================
INSERT IGNORE INTO occupancy_tracking (occupancy_id, tenant_id, branch_id, event_id, tracking_date, current_occupancy, current_count, max_capacity, entry_count, exit_count, status) VALUES
(1, 13, 13, 1, CURDATE(), 185, 185, 300, 190, 5, 'ACTIVE');

-- ============================================================
-- TIER 3: QR TICKET SCANS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO qr_ticket_scans (scan_id, tenant_id, branch_id, event_id, qr_code, scan_time, gate, is_valid, scan_result, scanned_by, device_id) VALUES
(1, 13, 13, 1, 'QR-EVENT1-0001', NOW() - INTERVAL 1 HOUR, 'MAIN_ENTRANCE', 1, 'APPROVED', 'door_staff_01', 'SCANNER-001'),
(2, 13, 13, 1, 'QR-EVENT1-0002', NOW() - INTERVAL 50 MINUTE, 'MAIN_ENTRANCE', 1, 'APPROVED', 'door_staff_01', 'SCANNER-001'),
(3, 13, 13, 1, 'QR-EVENT1-0003', NOW() - INTERVAL 45 MINUTE, 'VIP_ENTRANCE', 1, 'APPROVED', 'door_staff_02', 'SCANNER-002');

-- ============================================================
-- TIER 3: AI SALES PREDICTIONS (Tenant 1)
-- ============================================================
INSERT IGNORE INTO ai_sales_predictions (prediction_id, tenant_id, branch_id, prediction_date, product_id, predicted_qty, confidence_level, model_version, predicted_hour, predicted_revenue, predicted_orders, confidence_score) VALUES
(1, 1, 1, CURDATE(), 1, 45, 'HIGH', 'v2.1', 12, 1575000.00, 45, 0.87),
(2, 1, 1, CURDATE(), 1, 65, 'HIGH', 'v2.1', 18, 2275000.00, 65, 0.89),
(3, 1, 1, CURDATE(), 2, 30, 'MEDIUM', 'v2.1', 19, 1350000.00, 30, 0.72),
(4, 1, 1, CURDATE() + INTERVAL 1 DAY, 1, 50, 'MEDIUM', 'v2.1', 12, 1750000.00, 50, 0.75);

-- ============================================================
-- TIER 3: ORDER THROTTLING (Tenant 1)
-- ============================================================
INSERT IGNORE INTO order_throttling_config (config_id, tenant_id, branch_id, max_orders_per_slot, slot_duration_minutes, is_paused, channel, auto_pause_threshold, is_active) VALUES
(1, 1, 1, 15, 15, 0, 'ONLINE', 12, 1),
(2, 1, 1, 20, 15, 0, 'DINE_IN', 18, 1),
(3, 1, 1, 10, 15, 0, 'DELIVERY', 8, 1);

-- ============================================================
-- TIER 3: AUTO PO RULES (Tenant 1)
-- ============================================================
INSERT IGNORE INTO auto_po_rules (rule_id, tenant_id, branch_id, inventory_id, reorder_point, reorder_quantity, auto_generate, requires_approval, is_active) VALUES
(1, 1, 1, 1, 20, 100, 1, 1, 1),
(2, 1, 1, 2, 15, 80, 1, 1, 1),
(3, 1, 1, 3, 50, 200, 1, 0, 1),
(4, 1, 1, 4, 30, 150, 1, 1, 1);

-- ============================================================
-- TIER 3: DAILY PRODUCTION PLANS (Tenant 1)
-- ============================================================
INSERT IGNORE INTO daily_production_plans (plan_id, tenant_id, branch_id, plan_date, recipe_id, product_id, product_name, planned_quantity, produced_quantity, sold_quantity, wasted_quantity, remaining_quantity, status) VALUES
(1, 1, 1, CURDATE(), 1, 1, 'Nasi Goreng Spesial', 50, 50, 35, 2, 13, 'COMPLETED'),
(2, 1, 1, CURDATE(), 2, 2, 'Ayam Bakar Madu', 30, 28, 20, 1, 7, 'COMPLETED'),
(3, 1, 1, CURDATE() + INTERVAL 1 DAY, 1, 1, 'Nasi Goreng Spesial', 60, 0, 0, 0, 0, 'PLANNED');

-- ============================================================
-- TIER 3: SERVICE SPEED METRICS (Tenant 1)
-- ============================================================
INSERT IGNORE INTO service_speed_metrics (metric_id, tenant_id, branch_id, order_id, metric_date, metric_hour, total_prep_seconds, total_service_seconds, order_type, items_count, order_received_at, order_started_at, order_ready_at, order_served_at) VALUES
(1, 1, 1, 1, CURDATE(), 12, 720, 900, 'DINE_IN', 3, '2026-07-19 12:00:00', '2026-07-19 12:01:00', '2026-07-19 12:13:00', '2026-07-19 12:15:00'),
(2, 1, 1, 2, CURDATE(), 13, 480, 600, 'DINE_IN', 2, '2026-07-19 13:00:00', '2026-07-19 13:02:00', '2026-07-19 13:10:00', '2026-07-19 13:12:00'),
(3, 1, 1, 3, CURDATE(), 18, 900, 1200, 'DINE_IN', 5, '2026-07-19 18:00:00', '2026-07-19 18:03:00', '2026-07-19 18:18:00', '2026-07-19 18:20:00');

-- ============================================================
-- TIER 3: EVENT HOLDS CALENDAR (Tenant 13)
-- ============================================================
INSERT IGNORE INTO event_holds_calendar (hold_id, tenant_id, branch_id, event_id, event_date, artist_name, hold_type, priority_rank, promoter_name, hold_expires_at) VALUES
(1, 13, 13, 1, CURDATE() + INTERVAL 14 DAY, 'DJ Blast', 'SOFT_HOLD', 2, 'Rudi Hartono', CURDATE() + INTERVAL 3 DAY),
(2, 13, 13, 2, CURDATE() + INTERVAL 21 DAY, 'DJ Mixmaster', 'FIRM_HOLD', 1, 'Sara Dewi', CURDATE() + INTERVAL 7 DAY);

-- ============================================================
-- TIER 3: BOOKING CHANNEL SYNC (Tenant 1)
-- ============================================================
INSERT IGNORE INTO booking_channel_sync (sync_id, tenant_id, branch_id, channel_name, sync_status, last_synced_at, booking_count, channel_type) VALUES
(1, 1, 1, 'GoFood', 'SUCCESS', NOW() - INTERVAL 5 MINUTE, 12, 'DELIVERY'),
(2, 1, 1, 'GrabFood', 'SUCCESS', NOW() - INTERVAL 5 MINUTE, 8, 'DELIVERY'),
(3, 1, 1, 'ShopeeFood', 'PARTIAL', NOW() - INTERVAL 10 MINUTE, 5, 'DELIVERY');

-- ============================================================
-- TIER 4: COAT CHECK (Tenant 13)
-- ============================================================
INSERT IGNORE INTO coat_check_items (coat_check_id, tenant_id, branch_id, event_id, item_description, check_number, customer_name, item_type, item_count, fee_charged, fee_paid, checked_in_at, status, handled_by) VALUES
(1, 13, 13, 1, 'Black leather jacket', 'CC-001', 'Andi Wijaya', 'JACKET', 1, 20000.00, 20000.00, NOW() - INTERVAL 2 HOUR, 'CHECKED_IN', 'coat_staff_01'),
(2, 13, 13, 1, 'Blue blazer + bag', 'CC-002', 'Sara Dewi', 'JACKET', 2, 30000.00, 30000.00, NOW() - INTERVAL 90 MINUTE, 'CHECKED_IN', 'coat_staff_01');

-- ============================================================
-- TIER 4: EQUIPMENT ASSETS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO equipment_assets (equipment_id, asset_id, tenant_id, branch_id, asset_name, equipment_name, category, equipment_type, serial_number, brand, model, purchase_cost, condition_status, status) VALUES
(1, 1, 13, 13, 'Pioneer CDJ-3000', 'Pioneer CDJ-3000', 'DJ_EQUIPMENT', 'CD_PLAYER', 'PION-CDJ3K-001', 'Pioneer', 'CDJ-3000', 25000000.00, 'EXCELLENT', 'IN_USE'),
(2, 2, 13, 13, 'JBL VTX V25', 'JBL VTX V25 Speaker', 'SOUND_SYSTEM', 'SPEAKER', 'JBL-V25-001', 'JBL', 'VTX V25', 45000000.00, 'GOOD', 'IN_USE'),
(3, 3, 13, 13, 'Shure SM58 Mic', 'Shure SM58 Microphone', 'AUDIO', 'MICROPHONE', 'SHU-SM58-001', 'Shure', 'SM58', 1500000.00, 'GOOD', 'IN_USE');

-- ============================================================
-- TIER 4: WINE LIST (Tenant 7 - Fine Dining)
-- ============================================================
INSERT IGNORE INTO wine_list (wine_id, tenant_id, branch_id, wine_name, vintage, varietal, region, bottle_price, glass_price, stock_qty, is_active, country, wine_type, cost_per_bottle, inventory_bottles, tasting_notes, alcohol_pct, rating, is_available) VALUES
(1, 7, 7, 'Chateau Margaux', 2015, 'Cabernet Sauvignon', 'Bordeaux, France', 8500000.00, 1500000.00, 12, 1, 'France', 'RED', 6000000.00, 12, 'Full-bodied with notes of blackcurrant and cedar', 13.5, 4.8, 1),
(2, 7, 7, 'Dom Perignon Brut', 2012, 'Chardonnay/Pinot Noir', 'Champagne, France', 5500000.00, 900000.00, 8, 1, 'France', 'SPARKLING', 4000000.00, 8, 'Crisp with brioche and citrus notes', 12.5, 4.7, 1),
(3, 7, 7, 'Penfolds Grange', 2017, 'Shiraz', 'South Australia', 6500000.00, 1100000.00, 6, 1, 'Australia', 'RED', 4800000.00, 6, 'Rich dark fruit with chocolate and spice', 14.5, 4.9, 1);

INSERT IGNORE INTO wine_pairing_suggestions (pairing_id, tenant_id, product_id, wine_id, pairing_notes) VALUES
(1, 7, 2, 1, 'Cabernet pairs perfectly with grilled meats'),
(2, 7, 1, 3, 'Shiraz complements spicy Asian dishes');

-- ============================================================
-- TIER 4: WAITER BUTTON PRESSES (Tenant 13)
-- ============================================================
INSERT IGNORE INTO waiter_button_presses (press_id, tenant_id, branch_id, room_id, pressed_at, responded_at, responded_by, status, response_seconds) VALUES
(1, 13, 13, 3, NOW() - INTERVAL 1 HOUR, NOW() - INTERVAL 55 MINUTE, 'waiter_01', 'RESPONDED', 300),
(2, 13, 13, 3, NOW() - INTERVAL 30 MINUTE, NOW() - INTERVAL 25 MINUTE, 'waiter_01', 'RESPONDED', 300),
(3, 13, 13, 1, NOW() - INTERVAL 10 MINUTE, NULL, NULL, 'PENDING', 0);

-- ============================================================
-- TIER 4: ENTERTAINER ROTATIONS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO entertainer_rotations (rotation_id, tenant_id, branch_id, event_id, entertainer_name, stage, start_time, end_time, performance_type, entertainer_type, set_number, set_start_time, set_end_time, set_duration_minutes, status) VALUES
(1, 13, 13, 1, 'DJ Blast', 'MAIN_STAGE', '22:00:00', '04:00:00', 'DJ_SET', 'DJ', 1, '22:00:00', '23:30:00', 90, 'COMPLETED'),
(2, 13, 13, 1, 'DJ Blast', 'MAIN_STAGE', '22:00:00', '04:00:00', 'DJ_SET', 'DJ', 2, '00:00:00', '01:30:00', 90, 'COMPLETED'),
(3, 13, 13, 1, 'DJ Sparkle', 'MAIN_STAGE', '22:00:00', '04:00:00', 'DJ_SET', 'DJ', 3, '02:00:00', '04:00:00', 120, 'IN_PROGRESS');

-- ============================================================
-- TIER 4: SOCIAL GROUP BOOKINGS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO social_group_bookings (group_booking_id, sgb_id, tenant_id, branch_id, event_date, organizer_name, organizer_phone, organizer_email, event_name, total_party_size, per_person_amount, total_amount, deposit_collected, split_type, status) VALUES
(1, 1, 13, 13, CURDATE() + INTERVAL 7 DAY, 'Andi Wijaya', '081234500004', 'andi@example.com', 'Birthday Party Group', 10, 500000.00, 5000000.00, 2500000.00, 'EVEN_SPLIT', 'CONFIRMED');

-- ============================================================
-- TIER 4: RADIUS CLAUSE CHECKS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO radius_clause_checks (check_id, tenant_id, deal_id, artist_name, clause_radius_km, clause_days, event_date, conflicting_venue, conflicting_venue_distance_km, conflicting_event_date, check_result) VALUES
(1, 13, 1, 'DJ Blast', 50, 30, CURDATE() + INTERVAL 7 DAY, 'Club X Jakarta', 15, CURDATE() + INTERVAL 5 DAY, 'VIOLATION'),
(2, 13, 2, 'DJ Mixmaster', 100, 60, CURDATE() + INTERVAL 14 DAY, NULL, NULL, NULL, 'CLEARED');

-- ============================================================
-- GAP FEATURES: ID SCANS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO id_scans (scan_id, tenant_id, branch_id, event_id, guest_name, id_type, id_number, date_of_birth, age_calculated, is_over_21, is_over_18, scan_result, scanned_by) VALUES
(1, 13, 13, 1, 'Andi Wijaya', 'KTP', '3171234567890001', '1995-05-15', 31, 1, 1, 'APPROVED', 'door_staff_01'),
(2, 13, 13, 1, 'Sara Dewi', 'KTP', '3171234567890002', '1998-08-22', 27, 1, 1, 'APPROVED', 'door_staff_01'),
(3, 13, 13, 1, 'Young Guest', 'SIM', '3171234567890003', '2010-03-10', 16, 0, 0, 'REJECTED', 'door_staff_01');

-- ============================================================
-- GAP FEATURES: BEVERAGE COGS (Tenant 3 - Bar)
-- ============================================================
INSERT IGNORE INTO beverage_cogs (cogs_id, tenant_id, branch_id, report_date, beverage_category, product_id, product_name, unit_type, opening_qty, received_qty, sold_qty, closing_qty, unit_cost, total_cost, revenue, pour_cost_pct) VALUES
(1, 3, 3, CURDATE() - INTERVAL 1 DAY, 'DRAFT_BEER', 1, 'Draft Beer - Heineken', 'KEG', 2, 1, 0.8, 2.2, 1500000.00, 1200000.00, 2400000.00, 50.00),
(2, 3, 3, CURDATE() - INTERVAL 1 DAY, 'SPIRITS', 2, 'Vodka - Absolut', 'BOTTLE', 5, 2, 1.5, 5.5, 850000.00, 1275000.00, 4500000.00, 28.33),
(3, 3, 3, CURDATE() - INTERVAL 1 DAY, 'WINE', NULL, 'House Red Wine', 'BOTTLE', 8, 0, 3, 5, 500000.00, 1500000.00, 4500000.00, 33.33);

-- ============================================================
-- GAP FEATURES: E-SIGNATURES (Tenant 6 - Catering)
-- ============================================================
INSERT IGNORE INTO e_signatures (signature_id, tenant_id, branch_id, contract_id, document_type, document_title, document_content, document_hash, signer_name, signer_email, signer_role, signed_at, status, expires_at) VALUES
(1, 6, 6, 1, 'CATERING_CONTRACT', 'Catering Contract - PT Maju Bersama', 'This agreement is between EBP Catering and PT Maju Bersama for corporate lunch catering services on specified date...', SHA2('contract1', 256), 'Pak Andi', 'andi@majubersama.com', 'CLIENT', NULL, 'PENDING', CURDATE() + INTERVAL 30 DAY),
(2, 6, 6, 2, 'CATERING_CONTRACT', 'Wedding Contract - Sari Wedding', 'This agreement is between EBP Catering and Sari for wedding reception catering services...', SHA2('contract2', 256), 'Sari', 'sari@email.com', 'CLIENT', NOW() - INTERVAL 2 DAY, 'SIGNED', CURDATE() + INTERVAL 60 DAY);

-- ============================================================
-- GAP FEATURES: CORPORATE MEAL SUBSCRIPTIONS (Tenant 6)
-- ============================================================
INSERT IGNORE INTO corporate_meal_subscriptions (subscription_id, tenant_id, branch_id, company_name, contact_person, contact_phone, contact_email, meal_plan, head_count, delivery_address, delivery_time, frequency, days_of_week, price_per_head, monthly_total, start_date, end_date, auto_renew, status) VALUES
(1, 6, 6, 'PT Tech Solutions', 'Bu Lina', '081234550003', 'lina@techsol.com', 'DAILY_LUNCH', 50, 'Jl. Sudirman Kav. 52, Jakarta', '12:00:00', 'WEEKLY', 'MON,TUE,WED,THU,FRI', 35000.00, 38500000.00, CURDATE() - INTERVAL 30 DAY, CURDATE() + INTERVAL 335 DAY, 1, 'ACTIVE');

INSERT IGNORE INTO corporate_meal_deliveries (delivery_id, subscription_id, tenant_id, delivery_date, head_count_served, total_amount, status, delivered_at) VALUES
(1, 1, 6, CURDATE() - INTERVAL 1 DAY, 48, 1680000.00, 'DELIVERED', NOW() - INTERVAL 1 DAY),
(2, 1, 6, CURDATE(), 50, 1750000.00, 'DELIVERED', NOW() - INTERVAL 2 HOUR);

-- ============================================================
-- GAP FEATURES: DRIVE-THRU SESSIONS (Tenant 4 - Fast Food)
-- ============================================================
INSERT IGNORE INTO drive_thru_sessions (session_id, tenant_id, branch_id, lane_number, vehicle_description, detected_at, greeted_at, order_taken_at, payment_at, pickup_at, total_wait_seconds, status, order_total) VALUES
(1, 4, 4, 1, 'Red Toyota Avanza', NOW() - INTERVAL 30 MINUTE, NOW() - INTERVAL 28 MINUTE, NOW() - INTERVAL 25 MINUTE, NOW() - INTERVAL 20 MINUTE, NOW() - INTERVAL 18 MINUTE, 720, 'COMPLETED', 75000.00),
(2, 4, 4, 1, 'Black Honda Brio', NOW() - INTERVAL 10 MINUTE, NOW() - INTERVAL 8 MINUTE, NOW() - INTERVAL 5 MINUTE, NULL, NULL, NULL, 'PAID', 45000.00),
(3, 4, 4, 1, 'White Suzuki Ertiga', NOW() - INTERVAL 2 MINUTE, NOW() - INTERVAL 1 MINUTE, NULL, NULL, NULL, NULL, 'ORDERED', 0.00);

-- ============================================================
-- GAP FEATURES: TASTING MENUS (Tenant 7 - Fine Dining)
-- ============================================================
INSERT IGNORE INTO tasting_menus (tasting_menu_id, tenant_id, branch_id, menu_name, description, price_per_cover, course_count, is_active) VALUES
(1, 7, 7, 'Chef\'s Signature Tasting Menu', 'A 7-course journey through Indonesian-modern fusion cuisine', 1250000.00, 7, 1),
(2, 7, 7, 'Seasonal Seafood Tasting', 'Fresh catch of the day prepared 5 ways', 950000.00, 5, 1);

INSERT IGNORE INTO tasting_menu_courses (course_id, tasting_menu_id, course_number, course_name, course_description, product_id, pairing_beverage, prep_time_minutes, is_optional) VALUES
(1, 1, 1, 'Amuse Bouche', 'Complimentary bite from the chef', NULL, 'Champagne', 5, 0),
(2, 1, 2, 'First Course - Sashimi', 'Fresh tuna sashimi with ponzu', NULL, 'Sake', 10, 0),
(3, 1, 3, 'Second Course - Soup', 'Velvety pumpkin soup with truffle', NULL, 'Chardonnay', 15, 0),
(4, 1, 4, 'Third Course - Seafood', 'Pan-seared scallops with cauliflower puree', NULL, 'Sauvignon Blanc', 20, 0),
(5, 1, 5, 'Fourth Course - Meat', 'Wagyu beef with bone marrow reduction', NULL, 'Cabernet Sauvignon', 25, 0),
(6, 1, 6, 'Cheese Course', 'Selection of artisanal cheeses', NULL, 'Port', 10, 1),
(7, 1, 7, 'Dessert', 'Dark chocolate fondant with vanilla ice cream', NULL, 'Dessert Wine', 15, 0);

INSERT IGNORE INTO tasting_menu_reservations (reservation_id, tenant_id, branch_id, tasting_menu_id, customer_name, phone, party_size, reservation_date, reservation_time, total_amount, status, special_requests) VALUES
(1, 7, 7, 1, 'Budi Santoso', '081234567890', 4, CURDATE() + INTERVAL 3 DAY, '19:30:00', 5000000.00, 'CONFIRMED', 'Anniversary dinner, please prepare a candle on dessert'),
(2, 7, 7, 2, 'Ani Wijaya', '082345678901', 2, CURDATE() + INTERVAL 7 DAY, '20:00:00', 1900000.00, 'PENDING', NULL);

-- ============================================================
-- GAP FEATURES: RESERVATION DEPOSITS (Tenant 7 - Fine Dining)
-- ============================================================
INSERT IGNORE INTO reservation_deposits (deposit_id, tenant_id, branch_id, reservation_id, customer_name, phone, party_size, reservation_date, reservation_time, deposit_amount, deposit_type, payment_method, payment_ref, paid_at, no_show_cutoff, status) VALUES
(1, 7, 7, 1, 'Budi Santoso', '081234567890', 4, CURDATE() + INTERVAL 3 DAY, '19:30:00', 2000000.00, 'FIXED', 'CREDIT_CARD', 'CC-REF-001', NOW() - INTERVAL 1 DAY, CONCAT(CURDATE() + INTERVAL 3 DAY, ' 19:30:00'), 'PAID'),
(2, 7, 7, 2, 'Ani Wijaya', '082345678901', 2, CURDATE() + INTERVAL 7 DAY, '20:00:00', 500000.00, 'PER_PERSON', 'BANK_TRANSFER', 'VA-BCA-001', NOW() - INTERVAL 2 DAY, CONCAT(CURDATE() + INTERVAL 7 DAY, ' 20:00:00'), 'PAID');

-- ============================================================
-- SPLIT BILLS (Tenant 1)
-- ============================================================
INSERT IGNORE INTO split_bills (split_bill_id, order_id, split_type, total_splits) VALUES
(1, 1, 'PER_PERSON', 3),
(2, 2, 'CUSTOM', 2);
