<?php

// Kitchen Performance Routes
$router->addRoute('POST', '/api/v1/kitchen/chef-performance', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->recordChefPerformance($request);
});
$router->addRoute('GET', '/api/v1/kitchen/metrics', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getKitchenMetrics($request);
});
$router->addRoute('GET', '/api/v1/kitchen/chef-performance/{employee_id}', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getChefPerformance($request);
});
$router->addRoute('GET', '/api/v1/kitchen/bottleneck-analysis', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getBottleneckAnalysis($request);
});

