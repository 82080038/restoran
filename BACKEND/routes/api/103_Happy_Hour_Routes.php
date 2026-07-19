<?php

// Happy Hour / Promotional Pricing Routes
$router->addRoute('GET', '/api/v1/promotions', function($request) use ($happyHourController) {
    return $happyHourController->getPromotions($request);
});
$router->addRoute('GET', '/api/v1/promotions/{id}', function($request) use ($happyHourController) {
    return $happyHourController->getPromotion($request);
});
$router->addRoute('POST', '/api/v1/promotions', function($request) use ($happyHourController) {
    return $happyHourController->createPromotion($request);
});
$router->addRoute('PUT', '/api/v1/promotions/{id}', function($request) use ($happyHourController) {
    return $happyHourController->updatePromotion($request);
});
$router->addRoute('DELETE', '/api/v1/promotions/{id}', function($request) use ($happyHourController) {
    return $happyHourController->deletePromotion($request);
});
$router->addRoute('POST', '/api/v1/promotions/calculate', function($request) use ($happyHourController) {
    return $happyHourController->calculateDiscount($request);
});
$router->addRoute('GET', '/api/v1/promotions/{id}/stats', function($request) use ($happyHourController) {
    return $happyHourController->getPromotionStats($request);
});

// Public endpoint for checking active promotions
$router->addRoute('GET', '/api/v1/public/promotions/active', function($request) use ($happyHourController) {
    $request['query']['active'] = 'true';
    return $happyHourController->getPromotions($request);
});
