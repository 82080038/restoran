<?php

// Bank Reconciliation Routes
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations', withAuth(function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->createReconciliation($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/bank-reconciliations', withAuth(function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getReconciliations($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/bank-reconciliations/{id}', withAuth(function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getReconciliation($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations/items', withAuth(function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->addItem($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations/{id}/reconcile', withAuth(function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->reconcile($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/bank-accounts', withAuth(function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getBankAccounts($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/bank-accounts', withAuth(function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->createBankAccount($request);
}, $authMiddleware));

