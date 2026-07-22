<?php

// QR Ordering Routes

// Authenticated routes (staff/admin)
$router->addRoute('POST', '/api/v1/qr-ordering/generate', withAuth(function($request) use ($qrOrderingController) {
    return $qrOrderingController->generateQRCode($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/qr-ordering/codes', withAuth(function($request) use ($qrOrderingController) {
    return $qrOrderingController->getQRCodes($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/qr-ordering/codes/{qrId}/toggle', withAuth(function($request) use ($qrOrderingController) {
    return $qrOrderingController->toggleQRCode($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/qr-ordering/analytics', withAuth(function($request) use ($qrOrderingController) {
    return $qrOrderingController->getAnalytics($request);
}, $authMiddleware));

// Public routes (no auth - for customer QR scan)
$router->addRoute('GET', '/api/v1/qr-ordering/menu', withAuth(function($request) use ($qrOrderingController) {
    return $qrOrderingController->getMenuByQR($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/qr-ordering/order', withAuth(function($request) use ($qrOrderingController) {
    return $qrOrderingController->placeOrder($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/qr-ordering/order/status', withAuth(function($request) use ($qrOrderingController) {
    return $qrOrderingController->getOrderStatus($request);
}, $authMiddleware));
