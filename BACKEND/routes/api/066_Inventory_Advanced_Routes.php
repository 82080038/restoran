<?php

// Inventory Advanced Routes
$router->addRoute('POST', '/api/v1/inventory/repurpose', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->repurposeStock($request);
});
$router->addRoute('POST', '/api/v1/inventory/zero-cost-stock', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->zeroCostStockIn($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-transfer', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->createStockTransfer($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-transfer/{id}/receive', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->receiveStockTransfer($request);
});
$router->addRoute('GET', '/api/v1/inventory/stock-transfers', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->getStockTransfers($request);
});
$router->addRoute('GET', '/api/v1/inventory/repurposing-history', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->getRepurposingHistory($request);
});

