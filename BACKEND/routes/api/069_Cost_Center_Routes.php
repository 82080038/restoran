<?php

// Cost Center Routes
$router->addRoute('POST', '/api/v1/accounting/cost-centers', function($request) use ($costCenterController) {
    return $costCenterController->createCostCenter($request);
});
$router->addRoute('GET', '/api/v1/accounting/cost-centers', function($request) use ($costCenterController) {
    return $costCenterController->getCostCenters($request);
});
$router->addRoute('GET', '/api/v1/accounting/cost-centers/{id}/report', function($request) use ($costCenterController) {
    return $costCenterController->getCostCenterReport($request);
});
$router->addRoute('PUT', '/api/v1/accounting/cost-centers/{id}', function($request) use ($costCenterController) {
    return $costCenterController->updateCostCenter($request);
});

