<?php

// Tax Calculation Routes
$router->addRoute('POST', '/api/v1/accounting/tax-rates', function($request) use ($taxCalculationController) {
    return $taxCalculationController->saveTaxRate($request);
});
$router->addRoute('GET', '/api/v1/accounting/tax-rates', function($request) use ($taxCalculationController) {
    return $taxCalculationController->getTaxRate($request);
});
$router->addRoute('POST', '/api/v1/accounting/orders/{id}/tax', function($request) use ($taxCalculationController) {
    return $taxCalculationController->calculateOrderTax($request);
});
$router->addRoute('GET', '/api/v1/accounting/tax/monthly', function($request) use ($taxCalculationController) {
    return $taxCalculationController->calculateMonthlyTax($request);
});
$router->addRoute('GET', '/api/v1/accounting/tax/report', function($request) use ($taxCalculationController) {
    return $taxCalculationController->generateTaxReport($request);
});

