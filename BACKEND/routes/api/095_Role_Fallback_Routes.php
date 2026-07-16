<?php

// Role Fallback Routes
$router->addRoute('POST', '/api/v1/tenant/single-member', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->setSingleMember($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/tenant/single-member', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->checkSingleMember($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/roles/fallback', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->setFallback($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/fallback', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->getFallbacks($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/available', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->getAvailableRoles($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/exists', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->checkRoleExists($request);
    },
    $authMiddleware
));

