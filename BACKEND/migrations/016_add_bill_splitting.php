<?php

declare(strict_types=1);

/**
 * Migration 016: Add table groups and bill splitting tables
 * - table_groups: groups of people at the same table with separate bills
 * - table_group_members: which chairs belong to which group
 * - bill_splits: individual bills per group
 * - bill_split_items: which order items belong to which bill
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

$tables = [
    'table_groups' => "CREATE TABLE IF NOT EXISTS table_groups (
        group_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        table_id BIGINT NOT NULL,
        group_name VARCHAR(50) NULL,
        group_color VARCHAR(20) DEFAULT '#667eea',
        group_size INT DEFAULT 1,
        status VARCHAR(20) DEFAULT 'active',
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ended_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_table (table_id),
        INDEX idx_tenant (tenant_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'table_group_members' => "CREATE TABLE IF NOT EXISTS table_group_members (
        member_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        group_id BIGINT NOT NULL,
        chair_id BIGINT NULL,
        chair_number INT NULL,
        member_name VARCHAR(50) NULL,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_group (group_id),
        INDEX idx_chair (chair_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'bill_splits' => "CREATE TABLE IF NOT EXISTS bill_splits (
        bill_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        table_id BIGINT NOT NULL,
        group_id BIGINT NULL,
        order_id BIGINT NULL,
        bill_number VARCHAR(50) NULL,
        bill_type VARCHAR(20) DEFAULT 'group',
        subtotal DECIMAL(12,2) DEFAULT 0,
        tax DECIMAL(12,2) DEFAULT 0,
        service_charge DECIMAL(12,2) DEFAULT 0,
        discount DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(12,2) DEFAULT 0,
        payment_status VARCHAR(20) DEFAULT 'unpaid',
        payment_method VARCHAR(30) NULL,
        paid_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_table (table_id),
        INDEX idx_group (group_id),
        INDEX idx_order (order_id),
        INDEX idx_tenant (tenant_id),
        INDEX idx_payment_status (payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'bill_split_items' => "CREATE TABLE IF NOT EXISTS bill_split_items (
        item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        bill_id BIGINT NOT NULL,
        order_item_id BIGINT NOT NULL,
        product_name VARCHAR(100) NULL,
        quantity DECIMAL(10,2) DEFAULT 1,
        unit_price DECIMAL(12,2) DEFAULT 0,
        total_price DECIMAL(12,2) DEFAULT 0,
        split_type VARCHAR(20) DEFAULT 'full',
        split_ratio DECIMAL(5,2) DEFAULT 1.00,
        assigned_by VARCHAR(20) DEFAULT 'manual',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_bill (bill_id),
        INDEX idx_order_item (order_item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'bill_split_history' => "CREATE TABLE IF NOT EXISTS bill_split_history (
        history_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        table_id BIGINT NOT NULL,
        action VARCHAR(30) NOT NULL,
        description TEXT NULL,
        performed_by BIGINT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_table (table_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "  + Created table: {$name}\n";
        $created++;
    } catch (\PDOException $e) {
        echo "  x Failed: {$name}: " . $e->getMessage() . "\n";
    }
}

echo "\nMigration 016 complete. Tables: {$created}\n";
