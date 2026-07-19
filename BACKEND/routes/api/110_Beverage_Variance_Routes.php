<?php

// Beverage Variance Routes
$router->addRoute('GET', '/api/v1/beverage-variance/bar-counts', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getBarCounts($request);
});
$router->addRoute('GET', '/api/v1/beverage-variance/bar-counts/{id}', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getBarCount($request);
});
$router->addRoute('POST', '/api/v1/beverage-variance/bar-counts', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->createBarCount($request);
});
$router->addRoute('POST', '/api/v1/beverage-variance/bar-counts/{id}/submit', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->submitBarCount($request);
});
$router->addRoute('GET', '/api/v1/beverage-variance/reports', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getVarianceReports($request);
});
$router->addRoute('POST', '/api/v1/beverage-variance/reports/generate', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->generateVarianceReport($request);
});
$router->addRoute('GET', '/api/v1/beverage-variance/kegs', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getKegs($request);
});
$router->addRoute('POST', '/api/v1/beverage-variance/kegs/receive', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->receiveKeg($request);
});
$router->addRoute('POST', '/api/v1/beverage-variance/kegs/{id}/tap', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->tapKeg($request);
});
$router->addRoute('POST', '/api/v1/beverage-variance/kegs/{id}/weight', function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->updateKegWeight($request);
});
