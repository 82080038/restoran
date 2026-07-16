<?php

namespace App\Core;


use PDO;
/**
 * Currency Service
 * 
 * Handles multi-currency support including conversion rates,
 * currency formatting, and tenant-specific currency configurations.
 * 
 * @package EBP\App\Core\Services
 * @version 1.0.0
 */

class CurrencyService
{
    private $db;
    
    // Default exchange rates (base: IDR)
    private $defaultRates = [
        'IDR' => 1.0,
        'USD' => 0.000065,
        'EUR' => 0.000059,
        'SGD' => 0.000087,
        'MYR' => 0.00030,
        'THB' => 0.0022,
        'JPY' => 0.0097,
        'CNY' => 0.00046,
        'AUD' => 0.000098,
        'GBP' => 0.000051
    ];

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Convert amount from one currency to another
     */
    public function convert($amount, $fromCurrency, $toCurrency, $tenantId = null)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rate = $this->getExchangeRate($fromCurrency, $toCurrency, $tenantId);
        
        return $amount * $rate;
    }

    /**
     * Get exchange rate between two currencies
     */
    public function getExchangeRate($fromCurrency, $toCurrency, $tenantId = null)
    {
        // Try to get tenant-specific rate
        if ($tenantId) {
            $sql = "SELECT rate FROM currency_rates 
                    WHERE tenant_id = ? 
                    AND from_currency = ? 
                    AND to_currency = ? 
                    AND is_active = 1 
                    AND valid_from <= CURDATE() 
                    AND (valid_until IS NULL OR valid_until >= CURDATE())
                    ORDER BY updated_at DESC 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $fromCurrency, $toCurrency]);
            $rate = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rate) {
                return $rate['rate'];
            }
        }

        // Use default rates
        $fromRate = $this->defaultRates[$fromCurrency] ?? 1.0;
        $toRate = $this->defaultRates[$toCurrency] ?? 1.0;
        
        return $toRate / $fromRate;
    }

    /**
     * Format amount for display in specific currency
     */
    public function format($amount, $currency = 'IDR', $locale = 'id_ID')
    {
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'SGD' => 'S$',
            'MYR' => 'RM',
            'THB' => '฿',
            'JPY' => '¥',
            'CNY' => '¥',
            'AUD' => 'A$',
            'GBP' => '£'
        ];

        $symbol = $symbols[$currency] ?? $currency;
        
        // Format based on currency
        if (in_array($currency, ['IDR', 'JPY'])) {
            // No decimal places for these currencies
            return $symbol . number_format($amount, 0, ',', '.');
        } else {
            // 2 decimal places for others
            return $symbol . number_format($amount, 2, '.', ',');
        }
    }

    /**
     * Get tenant's default currency
     */
    public function getTenantCurrency($tenantId)
    {
        $sql = "SELECT default_currency FROM tenants WHERE tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['default_currency'] ?? 'IDR';
    }

    /**
     * Set tenant's default currency
     */
    public function setTenantCurrency($tenantId, $currency)
    {
        $sql = "UPDATE tenants SET default_currency = ? WHERE tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$currency, $tenantId]);
        
        return [
            'success' => true,
            'message' => 'Tenant currency updated successfully'
        ];
    }

    /**
     * Update exchange rate
     */
    public function updateExchangeRate($tenantId, $fromCurrency, $toCurrency, $rate, $validUntil = null)
    {
        $sql = "INSERT INTO currency_rates 
                (tenant_id, from_currency, to_currency, rate, valid_from, valid_until, is_active) 
                VALUES (?, ?, ?, ?, CURDATE(), ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $fromCurrency, $toCurrency, $rate, $validUntil]);
        
        return [
            'success' => true,
            'message' => 'Exchange rate updated successfully'
        ];
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies()
    {
        return [
            'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'locale' => 'id_ID'],
            'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'locale' => 'en_US'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€', 'locale' => 'de_DE'],
            'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'locale' => 'en_SG'],
            'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'locale' => 'ms_MY'],
            'THB' => ['name' => 'Thai Baht', 'symbol' => '฿', 'locale' => 'th_TH'],
            'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'locale' => 'ja_JP'],
            'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'locale' => 'zh_CN'],
            'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'locale' => 'en_AU'],
            'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'locale' => 'en_GB']
        ];
    }

    /**
     * Get exchange rates for a tenant
     */
    public function getExchangeRates($tenantId)
    {
        $sql = "SELECT * FROM currency_rates 
                WHERE tenant_id = ? 
                AND is_active = 1 
                AND valid_from <= CURDATE() 
                AND (valid_until IS NULL OR valid_until >= CURDATE())
                ORDER BY from_currency, to_currency";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $rates
        ];
    }

    /**
     * Convert order to tenant currency
     */
    public function convertOrderToTenantCurrency($order, $tenantId)
    {
        $tenantCurrency = $this->getTenantCurrency($tenantId);
        
        if ($order['currency'] === $tenantCurrency) {
            return $order;
        }

        $convertedAmount = $this->convert(
            $order['total_amount'],
            $order['currency'],
            $tenantCurrency,
            $tenantId
        );

        $order['original_amount'] = $order['total_amount'];
        $order['original_currency'] = $order['currency'];
        $order['total_amount'] = $convertedAmount;
        $order['currency'] = $tenantCurrency;
        
        return $order;
    }

    /**
     * Get currency conversion summary
     */
    public function getConversionSummary($amount, $fromCurrency, $tenantId)
    {
        $tenantCurrency = $this->getTenantCurrency($tenantId);
        $supportedCurrencies = $this->getSupportedCurrencies();
        
        $summary = [
            'original' => [
                'amount' => $amount,
                'currency' => $fromCurrency,
                'formatted' => $this->format($amount, $fromCurrency)
            ],
            'tenant_currency' => $tenantCurrency,
            'conversions' => []
        ];

        foreach ($supportedCurrencies as $code => $info) {
            if ($code === $fromCurrency) {
                continue;
            }

            $convertedAmount = $this->convert($amount, $fromCurrency, $code, $tenantId);
            $summary['conversions'][$code] = [
                'amount' => $convertedAmount,
                'formatted' => $this->format($convertedAmount, $code),
                'symbol' => $info['symbol'],
                'name' => $info['name']
            ];
        }

        return $summary;
    }
}
