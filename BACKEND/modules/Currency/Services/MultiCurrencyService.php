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
            $this->db->beginTransaction();

            $rateData = [
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
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $rateData['tenant_id'],
                $rateData['currency_code'],
                $rateData['exchange_rate'],
                $rateData['base_currency'],
                $rateData['rate_source'],
                $rateData['effective_date'],
                $rateData['updated_by']
            ]);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'exchange_rate', $data->currency_code, 'UPDATE', json_encode($rateData));

            return [
                'success' => true,
                'message' => 'Exchange rate updated'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
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
            $this->db->beginTransaction();

            foreach ($data->prices as $price) {
                $sql = "INSERT INTO product_prices (tenant_id, product_id, currency_code, price, effective_date, created_by, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE 
                        price = VALUES(price),
                        effective_date = VALUES(effective_date),
                        created_by = VALUES(created_by),
                        created_at = NOW()";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $tenantId,
                    $data->product_id,
                    $price->currency_code,
                    $price->price,
                    $price->effective_date ?? date('Y-m-d'),
                    $userId
                ]);
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'product_multi_currency_price', $data->product_id, 'UPDATE', json_encode($data));

            return [
                'success' => true,
                'message' => 'Multi-currency prices set'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
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
     * Auto-update exchange rates from external API (placeholder)
     */
    public function autoUpdateExchangeRates($tenantId, $userId)
    {
        try {
            // In production, integrate with external API like Fixer.io, Open Exchange Rates, XE.com, or Central bank APIs
            
            // Placeholder: simulate rate update
            $currencies = ['EUR', 'GBP', 'JPY', 'SGD', 'AUD', 'CAD'];
            
            foreach ($currencies as $currency) {
                // Simulate API call
                $mockRate = $this->getMockExchangeRate($currency);
                
                if ($mockRate) {
                    $this->updateExchangeRate($tenantId, $userId, (object)[
                        'currency_code' => $currency,
                        'exchange_rate' => $mockRate,
                        'base_currency' => 'USD',
                        'rate_source' => 'AUTO_API',
                        'effective_date' => date('Y-m-d')
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Exchange rates auto-updated',
                'currencies_updated' => count($currencies)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to auto-update: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get mock exchange rate (placeholder for external API)
     */
    private function getMockExchangeRate($currency)
    {
        $rates = [
            'EUR' => 0.92,
            'GBP' => 0.79,
            'JPY' => 149.50,
            'SGD' => 1.35,
            'AUD' => 1.53,
            'CAD' => 1.36
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
