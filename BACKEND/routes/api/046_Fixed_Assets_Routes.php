<?php

// Fixed Assets Routes
$router->addRoute('POST', '/api/v1/accounting/fixed-assets', withAuth(function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->createAsset($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/fixed-assets', withAuth(function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getAssets($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/fixed-assets/{id}', withAuth(function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getAsset($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/fixed-assets/depreciation', withAuth(function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->calculateDepreciation($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/fixed-assets/{id}/depreciation-schedule', withAuth(function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getDepreciationSchedule($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/fixed-assets/{id}/dispose', withAuth(function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->disposeAsset($request);
}, $authMiddleware));

