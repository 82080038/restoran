<?php

/**
 * Migration 011: Create Accounting Tables
 * 
 * Creates tables for accounting including journal entries,
 * accounts, and financial transactions
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create accounts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS accounts (
                account_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                account_code VARCHAR(50) UNIQUE,
                account_name VARCHAR(255) NOT NULL,
                account_type VARCHAR(50) NOT NULL,
                parent_account_id BIGINT,
                balance_type VARCHAR(10) DEFAULT 'DEBIT',
                opening_balance DECIMAL(18,2) DEFAULT 0,
                current_balance DECIMAL(18,2) DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_account_id) REFERENCES accounts(account_id) ON DELETE SET NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_code (account_code),
                INDEX idx_type (account_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create journal_entries table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS journal_entries (
                journal_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                journal_number VARCHAR(50) UNIQUE,
                journal_date DATE NOT NULL,
                journal_type VARCHAR(50) NOT NULL,
                reference_type VARCHAR(50),
                reference_id BIGINT,
                description TEXT,
                status VARCHAR(20) DEFAULT 'POSTED',
                total_debit DECIMAL(18,2) DEFAULT 0,
                total_credit DECIMAL(18,2) DEFAULT 0,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_date (journal_date),
                INDEX idx_type (journal_type),
                INDEX idx_reference (reference_type, reference_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create journal_lines table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS journal_lines (
                line_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                journal_id BIGINT NOT NULL,
                account_id BIGINT NOT NULL,
                description TEXT,
                debit_amount DECIMAL(18,2) DEFAULT 0,
                credit_amount DECIMAL(18,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (journal_id) REFERENCES journal_entries(journal_id) ON DELETE CASCADE,
                FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE RESTRICT,
                INDEX idx_journal (journal_id),
                INDEX idx_account (account_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS journal_lines");
        $pdo->exec("DROP TABLE IF EXISTS journal_entries");
        $pdo->exec("DROP TABLE IF EXISTS accounts");
    }
];
