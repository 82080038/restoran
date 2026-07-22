<?php

// Happy Hour / Promotional Pricing Routes
$router->addRoute('GET', '/api/v1/promotions', withAuth(function($request) use ($happyHourController) {
    return $happyHourController->getPromotions($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/promotions/{id}', withAuth(function($request) use ($happyHourController) {
    return $happyHourController->getPromotion($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/promotions', withAuth(function($request) use ($happyHourController) {
    return $happyHourController->createPromotion($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/promotions/{id}', withAuth(function($request) use ($happyHourController) {
    return $happyHourController->updatePromotion($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/promotions/{id}', withAuth(function($request) use ($happyHourController) {
    return $happyHourController->deletePromotion($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/promotions/calculate', withAuth(function($request) use ($happyHourController) {
    return $happyHourController->calculateDiscount($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/promotions/{id}/stats', withAuth(function($request) use ($happyHourController) {
    return $happyHourController->getPromotionStats($request);
}, $authMiddleware));

// Public endpoint for checking active promotions
$router->addRoute('GET', '/api/v1/public/promotions/active', withAuth(function($request) use ($happyHourController) {
    $request['query']['active'] = 'true';
    return $happyHourController->getPromotions($request);
}, $authMiddleware));
