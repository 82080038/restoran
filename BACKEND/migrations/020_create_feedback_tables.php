<?php

/**
 * Migration 020: Create Feedback Tables
 * 
 * Creates tables for customer feedback including
 * reviews, surveys, and sentiment analysis
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create feedback table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS feedback (
                feedback_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                customer_id BIGINT,
                order_id BIGINT,
                feedback_type VARCHAR(50) NOT NULL,
                rating INT,
                comment TEXT,
                sentiment_score DECIMAL(3,2),
                sentiment_label VARCHAR(20),
                status VARCHAR(20) DEFAULT 'NEW',
                responded_at TIMESTAMP NULL,
                responded_by BIGINT,
                response_text TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_customer (customer_id),
                INDEX idx_order (order_id),
                INDEX idx_type (feedback_type),
                INDEX idx_status (status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create surveys table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS surveys (
                survey_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT,
                survey_name VARCHAR(255) NOT NULL,
                survey_type VARCHAR(50),
                questions JSON,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create survey_responses table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS survey_responses (
                response_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                survey_id BIGINT NOT NULL,
                customer_id BIGINT,
                order_id BIGINT,
                answers JSON,
                completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_survey (survey_id),
                INDEX idx_customer (customer_id),
                INDEX idx_order (order_id),
                INDEX idx_date (completed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS survey_responses");
        $pdo->exec("DROP TABLE IF EXISTS surveys");
        $pdo->exec("DROP TABLE IF EXISTS feedback");
    }
];
