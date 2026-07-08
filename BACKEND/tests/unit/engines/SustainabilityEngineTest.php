<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../core/Database.php';
require_once __DIR__ . '/../../../../core/Engines/SustainabilityEngine.php';

/**
 * SustainabilityEngine Test
 * 
 * Unit tests for the SustainabilityEngine class
 * 
 * @package EBP\Tests\Unit\Engines
 * @version 1.0.0
 */

class SustainabilityEngineTest extends TestCase
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
        $this->engine = new SustainabilityEngine($this->db);
    }

    private function createTestTables()
    {
        $this->db->exec("
            CREATE TABLE food_waste (
                waste_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                waste_type VARCHAR(50),
                quantity_kg DECIMAL(10,4),
                waste_date DATE
            )
        ");

        $this->db->exec("
            CREATE TABLE energy_consumption (
                consumption_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                energy_type VARCHAR(50),
                consumption_kwh DECIMAL(10,4),
                consumption_date DATE
            )
        ");

        $this->db->exec("
            CREATE TABLE transportation_logs (
                transport_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                transport_type VARCHAR(50),
                distance_km DECIMAL(10,2),
                transport_date DATE
            )
        ");
    }

    private function insertTestData()
    {
        // Insert test food waste
        $this->db->exec("
            INSERT INTO food_waste (tenant_id, branch_id, waste_type, quantity_kg, waste_date)
            VALUES (1, 1, 'PREP_WASTE', 5.5, '2026-07-01')
        ");

        // Insert test energy consumption
        $this->db->exec("
            INSERT INTO energy_consumption (tenant_id, branch_id, energy_type, consumption_kwh, consumption_date)
            VALUES (1, 1, 'ELECTRICITY', 150.0, '2026-07-01')
        ");

        // Insert test transportation
        $this->db->exec("
            INSERT INTO transportation_logs (tenant_id, branch_id, transport_type, distance_km, transport_date)
            VALUES (1, 1, 'TRUCK', 25.5, '2026-07-01')
        ");
    }

    public function testCalculateCarbonFootprint()
    {
        $result = $this->engine->execute([
            'action' => 'calculate_carbon_footprint',
            'tenant_id' => 1,
            'branch_id' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-07'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('carbon_footprint', $result);
    }

    public function testTrackFoodWaste()
    {
        $result = $this->engine->execute([
            'action' => 'track_food_waste',
            'tenant_id' => 1,
            'branch_id' => 1,
            'waste_type' => 'PREP_WASTE',
            'quantity_kg' => 2.5,
            'waste_date' => '2026-07-08'
        ]);

        $this->assertTrue($result['success']);
    }

    public function testGenerateSustainabilityReport()
    {
        $result = $this->engine->execute([
            'action' => 'generate_report',
            'tenant_id' => 1,
            'branch_id' => 1,
            'report_period_start' => '2026-07-01',
            'report_period_end' => '2026-07-07'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('report', $result);
    }

    public function testGetEcoFriendlySuggestions()
    {
        $result = $this->engine->execute([
            'action' => 'get_suggestions',
            'tenant_id' => 1,
            'branch_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('suggestions', $result);
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
        $this->assertEquals('Sustainability Engine', $metadata['name']);
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
