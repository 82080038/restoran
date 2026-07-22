<?php

use PHPUnit\Framework\TestCase;
use App\Modules\GapFeatures\Services\ExternalIntegrationService;
use App\Core\Database;

class ExternalIntegrationServiceTest extends TestCase
{
    private $service;
    private $pdo;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->service = new ExternalIntegrationService();
        $this->pdo = Database::getInstance()->connect();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->pdo->exec("DELETE FROM ewallet_payments WHERE tenant_id = {$this->testTenantId} AND provider_ref LIKE 'TEST-%'");
        $this->pdo->exec("DELETE FROM ticketing_sync_log WHERE tenant_id = {$this->testTenantId} AND platform = 'TEST'");
        $this->pdo->exec("DELETE FROM external_offline_queue WHERE tenant_id = {$this->testTenantId} AND device_id = 'TEST-DEVICE'");
    }

    // ==================== E-WALLET / QRIS ====================

    public function testGetEwalletProvidersReturnsArray()
    {
        $providers = $this->service->getEwalletProviders();
        $this->assertIsArray($providers);
        $this->assertNotEmpty($providers);
    }

    public function testGetEwalletProvidersHasExpectedFields()
    {
        $providers = $this->service->getEwalletProviders();
        $first = $providers[0];
        $this->assertArrayHasKey('code', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('status', $first);
    }

    public function testCreateQrisPaymentReturnsPaymentData()
    {
        $result = $this->service->createQrisPayment([
            'tenant_id' => $this->testTenantId,
            'branch_id' => 1,
            'order_id' => 1,
            'amount' => 50000,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment_id', $result);
        $this->assertArrayHasKey('qr_string', $result);
        $this->assertArrayHasKey('provider_ref', $result);
        $this->assertEquals('PENDING', $result['status']);
        $this->assertEquals(50000, $result['amount']);
    }

    public function testCreateQrisPaymentWithZeroAmount()
    {
        $result = $this->service->createQrisPayment([
            'tenant_id' => $this->testTenantId,
            'amount' => 0,
        ]);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['amount']);
        $this->assertEquals('PENDING', $result['status']);
    }

    public function testProcessEwalletPaymentReturnsSuccess()
    {
        $result = $this->service->processEwalletPayment([
            'tenant_id' => $this->testTenantId,
            'branch_id' => 1,
            'order_id' => 1,
            'provider' => 'GOPAY',
            'amount' => 25000,
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('SUCCESS', $result['status']);
        $this->assertArrayHasKey('payment_id', $result);
        $this->assertArrayHasKey('fee_amount', $result);
        $this->assertArrayHasKey('net_amount', $result);
        $this->assertGreaterThan(0, $result['fee_amount']);
        $this->assertGreaterThan(0, $result['net_amount']);
    }

    public function testProcessEwalletPaymentCalculatesFeeCorrectly()
    {
        $amount = 100000;
        $result = $this->service->processEwalletPayment([
            'tenant_id' => $this->testTenantId,
            'provider' => 'QRIS',
            'amount' => $amount,
        ]);

        $this->assertEquals($amount, $result['amount']);
        $this->assertEquals($amount - $result['fee_amount'], $result['net_amount']);
    }

    // ==================== TICKETING ====================

    public function testGetTicketingPlatformsReturnsArray()
    {
        $platforms = $this->service->getTicketingPlatforms();
        $this->assertIsArray($platforms);
        $this->assertNotEmpty($platforms);
    }

    public function testSyncTicketSalesCreatesLogEntry()
    {
        $result = $this->service->syncTicketSales([
            'tenant_id' => $this->testTenantId,
            'branch_id' => 1,
            'platform' => 'INTERNAL',
            'event_id' => 'EVT-TEST-001',
            'tickets_synced' => 42,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sync_id', $result);
        $this->assertEquals('SYNCED', $result['status']);
        $this->assertEquals(42, $result['tickets_synced']);
    }

    // ==================== OFFLINE MODE ====================

    public function testGetOfflineStatusReturnsArray()
    {
        $status = $this->service->getOfflineStatus();
        $this->assertIsArray($status);
        $this->assertArrayHasKey('mode', $status);
        $this->assertArrayHasKey('pending_transactions', $status);
        $this->assertArrayHasKey('failed_transactions', $status);
    }

    public function testSyncOfflineQueueWithEmptyQueue()
    {
        $result = $this->service->syncOfflineQueue([
            'tenant_id' => $this->testTenantId,
            'transactions' => [],
        ]);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['synced']);
        $this->assertEquals(0, $result['failed']);
    }

    public function testSyncOfflineQueueWithTransactions()
    {
        $result = $this->service->syncOfflineQueue([
            'tenant_id' => $this->testTenantId,
            'branch_id' => 1,
            'device_id' => 'TEST-DEVICE',
            'transactions' => [
                ['type' => 'ORDER', 'order_id' => 1, 'amount' => 50000],
                ['type' => 'PAYMENT', 'order_id' => 1, 'amount' => 50000],
                ['type' => 'INVENTORY', 'product_id' => 1, 'quantity' => -2],
            ],
        ]);

        $this->assertIsArray($result);
        $this->assertEquals(3, $result['synced']);
        $this->assertEquals(0, $result['failed']);
    }

    // ==================== LINE BUSTING ====================

    public function testGetLineBustStatsReturnsArray()
    {
        $stats = $this->service->getLineBustStats($this->testTenantId, null, date('Y-m-d'));
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('active_devices', $stats);
        $this->assertArrayHasKey('orders_taken', $stats);
        $this->assertArrayHasKey('avg_queue_time_seconds', $stats);
    }

    public function testGetLineBustStatsWithBranchId()
    {
        $stats = $this->service->getLineBustStats($this->testTenantId, 1, date('Y-m-d'));
        $this->assertIsArray($stats);
        $this->assertIsInt($stats['active_devices']);
        $this->assertIsInt($stats['orders_taken']);
    }
}
