<?php

// QR Ordering Routes

// Authenticated routes (staff/admin)
$router->addRoute('POST', '/api/v1/qr-ordering/generate', function($request) use ($qrOrderingController) {
    return $qrOrderingController->generateQRCode($request);
});
$router->addRoute('GET', '/api/v1/qr-ordering/codes', function($request) use ($qrOrderingController) {
    return $qrOrderingController->getQRCodes($request);
});
$router->addRoute('POST', '/api/v1/qr-ordering/codes/{qrId}/toggle', function($request) use ($qrOrderingController) {
    return $qrOrderingController->toggleQRCode($request);
});
$router->addRoute('GET', '/api/v1/qr-ordering/analytics', function($request) use ($qrOrderingController) {
    return $qrOrderingController->getAnalytics($request);
});

// Public routes (no auth - for customer QR scan)
$router->addRoute('GET', '/api/v1/qr-ordering/menu', function($request) use ($qrOrderingController) {
    return $qrOrderingController->getMenuByQR($request);
});
$router->addRoute('POST', '/api/v1/qr-ordering/order', function($request) use ($qrOrderingController) {
    return $qrOrderingController->placeOrder($request);
});
$router->addRoute('GET', '/api/v1/qr-ordering/order/status', function($request) use ($qrOrderingController) {
    return $qrOrderingController->getOrderStatus($request);
});
