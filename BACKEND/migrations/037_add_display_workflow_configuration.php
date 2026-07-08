<?php

/**
 * Migration 037: Add Display Workflow Configuration
 * 
 * This migration adds tenant-specific display workflow configurations
 * for different restaurant styles (Padang, buffet, display-based, etc.)
 * 
 * @package EBP\Migrations
 * @version 1.0.0
 */

use PDO;

class Migration_037_Add_Display_Workflow_Configuration
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function up()
    {
        try {
            // Create display_workflow_configurations table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS display_workflow_configurations (
                    config_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id BIGINT NOT NULL,
                    branch_id BIGINT,
                    workflow_type ENUM(
                        'STANDARD',
                        'PADANG_DISPLAY',
                        'BUFFET',
                        'CAFETERIA',
                        'FOOD_COURT',
                        'COUNTER_SERVICE',
                        'TABLE_SERVICE',
                        'SELF_SERVICE'
                    ) NOT NULL DEFAULT 'STANDARD',
                    config_name VARCHAR(100) NOT NULL,
                    display_mode ENUM(
                        'INDIVIDUAL_ITEMS',
                        'GROUPED_DISPLAY',
                        'COMBO_DISPLAY',
                        'CATEGORY_DISPLAY',
                        'PRICE_BASED_DISPLAY'
                    ) DEFAULT 'INDIVIDUAL_ITEMS',
                    show_out_of_stock TINYINT(1) DEFAULT 0,
                    show_low_stock TINYINT(1) DEFAULT 1,
                    auto_hide_out_of_stock TINYINT(1) DEFAULT 0,
                    display_categories JSON COMMENT 'Array of category IDs to display',
                    display_order JSON COMMENT 'Category display order',
                    price_display_mode ENUM(
                        'SHOW_ALL',
                        'HIDE_PRICES',
                        'SHOW_ON_REQUEST',
                        'SHOW_RANGE'
                    ) DEFAULT 'SHOW_ALL',
                    allow_customer_selection TINYINT(1) DEFAULT 1,
                    require_table_assignment TINYINT(1) DEFAULT 0,
                    kitchen_notification_mode ENUM(
                        'AUTO',
                        'MANUAL',
                        'BATCH'
                    ) DEFAULT 'AUTO',
                    serving_mode ENUM(
                        'SELF_SERVE',
                        'STAFF_SERVE',
                        'HYBRID'
                    ) DEFAULT 'STAFF_SERVE',
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    created_by BIGINT,
                    updated_by BIGINT,
                    INDEX idx_tenant (tenant_id),
                    INDEX idx_branch (branch_id),
                    INDEX idx_workflow_type (workflow_type),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Add display_workflow_config_id to branches table
            $this->db->exec("
                ALTER TABLE branches 
                ADD COLUMN IF NOT EXISTS display_workflow_config_id BIGINT NULL,
                ADD INDEX idx_display_workflow (display_workflow_config_id)
            ");

            // Insert default configurations for common workflow types
            $this->insertDefaultConfigurations();

            echo "Migration 037: Successfully added display workflow configuration.\n";
            return true;

        } catch (Exception $e) {
            echo "Migration 037 Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function insertDefaultConfigurations()
    {
        // Get all tenants
        $tenants = $this->db->query("SELECT tenant_id FROM tenants")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tenants as $tenant) {
            $tenantId = $tenant['tenant_id'];

            // Check if configurations already exist for this tenant
            $existing = $this->db->query("
                SELECT COUNT(*) as count 
                FROM display_workflow_configurations 
                WHERE tenant_id = {$tenantId}
            ")->fetch(PDO::FETCH_ASSOC)['count'];

            if ($existing > 0) {
                continue; // Skip if configurations already exist
            }

            // Insert Standard configuration
            $this->db->exec("
                INSERT INTO display_workflow_configurations 
                (tenant_id, workflow_type, config_name, display_mode, is_active) 
                VALUES 
                ({$tenantId}, 'STANDARD', 'Standard Display', 'INDIVIDUAL_ITEMS', 1)
            ");

            // Insert Padang Display configuration
            $this->db->exec("
                INSERT INTO display_workflow_configurations 
                (tenant_id, workflow_type, config_name, display_mode, serving_mode, is_active) 
                VALUES 
                ({$tenantId}, 'PADANG_DISPLAY', 'Padang Style Display', 'GROUPED_DISPLAY', 'SELF_SERVE', 0)
            ");

            // Insert Buffet configuration
            $this->db->exec("
                INSERT INTO display_workflow_configurations 
                (tenant_id, workflow_type, config_name, display_mode, serving_mode, is_active) 
                VALUES 
                ({$tenantId}, 'BUFFET', 'Buffet Display', 'CATEGORY_DISPLAY', 'SELF_SERVE', 0)
            ");
        }
    }

    public function down()
    {
        try {
            // Remove foreign key from branches
            $this->db->exec("
                ALTER TABLE branches 
                DROP FOREIGN KEY IF EXISTS fk_branch_display_workflow
            ");

            // Remove column from branches
            $this->db->exec("
                ALTER TABLE branches 
                DROP COLUMN IF EXISTS display_workflow_config_id
            ");

            // Drop display_workflow_configurations table
            $this->db->exec("DROP TABLE IF EXISTS display_workflow_configurations");

            echo "Migration 037: Successfully reverted display workflow configuration.\n";
            return true;

        } catch (Exception $e) {
            echo "Migration 037 Rollback Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run migration if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['argv'][0])) {
    $migration = new Migration_037_Add_Display_Workflow_Configuration();
    
    if (isset($argv[1]) && $argv[1] === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }
}
