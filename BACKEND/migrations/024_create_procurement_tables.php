<?php

/**
 * Migration 024: Create Procurement Tables
 * 
 * Creates tables for procurement management including
 * purchase orders, requisitions, and supplier contracts
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create purchase_orders table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS purchase_orders (
                po_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                po_number VARCHAR(50) UNIQUE,
                supplier_id BIGINT NOT NULL,
                order_date DATE NOT NULL,
                expected_delivery_date DATE,
                status VARCHAR(20) DEFAULT 'DRAFT',
                subtotal DECIMAL(18,2),
                tax_amount DECIMAL(18,2),
                total_amount DECIMAL(18,2),
                notes TEXT,
                created_by BIGINT,
                approved_by BIGINT,
                approved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE RESTRICT,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_supplier (supplier_id),
                INDEX idx_status (status),
                INDEX idx_date (order_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create purchase_order_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS purchase_order_items (
                poi_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                po_id BIGINT NOT NULL,
                inventory_id BIGINT NOT NULL,
                quantity DECIMAL(10,4) NOT NULL,
                unit_price DECIMAL(18,2) NOT NULL,
                line_total DECIMAL(18,2),
                received_quantity DECIMAL(10,4) DEFAULT 0,
                INDEX idx_po (po_id),
                INDEX idx_inventory (inventory_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create supplier_contracts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS supplier_contracts (
                contract_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                supplier_id BIGINT NOT NULL,
                contract_number VARCHAR(50) UNIQUE,
                start_date DATE NOT NULL,
                end_date DATE,
                terms TEXT,
                payment_terms VARCHAR(100),
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_supplier (supplier_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS supplier_contracts");
        $pdo->exec("DROP TABLE IF EXISTS purchase_order_items");
        $pdo->exec("DROP TABLE IF EXISTS purchase_orders");
    }
];
