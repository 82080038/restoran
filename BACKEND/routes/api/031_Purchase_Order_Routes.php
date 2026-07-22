<?php

// Purchase Order Routes
$router->addRoute('POST', '/api/v1/inventory/purchase-orders', withAuth(function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/inventory/purchase-orders', withAuth(function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->getAll($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/purchase-orders/{id}/approve', withAuth(function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->approve($request);
}, $authMiddleware));

