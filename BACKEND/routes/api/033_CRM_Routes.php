<?php

// CRM Routes
$router->addRoute('POST', '/api/v1/crm/customers', function($request) use ($customerController) {
    return $customerController->create($request);
});
$router->addRoute('GET', '/api/v1/crm/customers', function($request) use ($customerController) {
    return $customerController->getAll($request);
});
$router->addRoute('PUT', '/api/v1/crm/customers/{id}', function($request) use ($customerController) {
    return $customerController->update($request);
});
$router->addRoute('POST', '/api/v1/crm/customers/{id}/loyalty-points', function($request) use ($customerController) {
    return $customerController->addLoyaltyPoints($request);
});
$router->addRoute('POST', '/api/v1/crm/customers/{id}/visit', function($request) use ($customerController) {
    return $customerController->recordVisit($request);
});

