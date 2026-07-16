<?php

// Kitchen Routes
$router->addRoute('GET', '/api/v1/kitchen/orders', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getKitchenOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/pending', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getPendingOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/in-progress', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getInProgressOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/ready', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getReadyOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/{id}', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getKitchenOrder($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/kitchen/orders', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->createKitchenOrder($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/kitchen/orders/{id}/status', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->updateKitchenOrderStatus($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/kitchen/orders/{id}/priority', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->updateKitchenOrderPriority($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/kitchen/items/{id}/status', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->updateKitchenItemStatus($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

