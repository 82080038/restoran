<?php

/**
 * Migration 031: Create Staff Messages Tables
 * 
 * Creates tables for staff communication tools
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create staff_messages table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS staff_messages (
                message_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                sender_id BIGINT NOT NULL,
                recipient_id BIGINT NOT NULL,
                message TEXT NOT NULL,
                message_type VARCHAR(50) DEFAULT 'GENERAL',
                status VARCHAR(20) DEFAULT 'SENT',
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_sender (sender_id),
                INDEX idx_recipient (recipient_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS staff_messages");
    }
];
