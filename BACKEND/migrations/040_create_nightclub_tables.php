<?php

declare(strict_types=1);

/**
 * Migration 040: Create Nightclub / Discotheque module tables
 *
 * Tables:
 * - nightclub_events (event/DJ schedule for the night)
 * - nightclub_entrance_fees (cover charge config per event/night)
 * - nightclub_entrance_tickets (individual entrance ticket sales)
 * - nightclub_guest_lists (guest list entries for VIP/free entry)
 * - nightclub_bottle_service (bottle service reservations with minimum spend)
 * - nightclub_table_reservations (VIP booth/table reservations)
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

$tables = [
    'nightclub_events' => "CREATE TABLE IF NOT EXISTS nightclub_events (
        event_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        event_name VARCHAR(200) NOT NULL,
        description TEXT NULL,
        event_date DATE NOT NULL,
        start_time TIME NOT NULL DEFAULT '22:00:00',
        end_time TIME NOT NULL DEFAULT '04:00:00',
        theme VARCHAR(100) NULL,
        dj_name VARCHAR(200) NULL,
        dj_genre VARCHAR(100) NULL,
        poster_url VARCHAR(500) NULL,
        capacity INT NULL,
        status VARCHAR(20) DEFAULT 'SCHEDULED',
        is_active TINYINT(1) DEFAULT 1,
        created_by BIGINT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_event_date (event_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'nightclub_entrance_fees' => "CREATE TABLE IF NOT EXISTS nightclub_entrance_fees (
        fee_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        event_id BIGINT NULL,
        fee_name VARCHAR(100) NOT NULL,
        fee_type VARCHAR(20) NOT NULL DEFAULT 'COVER_CHARGE',
        price DECIMAL(15,2) NOT NULL,
        applicable_days VARCHAR(20) DEFAULT '5,6',
        start_time TIME NULL,
        end_time TIME NULL,
        min_age INT NULL,
        gender_restriction VARCHAR(10) NULL,
        includes_drink TINYINT(1) DEFAULT 0,
        description TEXT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_event (event_id),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'nightclub_entrance_tickets' => "CREATE TABLE IF NOT EXISTS nightclub_entrance_tickets (
        ticket_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        event_id BIGINT NULL,
        fee_id BIGINT NULL,
        customer_name VARCHAR(200) NOT NULL,
        phone VARCHAR(50) NULL,
        email VARCHAR(255) NULL,
        id_type VARCHAR(20) NULL,
        id_number VARCHAR(100) NULL,
        age_verified TINYINT(1) DEFAULT 0,
        gender VARCHAR(10) NULL,
        quantity INT DEFAULT 1,
        unit_price DECIMAL(15,2) NOT NULL,
        total_amount DECIMAL(15,2) NOT NULL,
        payment_status VARCHAR(20) DEFAULT 'PENDING',
        payment_method VARCHAR(50) NULL,
        check_in_status TINYINT(1) DEFAULT 0,
        check_in_at TIMESTAMP NULL,
        ticket_code VARCHAR(50) NULL,
        sold_by BIGINT NULL,
        sold_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_event (event_id),
        INDEX idx_ticket_code (ticket_code),
        INDEX idx_check_in (check_in_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'nightclub_guest_lists' => "CREATE TABLE IF NOT EXISTS nightclub_guest_lists (
        guest_list_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        event_id BIGINT NULL,
        guest_name VARCHAR(200) NOT NULL,
        phone VARCHAR(50) NULL,
        email VARCHAR(255) NULL,
        party_size INT DEFAULT 1,
        entry_type VARCHAR(20) DEFAULT 'FREE_ENTRY',
        discount_percentage INT DEFAULT 0,
        added_by VARCHAR(200) NULL,
        notes TEXT NULL,
        check_in_status TINYINT(1) DEFAULT 0,
        check_in_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_event (event_id),
        INDEX idx_check_in (check_in_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'nightclub_bottle_service' => "CREATE TABLE IF NOT EXISTS nightclub_bottle_service (
        bottle_service_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        event_id BIGINT NULL,
        table_id BIGINT NULL,
        zone_id BIGINT NULL,
        customer_name VARCHAR(200) NOT NULL,
        phone VARCHAR(50) NULL,
        party_size INT DEFAULT 1,
        package_name VARCHAR(200) NOT NULL,
        bottle_type VARCHAR(100) NULL,
        bottle_quantity INT DEFAULT 1,
        unit_price DECIMAL(15,2) NOT NULL,
        minimum_spend DECIMAL(15,2) NOT NULL,
        total_amount DECIMAL(15,2) NOT NULL,
        reservation_date DATE NOT NULL,
        reservation_time TIME NULL,
        status VARCHAR(20) DEFAULT 'PENDING',
        payment_status VARCHAR(20) DEFAULT 'PENDING',
        payment_method VARCHAR(50) NULL,
        special_requests TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_event (event_id),
        INDEX idx_table (table_id),
        INDEX idx_reservation_date (reservation_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'nightclub_table_reservations' => "CREATE TABLE IF NOT EXISTS nightclub_table_reservations (
        reservation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT NULL,
        event_id BIGINT NULL,
        table_id BIGINT NULL,
        zone_id BIGINT NULL,
        customer_name VARCHAR(200) NOT NULL,
        phone VARCHAR(50) NULL,
        email VARCHAR(255) NULL,
        party_size INT NOT NULL,
        reservation_date DATE NOT NULL,
        arrival_time TIME NULL,
        minimum_spend DECIMAL(15,2) DEFAULT 0,
        table_type VARCHAR(50) NULL,
        status VARCHAR(20) DEFAULT 'PENDING',
        assigned_by BIGINT NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id),
        INDEX idx_event (event_id),
        INDEX idx_table (table_id),
        INDEX idx_reservation_date (reservation_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "  + Created/verified table: {$tableName}\n";
        $created++;
    } catch (\PDOException $e) {
        echo "  x Failed: {$tableName}: " . $e->getMessage() . "\n";
    }
}

echo "\nMigration 040 complete. Tables: {$created}\n";
