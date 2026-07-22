<?php

// Cost Center Routes
$router->addRoute('POST', '/api/v1/accounting/cost-centers', withAuth(function($request) use ($costCenterController) {
    return $costCenterController->createCostCenter($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/cost-centers', withAuth(function($request) use ($costCenterController) {
    return $costCenterController->getCostCenters($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/cost-centers/{id}/report', withAuth(function($request) use ($costCenterController) {
    return $costCenterController->getCostCenterReport($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/accounting/cost-centers/{id}', withAuth(function($request) use ($costCenterController) {
    return $costCenterController->updateCostCenter($request);
}, $authMiddleware));

