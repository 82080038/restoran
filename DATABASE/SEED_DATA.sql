USE ebp_restaurant_db;
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================================
-- Minimal seed data aligned with the migration schema
-- ========================================================

-- Tenants - Complete F&B Types
INSERT IGNORE INTO tenants (tenant_id, tenant_code, tenant_name, business_type, status) VALUES
(1, 'EBP_RESTO', 'EBP Restaurant Demo', 'RESTAURANT', 'ACTIVE'),
(2, 'EBP_CAFE', 'EBP Coffee House', 'COFFEE_SHOP', 'ACTIVE'),
(3, 'EBP_BAR', 'EBP Bar & Pub', 'BAR_PUB', 'ACTIVE'),
(4, 'EBP_FAST', 'EBP Fast Food', 'FAST_FOOD', 'ACTIVE'),
(5, 'EBP_FOODC', 'EBP Food Court', 'FOOD_COURT', 'ACTIVE'),
(6, 'EBP_CATER', 'EBP Catering Service', 'CATERING', 'ACTIVE'),
(7, 'EBP_FINE', 'EBP Fine Dining', 'FINE_DINING', 'ACTIVE'),
(8, 'EBP_HOTEL', 'EBP Hotel Restaurant', 'HOTEL', 'ACTIVE'),
(9, 'EBP_AIRP', 'EBP Airport Restaurant', 'AIRPORT', 'ACTIVE'),
(10, 'EBP_MALL', 'EBP Mall Food Court', 'MALL', 'ACTIVE'),
(11, 'EBP_TRUCK', 'EBP Food Truck', 'FOOD_TRUCK', 'ACTIVE'),
(12, 'EBP_STALL', 'EBP Stall Kiosk', 'STALL_KIOSK', 'ACTIVE'),
(13, 'EBP_DISCO', 'EBP Neon Nightclub', 'DISCOTHEQUE', 'ACTIVE');

-- Companies
INSERT IGNORE INTO companies (company_id, tenant_id, company_code, company_name, status) VALUES
(1, 1, 'EBP_CO', 'EBP Restaurant Company', 'ACTIVE'),
(2, 2, 'EBP_CAFE_CO', 'EBP Coffee House Company', 'ACTIVE'),
(3, 3, 'EBP_BAR_CO', 'EBP Bar Company', 'ACTIVE'),
(4, 4, 'EBP_FAST_CO', 'EBP Fast Food Company', 'ACTIVE'),
(5, 5, 'EBP_FOODC_CO', 'EBP Food Court Company', 'ACTIVE'),
(6, 6, 'EBP_CATER_CO', 'EBP Catering Company', 'ACTIVE'),
(7, 7, 'EBP_FINE_CO', 'EBP Fine Dining Company', 'ACTIVE'),
(8, 8, 'EBP_HOTEL_CO', 'EBP Hotel Company', 'ACTIVE'),
(9, 9, 'EBP_AIRP_CO', 'EBP Airport Company', 'ACTIVE'),
(10, 10, 'EBP_MALL_CO', 'EBP Mall Company', 'ACTIVE'),
(11, 11, 'EBP_TRUCK_CO', 'EBP Food Truck Company', 'ACTIVE'),
(12, 12, 'EBP_STALL_CO', 'EBP Stall Company', 'ACTIVE'),
(13, 13, 'EBP_DISCO_CO', 'EBP Neon Nightclub Company', 'ACTIVE');

-- Branches
INSERT IGNORE INTO branches (branch_id, tenant_id, company_id, branch_code, branch_name, address, phone, status) VALUES
(1, 1, 1, 'JKT001', 'EBP Restaurant Jakarta', 'Jl. Sudirman No. 123', '+62 21 1234 5678', 'ACTIVE'),
(2, 2, 2, 'CAFE001', 'EBP Coffee House Jakarta', 'Jl. Senopati No. 45', '+62 21 2345 6789', 'ACTIVE'),
(3, 3, 3, 'BAR001', 'EBP Bar Jakarta', 'Jl. Gatot Subroto No. 78', '+62 21 3456 7890', 'ACTIVE'),
(4, 4, 4, 'FAST001', 'EBP Fast Food Jakarta', 'Jl. Thamrin No. 90', '+62 21 4567 8901', 'ACTIVE'),
(5, 5, 5, 'FOODC001', 'EBP Food Court Jakarta', 'Jl. MH Thamrin Mall', '+62 21 5678 9012', 'ACTIVE'),
(6, 6, 6, 'CATER001', 'EBP Catering Jakarta', 'Jl. Rasuna Said No. 56', '+62 21 6789 0123', 'ACTIVE'),
(7, 7, 7, 'FINE001', 'EBP Fine Dining Jakarta', 'Jl. SCBD No. 12', '+62 21 7890 1234', 'ACTIVE'),
(8, 8, 8, 'HOTEL001', 'EBP Hotel Restaurant Jakarta', 'Jl. Sudirman Hotel', '+62 21 8901 2345', 'ACTIVE'),
(9, 9, 9, 'AIRP001', 'EBP Airport Restaurant', 'Soekarno-Hatta Airport', '+62 21 9012 3456', 'ACTIVE'),
(10, 10, 10, 'MALL001', 'EBP Mall Food Court', 'Grand Indonesia Mall', '+62 21 0123 4567', 'ACTIVE'),
(11, 11, 11, 'TRUCK001', 'EBP Food Truck Jakarta', 'Mobile Location', '+62 21 1234 5678', 'ACTIVE'),
(12, 12, 12, 'STALL001', 'EBP Stall Kiosk Jakarta', 'FX Sudirman Mall', '+62 21 2345 6789', 'ACTIVE'),
(13, 13, 13, 'DISCO001', 'EBP Neon Nightclub Jakarta', 'Jl. SCBD Lot 14', '+62 21 5555 9999', 'ACTIVE');

-- ========================================================
-- FLOORS - Multi-Floor Support
-- ========================================================
-- Tenant 1: Restaurant (2 floors)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(1, 1, 1, 'FL1', 'Ground Floor', 1, 'DINING', 1, 'ACTIVE'),
(2, 1, 1, 'FL2', 'Second Floor', 2, 'DINING', 2, 'ACTIVE');

-- Tenant 2: Coffee Shop (1 floor)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(3, 2, 2, 'FL1', 'Ground Floor', 1, 'DINING', 1, 'ACTIVE');

-- Tenant 3: Bar (2 floors)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(4, 3, 3, 'FL1', 'Ground Floor Bar', 1, 'DINING', 1, 'ACTIVE'),
(5, 3, 3, 'FL2', 'Rooftop Bar', 2, 'DINING', 2, 'ACTIVE');

-- Tenant 4: Fast Food (1 floor)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(6, 4, 4, 'FL1', 'Ground Floor', 1, 'DINING', 1, 'ACTIVE');

-- Tenant 5: Food Court (1 floor)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(7, 5, 5, 'FL1', 'Ground Floor', 1, 'DINING', 1, 'ACTIVE');

