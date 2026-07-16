<?php

// Currency Service Routes
if (!class_exists('ExchangeRateService')) {
    require_once __DIR__ . '/../../core/CurrencyService.php';
}
$currencyService = new ExchangeRateService();

$router->addRoute('GET', '/api/v1/accounting/currencies', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $currencies = $currencyService->getCurrencies();
    Response::success($currencies, 'Currencies retrieved successfully');
});

$router->addRoute('POST', '/api/v1/accounting/exchange-rates', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $data = $request['body'] ?? [];
    $result = $currencyService->setExchangeRate($user['tenant_id'], $data['from_currency'], $data['to_currency'], $data['rate'], $data['effective_date'], $user['user_id']);
    if ($result['success']) {
        Response::success([], $result['message']);
    } else {
        Response::error($result['message']);
    }
});

$router->addRoute('GET', '/api/v1/accounting/exchange-rates', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $fromCurrency = $_GET['from_currency'] ?? null;
    $toCurrency = $_GET['to_currency'] ?? null;
    $rates = $currencyService->getExchangeRates($user['tenant_id'], $fromCurrency, $toCurrency);
    Response::success($rates, 'Exchange rates retrieved successfully');
});

$router->addRoute('GET', '/api/v1/accounting/exchange-rates/latest', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $rates = $currencyService->getLatestExchangeRates($user['tenant_id']);
    Response::success($rates, 'Latest exchange rates retrieved successfully');
});

