<?php

// Menu Engineering Routes
$router->addRoute('GET', '/api/v1/menu-engineering/profitability/{product_id}', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getProfitability($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/menu-mix', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getMenuMix($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/category-performance', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getCategoryPerformance($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/recommendations', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getRecommendations($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/food-cost-variance', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getFoodCostVariance($request);
    },
    $authMiddleware
));

