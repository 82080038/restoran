<?php

// Payment Management Routes
$router->addRoute('POST', '/api/v1/sales/credit-notes', withAuth(function($request) use ($paymentManagementController) {
    return $paymentManagementController->createCreditNote($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sales/vouchers', withAuth(function($request) use ($paymentManagementController) {
    return $paymentManagementController->createVoucher($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sales/vouchers/apply', withAuth(function($request) use ($paymentManagementController) {
    return $paymentManagementController->applyVoucher($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sales/cash-drawers/{id}/open', withAuth(function($request) use ($paymentManagementController) {
    return $paymentManagementController->openCashDrawer($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sales/cash-drawers/{id}/close', withAuth(function($request) use ($paymentManagementController) {
    return $paymentManagementController->closeCashDrawer($request);
}, $authMiddleware));

