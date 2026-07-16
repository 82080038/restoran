<?php

// Currency Routes
$router->addRoute('POST', '/api/v1/settings/currencies', function($request) use ($currencyController) {
    return $currencyController->addCurrency($request);
});
$router->addRoute('PUT', '/api/v1/settings/currencies/{id}/exchange-rate', function($request) use ($currencyController) {
    return $currencyController->updateExchangeRate($request);
});
$router->addRoute('GET', '/api/v1/settings/currencies', function($request) use ($currencyController) {
    return $currencyController->getCurrencies($request);
});
$router->addRoute('GET', '/api/v1/settings/currencies/convert', function($request) use ($currencyController) {
    return $currencyController->convertCurrency($request);
});

