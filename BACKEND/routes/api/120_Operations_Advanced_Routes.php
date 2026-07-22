<?php

// Operations Advanced Routes (86-ing, Custom Orders, Delivery Routing, Lead Pipeline, Allergen Tracking)

// 86-ing
$router->addRoute('GET', '/api/v1/operations/86-items', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->get86Items($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/86-items', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->set86Status($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/86-items/{product_id}/restock', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->restockItem($request);
}, $authMiddleware));

// Custom Orders
$router->addRoute('GET', '/api/v1/operations/custom-orders', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getCustomOrders($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/custom-orders', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->createCustomOrder($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/operations/custom-orders/{id}/status', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->updateCustomOrderStatus($request);
}, $authMiddleware));

// Delivery Routing
$router->addRoute('GET', '/api/v1/operations/delivery-routes', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getRoutes($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/operations/delivery-routes/{id}', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getRoute($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/delivery-routes', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->createRoute($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/delivery-routes/{id}/stops', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->addRouteStop($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/delivery-routes/{id}/start', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->startRoute($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/delivery-routes/{id}/complete', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->completeRoute($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/operations/delivery-stops/{stop_id}/status', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->updateStopStatus($request);
}, $authMiddleware));

// Lead Pipeline
$router->addRoute('GET', '/api/v1/operations/leads', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getLeads($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/leads', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->createLead($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/operations/leads/{id}/stage', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->updateLeadStage($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/operations/leads/pipeline-summary', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getLeadPipelineSummary($request);
}, $authMiddleware));

// Allergen Tracking
$router->addRoute('GET', '/api/v1/operations/allergens/{product_id}', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->getAllergenInfo($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/operations/allergens', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->setAllergenInfo($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/operations/allergens/filter/{tag}', withAuth(function($request) use ($operationsAdvancedController) {
    return $operationsAdvancedController->filterByDietaryTag($request);
}, $authMiddleware));
