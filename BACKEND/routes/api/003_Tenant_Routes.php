<?php

// Tenant Routes
$router->addRoute('POST', '/api/v1/tenant/register', function($request) use ($tenantController) {
    return $tenantController->register($request);
});
$router->addRoute('GET', '/api/v1/tenants', function($request) use ($tenantController) {
    return $tenantController->getTenants($request);
});
$router->addRoute('GET', '/api/v1/tenants/{id}', function($request) use ($tenantController) {
    return $tenantController->getTenant($request);
});
$router->addRoute('POST', '/api/v1/tenant/configure', function($request) use ($tenantController) {
    return $tenantController->configure($request);
});

