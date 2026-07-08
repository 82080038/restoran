<?php

/**
 * Migration 038: Add Complimentary Flag
 * 
 * This migration adds a complimentary flag to the customer_pricing table
 * for better tracking and reporting of complimentary items.
 * 
 * @package EBP\Migrations
 * @version 1.0.0
 */

use PDO;

class Migration_038_Add_Complimentary_Flag
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
            // Check if customer_pricing table exists
            $tableExists = $this->db->query("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = 'ebp_restaurant_db' 
                AND table_name = 'customer_pricing'
            ")->fetch(PDO::FETCH_ASSOC)['count'] > 0;

            if (!$tableExists) {
                // Create the customer_pricing table with complimentary flag
                $this->db->exec("
                    CREATE TABLE customer_pricing (
                        pricing_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                        tenant_id BIGINT NOT NULL,
                        branch_id BIGINT,
                        customer_id BIGINT NOT NULL,
                        product_id BIGINT NOT NULL,
                        special_price DECIMAL(18,2) NOT NULL,
                        discount_percentage DECIMAL(5,2) DEFAULT 0,
                        is_complimentary TINYINT(1) DEFAULT 0 COMMENT 'Flag for complimentary items (free for customer)',
                        complimentary_reason VARCHAR(255) NULL COMMENT 'Reason for complimentary item (birthday, VIP, etc.)',
                        complimentary_code VARCHAR(50) NULL COMMENT 'Code for complimentary tracking and reporting',
                        valid_from DATE NULL,
                        valid_until DATE NULL,
                        is_active TINYINT(1) DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        created_by BIGINT,
                        updated_by BIGINT,
                        INDEX idx_tenant (tenant_id),
                        INDEX idx_branch (branch_id),
                        INDEX idx_customer (customer_id),
                        INDEX idx_product (product_id),
                        INDEX idx_complimentary (is_complimentary),
                        INDEX idx_validity (valid_from, valid_until),
                        INDEX idx_is_active (is_active)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");

                echo "Migration 038: Successfully created customer_pricing table with complimentary flag.\n";
                return true;
            }

            // Check if complimentary column already exists
            $columnExists = $this->db->query("
                SELECT COUNT(*) as count 
                FROM information_schema.columns 
                WHERE table_schema = 'ebp_restaurant_db' 
                AND table_name = 'customer_pricing' 
                AND column_name = 'is_complimentary'
            ")->fetch(PDO::FETCH_ASSOC)['count'] > 0;

            if ($columnExists) {
                echo "Column is_complimentary already exists in customer_pricing. Skipping migration.\n";
                return;
            }

            // Add complimentary flag column
            $this->db->exec("
                ALTER TABLE customer_pricing 
                ADD COLUMN is_complimentary TINYINT(1) DEFAULT 0 
                COMMENT 'Flag for complimentary items (free for customer)' 
                AFTER discount_percentage
            ");

            // Add complimentary_reason column
            $this->db->exec("
                ALTER TABLE customer_pricing 
                ADD COLUMN complimentary_reason VARCHAR(255) NULL 
                COMMENT 'Reason for complimentary item (birthday, VIP, etc.)' 
                AFTER is_complimentary
            ");

            // Add complimentary_code column for tracking
            $this->db->exec("
                ALTER TABLE customer_pricing 
                ADD COLUMN complimentary_code VARCHAR(50) NULL 
                COMMENT 'Code for complimentary tracking and reporting' 
                AFTER complimentary_reason
            ");

            // Add index for complimentary queries
            $this->db->exec("
                CREATE INDEX idx_complimentary 
                ON customer_pricing(is_complimentary)
            ");

            echo "Migration 038: Successfully added complimentary flag to customer_pricing.\n";
            return true;

        } catch (Exception $e) {
            echo "Migration 038 Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function down()
    {
        try {
            // Drop index
            $this->db->exec("DROP INDEX IF EXISTS idx_complimentary ON customer_pricing");

            // Drop columns
            $this->db->exec("ALTER TABLE customer_pricing DROP COLUMN IF EXISTS complimentary_code");
            $this->db->exec("ALTER TABLE customer_pricing DROP COLUMN IF EXISTS complimentary_reason");
            $this->db->exec("ALTER TABLE customer_pricing DROP COLUMN IF EXISTS is_complimentary");

            echo "Migration 038: Successfully reverted complimentary flag.\n";
            return true;

        } catch (Exception $e) {
            echo "Migration 038 Rollback Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run migration if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['argv'][0])) {
    $migration = new Migration_038_Add_Complimentary_Flag();
    
    if (isset($argv[1]) && $argv[1] === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }
}
