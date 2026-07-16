<?php

// Payment Management Routes
$router->addRoute('POST', '/api/v1/sales/credit-notes', function($request) use ($paymentManagementController) {
    return $paymentManagementController->createCreditNote($request);
});
$router->addRoute('POST', '/api/v1/sales/vouchers', function($request) use ($paymentManagementController) {
    return $paymentManagementController->createVoucher($request);
});
$router->addRoute('POST', '/api/v1/sales/vouchers/apply', function($request) use ($paymentManagementController) {
    return $paymentManagementController->applyVoucher($request);
});
$router->addRoute('POST', '/api/v1/sales/cash-drawers/{id}/open', function($request) use ($paymentManagementController) {
    return $paymentManagementController->openCashDrawer($request);
});
$router->addRoute('POST', '/api/v1/sales/cash-drawers/{id}/close', function($request) use ($paymentManagementController) {
    return $paymentManagementController->closeCashDrawer($request);
});

