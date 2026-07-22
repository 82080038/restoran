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
INSERT IGNORE INTO variance_reports (report_id, tenant_id, branch_id, report_date, period_start, period_end, total_expected_cost, total_actual_cost, total_variance_cost, total_variance_pct, pour_cost_pct, items_with_variance, status) VALUES
(1, 3, 3, CURDATE() - INTERVAL 1 DAY, CURDATE() - INTERVAL 1 DAY, CURDATE() - INTERVAL 1 DAY, 5000000.00, 5585000.00, -585000.00, -11.70, 25.50, 8, 'REVIEWED'),
(2, 3, 3, CURDATE() - INTERVAL 2 DAY, CURDATE() - INTERVAL 2 DAY, CURDATE() - INTERVAL 2 DAY, 4000000.00, 4350000.00, -350000.00, -8.75, 28.00, 5, 'FINALIZED');

-- ============================================================
-- TIER 1: RECIPE DEPLETION (Tenant 1 - Restaurant)
-- ============================================================
INSERT IGNORE INTO recipe_depletion_logs (log_id, tenant_id, branch_id, order_id, product_id, recipe_id, quantity_sold, ingredient_inventory_item_id, ingredient_name, depletion_quantity, depletion_unit, depletion_cost) VALUES
(1, 1, 1, 1, 1, 1, 5.00, 1, 'Rice', 2.50, 'kg', 25000.00),
(2, 1, 1, 1, 1, 1, 5.00, 2, 'Chicken', 1.00, 'kg', 35000.00),
(3, 1, 1, 2, 2, 2, 3.00, 3, 'Vegetables', 0.50, 'kg', 15000.00);

-- ============================================================
-- TIER 1: PRODUCTION BATCHES (Tenant 1 - Restaurant Bakery)
-- ============================================================
INSERT IGNORE INTO production_batches (batch_id, tenant_id, branch_id, recipe_id, batch_number, quantity, status, yield_percentage, production_date) VALUES
(1, 1, 1, 1, 'BATCH-20260718-001', 50, 'COMPLETED', 98.00, CURDATE() - INTERVAL 1 DAY),
(2, 1, 1, 2, 'BATCH-20260719-001', 30, 'IN_PROGRESS', NULL, CURDATE());

-- ============================================================
-- TIER 1: SETTLEMENTS (Tenant 13 - Nightclub/Live Music)
-- ============================================================
INSERT IGNORE INTO settlements (settlement_id, tenant_id, branch_id, settlement_type, settlement_date, estimated_ticket_revenue, actual_ticket_revenue, ticket_count_sold, ticket_count_comp, bar_revenue, merch_revenue, total_revenue, artist_guarantee, artist_payout, venue_production_cost, venue_profit, status) VALUES
(1, 13, 13, 'INTERNAL', CURDATE() - INTERVAL 7 DAY, 15000000.00, 15000000.00, 150, 10, 25000000.00, 2000000.00, 42000000.00, 12000000.00, 12000000.00, 5000000.00, 25000000.00, 'FINALIZED'),
(2, 13, 13, 'INTERNAL', CURDATE() - INTERVAL 3 DAY, 8000000.00, 8000000.00, 80, 5, 18000000.00, 1000000.00, 27000000.00, 6000000.00, 6000000.00, 3000000.00, 18000000.00, 'DRAFT');

-- ============================================================
-- TIER 1: EVENT PROFITABILITY (Tenant 13)
-- ============================================================
INSERT IGNORE INTO event_profitability (profitability_id, tenant_id, branch_id, event_type, event_id, event_name, event_date, ticket_revenue, fnb_revenue, bar_revenue, merch_revenue, other_revenue, total_revenue, cogs, labor_cost, artist_cost, production_cost, marketing_cost, overhead_cost, other_cost, total_cost, gross_profit, gross_margin_pct, net_profit, net_margin_pct, attendance, revenue_per_head, cost_per_head, profit_per_head, status) VALUES
(1, 13, 13, 'NIGHTCLUB_EVENT', 1, 'Neon Friday Night', CURDATE() - INTERVAL 7 DAY, 15000000.00, 5000000.00, 25000000.00, 2000000.00, 0.00, 47000000.00, 8000000.00, 5000000.00, 12000000.00, 3000000.00, 1000000.00, 2000000.00, 0.00, 31000000.00, 39000000.00, 82.98, 16000000.00, 34.04, 160, 293750.00, 193750.00, 100000.00, 'FINALIZED');

