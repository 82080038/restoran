<?php

// Delivery Routes
$router->addRoute('POST', '/api/v1/delivery/orders', withAuth(function($request) use ($deliveryController) {
    return $deliveryController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/delivery/orders', withAuth(function($request) use ($deliveryController) {
    return $deliveryController->getAll($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/delivery/orders/{id}/assign-driver', withAuth(function($request) use ($deliveryController) {
    return $deliveryController->assignDriver($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/delivery/orders/{id}/status', withAuth(function($request) use ($deliveryController) {
    return $deliveryController->updateStatus($request);
}, $authMiddleware));

