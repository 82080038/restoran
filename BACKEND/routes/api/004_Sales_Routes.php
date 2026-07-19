<?php

// Sales Routes
$router->addRoute('POST', '/api/v1/sales/orders', function($request) use ($orderController) {
    return $orderController->create($request);
});

// Orders Routes (alias for frontend compatibility with permission check)
$router->addRoute('GET', '/api/v1/orders', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->getAll($request);
    },
    'ORDER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/orders/history', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->getAll($request);
    },
    'ORDER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/orders/recent', withAuthAndPermission(
    function($request) use ($orderController) {
        // Support limit and sort parameters
        $limit = $request['limit'] ?? 5;
        $sort = $request['sort'] ?? 'created_at';
        $order = $request['order'] ?? 'DESC';
        $request['limit'] = $limit;
        $request['sort'] = $sort;
        $request['order'] = $order;
        return $orderController->getAll($request);
    },
    'ORDER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/orders/{id}', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->get($request);
    },
    'ORDER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/orders', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->create($request);
    },
    'ORDER_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/orders/{id}', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->update($request);
    },
    'ORDER_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/sales/orders/{id}', function($request) use ($orderController) {
    return $orderController->update($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/close', function($request) use ($orderController) {
    return $orderController->close($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/hold', function($request) use ($orderController) {
    return $orderController->hold($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/recall', function($request) use ($orderController) {
    return $orderController->recall($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/priority', function($request) use ($orderController) {
    return $orderController->setPriority($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/split-bill', function($request) use ($orderController) {
    return $orderController->splitBill($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/payment', function($request) use ($orderController) {
    return $orderController->addPayment($request);
});

