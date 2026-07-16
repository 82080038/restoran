<?php

// Stock Adjustment Routes
$router->addRoute('POST', '/api/v1/inventory/stock-adjustments', function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/stock-adjustments', function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-adjustments/{id}/approve', function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->approve($request);
});

