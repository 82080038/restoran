<?php

// Tenant Routes
$router->addRoute('POST', '/api/v1/tenant/register', withAuth(function($request) use ($tenantController) {
    return $tenantController->register($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/tenants', withAuth(function($request) use ($tenantController) {
    return $tenantController->getTenants($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/tenants/{id}', withAuth(function($request) use ($tenantController) {
    return $tenantController->getTenant($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/tenant/configure', withAuth(function($request) use ($tenantController) {
    return $tenantController->configure($request);
}, $authMiddleware));

