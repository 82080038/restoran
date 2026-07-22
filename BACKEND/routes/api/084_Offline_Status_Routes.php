<?php

// Offline Status Routes
$router->addRoute('GET', '/api/v1/offline/status', withAuth(function($request) use ($offlineStatusController) {
    return $offlineStatusController->getStatus($request);
}, $authMiddleware));

// Public offline status check (no authentication required)
$router->addRoute('GET', '/api/v1/public/offline/status', withAuth(function($request) use ($offlineStatusController) {
    return $offlineStatusController->getPublicStatus($request);
}, $authMiddleware));

