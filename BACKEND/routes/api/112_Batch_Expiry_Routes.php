<?php

// Batch & Expiry Tracking Routes
$router->addRoute('GET', '/api/v1/batch-expiry/batches', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->getBatches($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/batch-expiry/batches/{id}', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->getBatch($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/batch-expiry/batches', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->receiveBatch($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/batch-expiry/batches/{id}/deduct', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->deductFromBatch($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/batch-expiry/batches/{id}/discount', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->applyDiscount($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/batch-expiry/batches/{id}/discard', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->discardBatch($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/batch-expiry/near-expiry', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->getNearExpiry($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/batch-expiry/dashboard', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->getExpiryDashboard($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/batch-expiry/update-statuses', withAuth(function($request) use ($batchExpiryController) {
    return $batchExpiryController->updateBatchStatuses($request);
}, $authMiddleware));
