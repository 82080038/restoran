<?php

/**
 * Migration 036: Enhance Product Condition Pricing
 *
 * This migration adds specific enum values for product conditions
 * (REFRIGERATED, WITH_ICE) to the product_prices table for better clarity
 * in handling different product condition pricing scenarios.
 *
 * @package EBP\Migrations
 * @version 1.0.0
 */

return [
    'up' => function (PDO $pdo) {
        // Check if product_prices table exists
        $tableExists = $pdo->query("
            SELECT COUNT(*) as count
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = 'product_prices'
        ")->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        if (!$tableExists) {
            echo "Table product_prices does not exist. Skipping migration.\n";
            return;
        }

        // Check current price_type column definition
        $columnInfo = $pdo->query("
            SHOW COLUMNS FROM product_prices LIKE 'price_type'
        ")->fetch(PDO::FETCH_ASSOC);

        if (!$columnInfo) {
            echo "Column price_type does not exist in product_prices. Skipping migration.\n";
            return;
        }

        // Modify the price_type column to include new enum values
        $pdo->exec("
            ALTER TABLE product_prices
            MODIFY COLUMN price_type ENUM(
                'REGULAR',
                'REFRIGERATED',
                'WITH_ICE',
                'HOT',
                'ROOM_TEMPERATURE',
                'FROZEN',
                'TAKEAWAY',
                'DINE_IN',
                'DELIVERY',
                'PROMOTIONAL',
                'BULK',
                'WHOLESALE'
            ) DEFAULT 'REGULAR'
            COMMENT 'Product condition/service type pricing'
        ");

        echo "Migration 036: Successfully enhanced product condition pricing.\n";
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("
            ALTER TABLE product_prices
            MODIFY COLUMN price_type ENUM(
                'REGULAR',
                'PROMOTIONAL',
                'BULK',
                'WHOLESALE'
            ) DEFAULT 'REGULAR'
        ");

        echo "Migration 036: Successfully reverted product condition pricing.\n";
    }
];
