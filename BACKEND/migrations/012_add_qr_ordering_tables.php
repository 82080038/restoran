<?php

declare(strict_types=1);

/**
 * Migration 012: Add tables for QR Code ordering
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

$tables = [
    'qr_order_codes' => "CREATE TABLE IF NOT EXISTS qr_order_codes (
        qr_id VARCHAR(100) PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        table_id BIGINT NULL,
        qr_url VARCHAR(500) NULL,
        code_type VARCHAR(20) DEFAULT 'static',
        is_active TINYINT(1) DEFAULT 1,
        created_by BIGINT NULL,
        scan_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_table (table_id),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'qr_order_sessions' => "CREATE TABLE IF NOT EXISTS qr_order_sessions (
        session_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        qr_id VARCHAR(100) NOT NULL,
        tenant_id BIGINT NOT NULL,
        table_id BIGINT NULL,
        order_id BIGINT NULL,
        session_token VARCHAR(64) NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_qr (qr_id),
        INDEX idx_order (order_id),
        INDEX idx_token (session_token),
        INDEX idx_status (status)
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

// Add image_url column to products if not exists
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) NULL");
    echo "  + Added column: products.image_url\n";
} catch (\PDOException $e) {
    echo "  - Skip (exists): products.image_url\n";
}

// Add special_request column to order_items if not exists
try {
    $pdo->exec("ALTER TABLE order_items ADD COLUMN IF NOT EXISTS special_request TEXT NULL");
    echo "  + Added column: order_items.special_request\n";
} catch (\PDOException $e) {
    echo "  - Skip (exists): order_items.special_request\n";
}

echo "\nMigration 012 complete.\n";
echo "  Tables created/verified: {$created}\n";
echo "  Errors: {$errors}\n";