-- Tenant 6: Catering (1 floor - central kitchen)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(8, 6, 6, 'FL1', 'Central Kitchen Floor', 1, 'KITCHEN', 1, 'ACTIVE');

-- Tenant 7: Fine Dining (2 floors)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(9, 7, 7, 'FL1', 'Main Dining Floor', 1, 'DINING', 1, 'ACTIVE'),
(10, 7, 7, 'FL2', 'Private Dining Floor', 2, 'DINING', 2, 'ACTIVE');

-- Tenant 8: Hotel (3 floors - lobby, restaurant floor, banquet floor)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(11, 8, 8, 'FL1', 'Lobby Floor', 1, 'DINING', 1, 'ACTIVE'),
(12, 8, 8, 'FL2', 'Restaurant Floor', 2, 'DINING', 2, 'ACTIVE'),
(13, 8, 8, 'FL3', 'Banquet Floor', 3, 'BANQUET', 3, 'ACTIVE');

-- Tenant 9: Airport (1 floor)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(14, 9, 9, 'FL1', 'Terminal Floor', 1, 'DINING', 1, 'ACTIVE');

-- Tenant 10: Mall (1 floor)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(15, 10, 10, 'FL1', 'Mall Floor', 1, 'DINING', 1, 'ACTIVE');

-- Tenant 11: Food Truck (1 floor - mobile)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(16, 11, 11, 'FL1', 'Mobile Unit', 1, 'DINING', 1, 'ACTIVE');

-- Tenant 12: Stall Kiosk (1 floor)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(17, 12, 12, 'FL1', 'Kiosk Floor', 1, 'DINING', 1, 'ACTIVE');

-- ========================================================
-- ZONES - Multi-Zone Support
-- ========================================================
-- Tenant 1: Restaurant Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(1, 1, 1, 1, 'MAIN', 'Main Dining Hall', 'DINING', 'TABLE_SERVICE', 50, 1, 'ACTIVE'),
(2, 1, 1, 1, 'BAR', 'Bar Area', 'BAR', 'TABLE_SERVICE', 20, 2, 'ACTIVE'),
(3, 1, 1, 2, 'VIP', 'VIP Private Room', 'PRIVATE', 'TABLE_SERVICE', 15, 1, 'ACTIVE'),
(4, 1, 1, 2, 'OUTDOOR', 'Outdoor Terrace', 'DINING', 'TABLE_SERVICE', 30, 2, 'ACTIVE');

-- Tenant 2: Coffee Shop Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(5, 2, 2, 3, 'MAIN', 'Main Cafe Area', 'DINING', 'TABLE_SERVICE', 40, 1, 'ACTIVE'),
(6, 2, 2, 3, 'PATIO', 'Outdoor Patio', 'DINING', 'SELF_SERVICE', 25, 2, 'ACTIVE');

-- Tenant 3: Bar Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(7, 3, 3, 4, 'MAIN', 'Main Bar', 'BAR', 'TABLE_SERVICE', 35, 1, 'ACTIVE'),
(8, 3, 3, 5, 'ROOFTOP', 'Rooftop Bar', 'BAR', 'TABLE_SERVICE', 45, 1, 'ACTIVE');

-- Tenant 4: Fast Food Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(9, 4, 4, 6, 'MAIN', 'Dining Area', 'DINING', 'SELF_SERVICE', 60, 1, 'ACTIVE'),
(10, 4, 4, 6, 'DRIVETHRU', 'Drive Thru', 'DINING', 'SELF_SERVICE', 0, 2, 'ACTIVE');

-- Tenant 5: Food Court Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(11, 5, 5, 7, 'STALL1', 'Stall Area 1', 'DINING', 'SELF_SERVICE', 100, 1, 'ACTIVE'),
(12, 5, 5, 7, 'STALL2', 'Stall Area 2', 'DINING', 'SELF_SERVICE', 100, 2, 'ACTIVE');

-- Tenant 6: Catering Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(13, 6, 6, 8, 'KITCHEN', 'Central Kitchen', 'KITCHEN', 'NONE', 0, 1, 'ACTIVE'),
(14, 6, 6, 8, 'PACKING', 'Packing Area', 'KITCHEN', 'NONE', 0, 2, 'ACTIVE');

-- Tenant 7: Fine Dining Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(15, 7, 7, 9, 'MAIN', 'Main Dining Room', 'DINING', 'TABLE_SERVICE', 40, 1, 'ACTIVE'),
(16, 7, 7, 10, 'VIP1', 'Private Room 1', 'PRIVATE', 'TABLE_SERVICE', 12, 1, 'ACTIVE'),
(17, 7, 7, 10, 'VIP2', 'Private Room 2', 'PRIVATE', 'TABLE_SERVICE', 12, 2, 'ACTIVE'),
(18, 7, 7, 10, 'WINE', 'Wine Cellar', 'BAR', 'TABLE_SERVICE', 8, 3, 'ACTIVE');

-- Tenant 8: Hotel Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(19, 8, 8, 11, 'LOBBY', 'Lobby Lounge', 'DINING', 'TABLE_SERVICE', 30, 1, 'ACTIVE'),
(20, 8, 8, 12, 'RESTAURANT', 'Main Restaurant', 'DINING', 'TABLE_SERVICE', 80, 1, 'ACTIVE'),
(21, 8, 8, 12, 'COFFEE', 'Coffee Shop', 'DINING', 'SELF_SERVICE', 25, 2, 'ACTIVE'),
(22, 8, 8, 13, 'BANQUET', 'Banquet Hall', 'BANQUET', 'TABLE_SERVICE', 200, 1, 'ACTIVE'),
(23, 8, 8, 13, 'MEETING', 'Meeting Room', 'PRIVATE', 'TABLE_SERVICE', 20, 2, 'ACTIVE');

-- Tenant 9: Airport Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(24, 9, 9, 14, 'MAIN', 'Main Terminal', 'DINING', 'SELF_SERVICE', 150, 1, 'ACTIVE'),
(25, 9, 9, 14, 'GATE', 'Gate Area', 'DINING', 'SELF_SERVICE', 50, 2, 'ACTIVE');

-- Tenant 10: Mall Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(26, 10, 10, 15, 'MAIN', 'Food Court Area', 'DINING', 'SELF_SERVICE', 200, 1, 'ACTIVE');

-- Tenant 11: Food Truck Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(27, 11, 11, 16, 'MAIN', 'Serving Window', 'DINING', 'SELF_SERVICE', 0, 1, 'ACTIVE');

-- Tenant 12: Stall Zones
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(28, 12, 12, 17, 'MAIN', 'Kiosk Counter', 'DINING', 'SELF_SERVICE', 10, 1, 'ACTIVE');

