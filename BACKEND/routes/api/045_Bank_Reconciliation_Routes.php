<?php

// Bank Reconciliation Routes
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->createReconciliation($request);
});
$router->addRoute('GET', '/api/v1/accounting/bank-reconciliations', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getReconciliations($request);
});
$router->addRoute('GET', '/api/v1/accounting/bank-reconciliations/{id}', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getReconciliation($request);
});
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations/items', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->addItem($request);
});
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations/{id}/reconcile', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->reconcile($request);
});
$router->addRoute('GET', '/api/v1/accounting/bank-accounts', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getBankAccounts($request);
});
$router->addRoute('POST', '/api/v1/accounting/bank-accounts', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->createBankAccount($request);
});

