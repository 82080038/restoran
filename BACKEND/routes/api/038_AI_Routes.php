<?php

// AI Routes
$router->addRoute('POST', '/api/v1/ai/sales-forecast', function($request) use ($aiController) {
    return $aiController->generateSalesForecast($request);
});
$router->addRoute('POST', '/api/v1/ai/inventory-prediction/{id}', function($request) use ($aiController) {
    return $aiController->generateInventoryPrediction($request);
});

