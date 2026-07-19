<?php

/**
 * Migration 044: Create POS-to-Bank Reconciliation tables
 *
 * Tables:
 * - pos_bank_deposits (daily deposit matching POS sales vs bank)
 * - merchant_fees (payment processor fee tracking per transaction)
 * - eod_closeouts (end-of-day close-out reconciliation workflow)
 */

return [
    'up' => function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pos_bank_deposits (
                deposit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                deposit_date DATE NOT NULL,
                pos_sales_total DECIMAL(18,2) NOT NULL DEFAULT 0,
                cash_sales_total DECIMAL(18,2) NOT NULL DEFAULT 0,
                non_cash_sales_total DECIMAL(18,2) NOT NULL DEFAULT 0,
                bank_deposit_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                cash_drawer_counted DECIMAL(18,2) NOT NULL DEFAULT 0,
                cash_drawer_expected DECIMAL(18,2) NOT NULL DEFAULT 0,
                cash_variance DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_variance DECIMAL(18,2) NOT NULL DEFAULT 0,
                status ENUM('PENDING','MATCHED','VARIANCE','RESOLVED','CLOSED') DEFAULT 'PENDING',
                matched_by BIGINT NULL,
                matched_at TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (deposit_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS merchant_fees (
                fee_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                order_id BIGINT NULL,
                transaction_date DATE NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                processor_name VARCHAR(100) NOT NULL,
                gross_amount DECIMAL(18,2) NOT NULL,
                fee_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                fee_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
                net_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                external_transaction_id VARCHAR(255),
                metadata JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_order (order_id),
                INDEX idx_date (transaction_date),
                INDEX idx_processor (processor_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS eod_closeouts (
                closeout_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                closeout_date DATE NOT NULL,
                opened_by BIGINT NOT NULL,
                closed_by BIGINT NULL,
                opened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                closed_at TIMESTAMP NULL,
                opening_cash DECIMAL(18,2) NOT NULL DEFAULT 0,
                cash_in DECIMAL(18,2) NOT NULL DEFAULT 0,
                cash_out DECIMAL(18,2) NOT NULL DEFAULT 0,
                expected_cash DECIMAL(18,2) NOT NULL DEFAULT 0,
                counted_cash DECIMAL(18,2) NOT NULL DEFAULT 0,
                cash_variance DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_sales DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_refunds DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_discounts DECIMAL(18,2) NOT NULL DEFAULT 0,
                payment_breakdown JSON,
                status ENUM('OPEN','CLOSED','RECONCILED','DISCREPANCY') DEFAULT 'OPEN',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (closeout_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS eod_closeouts");
        $pdo->exec("DROP TABLE IF EXISTS merchant_fees");
        $pdo->exec("DROP TABLE IF EXISTS pos_bank_deposits");
    }
];
