<?php

// Mobile Routes
$router->addRoute('GET', '/api/v1/mobile/menu', function($request) use ($mobileOrderController) {
    return $mobileOrderController->getMenu($request);
});
$router->addRoute('GET', '/api/v1/mobile/quick-order/{id}', function($request) use ($mobileOrderController) {
    return $mobileOrderController->getQuickOrder($request);
});

// Consumer Routes (Public - No Auth Required)
$router->addRoute('GET', '/api/v1/consumer/restaurants/featured', function($request) use ($consumerController) {
    return $consumerController->getFeaturedRestaurants($request);
});
$router->addRoute('GET', '/api/v1/consumer/restaurants/nearby', function($request) use ($consumerController) {
    return $consumerController->getNearbyRestaurants($request);
});
$router->addRoute('GET', '/api/v1/consumer/restaurants', function($request) use ($consumerController) {
    return $consumerController->getRestaurants($request);
});
$router->addRoute('GET', '/api/v1/consumer/restaurants/{id}', function($request) use ($consumerController) {
    return $consumerController->getRestaurantDetails($request);
});
$router->addRoute('GET', '/api/v1/consumer/cuisines', function($request) use ($consumerController) {
    return $consumerController->getCuisines($request);
});
$router->addRoute('GET', '/api/v1/consumer/menu/{restaurant_id}', function($request) use ($consumerController) {
    return $consumerController->getRestaurantMenu($request);
});
$router->addRoute('GET', '/api/v1/consumer/faq', function($request) use ($consumerController) {
    return $consumerController->getFaq($request);
});

