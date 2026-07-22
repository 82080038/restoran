<?php

// Integration Routes
$router->addRoute('POST', '/api/v1/integrations/{type}/settings', withAuth(function($request) use ($integrationController) {
    return $integrationController->saveSettings($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/integrations/{type}/settings', withAuth(function($request) use ($integrationController) {
    return $integrationController->getSettings($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/integrations/{type}/test', withAuth(function($request) use ($integrationController) {
    return $integrationController->testConnection($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/integrations/{type}/sync', withAuth(function($request) use ($integrationController) {
    return $integrationController->syncOrder($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/integrations/{type}/logs', withAuth(function($request) use ($integrationController) {
    return $integrationController->getLogs($request);
}, $authMiddleware));

