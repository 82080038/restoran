USE ebp_restaurant_db;
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================================
-- Minimal seed data aligned with the migration schema
-- ========================================================

-- Tenants
INSERT IGNORE INTO tenants (tenant_id, tenant_code, tenant_name, business_type, status) VALUES
(1, 'EBP_DEMO', 'EBP Restaurant Demo', 'RESTAURANT', 'ACTIVE'),
(2, 'EBP_CAFE', 'EBP Coffee House', 'COFFEE_SHOP', 'ACTIVE');

-- Companies
INSERT IGNORE INTO companies (company_id, tenant_id, company_code, company_name, status) VALUES
(1, 1, 'EBP_CO', 'EBP Restaurant Company', 'ACTIVE'),
(2, 2, 'EBP_CAFE_CO', 'EBP Coffee House Company', 'ACTIVE');

-- Branches
INSERT IGNORE INTO branches (branch_id, tenant_id, company_id, branch_code, branch_name, address, phone, status) VALUES
(1, 1, 1, 'JKT001', 'EBP Restaurant Jakarta', 'Jl. Sudirman No. 123', '+62 21 1234 5678', 'ACTIVE'),
(2, 2, 2, 'CAFE001', 'EBP Coffee House Jakarta', 'Jl. Senopati No. 45', '+62 21 2345 6789', 'ACTIVE');

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

-- Assign roles to tenant 1 users
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(30, 1),  -- resto_platform_owner -> PLATFORM_OWNER
(31, 2),  -- resto_admin -> ADMIN
(32, 3),  -- resto_manager -> MANAGER
(33, 4),  -- resto_cashier -> CASHIER
(34, 5);  -- resto_kitchen -> KITCHEN

-- Assign roles to tenant 2 users
INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
(40, 1),  -- cafe_platform_owner -> PLATFORM_OWNER
(41, 2),  -- cafe_admin -> ADMIN
(42, 3),  -- cafe_manager -> MANAGER
(43, 4),  -- cafe_cashier -> CASHIER
(44, 5);  -- cafe_kitchen -> KITCHEN

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
