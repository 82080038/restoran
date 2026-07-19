<?php

// Tier 3 Operations Routes (AI Prediction, Booking Sync, Order Throttling, Auto PO, Production Planning, Service Speed)

// AI Sales Prediction
$router->addRoute('GET', '/api/v1/operations/predictions', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getPredictions($request);
});
$router->addRoute('POST', '/api/v1/operations/predictions/generate', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->generatePrediction($request);
});

// Multi-Channel Booking Sync
$router->addRoute('POST', '/api/v1/operations/booking-sync', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->syncBooking($request);
});
$router->addRoute('GET', '/api/v1/operations/booking-sync/status', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getSyncStatus($request);
});

// Order Throttling
$router->addRoute('GET', '/api/v1/operations/throttling/check', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->checkThrottle($request);
});
$router->addRoute('POST', '/api/v1/operations/throttling/config', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->setThrottlingConfig($request);
});
$router->addRoute('POST', '/api/v1/operations/throttling/pause', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->pauseThrottling($request);
});
$router->addRoute('POST', '/api/v1/operations/throttling/resume', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->resumeThrottling($request);
});

// Auto Purchase Order
$router->addRoute('POST', '/api/v1/operations/auto-po/rules', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->createAutoPORule($request);
});
$router->addRoute('POST', '/api/v1/operations/auto-po/check', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->checkAndGeneratePOs($request);
});

// Daily Production Planning
$router->addRoute('GET', '/api/v1/operations/production-plans', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getProductionPlans($request);
});
$router->addRoute('POST', '/api/v1/operations/production-plans', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->createProductionPlan($request);
});
$router->addRoute('PATCH', '/api/v1/operations/production-plans/{id}', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->updateProductionPlan($request);
});

// Service Speed Metrics
$router->addRoute('POST', '/api/v1/operations/service-speed', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->recordServiceMetric($request);
});
$router->addRoute('GET', '/api/v1/operations/service-speed/report', function($request) use ($tier3OperationsController) {
    return $tier3OperationsController->getServiceSpeedReport($request);
});
