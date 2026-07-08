<?php

/**
 * Migration 021: Create Language Tables
 * 
 * Creates tables for multi-language support including
 * translations and language preferences
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // Create languages table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS languages (
                language_id INT AUTO_INCREMENT PRIMARY KEY,
                language_code VARCHAR(10) UNIQUE,
                language_name VARCHAR(100) NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                is_default BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create translations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS translations (
                translation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                language_id INT NOT NULL,
                translation_key VARCHAR(255) NOT NULL,
                translation_value TEXT NOT NULL,
                context VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE,
                UNIQUE KEY uk_language_key (language_id, translation_key),
                INDEX idx_key (translation_key),
                INDEX idx_context (context)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create user_language_preferences table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_language_preferences (
                preference_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                language_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    },

    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS user_language_preferences");
        $pdo->exec("DROP TABLE IF EXISTS translations");
        $pdo->exec("DROP TABLE IF EXISTS languages");
    }
];
