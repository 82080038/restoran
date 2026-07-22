<?php

// Supply Chain Routes
$router->addRoute('POST', '/api/v1/supply-chain/purchase-plans', withAuth(function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->generatePurchasePlan($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/supply-chain/purchase-plans/{id}/approve', withAuth(function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->approvePurchasePlan($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/supply-chain/purchase-plans', withAuth(function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->getPurchasePlans($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/supply-chain/quality-checks', withAuth(function($request) use ($qualityControlController) {
    return $qualityControlController->createQualityCheck($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/supply-chain/quality-checks/{id}', withAuth(function($request) use ($qualityControlController) {
    return $qualityControlController->updateQualityCheckResult($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/supply-chain/quality-checks', withAuth(function($request) use ($qualityControlController) {
    return $qualityControlController->getQualityChecks($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/supply-chain/quality-report', withAuth(function($request) use ($qualityControlController) {
    return $qualityControlController->getQualityReport($request);
}, $authMiddleware));

