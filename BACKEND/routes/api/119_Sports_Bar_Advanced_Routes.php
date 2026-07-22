<?php

// Sports Bar Advanced Routes (Pre-Authorization Bar Tab)
$router->addRoute('GET', '/api/v1/sports-bar/tabs', withAuth(function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->getTabs($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/sports-bar/tabs/{id}', withAuth(function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->getTab($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sports-bar/tabs', withAuth(function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->openTab($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/items', withAuth(function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->addToTab($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/close', withAuth(function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->closeTab($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/capture', withAuth(function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->captureTab($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/void', withAuth(function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->voidTab($request);
}, $authMiddleware));
