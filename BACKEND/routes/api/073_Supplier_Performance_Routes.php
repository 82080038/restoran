<?php

// Supplier Performance Routes
$router->addRoute('POST', '/api/v1/supply-chain/supplier-performance', withAuth(function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->evaluateSupplier($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/supply-chain/suppliers/{id}/performance', withAuth(function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->getSupplierPerformance($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/supply-chain/supplier-ranking', withAuth(function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->getSupplierRanking($request);
}, $authMiddleware));