-- ============================================================
-- TIER 1: EVENT PROPOSALS (Tenant 6 - Catering)
-- ============================================================
INSERT IGNORE INTO event_proposals (proposal_id, tenant_id, branch_id, proposal_number, client_name, client_company, client_phone, client_email, event_name, event_type, event_date, guest_count, service_style, per_head_price, subtotal, total_amount, deposit_required, deposit_paid, balance_due, status, notes) VALUES
(1, 6, 6, 'PROP-2026-001', 'Pak Andi', 'PT Maju Bersama', '081234550001', 'andi@majubersama.com', 'Annual Corporate Lunch', 'CORPORATE_LUNCH', CURDATE() + INTERVAL 14 DAY, 200, 'BUFFET', 175000.00, 35000000.00, 35000000.00, 5000000.00, 5000000.00, 30000000.00, 'SENT', 'Annual corporate lunch for 200 pax'),
(2, 6, 6, 'PROP-2026-002', 'Sari', 'Personal', '081234550002', 'sari@email.com', 'Wedding Reception', 'WEDDING', CURDATE() + INTERVAL 30 DAY, 500, 'PLATED', 300000.00, 150000000.00, 150000000.00, 30000000.00, 0.00, 150000000.00, 'DRAFT', 'Wedding reception - Indonesian menu');

-- ============================================================
-- TIER 2: NIGHTCLUB ADVANCED (Tenant 13)
-- ============================================================
INSERT IGNORE INTO table_deposits (deposit_id, tenant_id, branch_id, customer_name, customer_phone, event_date, deposit_amount, deposit_status, payment_method, payment_ref, minimum_spend, no_show_cutoff) VALUES
(1, 13, 13, 'Andi Wijaya', '081234500004', CURDATE() + INTERVAL 2 DAY, 2500000.00, 'PAID', 'CREDIT_CARD', 'CC-TD-001', 5000000.00, '23:59:00'),
(2, 13, 13, 'Chandra Tan', '081234500006', CURDATE() + INTERVAL 6 DAY, 4000000.00, 'PAID', 'BANK_TRANSFER', 'VA-TD-002', 8000000.00, '23:59:00');

INSERT IGNORE INTO nightclub_bottle_service (bottle_service_id, tenant_id, branch_id, event_id, customer_name, phone, party_size, package_name, bottle_type, bottle_quantity, unit_price, minimum_spend, total_amount, reservation_date, status, payment_status, payment_method) VALUES
(1, 13, 13, 1, 'Andi Wijaya', '081234500004', 4, 'Johnnie Walker Gold Label VIP Package', 'Johnnie Walker Gold Label 700ml', 2, 2500000.00, 5000000.00, 5000000.00, CURDATE() + INTERVAL 2 DAY, 'CONFIRMED', 'PAID', 'CREDIT_CARD'),
(2, 13, 13, 2, 'Chandra Tan', '081234500006', 6, 'Grey Goose VIP Package', 'Grey Goose Vodka 750ml', 3, 3000000.00, 8000000.00, 9000000.00, CURDATE() + INTERVAL 6 DAY, 'CONFIRMED', 'PAID', 'BANK_TRANSFER'),
(3, 13, 13, 1, 'Sara Dewi', '081234500002', 2, 'Moet & Chandon Champagne Package', 'Moet & Chandon Imperial 750ml', 1, 2200000.00, 3000000.00, 2200000.00, CURDATE() + INTERVAL 2 DAY, 'CONFIRMED', 'PENDING', NULL);

INSERT IGNORE INTO promoters (promoter_id, tenant_id, branch_id, promoter_name, promoter_code, phone, email, commission_type, commission_rate, guest_list_limit, is_active, total_guests_brought, total_commission_earned) VALUES
(1, 13, 13, 'Rudi Hartono', 'PROM001', '081234510001', 'rudi@neonclub.com', 'PER_HEAD', 15.00, 50, 1, 120, 1800000.00),
(2, 13, 13, 'Sara Dewi', 'PROM002', '081234510002', 'sara@neonclub.com', 'PER_HEAD', 10.00, 30, 1, 85, 850000.00),
(3, 13, 13, 'Joko Susilo', 'PROM003', '081234510003', 'joko@neonclub.com', 'PERCENTAGE', 12.00, 40, 1, 60, 720000.00);

