<?php

// Delivery Platform Integration Routes
$router->addRoute('GET', '/api/v1/integrations/delivery', function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->getPlatforms($request);
});
$router->addRoute('POST', '/api/v1/integrations/delivery/{platform}/configure', function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->configurePlatform($request);
});
$router->addRoute('POST', '/api/v1/integrations/delivery/{platform}/sync-menu', function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->syncMenu($request);
});
$router->addRoute('POST', '/api/v1/integrations/delivery/{platform}/webhook', function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->handleWebhook($request);
});
$router->addRoute('GET', '/api/v1/integrations/delivery/{platform}/logs', function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->getSyncLogs($request);
});
$router->addRoute('GET', '/api/v1/integrations/delivery/logs', function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->getSyncLogs($request);
});

// Public webhook endpoint (no auth - verified by signature)
$router->addRoute('POST', '/api/v1/public/integrations/delivery/{platform}/webhook', function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->handleWebhook($request);
});
