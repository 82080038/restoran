<?php

/**
 * Migration 038: Add Complimentary Flag
 *
 * This migration adds a complimentary flag to the customer_pricing table
 * for better tracking and reporting of complimentary items.
 *
 * @package EBP\Migrations
 * @version 1.0.0
 */

return [
    'up' => function (PDO $pdo) {
        $tableExists = $pdo->query("
            SELECT COUNT(*) as count
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = 'customer_pricing'
        ")->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        if (!$tableExists) {
            $pdo->exec("
                CREATE TABLE customer_pricing (
                    pricing_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id BIGINT NOT NULL,
                    branch_id BIGINT,
                    customer_id BIGINT NOT NULL,
                    product_id BIGINT NOT NULL,
                    special_price DECIMAL(18,2) NOT NULL,
                    discount_percentage DECIMAL(5,2) DEFAULT 0,
                    is_complimentary TINYINT(1) DEFAULT 0 COMMENT 'Flag for complimentary items (free for customer)',
                    complimentary_reason VARCHAR(255) NULL COMMENT 'Reason for complimentary item (birthday, VIP, etc.)',
                    complimentary_code VARCHAR(50) NULL COMMENT 'Code for complimentary tracking and reporting',
                    valid_from DATE NULL,
                    valid_until DATE NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    created_by BIGINT,
                    updated_by BIGINT,
                    INDEX idx_tenant (tenant_id),
                    INDEX idx_branch (branch_id),
                    INDEX idx_customer (customer_id),
                    INDEX idx_product (product_id),
                    INDEX idx_complimentary (is_complimentary),
                    INDEX idx_validity (valid_from, valid_until),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            echo "Migration 038: Successfully created customer_pricing table with complimentary flag.\n";
            return;
        }

        $columnExists = $pdo->query("
            SELECT COUNT(*) as count
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
            AND table_name = 'customer_pricing'
            AND column_name = 'is_complimentary'
        ")->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        if ($columnExists) {
            echo "Column is_complimentary already exists in customer_pricing. Skipping migration.\n";
            return;
        }

        $pdo->exec("
            ALTER TABLE customer_pricing
            ADD COLUMN is_complimentary TINYINT(1) DEFAULT 0
            COMMENT 'Flag for complimentary items (free for customer)'
            AFTER discount_percentage
        ");

        $pdo->exec("
            ALTER TABLE customer_pricing
            ADD COLUMN complimentary_reason VARCHAR(255) NULL
            COMMENT 'Reason for complimentary item (birthday, VIP, etc.)'
            AFTER is_complimentary
        ");

        $pdo->exec("
            ALTER TABLE customer_pricing
            ADD COLUMN complimentary_code VARCHAR(50) NULL
            COMMENT 'Code for complimentary tracking and reporting'
            AFTER complimentary_reason
        ");

        $pdo->exec("
            CREATE INDEX idx_complimentary
            ON customer_pricing(is_complimentary)
        ");

        echo "Migration 038: Successfully added complimentary flag to customer_pricing.\n";
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("DROP INDEX IF EXISTS idx_complimentary ON customer_pricing");
        $pdo->exec("ALTER TABLE customer_pricing DROP COLUMN IF EXISTS complimentary_code");
        $pdo->exec("ALTER TABLE customer_pricing DROP COLUMN IF EXISTS complimentary_reason");
        $pdo->exec("ALTER TABLE customer_pricing DROP COLUMN IF EXISTS is_complimentary");

        echo "Migration 038: Successfully reverted complimentary flag.\n";
    }
];
