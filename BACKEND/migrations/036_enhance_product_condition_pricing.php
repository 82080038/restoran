<?php

/**
 * Migration 036: Enhance Product Condition Pricing
 * 
 * This migration adds specific enum values for product conditions
 * (REFRIGERATED, WITH_ICE) to the product_prices table for better clarity
 * in handling different product condition pricing scenarios.
 * 
 * @package EBP\Migrations
 * @version 1.0.0
 */

use PDO;

class Migration_036_Enhance_Product_Condition_Pricing
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
            // Check if product_prices table exists
            $tableExists = $this->db->query("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = 'ebp_restaurant_db' 
                AND table_name = 'product_prices'
            ")->fetch(PDO::FETCH_ASSOC)['count'] > 0;

            if (!$tableExists) {
                echo "Table product_prices does not exist. Skipping migration.\n";
                return;
            }

            // Check current price_type column definition
            $columnInfo = $this->db->query("
                SHOW COLUMNS FROM product_prices LIKE 'price_type'
            ")->fetch(PDO::FETCH_ASSOC);

            if (!$columnInfo) {
                echo "Column price_type does not exist in product_prices. Skipping migration.\n";
                return;
            }

            // Modify the price_type column to include new enum values
            $this->db->exec("
                ALTER TABLE product_prices 
                MODIFY COLUMN price_type ENUM(
                    'REGULAR', 
                    'REFRIGERATED', 
                    'WITH_ICE', 
                    'HOT', 
                    'ROOM_TEMPERATURE',
                    'FROZEN',
                    'TAKEAWAY',
                    'DINE_IN',
                    'DELIVERY',
                    'PROMOTIONAL',
                    'BULK',
                    'WHOLESALE'
                ) DEFAULT 'REGULAR' 
                COMMENT 'Product condition/service type pricing'
            ");

            echo "Migration 036: Successfully enhanced product condition pricing.\n";
            return true;

        } catch (Exception $e) {
            echo "Migration 036 Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function down()
    {
        try {
            // Revert to simpler enum values
            $this->db->exec("
                ALTER TABLE product_prices 
                MODIFY COLUMN price_type ENUM(
                    'REGULAR', 
                    'PROMOTIONAL', 
                    'BULK', 
                    'WHOLESALE'
                ) DEFAULT 'REGULAR'
            ");

            echo "Migration 036: Successfully reverted product condition pricing.\n";
            return true;

        } catch (Exception $e) {
            echo "Migration 036 Rollback Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run migration if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['argv'][0])) {
    $migration = new Migration_036_Enhance_Product_Condition_Pricing();
    
    if (isset($argv[1]) && $argv[1] === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }
}
