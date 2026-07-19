<?php

/**
 * Migration 050: Create BEO & Event Proposal tables (Catering)
 *
 * Tables:
 * - event_proposals (reusable templates, quotes for catering events)
 * - proposal_menu_items (menu items attached to a proposal)
 * - proposal_addons (add-ons: equipment, staff, extras)
 * - beos (Banquet Event Orders - auto-generated from confirmed proposals)
 * - beo_items (prep lists, packing lists, timelines)
 */

return [
    'up' => function($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS event_proposals (
                proposal_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                proposal_number VARCHAR(50) NOT NULL,
                client_name VARCHAR(255) NOT NULL,
                client_company VARCHAR(255),
                client_phone VARCHAR(50),
                client_email VARCHAR(255),
                event_name VARCHAR(255),
                event_type VARCHAR(100),
                event_date DATE,
                event_end_date DATE,
                event_venue VARCHAR(255),
                guest_count INT NOT NULL DEFAULT 0,
                service_style ENUM('BUFFET','PLATED','FAMILY_STYLE','COCKTAIL','BOXED','DROP_OFF') DEFAULT 'BUFFET',
                menu_package VARCHAR(100),
                per_head_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
                discount_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                tax_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
                deposit_required DECIMAL(18,2) NOT NULL DEFAULT 0,
                deposit_paid DECIMAL(18,2) NOT NULL DEFAULT 0,
                balance_due DECIMAL(18,2) NOT NULL DEFAULT 0,
                deposit_due_date DATE,
                balance_due_date DATE,
                status ENUM('DRAFT','SENT','VIEWED','ACCEPTED','REJECTED','EXPIRED','CONVERTED') DEFAULT 'DRAFT',
                valid_until DATE,
                notes TEXT,
                internal_notes TEXT,
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_proposal_number (proposal_number),
                INDEX idx_status (status),
                INDEX idx_event_date (event_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS proposal_menu_items (
                item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                proposal_id BIGINT NOT NULL,
                product_id BIGINT NULL,
                item_name VARCHAR(255) NOT NULL,
                item_description TEXT,
                course_type VARCHAR(50),
                quantity_per_head DECIMAL(10,2) NOT NULL DEFAULT 1,
                unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                dietary_tags VARCHAR(255),
                allergen_info VARCHAR(500),
                sort_order INT DEFAULT 0,
                INDEX idx_proposal (proposal_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS proposal_addons (
                addon_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                proposal_id BIGINT NOT NULL,
                addon_type ENUM('EQUIPMENT','STAFF','DECOR','TRANSPORT','SERVICE','OTHER') NOT NULL,
                description VARCHAR(255) NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                total_price DECIMAL(18,2) NOT NULL DEFAULT 0,
                INDEX idx_proposal (proposal_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS beos (
                beo_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                tenant_id BIGINT NOT NULL,
                branch_id BIGINT NOT NULL,
                proposal_id BIGINT NULL,
                beo_number VARCHAR(50) NOT NULL,
                event_name VARCHAR(255) NOT NULL,
                event_date DATE NOT NULL,
                client_name VARCHAR(255) NOT NULL,
                guest_count INT NOT NULL DEFAULT 0,
                service_style VARCHAR(50),
                venue VARCHAR(255),
                contact_person VARCHAR(255),
                contact_phone VARCHAR(50),
                timeline JSON,
                staff_assignments JSON,
                equipment_list JSON,
                special_instructions TEXT,
                allergen_alerts TEXT,
                status ENUM('DRAFT','CONFIRMED','IN_PROGRESS','COMPLETED','CANCELLED') DEFAULT 'DRAFT',
                created_by BIGINT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_tenant (tenant_id),
                INDEX idx_branch (branch_id),
                INDEX idx_proposal (proposal_id),
                INDEX idx_beo_number (beo_number),
                INDEX idx_event_date (event_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS beo_items (
                item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                beo_id BIGINT NOT NULL,
                item_type ENUM('PREP','PACKING','MENU','EQUIPMENT','TIMELINE','STAFF') NOT NULL,
                description VARCHAR(255) NOT NULL,
                quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
                unit VARCHAR(20),
                assigned_to VARCHAR(255),
                completed TINYINT(1) DEFAULT 0,
                completed_at TIMESTAMP NULL,
                notes VARCHAR(500),
                sort_order INT DEFAULT 0,
                INDEX idx_beo (beo_id),
                INDEX idx_type (item_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS beo_items");
        $pdo->exec("DROP TABLE IF EXISTS beos");
        $pdo->exec("DROP TABLE IF EXISTS proposal_addons");
        $pdo->exec("DROP TABLE IF EXISTS proposal_menu_items");
        $pdo->exec("DROP TABLE IF EXISTS event_proposals");
    }
];