-- ========================================================
-- KITCHEN STATIONS - Multi-Kitchen Support
-- ========================================================
-- Tenant 1: Restaurant Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(1, 1, 1, 1, 'Main Hot Kitchen', 'PREPARATION', 'HK_MAIN', 'HOT_KITCHEN', 'Main hot kitchen for all dishes', 5, 1, 1, 1),
(2, 1, 1, 1, 'Cold Kitchen', 'PREPARATION', 'CK_MAIN', 'COLD_KITCHEN', 'Salads and cold appetizers', 3, 0, 2, 1),
(3, 1, 1, 1, 'Pastry Station', 'PREPARATION', 'PS_MAIN', 'BAKERY', 'Desserts and pastries', 2, 0, 3, 1);

-- Tenant 2: Coffee Shop Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(4, 2, 2, 3, 'Coffee Bar', 'PREPARATION', 'CB_MAIN', 'COFFEE_STATION', 'Coffee and beverage preparation', 3, 1, 1, 1),
(5, 2, 2, 3, 'Pastry Station', 'PREPARATION', 'PS_CAFE', 'BAKERY', 'Pastries and baked goods', 2, 0, 2, 1);

-- Tenant 3: Bar Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(6, 3, 3, 4, 'Main Bar Station', 'PREPARATION', 'BAR_MAIN', 'BAR_STATION', 'Main bar preparation', 4, 1, 1, 1),
(7, 3, 3, 5, 'Rooftop Bar Station', 'PREPARATION', 'BAR_ROOF', 'BAR_STATION', 'Rooftop bar preparation', 3, 0, 1, 1);

-- Tenant 4: Fast Food Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(8, 4, 4, 6, 'Main Kitchen', 'PREPARATION', 'KF_FAST', 'HOT_KITCHEN', 'Fast food preparation line', 6, 1, 1, 1);

-- Tenant 5: Food Court Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(9, 5, 5, 7, 'Central Kitchen', 'PREPARATION', 'KC_CENTRAL', 'HOT_KITCHEN', 'Central kitchen for all stalls', 8, 1, 1, 1);

-- Tenant 6: Catering Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(10, 6, 6, 8, 'Central Production Kitchen', 'PREPARATION', 'CPK_CATER', 'HOT_KITCHEN', 'Central production kitchen for catering', 10, 1, 1, 1);

-- Tenant 7: Fine Dining Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(11, 7, 7, 9, 'Main Kitchen', 'PREPARATION', 'HK_FINE', 'HOT_KITCHEN', 'Main fine dining kitchen', 8, 1, 1, 1),
(12, 7, 7, 9, 'Sauce Station', 'PREPARATION', 'SS_FINE', 'HOT_KITCHEN', 'Sauce and reduction station', 2, 0, 2, 1),
(13, 7, 7, 9, 'Pastry Station', 'PREPARATION', 'PS_FINE', 'BAKERY', 'Fine dining pastry', 3, 0, 3, 1);

-- Tenant 8: Hotel Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(14, 8, 8, 12, 'Main Production Kitchen', 'PREPARATION', 'MPK_HOTEL', 'HOT_KITCHEN', 'Central production kitchen', 12, 1, 1, 1),
(15, 8, 8, 12, 'Banquet Kitchen', 'PREPARATION', 'BK_HOTEL', 'HOT_KITCHEN', 'Banquet preparation kitchen', 15, 0, 2, 1),
(16, 8, 8, 11, 'Lobby Kitchen', 'PREPARATION', 'LK_HOTEL', 'HOT_KITCHEN', 'Lobby lounge kitchen', 4, 0, 1, 1),
(17, 8, 8, 12, 'Pastry Kitchen', 'PREPARATION', 'PK_HOTEL', 'BAKERY', 'Hotel pastry and bakery', 5, 0, 3, 1);

-- Tenant 9: Airport Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(18, 9, 9, 14, 'Airport Kitchen', 'PREPARATION', 'KA_AIRP', 'HOT_KITCHEN', 'Airport restaurant kitchen', 8, 1, 1, 1);

-- Tenant 10: Mall Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(19, 10, 10, 15, 'Food Court Kitchen', 'PREPARATION', 'KFC_MALL', 'HOT_KITCHEN', 'Food court central kitchen', 10, 1, 1, 1);

-- Tenant 11: Food Truck Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(20, 11, 11, 16, 'Truck Kitchen', 'PREPARATION', 'KT_TRUCK', 'HOT_KITCHEN', 'Food truck mobile kitchen', 3, 1, 1, 1);

-- Tenant 12: Stall Kitchens
INSERT IGNORE INTO kitchen_stations (station_id, tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) VALUES
(21, 12, 12, 17, 'Kiosk Kitchen', 'PREPARATION', 'KK_STALL', 'HOT_KITCHEN', 'Kiosk preparation area', 2, 1, 1, 1);

-- ========================================================
-- KDS SCREENS - Kitchen Display System Screens
-- ========================================================
-- Tenant 1: Restaurant KDS Screens
INSERT IGNORE INTO kds_screens (screen_id, tenant_id, branch_id, station_id, screen_name, screen_type, display_order, max_tickets_display, auto_refresh_seconds, is_active) VALUES
(1, 1, 1, 1, 'Hot Kitchen Screen', 'PREP_STATION', 1, 20, 10, 1),
(2, 1, 1, 2, 'Cold Kitchen Screen', 'PREP_STATION', 2, 15, 10, 1),
(3, 1, 1, 3, 'Pastry Screen', 'PREP_STATION', 3, 10, 10, 1),
(4, 1, 1, 1, 'Expediter Screen', 'EXPEDITER', 4, 30, 5, 1);

-- Tenant 2: Coffee Shop KDS Screens
INSERT IGNORE INTO kds_screens (screen_id, tenant_id, branch_id, station_id, screen_name, screen_type, display_order, max_tickets_display, auto_refresh_seconds, is_active) VALUES
(5, 2, 2, 4, 'Coffee Bar Screen', 'PREP_STATION', 1, 15, 10, 1),
(6, 2, 2, 5, 'Pastry Screen', 'PREP_STATION', 2, 10, 10, 1);

-- Tenant 3: Bar KDS Screens
INSERT IGNORE INTO kds_screens (screen_id, tenant_id, branch_id, station_id, screen_name, screen_type, display_order, max_tickets_display, auto_refresh_seconds, is_active) VALUES
(7, 3, 3, 6, 'Main Bar Screen', 'PREP_STATION', 1, 20, 10, 1),
(8, 3, 3, 7, 'Rooftop Bar Screen', 'PREP_STATION', 2, 15, 10, 1);

-- ========================================================
-- KDS ROUTING RULES - Automatic Order Routing
-- ========================================================
-- Tenant 1: Restaurant Routing Rules
INSERT IGNORE INTO kds_routing_rules (rule_id, tenant_id, branch_id, rule_name, rule_type, condition_value, target_station_id, priority, apply_to_dinein, apply_to_takeout, apply_to_delivery, is_active) VALUES
(1, 1, 1, 'Hot Items to Hot Kitchen', 'MENU_CATEGORY', 'HOT_ENTREES', 1, 1, 1, 1, 1, 1),
(2, 1, 1, 'Cold Items to Cold Kitchen', 'MENU_CATEGORY', 'SALADS', 2, 2, 2, 1, 1, 1),
(3, 1, 1, 'Desserts to Pastry', 'MENU_CATEGORY', 'DESSERTS', 3, 3, 3, 1, 1, 1),
(4, 1, 1, 'Takeout to Pack Station', 'DINING_OPTION', 'TAKE_OUT', 1, 1, 10, 0, 1, 1);

