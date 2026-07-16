<?php

// Work Order Routes
$router->addRoute('POST', '/api/v1/maintenance/work-orders', function($request) use ($workOrderController) {
    return $workOrderController->createWorkOrder($request);
});
$router->addRoute('PUT', '/api/v1/maintenance/work-orders/{id}', function($request) use ($workOrderController) {
    return $workOrderController->updateWorkOrder($request);
});
$router->addRoute('GET', '/api/v1/maintenance/work-orders', function($request) use ($workOrderController) {
    return $workOrderController->getWorkOrders($request);
});

