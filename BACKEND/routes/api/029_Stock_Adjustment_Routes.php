<?php

// Stock Adjustment Routes
$router->addRoute('POST', '/api/v1/inventory/stock-adjustments', withAuth(function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/inventory/stock-adjustments', withAuth(function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->getAll($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/stock-adjustments/{id}/approve', withAuth(function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->approve($request);
}, $authMiddleware));

