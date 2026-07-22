<?php

// Customer Advanced Routes
$router->addRoute('POST', '/api/v1/crm/customers/{customer_id}/favorites', withAuth(function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->addFavoriteProduct($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/favorites', withAuth(function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getCustomerFavorites($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/habit-analysis', withAuth(function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getCustomerHabitAnalysis($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/crm/birthday-promotions', withAuth(function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->createBirthdayPromotion($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/birthday-promotions', withAuth(function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getBirthdayPromotions($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/crm/birthday-promotions/{id}/use', withAuth(function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->useBirthdayPromotion($request);
}, $authMiddleware));

