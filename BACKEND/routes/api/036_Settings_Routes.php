<?php

// Settings Routes
$router->addRoute('GET', '/api/v1/settings', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->getSettings($request);
    },
    'SETTINGS_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/settings/{key}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->getSetting($request);
    },
    'SETTINGS_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/settings/group/{prefix}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->getSettingGroup($request);
    },
    'SETTINGS_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/settings', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->createSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/settings/{id}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->updateSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/settings/upsert', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->upsertSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/settings/{id}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->deleteSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/settings/initialize', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->initializeSettings($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));

