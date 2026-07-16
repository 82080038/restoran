<?php

// Upload Routes
$router->addRoute('POST', '/api/v1/upload/image', function($request) use ($uploadController) {
    return $uploadController->uploadImage($request);
});
$router->addRoute('DELETE', '/api/v1/upload/image', function($request) use ($uploadController) {
    return $uploadController->deleteImage($request);
});

// Menu Routes (with authentication and permission check)
$router->addRoute('GET', '/api/v1/menu/categories', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getCategories($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/products', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getProducts($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Table Routes (with authentication and permission check)
$router->addRoute('GET', '/api/v1/tables', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getTables($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

