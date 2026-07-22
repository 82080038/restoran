<?php

// CRM Routes
$router->addRoute('POST', '/api/v1/crm/customers', withAuth(function($request) use ($customerController) {
    return $customerController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/customers', withAuth(function($request) use ($customerController) {
    return $customerController->getAll($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/crm/customers/{id}', withAuth(function($request) use ($customerController) {
    return $customerController->update($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/crm/customers/{id}/loyalty-points', withAuth(function($request) use ($customerController) {
    return $customerController->addLoyaltyPoints($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/crm/customers/{id}/visit', withAuth(function($request) use ($customerController) {
    return $customerController->recordVisit($request);
}, $authMiddleware));

