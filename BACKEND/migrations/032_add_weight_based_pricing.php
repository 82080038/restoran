<?php

/**
 * Migration 032: Add Weight-Based Pricing Support
 * 
 * Adds fields to support weight-based and unit-based pricing for made-to-order products
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Add pricing_type field to products
        $pdo->exec("
            ALTER TABLE products 
            ADD COLUMN pricing_type ENUM('FIXED', 'WEIGHT_BASED', 'UNIT_BASED') DEFAULT 'FIXED' AFTER status
        ");

        // Add unit_price_per_kg field to products
        $pdo->exec("
            ALTER TABLE products 
            ADD COLUMN unit_price_per_kg DECIMAL(18,2) AFTER pricing_type
        ");

        // Add unit_price_per_unit field to products
        $pdo->exec("
            ALTER TABLE products 
            ADD COLUMN unit_price_per_unit DECIMAL(18,2) AFTER unit_price_per_kg
        ");

        // Add actual_weight field to order_items
        $pdo->exec("
            ALTER TABLE order_items 
            ADD COLUMN actual_weight DECIMAL(10,3) AFTER quantity
        ");

        // Add actual_unit_id field to order_items
        $pdo->exec("
            ALTER TABLE order_items 
            ADD COLUMN actual_unit_id BIGINT AFTER actual_weight
        ");

        // Add calculated_price field to order_items
        $pdo->exec("
            ALTER TABLE order_items 
            ADD COLUMN calculated_price DECIMAL(18,2) AFTER actual_unit_id
        ");

        // Add index for actual_unit_id
        $pdo->exec("
            ALTER TABLE order_items 
            ADD INDEX idx_actual_unit (actual_unit_id)
        ");
    },

    'down' => function($pdo) {
        // Remove calculated_price from order_items
        $pdo->exec("ALTER TABLE order_items DROP COLUMN calculated_price");
        
        // Remove actual_unit_id from order_items
        $pdo->exec("ALTER TABLE order_items DROP COLUMN actual_unit_id");
        
        // Remove actual_weight from order_items
        $pdo->exec("ALTER TABLE order_items DROP COLUMN actual_weight");
        
        // Remove unit_price_per_unit from products
        $pdo->exec("ALTER TABLE products DROP COLUMN unit_price_per_unit");
        
        // Remove unit_price_per_kg from products
        $pdo->exec("ALTER TABLE products DROP COLUMN unit_price_per_kg");
        
        // Remove pricing_type from products
        $pdo->exec("ALTER TABLE products DROP COLUMN pricing_type");
    }
];
