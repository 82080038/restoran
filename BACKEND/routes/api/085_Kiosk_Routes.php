<?php

// Kiosk Routes
$router->addRoute('GET', '/api/v1/kiosk/menu', withAuth(function($request) use ($kioskController) {
    return $kioskController->getMenu($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/kiosk/orders', withAuth(function($request) use ($kioskController) {
    return $kioskController->createOrder($request);
}, $authMiddleware));

