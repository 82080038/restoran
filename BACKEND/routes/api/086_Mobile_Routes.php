<?php

// Mobile Routes
$router->addRoute('GET', '/api/v1/mobile/menu', withAuth(function($request) use ($mobileOrderController) {
    return $mobileOrderController->getMenu($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/mobile/quick-order/{id}', withAuth(function($request) use ($mobileOrderController) {
    return $mobileOrderController->getQuickOrder($request);
}, $authMiddleware));

// Consumer Routes (Public - No Auth Required)
$router->addRoute('GET', '/api/v1/consumer/restaurants/featured', withAuth(function($request) use ($consumerController) {
    return $consumerController->getFeaturedRestaurants($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/consumer/restaurants/nearby', withAuth(function($request) use ($consumerController) {
    return $consumerController->getNearbyRestaurants($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/consumer/restaurants', withAuth(function($request) use ($consumerController) {
    return $consumerController->getRestaurants($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/consumer/restaurants/{id}', withAuth(function($request) use ($consumerController) {
    return $consumerController->getRestaurantDetails($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/consumer/cuisines', withAuth(function($request) use ($consumerController) {
    return $consumerController->getCuisines($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/consumer/menu/{restaurant_id}', withAuth(function($request) use ($consumerController) {
    return $consumerController->getRestaurantMenu($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/consumer/faq', withAuth(function($request) use ($consumerController) {
    return $consumerController->getFaq($request);
}, $authMiddleware));

