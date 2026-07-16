<?php

// Multi-Branch Routes
$router->addRoute('POST', '/api/v1/multi-branch/stock-transfers', withAuth(
    function($request) use ($multiBranchController) {
        return $multiBranchController->createStockTransfer($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/multi-branch/stock-transfers', withAuth(
    function($request) use ($multiBranchController) {
        return $multiBranchController->getStockTransfers($request);
    },
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/multi-branch/stock-transfers/{id}/status', withAuth(
    function($request) use ($multiBranchController) {
        return $multiBranchController->updateTransferStatus($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/multi-branch/centralized-purchases', withAuth(
    function($request) use ($multiBranchController) {
        return $multiBranchController->createCentralizedPurchase($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/multi-branch/branch-performance', withAuth(
    function($request) use ($multiBranchController) {
        return $multiBranchController->getBranchPerformance($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/multi-branch/standardize-pricing', withAuth(
    function($request) use ($multiBranchController) {
        return $multiBranchController->standardizePricing($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/multi-branch/summary', withAuth(
    function($request) use ($multiBranchController) {
        return $multiBranchController->getSummary($request);
    },
    $authMiddleware
));

