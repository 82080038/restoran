<?php

declare(strict_types=1);

/**
 * Migration 042: Create tables for Karaoke Bar, Beach Club, and Live Music Venue
 *
 * These three business types share similar flow with Nightclub:
 * - Events/sessions with time-based scheduling
 * - Reservations (rooms/cabanas/seats)
 * - Entrance fees / tickets
 * - F&B service integration
 * - Accounting integration via journal entries
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

$tables = [
    // ==================== KARAOKE BAR ====================
    "CREATE TABLE IF NOT EXISTS karaoke_rooms (
        room_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        room_code VARCHAR(20) NOT NULL,
        room_name VARCHAR(100) NOT NULL,
        room_type ENUM('STANDARD','VIP','PREMIUM_VIP','PARTY') DEFAULT 'STANDARD',
        capacity INT DEFAULT 4,
        hourly_rate DECIMAL(18,2) DEFAULT 0,
        minimum_spend DECIMAL(18,2) DEFAULT 0,
        has_private_bathroom TINYINT(1) DEFAULT 0,
        has_waiter_button TINYINT(1) DEFAULT 1,
        equipment_status ENUM('ACTIVE','MAINTENANCE','INACTIVE') DEFAULT 'ACTIVE',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_karaoke_rooms_tenant (tenant_id),
        INDEX idx_karaoke_rooms_branch (branch_id),
        INDEX idx_karaoke_rooms_type (room_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS karaoke_reservations (
        reservation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        room_id BIGINT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        party_size INT DEFAULT 1,
        reservation_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME,
        actual_end_time TIME,
        hourly_rate DECIMAL(18,2) NOT NULL,
        room_charge DECIMAL(18,2) DEFAULT 0,
        minimum_spend DECIMAL(18,2) DEFAULT 0,
        total_amount DECIMAL(18,2) DEFAULT 0,
        payment_status ENUM('PENDING','PAID','PARTIAL','REFUNDED') DEFAULT 'PENDING',
        payment_method VARCHAR(20),
        status ENUM('PENDING','CONFIRMED','CHECKED_IN','COMPLETED','CANCELLED','NO_SHOW') DEFAULT 'PENDING',
        special_requests TEXT,
        checked_in_at TIMESTAMP NULL,
        checked_out_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_karaoke_res_tenant (tenant_id),
        INDEX idx_karaoke_res_room (room_id),
        INDEX idx_karaoke_res_date (reservation_date),
        INDEX idx_karaoke_res_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS karaoke_sessions (
        session_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        reservation_id BIGINT,
        room_id BIGINT NOT NULL,
        session_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME,
        total_duration_minutes INT DEFAULT 0,
        songs_sung INT DEFAULT 0,
        room_charge DECIMAL(18,2) DEFAULT 0,
        fnb_total DECIMAL(18,2) DEFAULT 0,
        total_bill DECIMAL(18,2) DEFAULT 0,
        status ENUM('ACTIVE','COMPLETED','CANCELLED') DEFAULT 'ACTIVE',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_karaoke_sessions_tenant (tenant_id),
        INDEX idx_karaoke_sessions_room (room_id),
        INDEX idx_karaoke_sessions_date (session_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // ==================== BEACH CLUB ====================
    "CREATE TABLE IF NOT EXISTS beach_club_cabanas (
        cabana_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        cabana_code VARCHAR(20) NOT NULL,
        cabana_name VARCHAR(100) NOT NULL,
        cabana_type ENUM('SUNBED','DAYBED','CABANA','VIP_CABANA','POOL_VILLA') DEFAULT 'SUNBED',
        capacity INT DEFAULT 2,
        daily_rate DECIMAL(18,2) DEFAULT 0,
        minimum_spend DECIMAL(18,2) DEFAULT 0,
        location VARCHAR(100),
        has_butler TINYINT(1) DEFAULT 0,
        has_private_pool TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_beach_cabanas_tenant (tenant_id),
        INDEX idx_beach_cabanas_type (cabana_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS beach_club_reservations (
        reservation_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        cabana_id BIGINT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        party_size INT DEFAULT 1,
        reservation_date DATE NOT NULL,
        arrival_time TIME,
        daily_rate DECIMAL(18,2) NOT NULL,
        minimum_spend DECIMAL(18,2) DEFAULT 0,
        total_amount DECIMAL(18,2) DEFAULT 0,
        payment_status ENUM('PENDING','PAID','PARTIAL','REFUNDED') DEFAULT 'PENDING',
        payment_method VARCHAR(20),
        status ENUM('PENDING','CONFIRMED','CHECKED_IN','COMPLETED','CANCELLED','NO_SHOW') DEFAULT 'PENDING',
        includes_pool_access TINYINT(1) DEFAULT 1,
        includes_towel TINYINT(1) DEFAULT 1,
        special_requests TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_beach_res_tenant (tenant_id),
        INDEX idx_beach_res_cabana (cabana_id),
        INDEX idx_beach_res_date (reservation_date),
        INDEX idx_beach_res_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS beach_club_events (
        event_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        event_name VARCHAR(200) NOT NULL,
        description TEXT,
        event_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME,
        theme VARCHAR(100),
        dj_name VARCHAR(100),
        music_genre VARCHAR(50),
        entrance_fee DECIMAL(18,2) DEFAULT 0,
        capacity INT DEFAULT 200,
        status ENUM('SCHEDULED','ONGOING','COMPLETED','CANCELLED') DEFAULT 'SCHEDULED',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_beach_events_tenant (tenant_id),
        INDEX idx_beach_events_date (event_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // ==================== LIVE MUSIC VENUE ====================
    "CREATE TABLE IF NOT EXISTS live_music_concerts (
        concert_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        concert_name VARCHAR(200) NOT NULL,
        artist_name VARCHAR(200) NOT NULL,
        genre VARCHAR(50),
        concert_date DATE NOT NULL,
        doors_open_time TIME NOT NULL,
        show_time TIME NOT NULL,
        end_time TIME,
        venue_capacity INT DEFAULT 500,
        status ENUM('SCHEDULED','ONGOING','COMPLETED','CANCELLED','SOLD_OUT') DEFAULT 'SCHEDULED',
        poster_url VARCHAR(255),
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_concerts_tenant (tenant_id),
        INDEX idx_concerts_date (concert_date),
        INDEX idx_concerts_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS live_music_seating_sections (
        section_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        section_name VARCHAR(50) NOT NULL,
        section_type ENUM('GA_STANDING','GA_SEATED','VIP','VIP_BOX','BALCONY','PIT') DEFAULT 'GA_STANDING',
        capacity INT NOT NULL,
        price DECIMAL(18,2) NOT NULL,
        is_numbered TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_seating_sections_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS live_music_tickets (
        ticket_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        tenant_id BIGINT NOT NULL,
        branch_id BIGINT,
        concert_id BIGINT NOT NULL,
        section_id BIGINT NOT NULL,
        seat_number VARCHAR(20),
        customer_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        ticket_type ENUM('GA','RESERVED_SEAT','VIP','VIP_BOX') DEFAULT 'GA',
        unit_price DECIMAL(18,2) NOT NULL,
        quantity INT DEFAULT 1,
        total_amount DECIMAL(18,2) NOT NULL,
        ticket_code VARCHAR(50) UNIQUE,
        payment_status ENUM('PENDING','PAID','REFUNDED') DEFAULT 'PENDING',
        payment_method VARCHAR(20),
        check_in_status TINYINT(1) DEFAULT 0,
        check_in_at TIMESTAMP NULL,
        sold_by BIGINT,
        sold_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_lm_tickets_tenant (tenant_id),
        INDEX idx_lm_tickets_concert (concert_id),
        INDEX idx_lm_tickets_section (section_id),
        INDEX idx_lm_tickets_status (payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tables as $sql) {
    $pdo->exec($sql);
    // Extract table name for logging
    if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $m)) {
        echo "  + Created/verified table: {$m[1]}\n";
    }
}

echo "\nMigration 042 complete. Tables: " . count($tables) . "\n";
