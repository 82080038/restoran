<?php

// Advanced Procurement Routes
$router->addRoute('POST', '/api/v1/procurement/purchase-plans', withAuth(
    function($request) use ($advancedProcurementController) {
        return $advancedProcurementController->generatePurchasePlan($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/procurement/purchase-plans', withAuth(
    function($request) use ($advancedProcurementController) {
        return $advancedProcurementController->getPurchasePlans($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/procurement/three-way-match', withAuth(
    function($request) use ($advancedProcurementController) {
        return $advancedProcurementController->performThreeWayMatch($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/procurement/three-way-matches', withAuth(
    function($request) use ($advancedProcurementController) {
        return $advancedProcurementController->getThreeWayMatches($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/procurement/stock-forecast', withAuth(
    function($request) use ($advancedProcurementController) {
        return $advancedProcurementController->forecastStock($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/procurement/summary', withAuth(
    function($request) use ($advancedProcurementController) {
        return $advancedProcurementController->getSummary($request);
    },
    $authMiddleware
));

