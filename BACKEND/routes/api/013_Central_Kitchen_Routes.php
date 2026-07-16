<?php

// Central Kitchen Routes
$router->addRoute('POST', '/api/v1/central-kitchen/production-plans', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->createProductionPlan($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/central-kitchen/production-plans', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->getProductionPlans($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/central-kitchen/production-plans/{id}', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->getProductionPlanDetails($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/central-kitchen/production-plans/{id}/ingredient-requirements', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->calculateIngredientRequirements($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/central-kitchen/recipes/standardize', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->standardizeRecipe($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/central-kitchen/yields', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->trackYield($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/central-kitchen/yields', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->getYieldAnalytics($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/central-kitchen/distributions', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->createDistributionOrder($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/central-kitchen/distributions', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->getDistributionOrders($request);
    },
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/central-kitchen/distributions/{id}/status', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->updateDistributionStatus($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/central-kitchen/summary', withAuth(
    function($request) use ($centralKitchenController) {
        return $centralKitchenController->getSummary($request);
    },
    $authMiddleware
));

