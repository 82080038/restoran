<?php

// Batch & Expiry Tracking Routes
$router->addRoute('GET', '/api/v1/batch-expiry/batches', function($request) use ($batchExpiryController) {
    return $batchExpiryController->getBatches($request);
});
$router->addRoute('GET', '/api/v1/batch-expiry/batches/{id}', function($request) use ($batchExpiryController) {
    return $batchExpiryController->getBatch($request);
});
$router->addRoute('POST', '/api/v1/batch-expiry/batches', function($request) use ($batchExpiryController) {
    return $batchExpiryController->receiveBatch($request);
});
$router->addRoute('POST', '/api/v1/batch-expiry/batches/{id}/deduct', function($request) use ($batchExpiryController) {
    return $batchExpiryController->deductFromBatch($request);
});
$router->addRoute('POST', '/api/v1/batch-expiry/batches/{id}/discount', function($request) use ($batchExpiryController) {
    return $batchExpiryController->applyDiscount($request);
});
$router->addRoute('POST', '/api/v1/batch-expiry/batches/{id}/discard', function($request) use ($batchExpiryController) {
    return $batchExpiryController->discardBatch($request);
});
$router->addRoute('GET', '/api/v1/batch-expiry/near-expiry', function($request) use ($batchExpiryController) {
    return $batchExpiryController->getNearExpiry($request);
});
$router->addRoute('GET', '/api/v1/batch-expiry/dashboard', function($request) use ($batchExpiryController) {
    return $batchExpiryController->getExpiryDashboard($request);
});
$router->addRoute('POST', '/api/v1/batch-expiry/update-statuses', function($request) use ($batchExpiryController) {
    return $batchExpiryController->updateBatchStatuses($request);
});
