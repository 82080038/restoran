<?php

// Event Profitability Routes
$router->addRoute('GET', '/api/v1/event-profitability', function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->getProfitabilityList($request);
});
$router->addRoute('GET', '/api/v1/event-profitability/{id}', function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->getProfitability($request);
});
$router->addRoute('POST', '/api/v1/event-profitability', function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->createProfitability($request);
});
$router->addRoute('POST', '/api/v1/event-profitability/{id}/cost-items', function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->addCostItem($request);
});
$router->addRoute('POST', '/api/v1/event-profitability/{id}/finalize', function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->finalizeProfitability($request);
});
$router->addRoute('GET', '/api/v1/event-profitability/summary', function($request) use ($eventProfitabilityController) {
    return $eventProfitabilityController->getSummary($request);
});
