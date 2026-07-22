<?php

// Tax Calculation Routes
$router->addRoute('POST', '/api/v1/accounting/tax-rates', withAuth(function($request) use ($taxCalculationController) {
    return $taxCalculationController->saveTaxRate($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/tax-rates', withAuth(function($request) use ($taxCalculationController) {
    return $taxCalculationController->getTaxRate($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/orders/{id}/tax', withAuth(function($request) use ($taxCalculationController) {
    return $taxCalculationController->calculateOrderTax($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/tax/monthly', withAuth(function($request) use ($taxCalculationController) {
    return $taxCalculationController->calculateMonthlyTax($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/tax/report', withAuth(function($request) use ($taxCalculationController) {
    return $taxCalculationController->generateTaxReport($request);
}, $authMiddleware));

