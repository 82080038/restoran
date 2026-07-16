<?php

// Fixed Assets Routes
$router->addRoute('POST', '/api/v1/accounting/fixed-assets', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->createAsset($request);
});
$router->addRoute('GET', '/api/v1/accounting/fixed-assets', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getAssets($request);
});
$router->addRoute('GET', '/api/v1/accounting/fixed-assets/{id}', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getAsset($request);
});
$router->addRoute('POST', '/api/v1/accounting/fixed-assets/depreciation', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->calculateDepreciation($request);
});
$router->addRoute('GET', '/api/v1/accounting/fixed-assets/{id}/depreciation-schedule', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getDepreciationSchedule($request);
});
$router->addRoute('POST', '/api/v1/accounting/fixed-assets/{id}/dispose', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->disposeAsset($request);
});

