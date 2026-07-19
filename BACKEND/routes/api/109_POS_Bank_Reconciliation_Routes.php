<?php

// POS-to-Bank Reconciliation Routes
$router->addRoute('GET', '/api/v1/pos-reconciliation/deposits', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getDeposits($request);
});
$router->addRoute('POST', '/api/v1/pos-reconciliation/deposits', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->createDeposit($request);
});
$router->addRoute('POST', '/api/v1/pos-reconciliation/deposits/{id}/match', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->matchDeposit($request);
});
$router->addRoute('POST', '/api/v1/pos-reconciliation/deposits/{id}/resolve', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->resolveDeposit($request);
});
$router->addRoute('GET', '/api/v1/pos-reconciliation/variance-report', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getVarianceReport($request);
});
$router->addRoute('POST', '/api/v1/pos-reconciliation/merchant-fees', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->addMerchantFee($request);
});
$router->addRoute('GET', '/api/v1/pos-reconciliation/merchant-fees', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getMerchantFees($request);
});
$router->addRoute('POST', '/api/v1/pos-reconciliation/eod-closeouts', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->createEODCloseout($request);
});
$router->addRoute('POST', '/api/v1/pos-reconciliation/eod-closeouts/{id}/close', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->closeEODCloseout($request);
});
$router->addRoute('GET', '/api/v1/pos-reconciliation/eod-closeouts', function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getEODCloseouts($request);
});
