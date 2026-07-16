<?php

// Customer Advanced Routes
$router->addRoute('POST', '/api/v1/crm/customers/{customer_id}/favorites', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->addFavoriteProduct($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/favorites', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getCustomerFavorites($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/habit-analysis', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getCustomerHabitAnalysis($request);
});
$router->addRoute('POST', '/api/v1/crm/birthday-promotions', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->createBirthdayPromotion($request);
});
$router->addRoute('GET', '/api/v1/crm/birthday-promotions', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getBirthdayPromotions($request);
});
$router->addRoute('POST', '/api/v1/crm/birthday-promotions/{id}/use', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->useBirthdayPromotion($request);
});

