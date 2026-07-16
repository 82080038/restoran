<?php

// Accounts Receivable Routes
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->createInvoice($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoices($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable/invoices', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->createInvoice($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/invoices', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoices($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/invoices/{id}', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoice($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable/payments', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->addPayment($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/aging-report', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getAgingReport($request);
});

