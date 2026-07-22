<?php

// Supplier Routes
$router->addRoute('POST', '/api/v1/inventory/suppliers', withAuth(function($request) use ($supplierController) {
    return $supplierController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/inventory/suppliers', withAuth(function($request) use ($supplierController) {
    return $supplierController->getAll($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/inventory/suppliers/{id}', withAuth(function($request) use ($supplierController) {
    return $supplierController->update($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/inventory/suppliers/{id}', withAuth(function($request) use ($supplierController) {
    return $supplierController->delete($request);
}, $authMiddleware));

