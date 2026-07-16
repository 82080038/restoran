<?php

// Delivery Routes
$router->addRoute('POST', '/api/v1/delivery/orders', function($request) use ($deliveryController) {
    return $deliveryController->create($request);
});
$router->addRoute('GET', '/api/v1/delivery/orders', function($request) use ($deliveryController) {
    return $deliveryController->getAll($request);
});
$router->addRoute('POST', '/api/v1/delivery/orders/{id}/assign-driver', function($request) use ($deliveryController) {
    return $deliveryController->assignDriver($request);
});
$router->addRoute('POST', '/api/v1/delivery/orders/{id}/status', function($request) use ($deliveryController) {
    return $deliveryController->updateStatus($request);
});

