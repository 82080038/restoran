<?php

// Purchase Order Routes
$router->addRoute('POST', '/api/v1/inventory/purchase-orders', function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/purchase-orders', function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/purchase-orders/{id}/approve', function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->approve($request);
});

