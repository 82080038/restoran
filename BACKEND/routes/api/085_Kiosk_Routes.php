<?php

// Kiosk Routes
$router->addRoute('GET', '/api/v1/kiosk/menu', function($request) use ($kioskController) {
    return $kioskController->getMenu($request);
});
$router->addRoute('POST', '/api/v1/kiosk/orders', function($request) use ($kioskController) {
    return $kioskController->createOrder($request);
});

