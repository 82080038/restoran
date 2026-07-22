<?php

// Work Order Routes
$router->addRoute('POST', '/api/v1/maintenance/work-orders', withAuth(function($request) use ($workOrderController) {
    return $workOrderController->createWorkOrder($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/maintenance/work-orders/{id}', withAuth(function($request) use ($workOrderController) {
    return $workOrderController->updateWorkOrder($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/maintenance/work-orders', withAuth(function($request) use ($workOrderController) {
    return $workOrderController->getWorkOrders($request);
}, $authMiddleware));

