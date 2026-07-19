<?php

/**
 * Migration 039: Create Free Payment Tables
 *
 * Creates tables for zero-fee payment methods:
 * - transfer_proofs: Bukti transfer uploads for manual bank transfer verification
 * - qris_static_configs: QRIS static QR code configuration per tenant/branch
 * - wallets: Internal prepaid wallet for customers
 * - wallet_transactions: Wallet transaction history
 * - wallet_topup_requests: Top-up requests via manual bank transfer
 *
 * @version 1.0.0
 * @date 2026-07-19
 */

return [
    'up' => function($pdo) {
        // Create transfer_proofs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS transfer_proofs (
                proof_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                payment_id BIGINT NOT NULL,
                order_id BIGINT NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_size INT,
                file_type VARCHAR(50),
                bank_from VARCHAR(100),
                account_holder VARCHAR(200),
                transfer_amount DECIMAL(18,2) NOT NULL,
                transfer_date DATE,
                reference_number VARCHAR(100),
                uploaded_by BIGINT,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                verification_status VARCHAR(20) DEFAULT 'pending',
                verified_by BIGINT,
                verified_at TIMESTAMP NULL,
                rejection_reason TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_payment (payment_id),
                INDEX idx_order (order_id),
                INDEX idx_status (verification_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create qris_static_configs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS qris_static_configs (
                qris_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                merchant_name VARCHAR(200) NOT NULL,
                merchant_id VARCHAR(50),
                nmid VARCHAR(50),
                terminal_id VARCHAR(50),
                qr_content TEXT NOT NULL,
                qr_image_path VARCHAR(500),
                acquirer_bank VARCHAR(100),
                acquirer_code VARCHAR(20),
                mdr_rate DECIMAL(5,4) DEFAULT 0.0070,
                is_active BOOLEAN DEFAULT TRUE,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create wallets table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallets (
                wallet_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                customer_id BIGINT NOT NULL,
                wallet_number VARCHAR(50) UNIQUE,
                balance DECIMAL(18,2) DEFAULT 0,
                held_balance DECIMAL(18,2) DEFAULT 0,
                currency VARCHAR(3) DEFAULT 'IDR',
                status VARCHAR(20) DEFAULT 'active',
                pin_hash VARCHAR(255),
                last_topup_at TIMESTAMP NULL,
                last_transaction_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_tenant_customer (tenant_id, customer_id),
                INDEX idx_tenant (tenant_id),
                INDEX idx_customer (customer_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create wallet_transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallet_transactions (
                transaction_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                wallet_id BIGINT NOT NULL,
                customer_id BIGINT NOT NULL,
                transaction_type VARCHAR(20) NOT NULL,
                direction VARCHAR(10) NOT NULL,
                amount DECIMAL(18,2) NOT NULL,
                balance_before DECIMAL(18,2),
                balance_after DECIMAL(18,2),
                order_id BIGINT,
                payment_id BIGINT,
                topup_request_id BIGINT,
                reference_number VARCHAR(100),
                description VARCHAR(500),
                status VARCHAR(20) DEFAULT 'completed',
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_wallet (wallet_id),
                INDEX idx_customer (customer_id),
                INDEX idx_type (transaction_type),
                INDEX idx_order (order_id),
                INDEX idx_status (status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create wallet_topup_requests table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS wallet_topup_requests (
                topup_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                wallet_id BIGINT NOT NULL,
                customer_id BIGINT NOT NULL,
                amount DECIMAL(18,2) NOT NULL,
                topup_method VARCHAR(50) DEFAULT 'bank_transfer',
                bank_from VARCHAR(100),
                account_holder VARCHAR(200),
                transfer_date DATE,
                reference_number VARCHAR(100),
                proof_file_path VARCHAR(500),
                proof_file_name VARCHAR(255),
                status VARCHAR(20) DEFAULT 'pending',
                verified_by BIGINT,
                verified_at TIMESTAMP NULL,
                rejection_reason TEXT,
                expires_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_wallet (wallet_id),
                INDEX idx_customer (customer_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS wallet_topup_requests");
        $pdo->exec("DROP TABLE IF EXISTS wallet_transactions");
        $pdo->exec("DROP TABLE IF EXISTS wallets");
        $pdo->exec("DROP TABLE IF EXISTS qris_static_configs");
        $pdo->exec("DROP TABLE IF EXISTS transfer_proofs");
    }
];
