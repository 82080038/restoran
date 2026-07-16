<?php

// Custom Permission Routes
$router->addRoute('POST', '/api/v1/permissions/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->createPermission($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/permissions/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getCustomPermissions($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/permissions/all', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getAllPermissions($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/modules/assign', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->assignToRole($request);
    },
    $authMiddleware
));

