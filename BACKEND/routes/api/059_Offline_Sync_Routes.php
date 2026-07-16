<?php

// Offline Sync Routes
$router->addRoute('POST', '/api/v1/offline/queue', function($request) use ($offlineSyncController) {
    return $offlineSyncController->queueOperation($request);
});
$router->addRoute('POST', '/api/v1/offline/sync', function($request) use ($offlineSyncController) {
    return $offlineSyncController->syncPending($request);
});
$router->addRoute('POST', '/api/v1/offline/conflicts/{id}/resolve', function($request) use ($offlineSyncController) {
    return $offlineSyncController->resolveConflict($request);
});
$router->addRoute('GET', '/api/v1/offline/status', function($request) use ($offlineSyncController) {
    return $offlineSyncController->getSyncStatus($request);
});
$router->addRoute('GET', '/api/v1/offline/conflicts', function($request) use ($offlineSyncController) {
    return $offlineSyncController->getConflicts($request);
});

