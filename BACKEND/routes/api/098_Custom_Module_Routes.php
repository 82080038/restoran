<?php

// Custom Module Routes
$router->addRoute('POST', '/api/v1/modules/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->createModule($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/modules/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getCustomModules($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/modules/all', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getAllModules($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/modules/custom/{custom_module_id}', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->updateModule($request);
    },
    $authMiddleware
));

$router->addRoute('DELETE', '/api/v1/modules/custom/{custom_module_id}', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->deleteModule($request);
    },
    $authMiddleware
));