-- Tenant 2: Coffee Shop Routing Rules
INSERT IGNORE INTO kds_routing_rules (rule_id, tenant_id, branch_id, rule_name, rule_type, condition_value, target_station_id, priority, apply_to_dinein, apply_to_takeout, apply_to_delivery, is_active) VALUES
(5, 2, 2, 'Coffee to Coffee Bar', 'MENU_CATEGORY', 'COFFEE', 4, 1, 1, 1, 1, 1),
(6, 2, 2, 'Pastries to Pastry', 'MENU_CATEGORY', 'PASTRIES', 5, 2, 2, 1, 1, 1);

-- ========================================================
-- PEAK HOUR SCHEDULES - Peak Hour Definitions
-- ========================================================
-- Tenant 1: Restaurant Peak Hours
INSERT IGNORE INTO peak_hour_schedules (schedule_id, tenant_id, branch_id, day_of_week, start_time, end_time, peak_level, expected_volume_multiplier, staff_multiplier, is_active) VALUES
(1, 1, 1, 'FRIDAY', '18:00:00', '22:00:00', 'HIGH', 1.50, 1.25, 1),
(2, 1, 1, 'SATURDAY', '12:00:00', '15:00:00', 'HIGH', 1.75, 1.50, 1),
(3, 1, 1, 'SATURDAY', '18:00:00', '23:00:00', 'EXTREME', 2.00, 1.75, 1),
(4, 1, 1, 'SUNDAY', '12:00:00', '15:00:00', 'HIGH', 1.50, 1.25, 1);

-- Tenant 2: Coffee Shop Peak Hours
INSERT IGNORE INTO peak_hour_schedules (schedule_id, tenant_id, branch_id, day_of_week, start_time, end_time, peak_level, expected_volume_multiplier, staff_multiplier, is_active) VALUES
(5, 2, 2, 'MONDAY', '07:00:00', '10:00:00', 'HIGH', 1.50, 1.25, 1),
(6, 2, 2, 'SATURDAY', '08:00:00', '12:00:00', 'HIGH', 1.75, 1.50, 1),
(7, 2, 2, 'SUNDAY', '08:00:00', '12:00:00', 'MEDIUM', 1.25, 1.10, 1);

-- ========================================================
-- COURSE SEQUENCES - Course Definitions
-- ========================================================
-- Tenant 1: Restaurant Courses
INSERT IGNORE INTO course_sequences (course_id, tenant_id, branch_id, course_number, course_name, course_type, auto_fire_delay_minutes, manual_fire_only, display_order, is_active) VALUES
(1, 1, 1, 1, 'Appetizers', 'APPETIZER', 0, 0, 1, 1),
(2, 1, 1, 2, 'Soup', 'SOUP', 10, 0, 2, 1),
(3, 1, 1, 3, 'Salad', 'SALAD', 15, 0, 3, 1),
(4, 1, 1, 4, 'Main Course', 'MAIN', 20, 0, 4, 1),
(5, 1, 1, 5, 'Dessert', 'DESSERT', 0, 1, 5, 1);

-- Tenant 7: Fine Dining Courses
INSERT IGNORE INTO course_sequences (course_id, tenant_id, branch_id, course_number, course_name, course_type, auto_fire_delay_minutes, manual_fire_only, display_order, is_active) VALUES
(6, 7, 7, 1, 'Amuse Bouche', 'APPETIZER', 0, 0, 1, 1),
(7, 7, 7, 2, 'First Course', 'APPETIZER', 15, 0, 2, 1),
(8, 7, 7, 3, 'Second Course', 'MAIN', 20, 0, 3, 1),
(9, 7, 7, 4, 'Main Course', 'MAIN', 25, 0, 4, 1),
(10, 7, 7, 5, 'Cheese Course', 'CUSTOM', 10, 1, 5, 1),
(11, 7, 7, 6, 'Dessert', 'DESSERT', 0, 1, 6, 1);

-- ========================================================
-- SAMPLE PERFORMANCE METRICS
-- ========================================================
-- Tenant 1: Restaurant Performance Metrics
INSERT IGNORE INTO performance_metrics (metric_id, tenant_id, branch_id, station_id, date, hour, total_orders, completed_orders, cancelled_orders, avg_order_time, avg_prep_time, error_count, error_rate, on_time_rate, bottleneck_flag) VALUES
(1, 1, 1, 1, CURDATE(), 18, 45, 42, 3, 900, 720, 2, 4.44, 93.33, 0),
(2, 1, 1, 2, CURDATE(), 18, 30, 28, 2, 600, 480, 1, 3.33, 93.33, 0),
(3, 1, 1, 3, CURDATE(), 18, 25, 24, 1, 480, 360, 0, 0.00, 96.00, 0);

-- System roles
INSERT IGNORE INTO roles (role_id, tenant_id, role_code, role_name, is_system, status) VALUES
(1, NULL, 'PLATFORM_OWNER', 'Platform Owner', 1, 'ACTIVE'),
(2, 1, 'ADMIN', 'Administrator', 1, 'ACTIVE'),
(3, 1, 'MANAGER', 'Manager', 1, 'ACTIVE'),
(4, 1, 'CASHIER', 'Cashier', 1, 'ACTIVE'),
(5, 1, 'KITCHEN', 'Kitchen Staff', 1, 'ACTIVE');

-- Permissions (core set)
INSERT IGNORE INTO permissions (permission_id, permission_code, permission_name, module, action) VALUES
(1, 'DASHBOARD_VIEW', 'View Dashboard', 'DASHBOARD', 'VIEW'),
(2, 'MENU_VIEW', 'View Menu', 'MENU', 'VIEW'),
(3, 'MENU_MANAGE', 'Manage Menu', 'MENU', 'MANAGE'),
(4, 'ORDER_VIEW', 'View Orders', 'ORDER', 'VIEW'),
(5, 'ORDER_MANAGE', 'Manage Orders', 'ORDER', 'MANAGE'),
(6, 'INVENTORY_VIEW', 'View Inventory', 'INVENTORY', 'VIEW'),
(7, 'INVENTORY_MANAGE', 'Manage Inventory', 'INVENTORY', 'MANAGE'),
(8, 'REPORT_VIEW', 'View Reports', 'REPORT', 'VIEW');

-- Admin gets all permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, permission_id FROM permissions;

