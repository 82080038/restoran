<?php

// Supplier Performance Routes
$router->addRoute('POST', '/api/v1/supply-chain/supplier-performance', function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->evaluateSupplier($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/suppliers/{id}/performance', function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->getSupplierPerformance($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/supplier-ranking', function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->getSupplierRanking($request);
});

