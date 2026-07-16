<?php

// Accounting Period Routes
$router->addRoute('POST', '/api/v1/accounting/periods', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->createPeriod($request);
});
$router->addRoute('GET', '/api/v1/accounting/periods', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->getPeriods($request);
});
$router->addRoute('GET', '/api/v1/accounting/periods/current', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->getCurrentPeriod($request);
});
$router->addRoute('POST', '/api/v1/accounting/periods/{id}/close', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->closePeriod($request);
});
$router->addRoute('POST', '/api/v1/accounting/periods/{id}/reopen', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->reopenPeriod($request);
});

