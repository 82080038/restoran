<?php

// User Routes
$router->addRoute('GET', '/api/v1/users', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getUsers($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/users/{id}', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getUser($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/users', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->createUser($request);
    },
    'USER_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/users/{id}', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->updateUser($request);
    },
    'USER_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/users/{id}/change-password', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->changePassword($request);
    },
    'USER_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/users/{id}', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->deleteUser($request);
    },
    'USER_DELETE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/users/with-role', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->createUserWithRole($request);
    },
    'USER_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/users/roles', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getAvailableRoles($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/users/{id}/permissions', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getUserPermissions($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

