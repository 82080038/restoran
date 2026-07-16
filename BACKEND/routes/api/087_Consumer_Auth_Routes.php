<?php

// Consumer Auth Routes
$router->addRoute('POST', '/api/v1/consumer/auth/login', function($request) use ($consumerController) {
    return $consumerController->login($request);
});
$router->addRoute('POST', '/api/v1/consumer/auth/send-otp', function($request) use ($consumerController) {
    return $consumerController->sendOtp($request);
});
$router->addRoute('POST', '/api/v1/consumer/auth/verify-otp', function($request) use ($consumerController) {
    return $consumerController->verifyOtp($request);
});

// Consumer Order Routes (Testing - No Auth Required)
$router->addRoute('POST', '/api/v1/consumer/orders', function($request) use ($consumerController) {
    return $consumerController->placeOrder($request);
});
$router->addRoute('GET', '/api/v1/consumer/orders', function($request) use ($consumerController) {
    return $consumerController->getOrders($request);
});

// Consumer Reservation Routes (Testing - No Auth Required)
$router->addRoute('POST', '/api/v1/consumer/reservations', function($request) use ($consumerController) {
    return $consumerController->makeReservation($request);
});
$router->addRoute('GET', '/api/v1/consumer/reservations', function($request) use ($consumerController) {
    return $consumerController->getReservations($request);
});

// Consumer Loyalty Routes (Testing - No Auth Required)
$router->addRoute('GET', '/api/v1/consumer/loyalty', function($request) use ($consumerController) {
    return $consumerController->getLoyaltyPoints($request);
});
$router->addRoute('POST', '/api/v1/consumer/loyalty/redeem', function($request) use ($consumerController) {
    return $consumerController->redeemReward($request);
});

// Consumer Review Routes (Testing - No Auth Required)
$router->addRoute('POST', '/api/v1/consumer/reviews', function($request) use ($consumerController) {
    return $consumerController->submitReview($request);
});

// Consumer Favorites Routes (Testing - No Auth Required)
$router->addRoute('GET', '/api/v1/consumer/favorites', function($request) use ($consumerController) {
    return $consumerController->getFavorites($request);
});