INSERT IGNORE INTO promoter_guest_lists (guest_id, promoter_id, tenant_id, branch_id, event_id, guest_name, guest_phone, party_size, check_in_status, entry_type, commission_amount) VALUES
(1, 1, 13, 13, 1, 'Dewi Asmara', '081234520001', 3, 'CHECKED_IN', 'FREE', 45000.00),
(2, 1, 13, 13, 1, 'Bambang Tri', '081234520002', 2, 'CHECKED_IN', 'FREE', 30000.00),
(3, 2, 13, 13, 2, 'Lina Marlina', '081234520003', 4, 'EXPECTED', 'FREE', 0.00),
(4, 3, 13, 13, 3, 'Agus Setiawan', '081234520004', 2, 'CHECKED_IN', 'FREE', 24000.00);

-- ============================================================
-- TIER 2: KARAOKE ADVANCED (Tenant 13 has karaoke rooms too)
-- ============================================================
INSERT IGNORE INTO karaoke_song_catalog (song_id, tenant_id, song_code, title, artist, genre, language, year, duration_seconds, play_count, is_active) VALUES
(1, 13, 'KAR001', 'Bohemian Rhapsody', 'Queen', 'ROCK', 'EN', 1975, 354, 342, 1),
(2, 13, 'KAR002', 'Dangdut Lawas', 'Rhoma Irama', 'DANGDUT', 'ID', 1985, 285, 567, 1),
(3, 13, 'KAR003', 'Shape of You', 'Ed Sheeran', 'POP', 'EN', 2017, 233, 891, 1),
(4, 13, 'KAR004', 'Bunga', 'Ari Lasso', 'POP', 'ID', 2003, 278, 234, 1),
(5, 13, 'KAR005', 'Sweet Caroline', 'Neil Diamond', 'POP', 'EN', 1969, 201, 456, 1);

INSERT IGNORE INTO karaoke_rooms (room_id, tenant_id, branch_id, room_code, room_name, capacity, hourly_rate, minimum_spend, has_private_bathroom, has_waiter_button, equipment_status, is_active) VALUES
(1, 13, 13, 'KR001', 'VIP Room 1', 8, 500000.00, 2000000.00, 1, 1, 'ACTIVE', 1),
(2, 13, 13, 'KR002', 'VIP Room 2', 10, 750000.00, 3000000.00, 1, 1, 'ACTIVE', 1),
(3, 13, 13, 'KR003', 'Standard Room 1', 6, 300000.00, 800000.00, 0, 1, 'ACTIVE', 1),
(4, 13, 13, 'KR004', 'Standard Room 2', 6, 300000.00, 800000.00, 0, 1, 'MAINTENANCE', 0);

INSERT IGNORE INTO karaoke_room_calendar (calendar_id, tenant_id, branch_id, room_id, start_time, end_time, status, customer_name) VALUES
(1, 13, 13, 3, CONCAT(CURDATE(), ' 20:00:00'), CONCAT(CURDATE(), ' 23:00:00'), 'BOOKED', 'Eka Putra'),
(2, 13, 13, 1, CONCAT(CURDATE() + INTERVAL 1 DAY, ' 21:00:00'), CONCAT(CURDATE() + INTERVAL 1 DAY, ' 01:00:00'), 'BOOKED', 'Fajar Nugroho');

INSERT IGNORE INTO karaoke_overtime_charges (overtime_id, tenant_id, branch_id, room_id, overtime_minutes, overtime_rate_per_hour, overtime_charge, status) VALUES
(1, 13, 13, 3, 30, 75000.00, 37500.00, 'PENDING');

INSERT IGNORE INTO karaoke_scores (score_id, tenant_id, branch_id, room_id, song_id, singer_name, score, pitch_accuracy, rhythm_accuracy, volume_level, duration_seconds, applause_rating, scored_at) VALUES
(1, 13, 13, 3, 1, 'Eka Putra', 92.00, 88.00, 95.00, 90.00, 354, 4, NOW() - INTERVAL 1 HOUR),
(2, 13, 13, 3, 5, 'Eka Putra', 87.00, 82.00, 90.00, 85.00, 201, 3, NOW() - INTERVAL 30 MINUTE);

