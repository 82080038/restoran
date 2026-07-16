<?php

// Offline Status Routes
$router->addRoute('GET', '/api/v1/offline/status', function($request) use ($offlineStatusController) {
    return $offlineStatusController->getStatus($request);
});

// Public offline status check (no authentication required)
$router->addRoute('GET', '/api/v1/public/offline/status', function($request) use ($offlineStatusController) {
    return $offlineStatusController->getPublicStatus($request);
});

