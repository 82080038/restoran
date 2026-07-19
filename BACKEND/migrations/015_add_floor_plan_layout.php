<?php

declare(strict_types=1);

/**
 * Migration 015: Add floor plan layout columns and table_chairs table
 * - Add position/shape columns to tables
 * - Create table_chairs for chair positions around each table
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

// Add layout columns to tables
$tableColumns = [
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS table_shape VARCHAR(20) DEFAULT 'rectangle'",
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS pos_x DECIMAL(10,2) DEFAULT 0",
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS pos_y DECIMAL(10,2) DEFAULT 0",
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS table_width DECIMAL(10,2) DEFAULT 80",
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS table_height DECIMAL(10,2) DEFAULT 80",
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS table_rotation DECIMAL(5,2) DEFAULT 0",
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS chair_count INT DEFAULT 4",
    "ALTER TABLE tables ADD COLUMN IF NOT EXISTS layout_data TEXT NULL",
];

foreach ($tableColumns as $sql) {
    try {
        $pdo->exec($sql);
        echo "  + " . substr($sql, 0, 60) . "...\n";
    } catch (\PDOException $e) {
        echo "  - Skip: " . substr($sql, 0, 60) . "... (" . $e->getMessage() . ")\n";
    }
}

// Create table_chairs table
$tables = [
    'table_chairs' => "CREATE TABLE IF NOT EXISTS table_chairs (
        chair_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        table_id BIGINT NOT NULL,
        chair_number INT NOT NULL,
        chair_shape VARCHAR(20) DEFAULT 'square',
        pos_x DECIMAL(10,2) NOT NULL DEFAULT 0,
        pos_y DECIMAL(10,2) NOT NULL DEFAULT 0,
        chair_rotation DECIMAL(5,2) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_table_chair (table_id, chair_number),
        INDEX idx_table (table_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "  + Created table: {$name}\n";
    } catch (\PDOException $e) {
        echo "  x Failed: {$name}: " . $e->getMessage() . "\n";
    }
}

// Add layout columns to floors
$floorColumns = [
    "ALTER TABLE floors ADD COLUMN IF NOT EXISTS canvas_width INT DEFAULT 1200",
    "ALTER TABLE floors ADD COLUMN IF NOT EXISTS canvas_height INT DEFAULT 800",
    "ALTER TABLE floors ADD COLUMN IF NOT EXISTS background_image VARCHAR(500) NULL",
    "ALTER TABLE floors ADD COLUMN IF NOT EXISTS grid_enabled TINYINT(1) DEFAULT 1",
    "ALTER TABLE floors ADD COLUMN IF NOT EXISTS grid_size INT DEFAULT 20",
];

foreach ($floorColumns as $sql) {
    try {
        $pdo->exec($sql);
        echo "  + " . substr($sql, 0, 60) . "...\n";
    } catch (\PDOException $e) {
        echo "  - Skip: " . substr($sql, 0, 60) . "...\n";
    }
}

// Add layout columns to zones
$zoneColumns = [
    "ALTER TABLE zones ADD COLUMN IF NOT EXISTS zone_color VARCHAR(20) NULL",
    "ALTER TABLE zones ADD COLUMN IF NOT EXISTS pos_x DECIMAL(10,2) DEFAULT 0",
    "ALTER TABLE zones ADD COLUMN IF NOT EXISTS pos_y DECIMAL(10,2) DEFAULT 0",
    "ALTER TABLE zones ADD COLUMN IF NOT EXISTS zone_width DECIMAL(10,2) DEFAULT 400",
    "ALTER TABLE zones ADD COLUMN IF NOT EXISTS zone_height DECIMAL(10,2) DEFAULT 300",
];

foreach ($zoneColumns as $sql) {
    try {
        $pdo->exec($sql);
        echo "  + " . substr($sql, 0, 60) . "...\n";
    } catch (\PDOException $e) {
        echo "  - Skip: " . substr($sql, 0, 60) . "...\n";
    }
}

echo "\nMigration 015 complete.\n";
