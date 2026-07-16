<?php

// Supply Chain Routes
$router->addRoute('POST', '/api/v1/supply-chain/purchase-plans', function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->generatePurchasePlan($request);
});
$router->addRoute('POST', '/api/v1/supply-chain/purchase-plans/{id}/approve', function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->approvePurchasePlan($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/purchase-plans', function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->getPurchasePlans($request);
});
$router->addRoute('POST', '/api/v1/supply-chain/quality-checks', function($request) use ($qualityControlController) {
    return $qualityControlController->createQualityCheck($request);
});
$router->addRoute('PUT', '/api/v1/supply-chain/quality-checks/{id}', function($request) use ($qualityControlController) {
    return $qualityControlController->updateQualityCheckResult($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/quality-checks', function($request) use ($qualityControlController) {
    return $qualityControlController->getQualityChecks($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/quality-report', function($request) use ($qualityControlController) {
    return $qualityControlController->getQualityReport($request);
});

