<?php

// AI Routes
$router->addRoute('POST', '/api/v1/ai/sales-forecast', withAuth(function($request) use ($aiController) {
    return $aiController->generateSalesForecast($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/ai/sales-forecast', withAuth(function($request) use ($aiController) {
    return $aiController->generateSalesForecast($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/ai/inventory-prediction/{id}', withAuth(function($request) use ($aiController) {
    return $aiController->generateInventoryPrediction($request);
}, $authMiddleware));

