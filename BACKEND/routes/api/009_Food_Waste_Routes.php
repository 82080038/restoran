<?php

// Food Waste Routes
$router->addRoute('POST', '/api/v1/food-waste', withAuth(
    function($request) use ($foodWasteController) {
        return $foodWasteController->create($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/food-waste', withAuth(
    function($request) use ($foodWasteController) {
        return $foodWasteController->index($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/food-waste/analysis', withAuth(
    function($request) use ($foodWasteController) {
        return $foodWasteController->analysis($request);
    },
    $authMiddleware
));

