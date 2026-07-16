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
(12, 'EBP_STALL', 'EBP Stall Kiosk', 'STALL_KIOSK', 'ACTIVE');

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
(12, 12, 'EBP_STALL_CO', 'EBP Stall Company', 'ACTIVE');

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
(12, 12, 12, 'STALL001', 'EBP Stall Kiosk Jakarta', 'FX Sudirman Mall', '+62 21 2345 6789', 'ACTIVE');

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

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Seed data inserted successfully!' AS Status;
