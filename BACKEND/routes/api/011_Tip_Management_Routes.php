<?php

// Tip Management Routes
$router->addRoute('POST', '/api/v1/tips', withAuth(
    function($request) use ($tipManagementController) {
        return $tipManagementController->create($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tips', withAuth(
    function($request) use ($tipManagementController) {
        return $tipManagementController->index($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tips/summary', withAuth(
    function($request) use ($tipManagementController) {
        return $tipManagementController->summary($request);
    },
    $authMiddleware
));

