<?php

/**
 * Migration 034: Add Raw Material Availability Check
 * 
 * Note: Due to MariaDB version incompatibility with stored procedures,
 * availability check logic will be implemented in application layer
 * This migration is kept as a placeholder for future implementation
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

return [
    'up' => function($pdo) {
        // This migration is intentionally empty
        // Availability check logic will be implemented in PHP application layer
        // See: BACKEND/helpers/InventoryAvailabilityHelper.php
    },

    'down' => function($pdo) {
        // Nothing to rollback
    }
];
