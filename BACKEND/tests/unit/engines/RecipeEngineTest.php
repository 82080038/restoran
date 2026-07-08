<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../core/Database.php';
require_once __DIR__ . '/../../../../core/Engines/RecipeEngine.php';

/**
 * RecipeEngine Test
 * 
 * Unit tests for the RecipeEngine class
 * 
 * @package EBP\Tests\Unit\Engines
 * @version 1.0.0
 */

class RecipeEngineTest extends TestCase
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
        $this->engine = new RecipeEngine($this->db);
    }

    private function createTestTables()
    {
        $this->db->exec("
            CREATE TABLE recipes (
                recipe_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                product_id INTEGER,
                name VARCHAR(255),
                yield_percentage DECIMAL(5,2),
                portions INTEGER
            )
        ");

        $this->db->exec("
            CREATE TABLE recipe_ingredients (
                recipe_ingredient_id INTEGER PRIMARY KEY AUTOINCREMENT,
                recipe_id INTEGER NOT NULL,
                ingredient_id INTEGER NOT NULL,
                quantity DECIMAL(10,4),
                unit VARCHAR(20)
            )
        ");

        $this->db->exec("
            CREATE TABLE inventory (
                inventory_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                name VARCHAR(255),
                unit_cost DECIMAL(18,2),
                unit VARCHAR(20)
            )
        ");

        $this->db->exec("
            CREATE TABLE stock_balances (
                stock_balance_id INTEGER PRIMARY KEY AUTOINCREMENT,
                branch_id INTEGER NOT NULL,
                inventory_id INTEGER NOT NULL,
                quantity DECIMAL(18,4),
                average_cost DECIMAL(18,2)
            )
        ");
    }

    private function insertTestData()
    {
        // Insert test recipe
        $this->db->exec("
            INSERT INTO recipes (tenant_id, product_id, name, yield_percentage, portions)
            VALUES (1, 1, 'Test Recipe', 100.00, 4)
        ");

        // Insert test ingredients
        $this->db->exec("
            INSERT INTO inventory (tenant_id, name, unit_cost, unit)
            VALUES (1, 'Flour', 2.50, 'kg')
        ");

        $this->db->exec("
            INSERT INTO inventory (tenant_id, name, unit_cost, unit)
            VALUES (1, 'Sugar', 3.00, 'kg')
        ");

        // Insert recipe ingredients
        $this->db->exec("
            INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit)
            VALUES (1, 1, 0.5, 'kg')
        ");

        $this->db->exec("
            INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit)
            VALUES (1, 2, 0.2, 'kg')
        ");

        // Insert stock balance
        $this->db->exec("
            INSERT INTO stock_balances (branch_id, inventory_id, quantity, average_cost)
            VALUES (1, 1, 10.0, 2.50)
        ");
    }

    public function testCalculateRecipeCost()
    {
        $result = $this->engine->execute([
            'action' => 'calculate_recipe_cost',
            'recipe_id' => 1,
            'tenant_id' => 1,
            'branch_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('cost_analysis', $result);
    }

    public function testOptimizeYield()
    {
        $result = $this->engine->execute([
            'action' => 'optimize_yield',
            'recipe_id' => 1,
            'tenant_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('optimization', $result);
    }

    public function testSuggestSubstitutes()
    {
        $result = $this->engine->execute([
            'action' => 'suggest_substitutes',
            'ingredient_id' => 1,
            'tenant_id' => 1
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('substitutes', $result);
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
        $this->assertEquals('Recipe Engine', $metadata['name']);
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
