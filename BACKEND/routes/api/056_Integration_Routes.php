<?php

// Integration Routes
$router->addRoute('POST', '/api/v1/integrations/{type}/settings', function($request) use ($integrationController) {
    return $integrationController->saveSettings($request);
});
$router->addRoute('GET', '/api/v1/integrations/{type}/settings', function($request) use ($integrationController) {
    return $integrationController->getSettings($request);
});
$router->addRoute('POST', '/api/v1/integrations/{type}/test', function($request) use ($integrationController) {
    return $integrationController->testConnection($request);
});
$router->addRoute('POST', '/api/v1/integrations/{type}/sync', function($request) use ($integrationController) {
    return $integrationController->syncOrder($request);
});
$router->addRoute('GET', '/api/v1/integrations/{type}/logs', function($request) use ($integrationController) {
    return $integrationController->getLogs($request);
});

