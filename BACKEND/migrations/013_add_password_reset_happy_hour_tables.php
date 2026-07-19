<?php

declare(strict_types=1);

/**
 * Migration 013: Add password_reset_tokens table and happy_hour_promotions table
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

$tables = [
    'password_reset_tokens' => "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        reset_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT NOT NULL,
        token VARCHAR(64) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        used TINYINT(1) DEFAULT 0,
        used_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_token (token),
        INDEX idx_user (user_id),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'happy_hour_promotions' => "CREATE TABLE IF NOT EXISTS happy_hour_promotions (
        promotion_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        promotion_name VARCHAR(100) NOT NULL,
        description TEXT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        applicable_days VARCHAR(20) DEFAULT '1,2,3,4,5,6,7',
        discount_type VARCHAR(20) NOT NULL DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        min_order_amount DECIMAL(15,2) DEFAULT 0,
        max_discount_amount DECIMAL(15,2) NULL,
        applicable_categories VARCHAR(500) NULL,
        applicable_products VARCHAR(500) NULL,
        is_active TINYINT(1) DEFAULT 1,
        priority INT DEFAULT 0,
        start_date DATE NULL,
        end_date DATE NULL,
        created_by BIGINT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_active (is_active),
        INDEX idx_time (start_time, end_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'promotion_usages' => "CREATE TABLE IF NOT EXISTS promotion_usages (
        usage_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        promotion_id BIGINT NOT NULL,
        tenant_id BIGINT NOT NULL,
        order_id BIGINT NULL,
        discount_amount DECIMAL(15,2) NOT NULL,
        original_amount DECIMAL(15,2) NOT NULL,
        final_amount DECIMAL(15,2) NOT NULL,
        used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_promotion (promotion_id),
        INDEX idx_order (order_id),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "  + Created/verified table: {$tableName}\n";
        $created++;
    } catch (\PDOException $e) {
        echo "  x Failed: {$tableName}: " . $e->getMessage() . "\n";
    }
}

// Add email column to users if not exists
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(255) NULL");
    echo "  + Added column: users.email\n";
} catch (\PDOException $e) {
    echo "  - Skip (exists): users.email\n";
}

echo "\nMigration 013 complete. Tables: {$created}\n";
