<?php

namespace App\Modules\Currency\Services;

use App\Core\Database;
use App\Core\Audit;

class MultiCurrencyService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = Audit::getInstance();
    }

    /**
     * Update exchange rate
     */
    public function updateExchangeRate($tenantId, $userId, $data)
    {
        try {
            $pdo = $this->db->connect();
            $pdo->beginTransaction();

            $rateData = [
                'tenant_id' => $tenantId,
                'currency_code' => $data->currency_code,
                'exchange_rate' => $data->exchange_rate,
                'base_currency' => $data->base_currency ?? 'USD',
                'rate_source' => $data->rate_source ?? 'MANUAL',
                'effective_date' => $data->effective_date ?? date('Y-m-d'),
                'updated_by' => $userId
            ];

            $sql = "INSERT INTO exchange_rates (tenant_id, currency_code, exchange_rate, base_currency, rate_source, effective_date, updated_by, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    exchange_rate = VALUES(exchange_rate),
                    rate_source = VALUES(rate_source),
                    effective_date = VALUES(effective_date),
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $rateData['tenant_id'],
                $rateData['currency_code'],
                $rateData['exchange_rate'],
                $rateData['base_currency'],
                $rateData['rate_source'],
                $rateData['effective_date'],
                $rateData['updated_by']
            ]);

            $pdo->commit();

            // Log audit
            $this->audit->log($tenantId, $userId, 'currency', 'exchange_rate_update', null, 'exchange_rates', null, $rateData);

            return [
                'success' => true,
                'message' => 'Exchange rate updated'
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update exchange rate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get exchange rates
     */
    public function getExchangeRates($tenantId, $baseCurrency)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($baseCurrency) {
            $where .= " AND base_currency = ?";
            $params[] = $baseCurrency;
        }

        $sql = "SELECT er.*, 
                    (SELECT MAX(effective_date) FROM exchange_rates WHERE currency_code = er.currency_code) as latest_date
                FROM exchange_rates er
                {$where}
                ORDER BY er.effective_date DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Set product price in multiple currencies
     */
    public function setMultiCurrencyPrice($tenantId, $userId, $data)
    {
        try {
            $pdo = $this->db->connect();
            $pdo->beginTransaction();

            foreach ($data->prices as $price) {
                $sql = "INSERT INTO product_prices (tenant_id, product_id, currency_code, price, effective_date, created_by, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE 
                        price = VALUES(price),
                        effective_date = VALUES(effective_date),
                        created_by = VALUES(created_by),
                        created_at = NOW()";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $tenantId,
                    $data->product_id,
                    $price->currency_code,
                    $price->price,
                    $price->effective_date ?? date('Y-m-d'),
                    $userId
                ]);
            }

            $pdo->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'product_multi_currency_price', $data->product_id, 'UPDATE', json_encode($data));

            return [
                'success' => true,
                'message' => 'Multi-currency prices set'
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to set prices: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get product prices in all currencies
     */
    public function getProductPrices($tenantId, $productId, $currencyCode)
    {
        $params = [$tenantId, $productId];
        $where = "WHERE tenant_id = ? AND product_id = ?";
        
        if ($currencyCode) {
            $where .= " AND currency_code = ?";
            $params[] = $currencyCode;
        }

        $sql = "SELECT pp.*, 
                    c.currency_name,
                    c.currency_symbol,
                    c.exchange_rate
                FROM product_prices pp
                LEFT JOIN currencies c ON pp.currency_code = c.currency_code
                {$where}
                ORDER BY pp.effective_date DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Convert amount with historical rate
     */
    public function convertWithHistoricalRate($tenantId, $amount, $fromCurrency, $toCurrency, $date)
    {
        $sql = "SELECT exchange_rate FROM exchange_rates 
                WHERE tenant_id = ? AND currency_code = ? AND effective_date <= ?
                ORDER BY effective_date DESC LIMIT 1";
        
        $fromRate = $this->db->query($sql, [$tenantId, $fromCurrency, $date])->fetch();

        $sql = "SELECT exchange_rate FROM exchange_rates 
                WHERE tenant_id = ? AND currency_code = ? AND effective_date <= ?
                ORDER BY effective_date DESC LIMIT 1";
        
        $toRate = $this->db->query($sql, [$tenantId, $toCurrency, $date])->fetch();

        if (!$fromRate || !$toRate) {
            return ['success' => false, 'message' => 'Exchange rate not available for given date'];
        }

        $inBase = $amount / $fromRate['exchange_rate'];
        $converted = $inBase * $toRate['exchange_rate'];

        return [
            'success' => true,
            'amount' => $amount,
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'converted_amount' => round($converted, 2),
            'historical_date' => $date
        ];
    }

    /**
     * Get currency summary
     */
    public function getCurrencySummary($tenantId)
    {
        // Active currencies
        $currenciesSql = "SELECT COUNT(DISTINCT currency_code) as count FROM exchange_rates WHERE tenant_id = ?";
        $currencies = $this->db->query($currenciesSql, [$tenantId])->fetch();

        // Products with multi-currency pricing
        $productsSql = "SELECT COUNT(DISTINCT product_id) as count FROM product_prices WHERE tenant_id = ?";
        $products = $this->db->query($productsSql, [$tenantId])->fetch();

        // Last exchange rate update
        $lastUpdateSql = "SELECT MAX(updated_at) as last_update FROM exchange_rates WHERE tenant_id = ?";
        $lastUpdate = $this->db->query($lastUpdateSql, [$tenantId])->fetch();

        return [
            'active_currencies' => $currencies['count'] ?? 0,
            'products_with_multi_currency' => $products['count'] ?? 0,
            'last_exchange_rate_update' => $lastUpdate['last_update'] ?? null
        ];
    }

    /**
     * Auto-update exchange rates from external API or DB fallback
     * Attempts cURL to external API if configured, otherwise uses DB-stored baseline rates
     */
    public function autoUpdateExchangeRates($tenantId, $userId)
    {
        try {
            $pdo = $this->db->connect();

            // Get active currencies for this tenant
            $stmt = $pdo->prepare("
                SELECT DISTINCT currency_code FROM exchange_rates
                WHERE tenant_id = ? AND currency_code != 'USD'
                UNION
                SELECT DISTINCT a.currency_code FROM active_currencies a
                WHERE a.tenant_id = ? AND a.currency_code != 'USD' AND a.is_active = 1
            ");
            $stmt->execute([$tenantId, $tenantId]);
            $activeCurrencies = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            // If no active currencies, use default set
            if (empty($activeCurrencies)) {
                $activeCurrencies = ['EUR', 'GBP', 'JPY', 'SGD', 'AUD', 'CAD'];
            }

            // Check for external API configuration
            $apiUrl = getenv('EXCHANGE_RATE_API_URL') ?: null;
            $apiKey = getenv('EXCHANGE_RATE_API_KEY') ?: null;
            $useExternalApi = $apiUrl && function_exists('curl_init');

            $updated = 0;
            foreach ($activeCurrencies as $currency) {
                $rate = null;
                $source = 'DB_FALLBACK';

                if ($useExternalApi) {
                    // Call external API
                    $ch = curl_init($apiUrl . '?base=USD&symbols=' . $currency);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
                        CURLOPT_TIMEOUT => 10,
                    ]);
                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpCode === 200 && $response) {
                        $decoded = json_decode($response, true);
                        $rate = $decoded['rates'][$currency] ?? null;
                        $source = 'EXTERNAL_API';
                    }
                }

                // Fallback: get last known rate from DB
                if ($rate === null) {
                    $stmt2 = $pdo->prepare("
                        SELECT exchange_rate FROM exchange_rates
                        WHERE tenant_id = ? AND currency_code = ?
                        ORDER BY effective_date DESC, updated_at DESC LIMIT 1
                    ");
                    $stmt2->execute([$tenantId, $currency]);
                    $row = $stmt2->fetch(\PDO::FETCH_ASSOC);
                    $rate = $row ? (float)$row['exchange_rate'] : $this->getBaselineRate($currency);
                }

                if ($rate) {
                    $this->updateExchangeRate($tenantId, $userId, (object)[
                        'currency_code' => $currency,
                        'exchange_rate' => $rate,
                        'base_currency' => 'USD',
                        'rate_source' => $source,
                        'effective_date' => date('Y-m-d')
                    ]);
                    $updated++;
                }
            }

            return [
                'success' => true,
                'message' => 'Exchange rates auto-updated',
                'currencies_updated' => $updated,
                'source' => $useExternalApi ? 'EXTERNAL_API' : 'DB_FALLBACK'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to auto-update: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get baseline exchange rate (DB-stored fallback rates)
     */
    private function getBaselineRate($currency)
    {
        $rates = [
            'EUR' => 0.92,
            'GBP' => 0.79,
            'JPY' => 149.50,
            'SGD' => 1.35,
            'AUD' => 1.53,
            'CAD' => 1.36,
            'IDR' => 15800.00,
            'CNY' => 7.24,
            'KRW' => 1350.00,
            'MYR' => 4.68,
            'THB' => 35.80,
            'INR' => 83.25,
        ];

        return $rates[$currency] ?? null;
    }

    /**
     * Get currency conversion history
     */
    public function getConversionHistory($tenantId, $currencyCode, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($currencyCode) {
            $where .= " AND currency_code = ?";
            $params[] = $currencyCode;
        }
        
        if ($dateFrom) {
            $where .= " AND effective_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND effective_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT * FROM exchange_rates {$where} ORDER BY effective_date DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }
}
