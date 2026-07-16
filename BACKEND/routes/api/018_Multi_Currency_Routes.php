<?php

// Multi-Currency Routes
$router->addRoute('POST', '/api/v1/currency/exchange-rates', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->updateExchangeRate($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/currency/exchange-rates', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->getExchangeRates($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/currency/product-prices', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->setMultiCurrencyPrice($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/currency/product-prices', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->getProductPrices($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/currency/convert-historical', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->convertWithHistoricalRate($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/currency/auto-update-rates', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->autoUpdateExchangeRates($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/currency/summary', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->getCurrencySummary($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/currency/conversion-history', withAuth(
    function($request) use ($multiCurrencyController) {
        return $multiCurrencyController->getConversionHistory($request);
    },
    $authMiddleware
));

