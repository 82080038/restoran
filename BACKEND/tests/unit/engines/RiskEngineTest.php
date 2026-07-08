<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../core/Database.php';
require_once __DIR__ . '/../../../../core/Engines/RiskEngine.php';

/**
 * RiskEngine Test
 * 
 * Unit tests for the RiskEngine class
 * 
 * @package EBP\Tests\Unit\Engines
 * @version 1.0.0
 */

class RiskEngineTest extends TestCase
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
        $this->engine = new RiskEngine($this->db);
    }

    private function createTestTables()
    {
        $this->db->exec("
            CREATE TABLE risk_register (
                risk_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                risk_type VARCHAR(50),
                risk_category VARCHAR(50),
                description TEXT,
                risk_level VARCHAR(20),
                score INT,
                status VARCHAR(20)
            )
        ");

        $this->db->exec("
            CREATE TABLE risk_mitigation_plans (
                plan_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                risk_id INTEGER NOT NULL,
                plan_name VARCHAR(255),
                description TEXT,
                status VARCHAR(20),
                progress_percentage INT
            )
        ");

        $this->db->exec("
            CREATE TABLE equipment (
                equipment_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                equipment_name VARCHAR(255),
                equipment_type VARCHAR(50),
                status VARCHAR(20)
            )
        ");
    }

    private function insertTestData()
    {
        // Insert test risk
        $this->db->exec("
            INSERT INTO risk_register (tenant_id, branch_id, risk_type, risk_category, description, risk_level, score, status)
            VALUES (1, 1, 'OPERATIONAL', 'EQUIPMENT', 'Kitchen equipment failure', 'MEDIUM', 50, 'ACTIVE')
        ");

        // Insert test equipment
        $this->db->exec("
            INSERT INTO equipment (tenant_id, branch_id, equipment_name, equipment_type, status)
            VALUES (1, 1, 'Oven', 'KITCHEN', 'OPERATIONAL')
        ");
    }

    public function testAssessRisk()
    {
        $result = $this->engine->execute([
            'action' => 'assess_risk',
            'tenant_id' => 1,
            'branch_id' => 1,
            'risk_type' => 'OPERATIONAL',
            'risk_category' => 'EQUIPMENT',
            'description' => 'Test risk assessment'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('assessment', $result);
    }

    public function testCreateMitigationPlan()
    {
        $result = $this->engine->execute([
            'action' => 'create_mitigation_plan',
            'tenant_id' => 1,
            'branch_id' => 1,
            'risk_id' => 1,
            'plan_name' => 'Test Mitigation Plan',
            'description' => 'Test mitigation plan description'
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('plan', $result);
    }

    public function testMonitorRisks()
    {
        $result = $this->engine->execute([
            'action' => 'monitor_risks',
            'tenant_id' => 1,
            'branch_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('risks', $result);
    }

    public function testCalculateRiskScore()
    {
        $result = $this->engine->execute([
            'action' => 'calculate_risk_score',
            'tenant_id' => 1,
            'branch_id' => 1,
            'risk_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('score', $result);
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
        $this->assertEquals('Risk Engine', $metadata['name']);
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
