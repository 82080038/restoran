<?php

// Tier 3 Operations Routes (AI Prediction, Booking Sync, Order Throttling, Auto PO, Production Planning, Service Speed)

// AI Sales Prediction
$router->addRoute('GET', '/api/v1/operations/predictions', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getPredictions($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/predictions/generate', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->generatePrediction($request);
}, $authMiddleware));

// Multi-Channel Booking Sync
$router->addRoute('POST', '/api/v1/operations/booking-sync', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->syncBooking($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/operations/booking-sync/status', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getSyncStatus($request);
}, $authMiddleware));

// Order Throttling
$router->addRoute('GET', '/api/v1/operations/throttling/check', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->checkThrottle($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/throttling/config', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->setThrottlingConfig($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/throttling/pause', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->pauseThrottling($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/throttling/resume', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->resumeThrottling($request);
}, $authMiddleware));

// Auto Purchase Order
$router->addRoute('POST', '/api/v1/operations/auto-po/rules', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->createAutoPORule($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/auto-po/check', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->checkAndGeneratePOs($request);
}, $authMiddleware));

// Daily Production Planning
$router->addRoute('GET', '/api/v1/operations/production-plans', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getProductionPlans($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/production-plans', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->createProductionPlan($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/operations/production-plans/{id}', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->updateProductionPlan($request);
}, $authMiddleware));

// Service Speed Metrics
$router->addRoute('POST', '/api/v1/operations/service-speed', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->recordServiceMetric($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/operations/service-speed/report', withAuth(function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getServiceSpeedReport($request);
}, $authMiddleware));
