<?php

if (!class_exists('CurrencyService')) {
    require_once __DIR__ . '/../Services/CurrencyService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class CurrencyController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new CurrencyService();
    }

    public function addCurrency($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->addCurrency($data);

        if ($result['success']) {
            Response::success($result['message'], ['currency_id' => $result['currency_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateExchangeRate($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $currencyId = $request['params']['id'] ?? null;
        $exchangeRate = $request['body']['exchange_rate'] ?? null;

        if (!$currencyId || !$exchangeRate) {
            Response::error('Currency ID and exchange rate are required');
            return;
        }

        $result = $this->service->updateExchangeRate($currencyId, $exchangeRate);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCurrencies($request)
    {
        $result = $this->service->getCurrencies();

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function convertCurrency($request)
    {
        $amount = $request['params']['amount'] ?? null;
        $fromCurrency = $request['params']['from'] ?? null;
        $toCurrency = $request['params']['to'] ?? null;

        if (!$amount || !$fromCurrency || !$toCurrency) {
            Response::error('Amount, from currency, and to currency are required');
            return;
        }

        $result = $this->service->convertCurrency($amount, $fromCurrency, $toCurrency);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
