<?php

use PHPUnit\Framework\TestCase;

/**
 * ReconciliationEngine Test
 * 
 * Unit tests for the ReconciliationEngine
 * 
 * @package EBP\Tests\Unit\Engines
 * @version 1.0.0
 * @date 2026-07-08
 */

class ReconciliationEngineTest extends TestCase
{
    private $pdo;
    private $engine;

    protected function setUp(): void
    {
        // Create test database connection
        $this->pdo = new PDO('sqlite::memory:');
        
        // Create test tables
        $this->createTestTables();
        
        // Insert test data
        $this->insertTestData();
        
        // Initialize engine
        $this->engine = new ReconciliationEngine($this->pdo);
    }

    private function createTestTables()
    {
        // Create orders table
        $this->pdo->exec("
            CREATE TABLE orders (
                order_id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                total_amount DECIMAL(18,2) NOT NULL,
                status VARCHAR(50) DEFAULT 'COMPLETED',
                payment_status VARCHAR(50) DEFAULT 'PAID',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create payments table
        $this->pdo->exec("
            CREATE TABLE payments (
                payment_id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                amount DECIMAL(18,2) NOT NULL,
                payment_method VARCHAR(50),
                payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(50) DEFAULT 'COMPLETED',
                source VARCHAR(50) DEFAULT 'POS'
            )
        ");

        // Create reconciliation_logs table
        $this->pdo->exec("
            CREATE TABLE reconciliation_logs (
                log_id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                status VARCHAR(50) DEFAULT 'PENDING',
                expected_total DECIMAL(18,2) NOT NULL,
                actual_total DECIMAL(18,2) NOT NULL,
                discrepancies_count INTEGER DEFAULT 0,
                discrepancies_json TEXT,
                reconciled_at TIMESTAMP NULL,
                reconciled_by VARCHAR(100) DEFAULT 'SYSTEM',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create reconciliation_alerts table
        $this->pdo->exec("
            CREATE TABLE reconciliation_alerts (
                alert_id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                tenant_id INTEGER NOT NULL,
                branch_id INTEGER NOT NULL,
                alert_type VARCHAR(50) DEFAULT 'WARNING',
                message VARCHAR(500) NOT NULL,
                discrepancies_json TEXT,
                status VARCHAR(50) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    private function insertTestData()
    {
        // Insert test order
        $this->pdo->exec("
            INSERT INTO orders (order_id, tenant_id, branch_id, total_amount)
            VALUES (1, 1, 1, 100.00)
        ");

        // Insert matching payment
        $this->pdo->exec("
            INSERT INTO payments (order_id, tenant_id, branch_id, amount, status)
            VALUES (1, 1, 1, 100.00, 'COMPLETED')
        ");
    }

    public function testReconcileOrderSuccess()
    {
        $result = $this->engine->reconcileOrder(1, 1, 1);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('RECONCILED', $result['status']);
        $this->assertEquals(100.00, $result['expected_total']);
        $this->assertEquals(100.00, $result['actual_total']);
        $this->assertEmpty($result['discrepancies']);
    }

    public function testReconcileOrderNotFound()
    {
        $result = $this->engine->reconcileOrder(999, 1, 1);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Order not found', $result['message']);
    }

    public function testReconcileOrderWithDiscrepancy()
    {
        // Insert order with different payment amount
        $this->pdo->exec("
            INSERT INTO orders (order_id, tenant_id, branch_id, total_amount)
            VALUES (2, 1, 1, 150.00)
        ");

        $this->pdo->exec("
            INSERT INTO payments (order_id, tenant_id, branch_id, amount, status)
            VALUES (2, 1, 1, 100.00, 'COMPLETED')
        ");

        $result = $this->engine->reconcileOrder(2, 1, 1);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('DISCREPANCY_HIGH', $result['status']);
        $this->assertEquals(150.00, $result['expected_total']);
        $this->assertEquals(100.00, $result['actual_total']);
        $this->assertNotEmpty($result['discrepancies']);
    }

    public function testReconcileBatch()
    {
        // Insert more test orders
        $this->pdo->exec("
            INSERT INTO orders (order_id, tenant_id, branch_id, total_amount)
            VALUES (3, 1, 1, 75.00)
        ");
        $this->pdo->exec("
            INSERT INTO payments (order_id, tenant_id, branch_id, amount, status)
            VALUES (3, 1, 1, 75.00, 'COMPLETED')
        ");

        $result = $this->engine->reconcileBatch([1, 3], 1, 1);
        
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertEquals(2, $result['summary']['total_orders']);
        $this->assertEquals(2, $result['summary']['reconciled']);
    }

    public function testDashboardData()
    {
        $result = $this->engine->getDashboardData(1, 1, '2026-01-01', '2026-12-31');
        
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('alerts', $result);
        $this->assertArrayHasKey('recent_discrepancies', $result);
    }

    public function testManualOverride()
    {
        // First reconcile with discrepancy
        $this->pdo->exec("
            INSERT INTO orders (order_id, tenant_id, branch_id, total_amount)
            VALUES (4, 1, 1, 200.00)
        ");
        $this->pdo->exec("
            INSERT INTO payments (order_id, tenant_id, branch_id, amount, status)
            VALUES (4, 1, 1, 150.00, 'COMPLETED')
        ");

        $this->engine->reconcileOrder(4, 1, 1);
        
        // Then override
        $result = $this->engine->manualOverride(4, 1, 1, 'Manual correction approved', 1);
        
        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        // Clean up
        $this->pdo = null;
        $this->engine = null;
    }
}
