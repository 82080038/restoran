<?php

namespace App\Modules\Currency\Controllers;

use App\Modules\Currency\Services\MultiCurrencyService;
use App\Core\Response;

class MultiCurrencyController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new MultiCurrencyService();
    }

    /**
     * Update exchange rate
     * POST /api/v1/currency/exchange-rates
     */
    public function updateExchangeRate($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->updateExchangeRate($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get exchange rates
     * GET /api/v1/currency/exchange-rates
     */
    public function getExchangeRates($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $baseCurrency = $request->base_currency ?? 'USD';

        $rates = $this->service->getExchangeRates($tenantId, $baseCurrency);

        return Response::json([
            'success' => true,
            'data' => $rates
        ]);
    }

    /**
     * Set multi-currency product prices
     * POST /api/v1/currency/product-prices
     */
    public function setMultiCurrencyPrice($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->setMultiCurrencyPrice($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get product prices
     * GET /api/v1/currency/product-prices
     */
    public function getProductPrices($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $productId = $request->product_id;
        $currencyCode = $request->currency_code ?? null;

        $prices = $this->service->getProductPrices($tenantId, $productId, $currencyCode);

        return Response::json([
            'success' => true,
            'data' => $prices
        ]);
    }

    /**
     * Convert with historical rate
     * POST /api/v1/currency/convert-historical
     */
    public function convertWithHistoricalRate($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $result = $this->service->convertWithHistoricalRate(
            $tenantId,
            $request->amount,
            $request->from_currency,
            $request->to_currency,
            $request->date
        );

        return Response::json($result);
    }

    /**
     * Auto-update exchange rates
     * POST /api/v1/currency/auto-update-rates
     */
    public function autoUpdateExchangeRates($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->autoUpdateExchangeRates($tenantId, $userId);

        return Response::json($result);
    }

    /**
     * Get currency summary
     * GET /api/v1/currency/summary
     */
    public function getCurrencySummary($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $summary = $this->service->getCurrencySummary($tenantId);

        return Response::json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get conversion history
     * GET /api/v1/currency/conversion-history
     */
    public function getConversionHistory($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $currencyCode = $request->currency_code ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $history = $this->service->getConversionHistory($tenantId, $currencyCode, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $history
        ]);
    }
}