-- ============================================================
-- TIER 2: BEACH CLUB (Use tenant 13 for beach seats too)
-- ============================================================
INSERT IGNORE INTO beach_seat_map (seat_id, tenant_id, branch_id, zone_name, seat_label, seat_type, capacity, position_x, position_y, base_price, premium_multiplier, minimum_spend, is_bookable) VALUES
(1, 13, 13, 'Beach Cabanas', 'CAB-01', 'CABANA', 6, 10, 10, 2500000.00, 1.50, 5000000.00, 1),
(2, 13, 13, 'Beach Cabanas', 'CAB-02', 'CABANA', 6, 30, 10, 2500000.00, 1.50, 5000000.00, 1),
(3, 13, 13, 'Sun Loungers', 'SUN-01', 'LOUNGER', 1, 50, 20, 250000.00, 1.00, 0.00, 1),
(4, 13, 13, 'Sun Loungers', 'SUN-02', 'LOUNGER', 1, 60, 20, 250000.00, 1.00, 0.00, 1),
(5, 13, 13, 'Day Beds', 'DAY-01', 'DAY_BED', 4, 70, 30, 1500000.00, 1.20, 3000000.00, 1);

INSERT IGNORE INTO weather_policies (policy_id, tenant_id, branch_id, policy_name, rain_threshold_mm, auto_issue_rain_check, reschedule_window_days, refund_policy, partial_refund_pct, is_active) VALUES
(1, 13, 13, 'Standard Rain Policy', 5.00, 1, 14, 'FULL', 50.00, 1);

INSERT IGNORE INTO weather_rain_checks (rain_check_id, tenant_id, branch_id, booking_id, customer_name, original_date, weather_condition, rescheduled_date, refund_amount, status, expiry_date) VALUES
(1, 13, 13, 1, 'Andi Wijaya', CURDATE() - INTERVAL 5 DAY, 'Heavy Rain', CURDATE() + INTERVAL 7 DAY, 2500000.00, 'RESCHEDULED', CURDATE() + INTERVAL 14 DAY);

-- ============================================================
-- TIER 2: SPORTS BAR ADVANCED (Tenant 3 - Bar)
-- ============================================================
INSERT IGNORE INTO keg_tracking (keg_id, tenant_id, branch_id, product_id, keg_number, size_liters, received_date, tapped_date, full_weight_kg, empty_weight_kg, current_weight_kg, theoretical_remaining_liters, status) VALUES
(1, 3, 3, 1, 'KEG-001', 50.00, CURDATE() - INTERVAL 10 DAY, CURDATE() - INTERVAL 5 DAY, 30.00, 8.00, 18.50, 17.50, 'TAPPED'),
(2, 3, 3, 2, 'KEG-002', 50.00, CURDATE() - INTERVAL 7 DAY, CURDATE() - INTERVAL 3 DAY, 30.00, 8.00, 22.00, 23.33, 'TAPPED'),
(3, 3, 3, 1, 'KEG-003', 50.00, CURDATE() - INTERVAL 15 DAY, CURDATE() - INTERVAL 12 DAY, 30.00, 8.00, 2.00, 0.00, 'EMPTY');

-- ============================================================
-- TIER 2: OPERATIONS ADVANCED (Tenant 1)
-- ============================================================
INSERT IGNORE INTO item_86_status (id, tenant_id, branch_id, product_id, is_86ed, 86ed_at, 86ed_by, reason, expected_restock_date) VALUES
(1, 1, 1, 6, 1, NOW() - INTERVAL 2 HOUR, 1, 'Out of stock - supply issue', CURDATE() + INTERVAL 1 DAY),
(2, 1, 1, 3, 0, NULL, NULL, NULL, NULL);

