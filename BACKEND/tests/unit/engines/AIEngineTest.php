<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../core/Database.php';
require_once __DIR__ . '/../../../../core/Engines/AIEngine.php';

/**
 * AIEngine Test
 * 
 * Unit tests for the AIEngine class
 * 
 * @package EBP\Tests\Unit\Engines
 * @version 1.0.0
 */

class AIEngineTest extends TestCase
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
        $this->engine = new AIEngine($this->db);
    }

    private function createTestTables()
    {
        $this->db->exec("
            CREATE TABLE orders (
                order_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                order_date DATE,
                total_amount DECIMAL(18,2)
            )
        ");

        $this->db->exec("
            CREATE TABLE order_items (
                order_item_id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INT,
                unit_price DECIMAL(18,2)
            )
        ");

        $this->db->exec("
            CREATE TABLE products (
                product_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                name VARCHAR(255),
                category_id INTEGER
            )
        ");
    }

    private function insertTestData()
    {
        // Insert test products
        $this->db->exec("
            INSERT INTO products (tenant_id, name, category_id)
            VALUES (1, 'Nasi Goreng', 1)
        ");

        $this->db->exec("
            INSERT INTO products (tenant_id, name, category_id)
            VALUES (1, 'Mie Ayam', 1)
        ");

        // Insert test orders
        $this->db->exec("
            INSERT INTO orders (tenant_id, branch_id, order_date, total_amount)
            VALUES (1, 1, '2026-07-01', 25000.00)
        ");

        $this->db->exec("
            INSERT INTO orders (tenant_id, branch_id, order_date, total_amount)
            VALUES (1, 1, '2026-07-02', 30000.00)
        ");

        // Insert test order items
        $this->db->exec("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price)
            VALUES (1, 1, 2, 15000.00)
        ");
    }

    public function testDemandForecasting()
    {
        $result = $this->engine->execute([
            'action' => 'demand_forecast',
            'tenant_id' => 1,
            'branch_id' => 1,
            'product_id' => 1,
            'forecast_days' => 7
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('forecast', $result);
    }

    public function testGetRecommendations()
    {
        $result = $this->engine->execute([
            'action' => 'get_recommendations',
            'tenant_id' => 1,
            'branch_id' => 1,
            'customer_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('recommendations', $result);
    }

    public function testDetectAnomalies()
    {
        $result = $this->engine->execute([
            'action' => 'detect_anomalies',
            'tenant_id' => 1,
            'branch_id' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-07'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('anomalies', $result);
    }

    public function testOptimizePricing()
    {
        $result = $this->engine->execute([
            'action' => 'optimize_pricing',
            'tenant_id' => 1,
            'branch_id' => 1,
            'product_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('pricing', $result);
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
        $this->assertEquals('AI Engine', $metadata['name']);
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
