<?php

// Currency Routes
$router->addRoute('POST', '/api/v1/settings/currencies', withAuth(function($request) use ($currencyController) {
    return $currencyController->addCurrency($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/settings/currencies/{id}/exchange-rate', withAuth(function($request) use ($currencyController) {
    return $currencyController->updateExchangeRate($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/settings/currencies', withAuth(function($request) use ($currencyController) {
    return $currencyController->getCurrencies($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/settings/currencies/convert', withAuth(function($request) use ($currencyController) {
    return $currencyController->convertCurrency($request);
}, $authMiddleware));

