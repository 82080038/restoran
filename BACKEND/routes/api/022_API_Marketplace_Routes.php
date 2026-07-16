<?php

// API Marketplace Routes
$router->addRoute('POST', '/api/v1/api-marketplace/keys', withAuth(
    function($request) use ($apiMarketplaceController) {
        return $apiMarketplaceController->generateAPIKey($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/api-marketplace/keys', withAuth(
    function($request) use ($apiMarketplaceController) {
        return $apiMarketplaceController->getAPIKeys($request);
    },
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/api-marketplace/keys/{id}', withAuth(
    function($request) use ($apiMarketplaceController) {
        return $apiMarketplaceController->revokeAPIKey($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/api-marketplace/webhooks', withAuth(
    function($request) use ($apiMarketplaceController) {
        return $apiMarketplaceController->createWebhook($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/api-marketplace/webhooks', withAuth(
    function($request) use ($apiMarketplaceController) {
        return $apiMarketplaceController->getWebhooks($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/api-marketplace/analytics', withAuth(
    function($request) use ($apiMarketplaceController) {
        return $apiMarketplaceController->getAPIAnalytics($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/api-marketplace/summary', withAuth(
    function($request) use ($apiMarketplaceController) {
        return $apiMarketplaceController->getSummary($request);
    },
    $authMiddleware
));

