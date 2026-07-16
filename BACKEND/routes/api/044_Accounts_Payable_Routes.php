<?php

// Accounts Payable Routes
$router->addRoute('POST', '/api/v1/accounting/accounts-payable', function($request) use ($accountsPayableController) {
    return $accountsPayableController->createBill($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBills($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-payable/bills', function($request) use ($accountsPayableController) {
    return $accountsPayableController->createBill($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/bills', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBills($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/bills/{id}', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBill($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-payable/payments', function($request) use ($accountsPayableController) {
    return $accountsPayableController->addPayment($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/aging-report', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getAgingReport($request);
});

