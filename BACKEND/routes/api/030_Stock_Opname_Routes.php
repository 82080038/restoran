<?php

// Stock Opname Routes
$router->addRoute('POST', '/api/v1/inventory/stock-opname', withAuth(function($request) use ($stockOpnameController) {
    return $stockOpnameController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/inventory/stock-opname', withAuth(function($request) use ($stockOpnameController) {
    return $stockOpnameController->getAll($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/stock-opname/{id}/items', withAuth(function($request) use ($stockOpnameController) {
    return $stockOpnameController->addItem($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/stock-opname/{id}/complete', withAuth(function($request) use ($stockOpnameController) {
    return $stockOpnameController->complete($request);
}, $authMiddleware));

