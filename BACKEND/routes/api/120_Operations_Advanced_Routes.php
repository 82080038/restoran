<?php

// Operations Advanced Routes (86-ing, Custom Orders, Delivery Routing, Lead Pipeline, Allergen Tracking)

// 86-ing
$router->addRoute('GET', '/api/v1/operations/86-items', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->get86Items($request);
});
$router->addRoute('POST', '/api/v1/operations/86-items', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->set86Status($request);
});
$router->addRoute('POST', '/api/v1/operations/86-items/{product_id}/restock', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->restockItem($request);
});

// Custom Orders
$router->addRoute('GET', '/api/v1/operations/custom-orders', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getCustomOrders($request);
});
$router->addRoute('POST', '/api/v1/operations/custom-orders', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->createCustomOrder($request);
});
$router->addRoute('PATCH', '/api/v1/operations/custom-orders/{id}/status', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->updateCustomOrderStatus($request);
});

// Delivery Routing
$router->addRoute('GET', '/api/v1/operations/delivery-routes', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getRoutes($request);
});
$router->addRoute('GET', '/api/v1/operations/delivery-routes/{id}', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getRoute($request);
});
$router->addRoute('POST', '/api/v1/operations/delivery-routes', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->createRoute($request);
});
$router->addRoute('POST', '/api/v1/operations/delivery-routes/{id}/stops', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->addRouteStop($request);
});
$router->addRoute('POST', '/api/v1/operations/delivery-routes/{id}/start', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->startRoute($request);
});
$router->addRoute('POST', '/api/v1/operations/delivery-routes/{id}/complete', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->completeRoute($request);
});
$router->addRoute('PATCH', '/api/v1/operations/delivery-stops/{stop_id}/status', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->updateStopStatus($request);
});

// Lead Pipeline
$router->addRoute('GET', '/api/v1/operations/leads', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getLeads($request);
});
$router->addRoute('POST', '/api/v1/operations/leads', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->createLead($request);
});
$router->addRoute('PATCH', '/api/v1/operations/leads/{id}/stage', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->updateLeadStage($request);
});
$router->addRoute('GET', '/api/v1/operations/leads/pipeline-summary', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getLeadPipelineSummary($request);
});

// Allergen Tracking
$router->addRoute('GET', '/api/v1/operations/allergens/{product_id}', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getAllergenInfo($request);
});
$router->addRoute('POST', '/api/v1/operations/allergens', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->setAllergenInfo($request);
});
$router->addRoute('GET', '/api/v1/operations/allergens/filter/{tag}', function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->filterByDietaryTag($request);
});
