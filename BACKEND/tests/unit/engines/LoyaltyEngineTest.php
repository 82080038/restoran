<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../core/Database.php';
require_once __DIR__ . '/../../../../core/Engines/LoyaltyEngine.php';

/**
 * LoyaltyEngine Test
 * 
 * Unit tests for the LoyaltyEngine class
 * 
 * @package EBP\Tests\Unit\Engines
 * @version 1.0.0
 */

class LoyaltyEngineTest extends TestCase
{
    private $db;
    private $engine;

    protected function setUp(): void
    {
        // Create in-memory SQLite database for testing
        $this->db = new PDO('sqlite::memory:');
        
        // Create test tables
        $this->createTestTables();
        
        // Insert test data
        $this->insertTestData();
        
        // Initialize engine
        $this->engine = new LoyaltyEngine($this->db);
    }

    private function createTestTables()
    {
        $this->db->exec("
            CREATE TABLE customers (
                customer_id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255)
            )
        ");

        $this->db->exec("
            CREATE TABLE orders (
                order_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                customer_id INTEGER,
                total_amount DECIMAL(18,2),
                status VARCHAR(20),
                created_at TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE loyalty_members (
                member_id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                tenant_id INTEGER NOT NULL,
                tier_level VARCHAR(20),
                points_balance INTEGER DEFAULT 0
            )
        ");

        $this->db->exec("
            CREATE TABLE loyalty_transactions (
                transaction_id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                tenant_id INTEGER NOT NULL,
                order_id INTEGER,
                points_earned INTEGER,
                transaction_type VARCHAR(20)
            )
        ");

        $this->db->exec("
            CREATE TABLE loyalty_rewards (
                reward_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                name VARCHAR(255),
                points_required INTEGER,
                quantity_available INTEGER
            )
        ");
    }

    private function insertTestData()
    {
        // Insert test customer
        $this->db->exec("
            INSERT INTO customers (name)
            VALUES ('Test Customer')
        ");

        // Insert test order
        $this->db->exec("
            INSERT INTO orders (tenant_id, customer_id, total_amount, status, created_at)
            VALUES (1, 1, 5000.00, 'COMPLETED', datetime('now'))
        ");

        // Insert loyalty member
        $this->db->exec("
            INSERT INTO loyalty_members (customer_id, tenant_id, tier_level, points_balance)
            VALUES (1, 1, 'BRONZE', 0)
        ");

        // Insert test reward
        $this->db->exec("
            INSERT INTO loyalty_rewards (tenant_id, name, points_required, quantity_available)
            VALUES (1, 'Free Dessert', 500, 10)
        ");
    }

    public function testCalculatePoints()
    {
        $result = $this->engine->execute([
            'action' => 'calculate_points',
            'customer_id' => 1,
            'order_id' => 1,
            'tenant_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('points', $result);
    }

    public function testCheckTier()
    {
        $result = $this->engine->execute([
            'action' => 'check_tier',
            'customer_id' => 1,
            'tenant_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('tier', $result);
    }

    public function testRedeemReward()
    {
        // First add points to customer
        $this->db->exec("
            UPDATE loyalty_members SET points_balance = 600 WHERE customer_id = 1
        ");

        $result = $this->engine->execute([
            'action' => 'redeem_reward',
            'customer_id' => 1,
            'reward_id' => 1,
            'tenant_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('redemption', $result);
    }

    public function testEngineInitialization()
    {
        $this->assertTrue($this->engine->validate());
    }

    public function testEngineMetadata()
    {
        $metadata = $this->engine->getMetadata();
        
        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('version', $metadata);
        $this->assertEquals('Loyalty Engine', $metadata['name']);
    }

    public function testEngineHealth()
    {
        $health = $this->engine->getHealth();
        
        $this->assertArrayHasKey('status', $health);
        $this->assertEquals('healthy', $health['status']);
    }

    protected function tearDown(): void
    {
        $this->db = null;
        $this->engine = null;
    }
}
