<?php

// Event Profitability Routes
$router->addRoute('GET', '/api/v1/event-profitability', withAuth(function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->getProfitabilityList($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/event-profitability/{id}', withAuth(function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->getProfitability($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/event-profitability', withAuth(function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->createProfitability($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/event-profitability/{id}/cost-items', withAuth(function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->addCostItem($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/event-profitability/{id}/finalize', withAuth(function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->finalizeProfitability($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/event-profitability/summary', withAuth(function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->getSummary($request);
}, $authMiddleware));
