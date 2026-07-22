<?php

// Delivery Platform Integration Routes
$router->addRoute('GET', '/api/v1/integrations/delivery', withAuth(function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->getPlatforms($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/integrations/delivery/{platform}/configure', withAuth(function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->configurePlatform($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/integrations/delivery/{platform}/sync-menu', withAuth(function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->syncMenu($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/integrations/delivery/{platform}/webhook', withAuth(function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->handleWebhook($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/integrations/delivery/{platform}/logs', withAuth(function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->getSyncLogs($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/integrations/delivery/logs', withAuth(function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->getSyncLogs($request);
}, $authMiddleware));

// Public webhook endpoint (no auth - verified by signature)
$router->addRoute('POST', '/api/v1/public/integrations/delivery/{platform}/webhook', withAuth(function($request) use ($deliveryIntegrationController) {
    return $deliveryIntegrationController->handleWebhook($request);
}, $authMiddleware));
