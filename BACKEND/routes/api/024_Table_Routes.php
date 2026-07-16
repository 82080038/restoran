<?php

// Table Routes
$router->addRoute('GET', '/api/v1/tables', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getTables($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tables/available', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getAvailableTables($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tables/{id}', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getTable($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/tables', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->createTable($request);
    },
    'TABLE_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/tables/{id}', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->updateTable($request);
    },
    'TABLE_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/tables/{id}/status', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->updateTableStatus($request);
    },
    'TABLE_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/tables/{id}', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->deleteTable($request);
    },
    'TABLE_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

