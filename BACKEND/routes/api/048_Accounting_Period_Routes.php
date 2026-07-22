<?php

// Accounting Period Routes
$router->addRoute('POST', '/api/v1/accounting/periods', withAuth(function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->createPeriod($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/periods', withAuth(function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->getPeriods($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/periods/current', withAuth(function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->getCurrentPeriod($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/periods/{id}/close', withAuth(function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->closePeriod($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/periods/{id}/reopen', withAuth(function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->reopenPeriod($request);
}, $authMiddleware));

