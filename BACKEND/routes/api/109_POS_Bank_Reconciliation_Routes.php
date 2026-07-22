<?php

// POS-to-Bank Reconciliation Routes
$router->addRoute('GET', '/api/v1/pos-reconciliation/deposits', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getDeposits($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/pos-reconciliation/deposits', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->createDeposit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/pos-reconciliation/deposits/{id}/match', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->matchDeposit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/pos-reconciliation/deposits/{id}/resolve', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->resolveDeposit($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/pos-reconciliation/variance-report', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getVarianceReport($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/pos-reconciliation/merchant-fees', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->addMerchantFee($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/pos-reconciliation/merchant-fees', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getMerchantFees($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/pos-reconciliation/eod-closeouts', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->createEODCloseout($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/pos-reconciliation/eod-closeouts/{id}/close', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->closeEODCloseout($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/pos-reconciliation/eod-closeouts', withAuth(function($request) use ($posBankReconciliationController) {
    return $posBankReconciliationController->getEODCloseouts($request);
}, $authMiddleware));
