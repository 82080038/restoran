<?php

// Kitchen Performance Routes
$router->addRoute('POST', '/api/v1/kitchen/chef-performance', withAuth(function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->recordChefPerformance($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/kitchen/metrics', withAuth(function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getKitchenMetrics($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/kitchen/chef-performance/{employee_id}', withAuth(function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getChefPerformance($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/kitchen/bottleneck-analysis', withAuth(function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getBottleneckAnalysis($request);
}, $authMiddleware));

