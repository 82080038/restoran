<?php

// Customer Credit Routes
$router->addRoute('POST', '/api/v1/crm/credits', function($request) use ($creditController) {
    return $creditController->createCredit($request);
});
$router->addRoute('POST', '/api/v1/crm/credits/{id}/pay', function($request) use ($creditController) {
    return $creditController->payCredit($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/credits', function($request) use ($creditController) {
    return $creditController->getCustomerCredits($request);
});
$router->addRoute('GET', '/api/v1/crm/credits/overdue', function($request) use ($creditController) {
    return $creditController->getOverdueCredits($request);
});

