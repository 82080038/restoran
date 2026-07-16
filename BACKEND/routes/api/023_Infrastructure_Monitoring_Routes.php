<?php

// Infrastructure Monitoring Routes
$router->addRoute('POST', '/api/v1/infrastructure/performance-metrics', withAuth(
    function($request) use ($infrastructureMonitoringController) {
        return $infrastructureMonitoringController->recordPerformanceMetrics($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/infrastructure/performance-metrics', withAuth(
    function($request) use ($infrastructureMonitoringController) {
        return $infrastructureMonitoringController->getPerformanceMetrics($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/infrastructure/alerts', withAuth(
    function($request) use ($infrastructureMonitoringController) {
        return $infrastructureMonitoringController->getAlerts($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/infrastructure/performance-report', withAuth(
    function($request) use ($infrastructureMonitoringController) {
        return $infrastructureMonitoringController->getPerformanceReport($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/infrastructure/summary', withAuth(
    function($request) use ($infrastructureMonitoringController) {
        return $infrastructureMonitoringController->getSummary($request);
    },
    $authMiddleware
));

