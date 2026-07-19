<?php

// Recipe Depletion Routes
$router->addRoute('POST', '/api/v1/recipe-depletion/deplete', function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->depleteFromOrder($request);
});
$router->addRoute('GET', '/api/v1/recipe-depletion/logs', function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->getDepletionLogs($request);
});
$router->addRoute('GET', '/api/v1/recipe-depletion/summary', function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->getDepletionSummary($request);
});
$router->addRoute('GET', '/api/v1/recipe-depletion/production-batches', function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->getProductionBatches($request);
});
$router->addRoute('POST', '/api/v1/recipe-depletion/production-batches', function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->createProductionBatch($request);
});
$router->addRoute('POST', '/api/v1/recipe-depletion/production-batches/{id}/complete', function($request) use ($recipeDepletionController) {
    return $recipeDepletionController->completeProductionBatch($request);
});
