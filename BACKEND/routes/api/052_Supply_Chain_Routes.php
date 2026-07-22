<?php

// Supply Chain Routes
$router->addRoute('POST', '/api/v1/supply-chain/requisitions', withAuth(function($request) use ($supplyChainController) {
    return $supplyChainController->createRequisition($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/supply-chain/requisitions', withAuth(function($request) use ($supplyChainController) {
    return $supplyChainController->getAll($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/supply-chain/requisitions/{id}/approve', withAuth(function($request) use ($supplyChainController) {
    return $supplyChainController->approveRequisition($request);
}, $authMiddleware));

