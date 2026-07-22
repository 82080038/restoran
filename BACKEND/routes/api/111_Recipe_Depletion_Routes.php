<?php

// Recipe Depletion Routes
$router->addRoute('POST', '/api/v1/recipe-depletion/deplete', withAuth(function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->depleteFromOrder($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/recipe-depletion/logs', withAuth(function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->getDepletionLogs($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/recipe-depletion/summary', withAuth(function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->getDepletionSummary($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/recipe-depletion/production-batches', withAuth(function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->getProductionBatches($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/recipe-depletion/production-batches', withAuth(function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->createProductionBatch($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/recipe-depletion/production-batches/{id}/complete', withAuth(function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->completeProductionBatch($request);
}, $authMiddleware));
