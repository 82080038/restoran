<?php

// Menu/Navigation Routes
$router->addRoute('GET', '/api/v1/menu/user', withAuth(
    function($request) {
        $menuController = new \App\Core\NavigationMenuController();
        return $menuController->getUserMenu($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/menu/role', withAuth(
    function($request) {
        $menuController = new \App\Core\NavigationMenuController();
        return $menuController->setRoleMenu($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/menu/role', withAuth(
    function($request) {
        $menuController = new \App\Core\NavigationMenuController();
        return $menuController->getRoleMenu($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/menu/role/copy', withAuth(
    function($request) {
        $menuController = new \App\Core\NavigationMenuController();
        return $menuController->copyRoleMenu($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/menu/access', withAuth(
    function($request) {
        $menuController = new \App\Core\NavigationMenuController();
        return $menuController->checkAccess($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/menu/role/modules', withAuth(
    function($request) {
        $menuController = new \App\Core\NavigationMenuController();
        return $menuController->getRoleModules($request);
    },
    $authMiddleware
));

// Custom Role & Module Creation Module
if (!class_exists('CustomRoleController')) {
    require_once __DIR__ . '/../../core/CustomRoleController.php';
}
if (!class_exists('CustomModuleController')) {
    require_once __DIR__ . '/../../core/CustomModuleController.php';
}