INSERT IGNORE INTO custom_orders (custom_order_id, tenant_id, branch_id, order_number, customer_name, customer_phone, product_name, product_description, specifications, quantity, unit_price, total_price, deposit_required, deposit_paid, pickup_date, pickup_time, fulfillment_type, status, notes) VALUES
(1, 1, 1, 'CO-2026-001', 'Siti Aminah', '08333333333', 'Custom Birthday Cake', '3-tier chocolate strawberry cake', '{"instructions": "Chocolate with strawberry filling, write Happy Birthday Sari"}', 1, 850000.00, 850000.00, 425000.00, 425000.00, CURDATE() + INTERVAL 3 DAY, '14:00:00', 'PICKUP', 'IN_PRODUCTION', 'Birthday cake for 20 people'),
(2, 1, 1, 'CO-2026-002', 'Budi Santoso', '081234567890', 'Office Lunch Boxes', 'Nasi goreng + ayam bakar combo', '{"instructions": "10 lunch boxes with nasi goreng and ayam bakar"}', 10, 35000.00, 350000.00, 175000.00, 0.00, CURDATE() + INTERVAL 5 DAY, '12:00:00', 'DELIVERY', 'PENDING', 'Catering for office meeting');

INSERT IGNORE INTO delivery_routes (route_id, tenant_id, branch_id, route_date, driver_name, driver_phone, vehicle, total_stops, estimated_duration_minutes, status) VALUES
(1, 1, 1, CURDATE(), 'Joko Driver', '081234540001', 'Motorcycle - B 1234 ABC', 5, 120, 'IN_PROGRESS'),
(2, 1, 1, CURDATE() + INTERVAL 1 DAY, 'Andi Driver', '081234540002', 'Van - B 5678 XYZ', 3, 90, 'PLANNED');

INSERT IGNORE INTO catering_lead_pipeline (lead_id, tenant_id, branch_id, lead_number, lead_source, client_name, client_company, client_phone, client_email, event_type, event_date, guest_count, estimated_value, stage, probability_pct) VALUES
(1, 6, 6, 'LEAD-001', 'WEBSITE', 'Pak Andi', 'PT Maju Bersama', '081234550001', 'andi@majubersama.com', 'CORPORATE_LUNCH', CURDATE() + INTERVAL 14 DAY, 200, 35000000.00, 'PROPOSAL_SENT', 60),
(2, 6, 6, 'LEAD-002', 'REFERRAL', 'Sari', 'Personal', '081234550002', 'sari@email.com', 'WEDDING', CURDATE() + INTERVAL 30 DAY, 500, 150000000.00, 'QUALIFIED', 40),
(3, 6, 6, 'LEAD-003', 'GOOGLE', 'Bu Lina', 'PT Tech Solutions', '081234550003', 'lina@techsol.com', 'CONFERENCE', CURDATE() + INTERVAL 45 DAY, 300, 55000000.00, 'INQUIRY', 20);

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

