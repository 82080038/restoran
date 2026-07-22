<?php

// Simple Payment Routes (compatible with current router pattern)
$router->addRoute('GET', '/api/v1/payments', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPayments($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/payments/{id}', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPayment($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/payments', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->createPayment($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/payments/{id}/process', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->processPayment($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/payments/methods', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPaymentMethods($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/payments/statistics', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->getStatistics($request);
}, $authMiddleware));

// Z-Report (end of day sales summary)
$router->addRoute('GET', '/api/v1/reports/z-report', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->getZReport($request);
}, $authMiddleware));

// Notification Routes (SSE + REST)
$router->addRoute('GET', '/api/v1/notifications/stream', withAuth(function($request) use ($notificationController) {
    return $notificationController->stream($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/notifications', withAuth(function($request) use ($notificationController) {
    return $notificationController->getNotifications($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/notifications/{id}/read', withAuth(function($request) use ($notificationController) {
    return $notificationController->markAsRead($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/notifications/unread-count', withAuth(function($request) use ($notificationController) {
    return $notificationController->getUnreadCount($request);
}, $authMiddleware));

// Public Payment Routes (without auth for testing)
$router->addRoute('GET', '/api/v1/public/payments', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->getPayments($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/public/payments/z-report', withAuth(function($request) use ($simplePaymentController) {
    return $simplePaymentController->getZReport($request);
}, $authMiddleware));
