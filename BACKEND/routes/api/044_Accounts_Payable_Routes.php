<?php

// Accounts Payable Routes
$router->addRoute('POST', '/api/v1/accounting/accounts-payable', withAuth(function($request) use ($accountsPayableController) {
    return $accountsPayableController->createBill($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-payable', withAuth(function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBills($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/accounts-payable/bills', withAuth(function($request) use ($accountsPayableController) {
    return $accountsPayableController->createBill($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/bills', withAuth(function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBills($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/bills/{id}', withAuth(function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBill($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/accounts-payable/payments', withAuth(function($request) use ($accountsPayableController) {
    return $accountsPayableController->addPayment($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/aging-report', withAuth(function($request) use ($accountsPayableController) {
    return $accountsPayableController->getAgingReport($request);
}, $authMiddleware));

