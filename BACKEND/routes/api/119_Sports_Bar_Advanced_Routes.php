<?php

// Sports Bar Advanced Routes (Pre-Authorization Bar Tab)
$router->addRoute('GET', '/api/v1/sports-bar/tabs', function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->getTabs($request);
});
$router->addRoute('GET', '/api/v1/sports-bar/tabs/{id}', function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->getTab($request);
});
$router->addRoute('POST', '/api/v1/sports-bar/tabs', function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->openTab($request);
});
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/items', function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->addToTab($request);
});
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/close', function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->closeTab($request);
});
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/capture', function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->captureTab($request);
});
$router->addRoute('POST', '/api/v1/sports-bar/tabs/{id}/void', function($request) use ($sportsBarAdvancedController) {
    return $sportsBarAdvancedController->voidTab($request);
});
