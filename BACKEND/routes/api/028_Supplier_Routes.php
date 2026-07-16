<?php

// Supplier Routes
$router->addRoute('POST', '/api/v1/inventory/suppliers', function($request) use ($supplierController) {
    return $supplierController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/suppliers', function($request) use ($supplierController) {
    return $supplierController->getAll($request);
});
$router->addRoute('PUT', '/api/v1/inventory/suppliers/{id}', function($request) use ($supplierController) {
    return $supplierController->update($request);
});
$router->addRoute('DELETE', '/api/v1/inventory/suppliers/{id}', function($request) use ($supplierController) {
    return $supplierController->delete($request);
});

