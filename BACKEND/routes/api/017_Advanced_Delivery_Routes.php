<?php

// Advanced Delivery Routes
$router->addRoute('POST', '/api/v1/delivery/drivers', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->createDriver($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/delivery/drivers', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->getDrivers($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/delivery/routes/optimize', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->optimizeRoute($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/delivery/routes', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->getDeliveryRoutes($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/delivery/tracking', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->trackDeliveryLocation($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/delivery/tracking/{delivery_order_id}', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->getDeliveryTracking($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/delivery/notifications', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->sendCustomerNotification($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/delivery/notifications', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->getDeliveryNotifications($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/delivery/summary', withAuth(
    function($request) use ($advancedDeliveryController) {
        return $advancedDeliveryController->getSummary($request);
    },
    $authMiddleware
));

