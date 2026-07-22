<?php

// Inventory Advanced Routes
$router->addRoute('POST', '/api/v1/inventory/repurpose', withAuth(function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->repurposeStock($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/zero-cost-stock', withAuth(function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->zeroCostStockIn($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/stock-transfer', withAuth(function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->createStockTransfer($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/stock-transfer/{id}/receive', withAuth(function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->receiveStockTransfer($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/inventory/stock-transfers', withAuth(function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->getStockTransfers($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/inventory/repurposing-history', withAuth(function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->getRepurposingHistory($request);
}, $authMiddleware));