-- Admin user for tenant 1 (password: admin123)
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(1, 1, 1, 'admin', 'admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'EBP Admin', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(1, 2);

-- Quick Login Users for All Tenants and Roles (password: admin123)
-- Tenant 1: EBP Restaurant Demo
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(30, 1, 1, 'resto_platform_owner', 'resto_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Restaurant Platform Owner', 'ACTIVE'),
(31, 1, 1, 'resto_admin', 'resto_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Restaurant Admin', 'ACTIVE'),
(32, 1, 1, 'resto_manager', 'resto_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Restaurant Manager', 'ACTIVE'),
(33, 1, 1, 'resto_cashier', 'resto_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Restaurant Cashier', 'ACTIVE'),
(34, 1, 1, 'resto_kitchen', 'resto_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Restaurant Kitchen Staff', 'ACTIVE');

-- Tenant 2: EBP Coffee House
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(40, 2, 2, 'cafe_platform_owner', 'cafe_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Coffee Platform Owner', 'ACTIVE'),
(41, 2, 2, 'cafe_admin', 'cafe_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Coffee Admin', 'ACTIVE'),
(42, 2, 2, 'cafe_manager', 'cafe_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Coffee Manager', 'ACTIVE'),
(43, 2, 2, 'cafe_cashier', 'cafe_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Coffee Cashier', 'ACTIVE'),
(44, 2, 2, 'cafe_kitchen', 'cafe_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Coffee Barista', 'ACTIVE');

-- Assign roles to tenant 1 users (RESTAURANT)
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(30, 2),  -- resto_platform_owner -> PLATFORM_OWNER
(31, 1),  -- resto_admin -> ADMIN
(32, 8),  -- resto_manager -> MANAGER
(33, 5),  -- resto_cashier -> KASIR
(34, 6);  -- resto_kitchen -> KOKI

-- Assign roles to tenant 2 users (COFFEE_SHOP)
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(40, 2),  -- cafe_platform_owner -> PLATFORM_OWNER
(41, 1),  -- cafe_admin -> ADMIN
(42, 8),  -- cafe_manager -> MANAGER
(43, 5),  -- cafe_cashier -> KASIR
(44, 11); -- cafe_kitchen -> BARISTA

-- Quick Login Users for All Tenants and Roles (password: admin123)
-- Tenant 3: EBP Bar & Pub
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(50, 3, 3, 'bar_platform_owner', 'bar_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Bar Platform Owner', 'ACTIVE'),
(51, 3, 3, 'bar_admin', 'bar_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Bar Admin', 'ACTIVE'),
(52, 3, 3, 'bar_manager', 'bar_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Bar Manager', 'ACTIVE'),
(53, 3, 3, 'bar_cashier', 'bar_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Bar Cashier', 'ACTIVE'),
(54, 3, 3, 'bar_bartender', 'bar_bartender@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Bar Bartender', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(50, 2),  -- bar_platform_owner -> PLATFORM_OWNER
(51, 1),  -- bar_admin -> ADMIN
(52, 8),  -- bar_manager -> MANAGER
(53, 5),  -- bar_cashier -> KASIR
(54, 10); -- bar_bartender -> BARTENDER

-- Tenant 4: EBP Fast Food
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(60, 4, 4, 'fast_platform_owner', 'fast_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fast Food Platform Owner', 'ACTIVE'),
(61, 4, 4, 'fast_admin', 'fast_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fast Food Admin', 'ACTIVE'),
(62, 4, 4, 'fast_manager', 'fast_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fast Food Manager', 'ACTIVE'),
(63, 4, 4, 'fast_cashier', 'fast_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fast Food Cashier', 'ACTIVE'),
(64, 4, 4, 'fast_kitchen', 'fast_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fast Food Kitchen Staff', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(60, 2),  -- fast_platform_owner -> PLATFORM_OWNER
(61, 1),  -- fast_admin -> ADMIN
(62, 8),  -- fast_manager -> MANAGER
(63, 5),  -- fast_cashier -> KASIR
(64, 6);  -- fast_kitchen -> KOKI

-- Tenant 5: EBP Food Court
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(70, 5, 5, 'foodc_platform_owner', 'foodc_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Court Platform Owner', 'ACTIVE'),
(71, 5, 5, 'foodc_admin', 'foodc_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Court Admin', 'ACTIVE'),
(72, 5, 5, 'foodc_manager', 'foodc_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Court Manager', 'ACTIVE'),
(73, 5, 5, 'foodc_cashier', 'foodc_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Court Cashier', 'ACTIVE'),
(74, 5, 5, 'foodc_kitchen', 'foodc_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Court Kitchen Staff', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(70, 2),  -- foodc_platform_owner -> PLATFORM_OWNER
(71, 1),  -- foodc_admin -> ADMIN
(72, 8),  -- foodc_manager -> MANAGER
(73, 5),  -- foodc_cashier -> KASIR
(74, 6);  -- foodc_kitchen -> KOKI

-- Tenant 6: EBP Catering Service
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(80, 6, 6, 'cater_platform_owner', 'cater_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Catering Platform Owner', 'ACTIVE'),
(81, 6, 6, 'cater_admin', 'cater_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Catering Admin', 'ACTIVE'),
(82, 6, 6, 'cater_manager', 'cater_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Catering Manager', 'ACTIVE'),
(83, 6, 6, 'cater_cashier', 'cater_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Catering Cashier', 'ACTIVE'),
(84, 6, 6, 'cater_kitchen', 'cater_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Catering Kitchen Staff', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(80, 2),  -- cater_platform_owner -> PLATFORM_OWNER
(81, 1),  -- cater_admin -> ADMIN
(82, 8),  -- cater_manager -> MANAGER
(83, 5),  -- cater_cashier -> KASIR
(84, 6);  -- cater_kitchen -> KOKI

-- Tenant 7: EBP Fine Dining
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(90, 7, 7, 'fine_platform_owner', 'fine_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fine Dining Platform Owner', 'ACTIVE'),
(91, 7, 7, 'fine_admin', 'fine_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fine Dining Admin', 'ACTIVE'),
(92, 7, 7, 'fine_manager', 'fine_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fine Dining Manager', 'ACTIVE'),
(93, 7, 7, 'fine_cashier', 'fine_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fine Dining Cashier', 'ACTIVE'),
(94, 7, 7, 'fine_kitchen', 'fine_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fine Dining Chef', 'ACTIVE'),
(95, 7, 7, 'fine_waiter', 'fine_waiter@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fine Dining Waiter', 'ACTIVE'),
(96, 7, 7, 'fine_sommelier', 'fine_sommelier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Fine Dining Sommelier', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(90, 2),  -- fine_platform_owner -> PLATFORM_OWNER
(91, 1),  -- fine_admin -> ADMIN
(92, 8),  -- fine_manager -> MANAGER
(93, 5),  -- fine_cashier -> KASIR
(94, 6),  -- fine_kitchen -> KOKI
(95, 7),  -- fine_waiter -> WAITER
(96, 12); -- fine_sommelier -> SOMMELIER

-- Tenant 8: EBP Hotel Restaurant
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(100, 8, 8, 'hotel_platform_owner', 'hotel_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Hotel Platform Owner', 'ACTIVE'),
(101, 8, 8, 'hotel_admin', 'hotel_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Hotel Admin', 'ACTIVE'),
(102, 8, 8, 'hotel_manager', 'hotel_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Hotel Manager', 'ACTIVE'),
(103, 8, 8, 'hotel_cashier', 'hotel_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Hotel Cashier', 'ACTIVE'),
(104, 8, 8, 'hotel_kitchen', 'hotel_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Hotel Kitchen Staff', 'ACTIVE'),
(105, 8, 8, 'hotel_host', 'hotel_host@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Hotel Host/Hostess', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(100, 2),  -- hotel_platform_owner -> PLATFORM_OWNER
(101, 1),  -- hotel_admin -> ADMIN
(102, 8),  -- hotel_manager -> MANAGER
(103, 5),  -- hotel_cashier -> KASIR
(104, 6),  -- hotel_kitchen -> KOKI
(105, 13); -- hotel_host -> HOST

-- Tenant 9: EBP Airport Restaurant
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(110, 9, 9, 'airp_platform_owner', 'airp_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Airport Platform Owner', 'ACTIVE'),
(111, 9, 9, 'airp_admin', 'airp_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Airport Admin', 'ACTIVE'),
(112, 9, 9, 'airp_manager', 'airp_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Airport Manager', 'ACTIVE'),
(113, 9, 9, 'airp_cashier', 'airp_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Airport Cashier', 'ACTIVE'),
(114, 9, 9, 'airp_kitchen', 'airp_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Airport Kitchen Staff', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(110, 2),  -- airp_platform_owner -> PLATFORM_OWNER
(111, 1),  -- airp_admin -> ADMIN
(112, 8),  -- airp_manager -> MANAGER
(113, 5),  -- airp_cashier -> KASIR
(114, 6);  -- airp_kitchen -> KOKI

-- Tenant 10: EBP Mall Food Court
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(120, 10, 10, 'mall_platform_owner', 'mall_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Mall Platform Owner', 'ACTIVE'),
(121, 10, 10, 'mall_admin', 'mall_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Mall Admin', 'ACTIVE'),
(122, 10, 10, 'mall_manager', 'mall_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Mall Manager', 'ACTIVE'),
(123, 10, 10, 'mall_cashier', 'mall_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Mall Cashier', 'ACTIVE'),
(124, 10, 10, 'mall_kitchen', 'mall_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Mall Kitchen Staff', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(120, 2),  -- mall_platform_owner -> PLATFORM_OWNER
(121, 1),  -- mall_admin -> ADMIN
(122, 8),  -- mall_manager -> MANAGER
(123, 5),  -- mall_cashier -> KASIR
(124, 6);  -- mall_kitchen -> KOKI

-- Tenant 11: EBP Food Truck
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(130, 11, 11, 'truck_platform_owner', 'truck_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Truck Platform Owner', 'ACTIVE'),
(131, 11, 11, 'truck_admin', 'truck_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Truck Admin', 'ACTIVE'),
(132, 11, 11, 'truck_manager', 'truck_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Truck Manager', 'ACTIVE'),
(133, 11, 11, 'truck_cashier', 'truck_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Truck Cashier', 'ACTIVE'),
(134, 11, 11, 'truck_kitchen', 'truck_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Food Truck Cook', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(130, 2),  -- truck_platform_owner -> PLATFORM_OWNER
(131, 1),  -- truck_admin -> ADMIN
(132, 8),  -- truck_manager -> MANAGER
(133, 5),  -- truck_cashier -> KASIR
(134, 6);  -- truck_kitchen -> KOKI

-- Tenant 12: EBP Stall Kiosk
INSERT IGNORE INTO users (user_id, tenant_id, branch_id, username, email, password, full_name, status) VALUES
(140, 12, 12, 'stall_platform_owner', 'stall_po@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Stall Platform Owner', 'ACTIVE'),
(141, 12, 12, 'stall_admin', 'stall_admin@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Stall Admin', 'ACTIVE'),
(142, 12, 12, 'stall_manager', 'stall_manager@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Stall Manager', 'ACTIVE'),
(143, 12, 12, 'stall_cashier', 'stall_cashier@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Stall Cashier', 'ACTIVE'),
(144, 12, 12, 'stall_kitchen', 'stall_kitchen@ebp.restaurant', '$2y$10$t.5aO/r4QatYko3Xpx7cseCiiCTc7K7wUcYAsFMaxLdmTgRomXDI6', 'Stall Staff', 'ACTIVE');

INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(140, 2),  -- stall_platform_owner -> PLATFORM_OWNER
(141, 1),  -- stall_admin -> ADMIN
(142, 8),  -- stall_manager -> MANAGER
(143, 5),  -- stall_cashier -> KASIR
(144, 6);  -- stall_kitchen -> KOKI

-- Product categories
INSERT IGNORE INTO categories (category_id, tenant_id, category_code, category_name, description, sort_order, status) VALUES
(1, 1, 'MAIN', 'Main Course', 'Primary dishes', 1, 'ACTIVE'),
(2, 1, 'APPETIZER', 'Appetizers', 'Starters', 2, 'ACTIVE'),
(3, 1, 'BEVERAGE', 'Beverages', 'Drinks', 3, 'ACTIVE'),
(4, 1, 'DESSERT', 'Desserts', 'Sweet treats', 4, 'ACTIVE'),
(5, 2, 'COFFEE', 'Coffee', 'Coffee beverages', 1, 'ACTIVE'),
(6, 2, 'PASTRY', 'Pastries', 'Baked goods', 2, 'ACTIVE');

-- Products
INSERT IGNORE INTO products (product_id, tenant_id, category_id, product_code, product_name, description, price, cost, image_url, status) VALUES
(1, 1, 1, 'NGS001', 'Nasi Goreng Spesial', 'Fried rice with chicken', 35000.00, 20000.00, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400', 'ACTIVE'),
(2, 1, 1, 'ABM001', 'Ayam Bakar Madu', 'Grilled chicken with honey', 45000.00, 28000.00, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=400', 'ACTIVE'),
(3, 1, 2, 'GADO001', 'Gado-Gado', 'Indonesian salad', 28000.00, 16000.00, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400', 'ACTIVE'),
(4, 1, 3, 'ETM001', 'Es Teh Manis', 'Sweet iced tea', 8000.00, 2000.00, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400', 'ACTIVE'),
(5, 1, 3, 'KSGA001', 'Kopi Susu Gula Aren', 'Coffee with palm sugar', 22000.00, 8000.00, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400', 'ACTIVE'),
(6, 1, 4, 'EST001', 'Es Teler', 'Mixed fruit dessert', 28000.00, 15000.00, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400', 'ACTIVE'),
(7, 2, 5, 'ESP001', 'Espresso', 'Single shot espresso', 25000.00, 5000.00, 'https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?w=400', 'ACTIVE'),
(8, 2, 5, 'CAP001', 'Cappuccino', 'Espresso with steamed milk', 35000.00, 8000.00, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400', 'ACTIVE'),
(9, 2, 6, 'CRO001', 'Croissant', 'Butter croissant', 22000.00, 10000.00, 'https://images.unsplash.com/photo-1555507036-ab1f4038808c?w=400', 'ACTIVE');

-- Inventory categories
INSERT IGNORE INTO inventory_categories (category_id, tenant_id, name, description) VALUES
(1, 1, 'Dry Goods', 'Rice, oil, spices'),
(2, 1, 'Fresh Produce', 'Vegetables and fruits'),
(3, 1, 'Proteins', 'Chicken, eggs, meat'),
(4, 1, 'Beverages', 'Drink ingredients');

-- Inventory items
INSERT IGNORE INTO inventory (inventory_id, tenant_id, branch_id, product_id, quantity, unit, minimum_stock, maximum_stock, status) VALUES
(1, 1, 1, 1, 100.00, 'portion', 20.00, 200.00, 'ACTIVE'),
(2, 1, 1, 2, 80.00, 'portion', 15.00, 150.00, 'ACTIVE'),
(3, 1, 1, 4, 200.00, 'cup', 50.00, 500.00, 'ACTIVE'),
(4, 1, 1, 5, 150.00, 'cup', 30.00, 300.00, 'ACTIVE');

-- Customers
INSERT IGNORE INTO customers (customer_id, tenant_id, branch_id, customer_code, name, phone, email, membership_level, status) VALUES
(1, 1, 1, 'CUST001', 'Budi Santoso', '081234567890', 'budi@example.com', 'REGULAR', 'ACTIVE'),
(2, 1, 1, 'CUST002', 'Ani Wijaya', '082345678901', 'ani@example.com', 'VIP', 'ACTIVE'),
(3, 2, 2, 'CUST003', 'Citra Lestari', '083456789012', 'citra@example.com', 'REGULAR', 'ACTIVE');

-- Employees
INSERT IGNORE INTO employees (employee_id, tenant_id, branch_id, employee_code, first_name, last_name, email, phone, position, department, hire_date, status) VALUES
(1, 1, 1, 'EMP001', 'Rina', 'Susanti', 'rina@ebp.restaurant', '08111111111', 'Restaurant Manager', 'Management', '2025-01-15', 'ACTIVE'),
(2, 1, 1, 'EMP002', 'Dedi', 'Kurniawan', 'dedi@ebp.restaurant', '08222222222', 'Head Chef', 'Kitchen', '2025-02-01', 'ACTIVE'),
(3, 1, 1, 'EMP003', 'Siti', 'Aminah', 'siti@ebp.restaurant', '08333333333', 'Cashier', 'Front Office', '2025-03-10', 'ACTIVE');

-- ========================================================
-- NIGHTCLUB / DISCOTHEQUE SEED DATA (Tenant 13)
-- ========================================================

-- Floors for Discotheque (3 floors: entrance/bar, dance floor, VIP)
INSERT IGNORE INTO floors (floor_id, tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, sort_order, status) VALUES
(18, 13, 13, 'FL1', 'Ground Floor - Bar & Lounge', 1, 'DINING', 1, 'ACTIVE'),
(19, 13, 13, 'FL2', 'Main Dance Floor', 2, 'DINING', 2, 'ACTIVE'),
(20, 13, 13, 'FL3', 'VIP Booth Floor', 3, 'DINING', 3, 'ACTIVE');

-- Zones for Discotheque
INSERT IGNORE INTO zones (zone_id, tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, capacity, sort_order, status) VALUES
(24, 13, 13, 18, 'BAR', 'Main Bar Area', 'BAR', 'TABLE_SERVICE', 50, 1, 'ACTIVE'),
(25, 13, 13, 18, 'LOUNGE', 'Lounge Area', 'DINING', 'TABLE_SERVICE', 40, 2, 'ACTIVE'),
(26, 13, 13, 19, 'DANCE', 'Dance Floor', 'DINING', 'SELF_SERVICE', 200, 1, 'ACTIVE'),
(27, 13, 13, 20, 'VIP1', 'VIP Booth 1', 'PRIVATE', 'TABLE_SERVICE', 12, 1, 'ACTIVE'),
(28, 13, 13, 20, 'VIP2', 'VIP Booth 2', 'PRIVATE', 'TABLE_SERVICE', 12, 2, 'ACTIVE'),
(29, 13, 13, 20, 'VIP3', 'VIP Booth 3', 'PRIVATE', 'TABLE_SERVICE', 8, 3, 'ACTIVE');

-- Nightclub Events
INSERT IGNORE INTO nightclub_events (event_id, tenant_id, branch_id, event_name, description, event_date, start_time, end_time, theme, dj_name, dj_genre, capacity, status, is_active) VALUES
(1, 13, 13, 'Neon Friday Night', 'Weekly Friday night party with top DJs', CURDATE() + INTERVAL 2 - DAYOFWEEK(CURDATE()) DAY, '22:00:00', '04:00:00', 'Neon Glow', 'DJ Blast', 'EDM', 300, 'SCHEDULED', 1),
(2, 13, 13, 'Saturday Bash', 'Saturday night with special guest DJ', CURDATE() + INTERVAL 6 - DAYOFWEEK(CURDATE()) DAY, '22:00:00', '04:00:00', 'Retro Night', 'DJ Mixmaster', 'House', 300, 'SCHEDULED', 1),
(3, 13, 13, 'Ladies Night Wednesday', 'Ladies get free entry before midnight', CURDATE() + INTERVAL 3 - DAYOFWEEK(CURDATE()) DAY, '21:00:00', '03:00:00', 'Ladies Night', 'DJ Sparkle', 'Pop/Top 40', 200, 'SCHEDULED', 1);

-- Entrance Fees
INSERT IGNORE INTO nightclub_entrance_fees (fee_id, tenant_id, branch_id, event_id, fee_name, fee_type, price, applicable_days, start_time, end_time, min_age, gender_restriction, includes_drink, is_active) VALUES
(1, 13, 13, 1, 'Standard Entry', 'COVER_CHARGE', 150000.00, '5', '22:00:00', '04:00:00', 21, NULL, 0, 1),
(2, 13, 13, 1, 'Early Bird (Before 11PM)', 'COVER_CHARGE', 100000.00, '5', '22:00:00', '23:00:00', 21, NULL, 1, 1),
(3, 13, 13, 2, 'Standard Entry', 'COVER_CHARGE', 200000.00, '6', '22:00:00', '04:00:00', 21, NULL, 0, 1),
(4, 13, 13, 3, 'Ladies Free Entry', 'COVER_CHARGE', 0.00, '3', '21:00:00', '00:00:00', 21, 'FEMALE', 0, 1),
(5, 13, 13, 3, 'Gentlemen Entry', 'COVER_CHARGE', 100000.00, '3', '21:00:00', '03:00:00', 21, 'MALE', 0, 1);

-- Guest List
INSERT IGNORE INTO nightclub_guest_lists (guest_list_id, tenant_id, branch_id, event_id, guest_name, phone, party_size, entry_type, discount_percentage, added_by, notes) VALUES
(1, 13, 13, 1, 'Rudi Hartono', '081234500001', 3, 'FREE_ENTRY', 100, 'Manager', 'VIP customer'),
(2, 13, 13, 1, 'Sara Dewi', '081234500002', 2, 'DISCOUNT_ENTRY', 50, 'Manager', 'Birthday celebration'),
(3, 13, 13, 2, 'Budi Gunawan', '081234500003', 5, 'FREE_ENTRY', 100, 'Owner', 'Business partner');

-- Bottle Service Reservations
INSERT IGNORE INTO nightclub_bottle_service (bottle_service_id, tenant_id, branch_id, event_id, table_id, zone_id, customer_name, phone, party_size, package_name, bottle_type, bottle_quantity, unit_price, minimum_spend, total_amount, reservation_date, reservation_time, status, payment_status) VALUES
(1, 13, 13, 1, NULL, 27, 'Andi Wijaya', '081234500004', 6, 'VIP Gold Package', 'Johnnie Walker Gold', 2, 2500000.00, 5000000.00, 5000000.00, CURDATE() + INTERVAL 2 - DAYOFWEEK(CURDATE()) DAY, '23:00:00', 'CONFIRMED', 'PAID'),
(2, 13, 13, 2, NULL, 28, 'Maya Putri', '081234500005', 4, 'VIP Silver Package', 'Grey Goose', 1, 3000000.00, 3000000.00, 3000000.00, CURDATE() + INTERVAL 6 - DAYOFWEEK(CURDATE()) DAY, '23:30:00', 'PENDING', 'PENDING');

-- Table Reservations (VIP Booth)
INSERT IGNORE INTO nightclub_table_reservations (reservation_id, tenant_id, branch_id, event_id, table_id, zone_id, customer_name, phone, email, party_size, reservation_date, arrival_time, minimum_spend, table_type, status, notes) VALUES
(1, 13, 13, 1, NULL, 27, 'Andi Wijaya', '081234500004', 'andi@example.com', 6, CURDATE() + INTERVAL 2 - DAYOFWEEK(CURDATE()) DAY, '23:00:00', 5000000.00, 'VIP_BOOTH', 'CONFIRMED', 'Birthday celebration'),
(2, 13, 13, 2, NULL, 29, 'Chandra Tan', '081234500006', 'chandra@example.com', 8, CURDATE() + INTERVAL 6 - DAYOFWEEK(CURDATE()) DAY, '22:30:00', 8000000.00, 'VIP_BOOTH', 'PENDING', 'Corporate event'),
(3, 13, 13, 3, NULL, 24, 'Lisa Maria', '081234500007', 'lisa@example.com', 4, CURDATE() + INTERVAL 3 - DAYOFWEEK(CURDATE()) DAY, '21:30:00', 1500000.00, 'BAR_TABLE', 'CONFIRMED', NULL);
-- ========================================================
-- CHART OF ACCOUNTS FOR DISCOTHEQUE (Tenant 13)
-- ========================================================
INSERT IGNORE INTO chart_of_accounts (tenant_id, account_code, account_name, account_type, is_active) VALUES
-- ASSETS
(13, '1000', 'Cash on Hand', 'ASSET', 1),
(13, '1010', 'Cash Register', 'ASSET', 1),
(13, '1020', 'Bank Account', 'ASSET', 1),
(13, '1100', 'Inventory - Beverages', 'ASSET', 1),
(13, '1110', 'Inventory - Bar Supplies', 'ASSET', 1),
(13, '1200', 'Sound & Lighting Equipment', 'ASSET', 1),
(13, '1210', 'Furniture & Fixtures', 'ASSET', 1),
(13, '1300', 'Accounts Receivable', 'ASSET', 1),
(13, '1400', 'Prepaid Expenses', 'ASSET', 1),
-- LIABILITIES
(13, '2000', 'Accounts Payable', 'LIABILITY', 1),
(13, '2010', 'Accrued DJ Fees', 'LIABILITY', 1),
(13, '2020', 'Accrued Staff Wages', 'LIABILITY', 1),
(13, '2100', 'Customer Deposits (Bottle Service)', 'LIABILITY', 1),
(13, '2200', 'Tax Payable', 'LIABILITY', 1),
(13, '2300', 'Service Charge Payable', 'LIABILITY', 1),
-- EQUITY
(13, '3000', 'Owner''s Equity', 'EQUITY', 1),
(13, '3100', 'Retained Earnings', 'EQUITY', 1),
(13, '3200', 'Current Year Profit/Loss', 'EQUITY', 1),
-- REVENUE
(13, '4000', 'Entrance Fee Revenue', 'REVENUE', 1),
(13, '4010', 'Early Bird Ticket Revenue', 'REVENUE', 1),
(13, '4100', 'Bottle Service Revenue', 'REVENUE', 1),
(13, '4110', 'VIP Booth Revenue', 'REVENUE', 1),
(13, '4200', 'Bar Revenue (Drinks)', 'REVENUE', 1),
(13, '4210', 'Food Revenue', 'REVENUE', 1),
(13, '4300', 'Table Reservation Revenue', 'REVENUE', 1),
(13, '4400', 'Guest List Revenue (Discounted)', 'REVENUE', 1),
(13, '4500', 'Event Ticket Revenue', 'REVENUE', 1),
(13, '4600', 'Coat Check Revenue', 'REVENUE', 1),
(13, '4700', 'Other Revenue', 'REVENUE', 1),
-- EXPENSE
(13, '5000', 'COGS - Beverages', 'EXPENSE', 1),
(13, '5010', 'COGS - Food', 'EXPENSE', 1),
(13, '5020', 'COGS - Bar Supplies', 'EXPENSE', 1),
(13, '5100', 'DJ Fees & Entertainment', 'EXPENSE', 1),
(13, '5110', 'Live Performance Fees', 'EXPENSE', 1),
(13, '5200', 'Security Staff Wages', 'EXPENSE', 1),
(13, '5210', 'Bar Staff Wages', 'EXPENSE', 1),
(13, '5220', 'Waitstaff Wages', 'EXPENSE', 1),
(13, '5230', 'Management Salaries', 'EXPENSE', 1),
(13, '5300', 'Marketing & Promotion', 'EXPENSE', 1),
(13, '5310', 'Social Media Advertising', 'EXPENSE', 1),
(13, '5400', 'Rent', 'EXPENSE', 1),
(13, '5410', 'Utilities (Electricity, Water)', 'EXPENSE', 1),
(13, '5420', 'Sound & Lighting Maintenance', 'EXPENSE', 1),
(13, '5500', 'Insurance', 'EXPENSE', 1),
(13, '5600', 'Licenses & Permits', 'EXPENSE', 1),
(13, '5700', 'Cleaning & Sanitation', 'EXPENSE', 1),
(13, '5800', 'Depreciation - Equipment', 'EXPENSE', 1),
(13, '5900', 'Miscellaneous Expenses', 'EXPENSE', 1);
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Seed data inserted successfully!' AS Status;
