<?php

// Simple Payment Routes (compatible with current router pattern)
$router->addRoute('GET', '/api/v1/payments', function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPayments($request);
});
$router->addRoute('GET', '/api/v1/payments/{id}', function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPayment($request);
});
$router->addRoute('POST', '/api/v1/payments', function($request) use ($simplePaymentController) {
    return $simplePaymentController->createPayment($request);
});
$router->addRoute('POST', '/api/v1/payments/{id}/process', function($request) use ($simplePaymentController) {
    return $simplePaymentController->processPayment($request);
});
$router->addRoute('GET', '/api/v1/payments/methods', function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPaymentMethods($request);
});
$router->addRoute('GET', '/api/v1/payments/statistics', function($request) use ($simplePaymentController) {
    return $simplePaymentController->getStatistics($request);
});

// Z-Report (end of day sales summary)
$router->addRoute('GET', '/api/v1/reports/z-report', function($request) use ($simplePaymentController) {
    return $simplePaymentController->getZReport($request);
});

// Notification Routes (SSE + REST)
$router->addRoute('GET', '/api/v1/notifications/stream', function($request) use ($notificationController) {
    return $notificationController->stream($request);
});
$router->addRoute('GET', '/api/v1/notifications', function($request) use ($notificationController) {
    return $notificationController->getNotifications($request);
});
$router->addRoute('POST', '/api/v1/notifications/{id}/read', function($request) use ($notificationController) {
    return $notificationController->markAsRead($request);
});
$router->addRoute('GET', '/api/v1/notifications/unread-count', function($request) use ($notificationController) {
    return $notificationController->getUnreadCount($request);
});

// Public Payment Routes (without auth for testing)
$router->addRoute('GET', '/api/v1/public/payments', function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPayments($request);
});
$router->addRoute('GET', '/api/v1/public/payments/z-report', function($request) use ($simplePaymentController) {
    return $simplePaymentController->getZReport($request);
});
