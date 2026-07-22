<?php

// Beverage Variance Routes
$router->addRoute('GET', '/api/v1/beverage-variance/bar-counts', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getBarCounts($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beverage-variance/bar-counts/{id}', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getBarCount($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beverage-variance/bar-counts', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->createBarCount($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beverage-variance/bar-counts/{id}/submit', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->submitBarCount($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beverage-variance/reports', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getVarianceReports($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beverage-variance/reports/generate', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->generateVarianceReport($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beverage-variance/kegs', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->getKegs($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beverage-variance/kegs/receive', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->receiveKeg($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beverage-variance/kegs/{id}/tap', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->tapKeg($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beverage-variance/kegs/{id}/weight', withAuth(function($request) use ($beverageVarianceController) {
    return $beverageVarianceController->updateKegWeight($request);
}, $authMiddleware));
