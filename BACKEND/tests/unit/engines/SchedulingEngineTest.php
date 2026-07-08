<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../core/Database.php';
require_once __DIR__ . '/../../../../core/Engines/SchedulingEngine.php';

/**
 * SchedulingEngine Test
 * 
 * Unit tests for the SchedulingEngine class
 * 
 * @package EBP\Tests\Unit\Engines
 * @version 1.0.0
 */

class SchedulingEngineTest extends TestCase
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
        $this->engine = new SchedulingEngine($this->db);
    }

    private function createTestTables()
    {
        $this->db->exec("
            CREATE TABLE employees (
                employee_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                first_name VARCHAR(100),
                hourly_rate DECIMAL(18,2)
            )
        ");

        $this->db->exec("
            CREATE TABLE schedules (
                schedule_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                employee_id INTEGER NOT NULL,
                shift_date DATE NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                status VARCHAR(20)
            )
        ");

        $this->db->exec("
            CREATE TABLE orders (
                order_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                order_date DATE,
                total_amount DECIMAL(18,2)
            )
        ");
    }

    private function insertTestData()
    {
        // Insert test employees
        $this->db->exec("
            INSERT INTO employees (tenant_id, branch_id, first_name, hourly_rate)
            VALUES (1, 1, 'John Doe', 15.00)
        ");

        $this->db->exec("
            INSERT INTO employees (tenant_id, branch_id, first_name, hourly_rate)
            VALUES (1, 1, 'Jane Smith', 18.00)
        ");

        // Insert test orders
        $this->db->exec("
            INSERT INTO orders (tenant_id, branch_id, order_date, total_amount)
            VALUES (1, 1, '2026-07-01', 1000.00)
        ");
    }

    public function testGenerateSchedule()
    {
        $result = $this->engine->execute([
            'action' => 'generate_schedule',
            'tenant_id' => 1,
            'branch_id' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-07'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('schedule', $result);
    }

    public function testOptimizeLaborCost()
    {
        $result = $this->engine->execute([
            'action' => 'optimize_labor_cost',
            'tenant_id' => 1,
            'branch_id' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-07'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('optimization', $result);
    }

    public function testCheckAvailability()
    {
        $result = $this->engine->execute([
            'action' => 'check_availability',
            'employee_id' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-07'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('availability', $result);
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
        $this->assertEquals('Scheduling Engine', $metadata['name']);
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
