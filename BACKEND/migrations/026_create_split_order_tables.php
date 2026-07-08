<?php

/**
 * Migration 026: Create Split Order Tables
 * 
 * Creates tables for split bill functionality
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Add split order columns to orders table if not exists
        $pdo->exec("
            ALTER TABLE orders 
            ADD COLUMN IF NOT EXISTS parent_order_id BIGINT NULL,
            ADD COLUMN IF NOT EXISTS split_identifier VARCHAR(50) NULL,
            ADD INDEX idx_parent_order (parent_order_id)
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("
            ALTER TABLE orders 
            DROP COLUMN IF EXISTS parent_order_id,
            DROP COLUMN IF EXISTS split_identifier
        ");
    }
];
