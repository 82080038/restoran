<?php

// Accounts Receivable Routes
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable', withAuth(function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->createInvoice($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable', withAuth(function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoices($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable/invoices', withAuth(function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->createInvoice($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/invoices', withAuth(function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoices($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/invoices/{id}', withAuth(function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoice($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable/payments', withAuth(function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->addPayment($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/aging-report', withAuth(function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getAgingReport($request);
}, $authMiddleware));