INSERT IGNORE INTO allergen_tracking (allergen_id, tenant_id, product_id, allergens, dietary_tags, contains_gluten, contains_dairy, contains_nuts, contains_eggs, contains_soy, contains_shellfish, contains_fish, contains_sesame, is_vegetarian, is_vegan, is_halal, is_kosher) VALUES
(1, 1, 1, '["Gluten", "Eggs"]', '["Halal"]', 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0),
(2, 1, 2, '[]', '["Halal"]', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(3, 1, 3, '["Gluten"]', '["Vegetarian", "Vegan", "Halal"]', 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0),
(4, 1, 4, '[]', '["Vegetarian", "Vegan", "Halal"]', 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0);

-- ============================================================
-- TIER 3: DYNAMIC PRICING (Tenant 13)
-- ============================================================
INSERT IGNORE INTO dynamic_pricing_rules (rule_id, tenant_id, branch_id, rule_name, trigger_type, trigger_condition, price_modifier_type, price_modifier_value, min_price, max_price, priority, is_active) VALUES
(1, 13, 13, 'Weekend Premium 20%', 'DAY_OF_WEEK', '{"days":["SAT","SUN"]}', 'PERCENTAGE', 20.00, 0, 999999999.00, 1, 1),
(2, 13, 13, 'Early Bird Discount 15%', 'TIME_OF_DAY', '{"before_event_days":7}', 'PERCENTAGE', -15.00, 0, 999999999.00, 2, 1),
(3, 13, 13, 'High Occupancy Surge 30%', 'OCCUPANCY', '{"threshold_pct":80}', 'PERCENTAGE', 30.00, 0, 999999999.00, 3, 1);

-- ============================================================
-- TIER 3: MEMBERSHIPS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO memberships (membership_id, tenant_id, branch_id, member_name, member_email, member_phone, tier, points_balance, total_spent, join_date, expiry_date, status) VALUES
(1, 13, 13, 'Andi Wijaya', 'andi@example.com', '081234500004', 'PLATINUM', 15000, 75000000.00, CURDATE() - INTERVAL 6 MONTH, CURDATE() + INTERVAL 6 MONTH, 'ACTIVE'),
(2, 13, 13, 'Sara Dewi', 'sara@example.com', '081234500002', 'GOLD', 5000, 30000000.00, CURDATE() - INTERVAL 3 MONTH, CURDATE() + INTERVAL 9 MONTH, 'ACTIVE');

-- ============================================================
-- TIER 3: OCCUPANCY TRACKING (Tenant 13)
-- ============================================================
INSERT IGNORE INTO occupancy_tracking (occupancy_id, tenant_id, branch_id, tracking_date, current_occupancy, max_capacity, entry_count, exit_count, status) VALUES
(1, 13, 13, CURDATE(), 185, 300, 190, 5, 'OPEN');

-- ============================================================
-- TIER 3: QR TICKET SCANS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO qr_ticket_scans (scan_id, tenant_id, branch_id, event_id, qr_code, scan_result, scanned_by, scanned_at, device_id) VALUES
(1, 13, 13, 1, 'QR-EVENT1-0001', 'VALID', 1, NOW() - INTERVAL 1 HOUR, 'SCANNER-001'),
(2, 13, 13, 1, 'QR-EVENT1-0002', 'VALID', 1, NOW() - INTERVAL 50 MINUTE, 'SCANNER-001'),
(3, 13, 13, 1, 'QR-EVENT1-0003', 'VALID', 2, NOW() - INTERVAL 45 MINUTE, 'SCANNER-002');

-- ============================================================
-- TIER 3: AI SALES PREDICTIONS (Tenant 1)
-- ============================================================
INSERT IGNORE INTO ai_sales_predictions (prediction_id, tenant_id, branch_id, prediction_date, predicted_hour, predicted_revenue, predicted_orders, confidence_score, model_version) VALUES
(1, 1, 1, CURDATE(), 12, 1575000.00, 45, 0.87, 'v2.1'),
(2, 1, 1, CURDATE(), 18, 2275000.00, 65, 0.89, 'v2.1'),
(3, 1, 1, CURDATE(), 19, 1350000.00, 30, 0.72, 'v2.1'),
(4, 1, 1, CURDATE() + INTERVAL 1 DAY, 12, 1750000.00, 50, 0.75, 'v2.1');

-- ============================================================
-- TIER 3: ORDER THROTTLING (Tenant 1)
-- ============================================================
INSERT IGNORE INTO order_throttling_config (config_id, tenant_id, branch_id, channel, max_orders_per_slot, slot_duration_minutes, auto_pause_threshold, is_paused, is_active) VALUES
(1, 1, 1, 'ONLINE', 15, 15, 12, 0, 1),
(2, 1, 1, 'ALL', 20, 15, 18, 0, 1),
(3, 1, 1, 'KIOSK', 10, 15, 8, 0, 1);

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
INSERT IGNORE INTO daily_production_plans (plan_id, tenant_id, branch_id, plan_date, product_id, product_name, planned_quantity, produced_quantity, sold_quantity, wasted_quantity, remaining_quantity, status) VALUES
(1, 1, 1, CURDATE(), 1, 'Nasi Goreng Spesial', 50, 50, 35, 2, 13, 'COMPLETED'),
(2, 1, 1, CURDATE(), 2, 'Ayam Bakar Madu', 30, 28, 20, 1, 7, 'COMPLETED'),
(3, 1, 1, CURDATE() + INTERVAL 1 DAY, 1, 'Nasi Goreng Spesial', 60, 0, 0, 0, 0, 'PLANNED');

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
INSERT IGNORE INTO event_holds_calendar (hold_id, tenant_id, branch_id, event_date, artist_name, hold_type, priority_rank, promoter_name, hold_expires_at) VALUES
(1, 13, 13, CURDATE() + INTERVAL 14 DAY, 'DJ Blast', 'SOFT_HOLD', 2, 'Rudi Hartono', CONCAT(CURDATE() + INTERVAL 3 DAY, ' 23:59:00')),
(2, 13, 13, CURDATE() + INTERVAL 21 DAY, 'DJ Mixmaster', 'FIRM_HOLD', 1, 'Sara Dewi', CONCAT(CURDATE() + INTERVAL 7 DAY, ' 23:59:00'));

-- ============================================================
-- TIER 3: BOOKING CHANNEL SYNC (Tenant 1)
-- ============================================================
INSERT IGNORE INTO booking_channel_sync (sync_id, tenant_id, branch_id, channel_name, channel_type, sync_status, synced_at) VALUES
(1, 1, 1, 'GoFood', 'APP', 'SYNCED', NOW() - INTERVAL 5 MINUTE),
(2, 1, 1, 'GrabFood', 'APP', 'SYNCED', NOW() - INTERVAL 5 MINUTE),
(3, 1, 1, 'ShopeeFood', 'APP', 'CONFLICT', NOW() - INTERVAL 10 MINUTE);

-- ============================================================
-- TIER 4: COAT CHECK (Tenant 13)
-- ============================================================
INSERT IGNORE INTO coat_check_items (coat_check_id, tenant_id, branch_id, event_id, check_number, customer_name, item_type, item_description, item_count, fee_charged, fee_paid, checked_in_at, status) VALUES
(1, 13, 13, 1, 'CC-001', 'Andi Wijaya', 'JACKET', 'Black leather jacket', 1, 20000.00, 1, NOW() - INTERVAL 2 HOUR, 'CHECKED_IN'),
(2, 13, 13, 1, 'CC-002', 'Sara Dewi', 'JACKET', 'Blue blazer + bag', 2, 30000.00, 1, NOW() - INTERVAL 90 MINUTE, 'CHECKED_IN');

-- ============================================================
-- TIER 4: EQUIPMENT ASSETS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO equipment_assets (equipment_id, tenant_id, branch_id, equipment_name, equipment_type, brand, model, serial_number, purchase_cost, condition_status, status) VALUES
(1, 13, 13, 'Pioneer CDJ-3000', 'SOUND', 'Pioneer', 'CDJ-3000', 'PION-CDJ3K-001', 25000000.00, 'EXCELLENT', 'IN_USE'),
(2, 13, 13, 'JBL VTX V25 Speaker', 'SOUND', 'JBL', 'VTX V25', 'JBL-V25-001', 45000000.00, 'GOOD', 'IN_USE'),
(3, 13, 13, 'Shure SM58 Microphone', 'MIC', 'Shure', 'SM58', 'SHU-SM58-001', 1500000.00, 'GOOD', 'IN_USE');

-- ============================================================
-- TIER 4: WINE LIST (Tenant 7 - Fine Dining)
-- ============================================================
INSERT IGNORE INTO wine_list (wine_id, tenant_id, wine_name, vintage, varietal, region, country, wine_type, bottle_price, glass_price, cost_per_bottle, inventory_bottles, tasting_notes, alcohol_pct, rating, is_available) VALUES
(1, 7, 'Chateau Margaux', 2015, 'Cabernet Sauvignon', 'Bordeaux', 'France', 'RED', 8500000.00, 1500000.00, 6000000.00, 12, 'Full-bodied with notes of blackcurrant and cedar', 13.50, 4.8, 1),
(2, 7, 'Dom Perignon Brut', 2012, 'Chardonnay/Pinot Noir', 'Champagne', 'France', 'SPARKLING', 5500000.00, 900000.00, 4000000.00, 8, 'Crisp with brioche and citrus notes', 12.50, 4.7, 1),
(3, 7, 'Penfolds Grange', 2017, 'Shiraz', 'South Australia', 'Australia', 'RED', 6500000.00, 1100000.00, 4800000.00, 6, 'Rich dark fruit with chocolate and spice', 14.50, 4.9, 1);

INSERT IGNORE INTO wine_pairing_suggestions (pairing_id, tenant_id, wine_id, product_id, pairing_strength, pairing_reason) VALUES
(1, 7, 1, 2, 'CLASSIC', 'Cabernet pairs perfectly with grilled meats'),
(2, 7, 3, 1, 'EXCELLENT', 'Shiraz complements spicy Asian dishes');

-- ============================================================
-- TIER 4: WAITER BUTTON PRESSES (Tenant 13)
-- ============================================================
INSERT IGNORE INTO waiter_button_presses (press_id, tenant_id, branch_id, room_id, pressed_at, responded_at, response_seconds, responded_by, response_type) VALUES
(1, 13, 13, 3, NOW() - INTERVAL 1 HOUR, NOW() - INTERVAL 55 MINUTE, 300, 1, 'ACKNOWLEDGED'),
(2, 13, 13, 3, NOW() - INTERVAL 30 MINUTE, NOW() - INTERVAL 25 MINUTE, 300, 1, 'SERVED'),
(3, 13, 13, 1, NOW() - INTERVAL 10 MINUTE, NULL, 0, NULL, 'AUTO_TIMEOUT');

-- ============================================================
-- TIER 4: ENTERTAINER ROTATIONS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO entertainer_rotations (rotation_id, tenant_id, branch_id, event_id, entertainer_name, entertainer_type, set_number, set_start_time, set_end_time, set_duration_minutes, status) VALUES
(1, 13, 13, 1, 'DJ Blast', 'DJ', 1, CONCAT(CURDATE(), ' 22:00:00'), CONCAT(CURDATE(), ' 23:30:00'), 90, 'COMPLETED'),
(2, 13, 13, 1, 'DJ Blast', 'DJ', 2, CONCAT(CURDATE(), ' 00:00:00'), CONCAT(CURDATE(), ' 01:30:00'), 90, 'COMPLETED'),
(3, 13, 13, 1, 'DJ Sparkle', 'DJ', 3, CONCAT(CURDATE(), ' 02:00:00'), CONCAT(CURDATE(), ' 04:00:00'), 120, 'PLAYING');

-- ============================================================
-- TIER 4: SOCIAL GROUP BOOKINGS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO social_group_bookings (group_booking_id, tenant_id, branch_id, organizer_name, organizer_phone, organizer_email, event_date, event_name, total_party_size, total_amount, deposit_collected, split_type, status) VALUES
(1, 13, 13, 'Andi Wijaya', '081234500004', 'andi@example.com', CURDATE() + INTERVAL 7 DAY, 'Birthday Party Group', 10, 5000000.00, 2500000.00, 'EVEN', 'CONFIRMED');

-- ============================================================
-- TIER 4: RADIUS CLAUSE CHECKS (Tenant 13)
-- ============================================================
INSERT IGNORE INTO radius_clause_checks (check_id, tenant_id, deal_id, artist_name, clause_radius_km, clause_days, event_date, conflicting_venue, conflicting_venue_distance_km, conflicting_event_date, check_result) VALUES
(1, 13, 1, 'DJ Blast', 50, 30, CURDATE() + INTERVAL 7 DAY, 'Club X Jakarta', 15, CURDATE() + INTERVAL 5 DAY, 'VIOLATION'),
(2, 13, 2, 'DJ Mixmaster', 100, 60, CURDATE() + INTERVAL 14 DAY, NULL, NULL, NULL, 'CLEAR');

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
INSERT IGNORE INTO e_signatures (signature_id, tenant_id, branch_id, document_type, document_title, document_content, document_hash, signer_name, signer_email, signer_role, signed_at, status, expires_at) VALUES
(1, 6, 6, 'CATERING_CONTRACT', 'Catering Contract - PT Maju Bersama', 'This agreement is between EBP Catering and PT Maju Bersama for corporate lunch catering services on specified date...', SHA2('contract1', 256), 'Pak Andi', 'andi@majubersama.com', 'CLIENT', NULL, 'PENDING', CURDATE() + INTERVAL 30 DAY),
(2, 6, 6, 'CATERING_CONTRACT', 'Wedding Contract - Sari Wedding', 'This agreement is between EBP Catering and Sari for wedding reception catering services...', SHA2('contract2', 256), 'Sari', 'sari@email.com', 'CLIENT', NOW() - INTERVAL 2 DAY, 'SIGNED', CURDATE() + INTERVAL 60 DAY);

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
(1, 3, 'PER_PERSON', 3),
(2, 4, 'CUSTOM', 2);
