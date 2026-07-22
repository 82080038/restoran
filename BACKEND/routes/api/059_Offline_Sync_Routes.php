<?php

// Offline Sync Routes
$router->addRoute('POST', '/api/v1/offline/queue', withAuth(function($request) use ($offlineSyncController) {
    return $offlineSyncController->queueOperation($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/offline/sync', withAuth(function($request) use ($offlineSyncController) {
    return $offlineSyncController->syncPending($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/offline/conflicts/{id}/resolve', withAuth(function($request) use ($offlineSyncController) {
    return $offlineSyncController->resolveConflict($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/offline/status', withAuth(function($request) use ($offlineSyncController) {
    return $offlineSyncController->getSyncStatus($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/offline/conflicts', withAuth(function($request) use ($offlineSyncController) {
    return $offlineSyncController->getConflicts($request);
}, $authMiddleware));

