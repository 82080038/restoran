<?php

// Customer Credit Routes
$router->addRoute('POST', '/api/v1/crm/credits', withAuth(function($request) use ($creditController) {
    return $creditController->createCredit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/crm/credits/{id}/pay', withAuth(function($request) use ($creditController) {
    return $creditController->payCredit($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/credits', withAuth(function($request) use ($creditController) {
    return $creditController->getCustomerCredits($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/credits/overdue', withAuth(function($request) use ($creditController) {
    return $creditController->getOverdueCredits($request);
}, $authMiddleware));

