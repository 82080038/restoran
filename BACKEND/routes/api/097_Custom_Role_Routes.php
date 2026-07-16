<?php

// Custom Role Routes
$router->addRoute('POST', '/api/v1/roles/from-template', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->createFromTemplate($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/roles/custom', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->createCustom($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/templates', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->getTemplates($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/templates/details', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->getTemplateDetails($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/roles/clone', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->cloneRole($request);
    },
    $authMiddleware
));

