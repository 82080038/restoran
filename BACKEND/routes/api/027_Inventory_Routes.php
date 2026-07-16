<?php

// Inventory Routes
$router->addRoute('GET', '/api/v1/inventory', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getInventory($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/inventory/low-stock', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getLowStock($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/inventory/{id}', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getInventoryItem($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/inventory', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->createInventory($request);
    },
    'INVENTORY_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/inventory/{id}', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->updateInventory($request);
    },
    'INVENTORY_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/inventory/adjust', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->adjustStock($request);
    },
    'INVENTORY_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/inventory/{id}', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->deleteInventory($request);
    },
    'INVENTORY_DELETE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/inventory/transactions', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getTransactions($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

