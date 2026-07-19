<?php

declare(strict_types=1);

/**
 * Migration 011: Add tables for payment, notification, email, and delivery integration features
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

$tables = [
    'payment_methods' => "CREATE TABLE IF NOT EXISTS payment_methods (
        method_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        method_name VARCHAR(100) NOT NULL,
        method_type VARCHAR(50) NOT NULL DEFAULT 'cash',
        method_code VARCHAR(50) NOT NULL,
        gateway_config JSON NULL,
        is_active TINYINT(1) DEFAULT 1,
        is_default TINYINT(1) DEFAULT 0,
        display_order INT DEFAULT 0,
        icon_url VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'payment_transaction_logs' => "CREATE TABLE IF NOT EXISTS payment_transaction_logs (
        log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NULL,
        payment_id BIGINT NULL,
        transaction_type VARCHAR(50) NOT NULL DEFAULT 'payment',
        transaction_status VARCHAR(50) NOT NULL,
        amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        processed_at TIMESTAMP NULL,
        error_message TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_payment (payment_id),
        INDEX idx_tenant (tenant_id),
        INDEX idx_status (transaction_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'email_logs' => "CREATE TABLE IF NOT EXISTS email_logs (
        email_log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        recipient VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body_preview TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        error_message TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_recipient (recipient),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'notification_reads' => "CREATE TABLE IF NOT EXISTS notification_reads (
        notification_read_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        notification_id BIGINT NOT NULL,
        user_id BIGINT NOT NULL,
        read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_notification_user (notification_id, user_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'delivery_platform_integrations' => "CREATE TABLE IF NOT EXISTS delivery_platform_integrations (
        integration_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        platform_name VARCHAR(50) NOT NULL,
        api_key VARCHAR(255) NOT NULL,
        api_secret VARCHAR(255) NULL,
        merchant_id VARCHAR(255) NOT NULL,
        webhook_url VARCHAR(500) NULL,
        is_active TINYINT(1) DEFAULT 1,
        last_sync_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_tenant_platform (tenant_id, platform_name),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'delivery_sync_logs' => "CREATE TABLE IF NOT EXISTS delivery_sync_logs (
        sync_log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        platform_name VARCHAR(50) NOT NULL,
        sync_type VARCHAR(50) NOT NULL DEFAULT 'menu',
        items_count INT DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        response TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_platform (platform_name),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'cash_drawers' => "CREATE TABLE IF NOT EXISTS cash_drawers (
        drawer_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        drawer_name VARCHAR(100) NOT NULL DEFAULT 'Main Drawer',
        opening_balance DECIMAL(15,2) DEFAULT 0,
        expected_amount DECIMAL(15,2) NULL,
        actual_amount DECIMAL(15,2) NULL,
        variance DECIMAL(15,2) NULL,
        opened_by BIGINT NULL,
        closed_by BIGINT NULL,
        opened_at TIMESTAMP NULL,
        closed_at TIMESTAMP NULL,
        status VARCHAR(20) DEFAULT 'closed',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_status (status),
        INDEX idx_date (opened_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'tips' => "CREATE TABLE IF NOT EXISTS tips (
        tip_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        order_id BIGINT NULL,
        payment_id BIGINT NULL,
        tip_amount DECIMAL(15,2) NOT NULL,
        tip_type VARCHAR(20) DEFAULT 'fixed',
        tip_percentage DECIMAL(5,2) NULL,
        distribution_method VARCHAR(50) DEFAULT 'equal',
        distribution_config JSON NULL,
        staff_id BIGINT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_order (order_id),
        INDEX idx_staff (staff_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
$errors = 0;

foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "  + Created/verified table: {$tableName}\n";
        $created++;
    } catch (\PDOException $e) {
        echo "  x Failed: {$tableName}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

// Add columns to orders table
$alterStatements = [
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS platform_name VARCHAR(50) NULL" => "orders.platform_name",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS platform_order_id VARCHAR(255) NULL" => "orders.platform_order_id",
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS paid_amount DECIMAL(15,2) DEFAULT 0" => "orders.paid_amount",
];

foreach ($alterStatements as $sql => $label) {
    try {
        $pdo->exec($sql);
        echo "  + Added column: {$label}\n";
    } catch (\PDOException $e) {
        echo "  - Skip (exists): {$label}\n";
    }
}

echo "\nMigration 011 complete.\n";
echo "  Tables created/verified: {$created}\n";
echo "  Errors: {$errors}\n";
