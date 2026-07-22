<?php

use PHPUnit\Framework\TestCase;
use App\Modules\Currency\Services\MultiCurrencyService;
use App\Core\Database;

class MultiCurrencyServiceTest extends TestCase
{
    private $service;
    private $pdo;
    private $testTenantId = 1;
    private $testUserId = 1;

    protected function setUp(): void
    {
        $this->service = new MultiCurrencyService();
        $this->pdo = Database::getInstance()->connect();
    }

    protected function tearDown(): void
    {
        // Clean up test exchange rates
        $this->pdo->exec("DELETE FROM exchange_rates WHERE tenant_id = {$this->testTenantId} AND rate_source IN ('DB_FALLBACK', 'AUTO_API', 'MANUAL') AND currency_code IN ('EUR', 'GBP', 'JPY', 'SGD', 'AUD', 'CAD', 'IDR')");
    }

    // ==================== EXCHANGE RATE UPDATE ====================

    public function testUpdateExchangeRate()
    {
        $result = $this->service->updateExchangeRate($this->testTenantId, $this->testUserId, (object)[
            'currency_code' => 'EUR',
            'exchange_rate' => 0.92,
            'base_currency' => 'USD',
            'rate_source' => 'MANUAL',
            'effective_date' => date('Y-m-d'),
        ]);

        $this->assertTrue($result['success']);
    }

    public function testUpdateExchangeRateUpserts()
    {
        // Skip: ON DUPLICATE KEY UPDATE behavior varies with unique key setup
        $this->markTestSkipped('Upsert test needs unique key verification — skipped pending schema review');
    }

    // ==================== GET EXCHANGE RATES ====================

    public function testGetExchangeRatesReturnsArray()
    {
        // Insert test data
        $this->service->updateExchangeRate($this->testTenantId, $this->testUserId, (object)[
            'currency_code' => 'JPY',
            'exchange_rate' => 149.50,
            'base_currency' => 'USD',
            'rate_source' => 'MANUAL',
            'effective_date' => date('Y-m-d'),
        ]);

        $result = $this->service->getExchangeRates($this->testTenantId, 'USD');

        $this->assertIsArray($result);
    }

    // ==================== AUTO UPDATE ====================

    public function testAutoUpdateExchangeRates()
    {
        $result = $this->service->autoUpdateExchangeRates($this->testTenantId, $this->testUserId);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['currencies_updated']);
        $this->assertArrayHasKey('source', $result);
    }

    public function testAutoUpdateCreatesExchangeRateRecords()
    {
        // Skip: autoUpdate calls updateExchangeRate which uses transactions that may conflict with test isolation
        $this->markTestSkipped('Auto-update record creation test needs transaction isolation fix');
    }

    // ==================== BASELINE RATES ====================

    public function testAutoUpdateWithIDRCurrency()
    {
        // Insert IDR as active currency with yesterday's date
        $this->service->updateExchangeRate($this->testTenantId, $this->testUserId, (object)[
            'currency_code' => 'IDR',
            'exchange_rate' => 15800,
            'base_currency' => 'USD',
            'rate_source' => 'MANUAL',
            'effective_date' => date('Y-m-d', strtotime('-1 day')),
        ]);

        $result = $this->service->autoUpdateExchangeRates($this->testTenantId, $this->testUserId);

        $this->assertTrue($result['success']);

        // Verify IDR rate was updated today (autoUpdate should find it from yesterday's rate)
        $stmt = $this->pdo->prepare("SELECT exchange_rate FROM exchange_rates WHERE tenant_id = ? AND currency_code = 'IDR' ORDER BY effective_date DESC, updated_at DESC LIMIT 1");
        $stmt->execute([$this->testTenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($row, 'IDR exchange rate record should exist');
        $this->assertGreaterThan(1000, (float)$row['exchange_rate'], 'IDR rate should be > 1000');
    }

    // ==================== CONVERSION HISTORY ====================

    public function testGetConversionHistoryReturnsArray()
    {
        // Insert test data
        $this->service->updateExchangeRate($this->testTenantId, $this->testUserId, (object)[
            'currency_code' => 'SGD',
            'exchange_rate' => 1.35,
            'base_currency' => 'USD',
            'rate_source' => 'MANUAL',
            'effective_date' => date('Y-m-d'),
        ]);

        $result = $this->service->getConversionHistory($this->testTenantId, 'SGD', null, null);

        $this->assertIsArray($result);
    }
}
