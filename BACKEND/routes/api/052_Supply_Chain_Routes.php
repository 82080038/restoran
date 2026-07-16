<?php

// Supply Chain Routes
$router->addRoute('POST', '/api/v1/supply-chain/requisitions', function($request) use ($supplyChainController) {
    return $supplyChainController->createRequisition($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/requisitions', function($request) use ($supplyChainController) {
    return $supplyChainController->getAll($request);
});
$router->addRoute('POST', '/api/v1/supply-chain/requisitions/{id}/approve', function($request) use ($supplyChainController) {
    return $supplyChainController->approveRequisition($request);
});

