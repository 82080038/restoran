<?php

// Stock Opname Routes
$router->addRoute('POST', '/api/v1/inventory/stock-opname', function($request) use ($stockOpnameController) {
    return $stockOpnameController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/stock-opname', function($request) use ($stockOpnameController) {
    return $stockOpnameController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-opname/{id}/items', function($request) use ($stockOpnameController) {
    return $stockOpnameController->addItem($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-opname/{id}/complete', function($request) use ($stockOpnameController) {
    return $stockOpnameController->complete($request);
});

