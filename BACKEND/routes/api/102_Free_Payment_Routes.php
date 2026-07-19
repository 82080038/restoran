<?php

// Free Payment Routes (Zero-fee payment methods)
// 1. Upload Bukti Transfer for bank_transfer
// 2. QRIS Static QR code
// 3. Internal Wallet / Prepaid

// === MODULE 1: TRANSFER PROOF ===
$router->addRoute('POST', '/api/v1/free-payment/transfer-proof/upload', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->uploadTransferProof($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/free-payment/transfer-proof', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->getTransferProofs($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/free-payment/transfer-proof/{id}/verify', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->verifyTransferProof($request);
    },
    $authMiddleware
));

// === MODULE 2: QRIS STATIC ===
$router->addRoute('GET', '/api/v1/free-payment/qris', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->getQrisConfig($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/free-payment/qris', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->saveQrisConfig($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/free-payment/qris/generate', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->generateQrisPayment($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/free-payment/qris/confirm', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->confirmQrisPayment($request);
    },
    $authMiddleware
));

// === MODULE 3: INTERNAL WALLET ===
$router->addRoute('GET', '/api/v1/free-payment/wallet', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->getWallet($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/free-payment/wallet/topup', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->requestTopup($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/free-payment/wallet/topup/{id}/verify', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->verifyTopup($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/free-payment/wallet/pay', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->payWithWallet($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/free-payment/wallet/transactions', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->getWalletTransactions($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/free-payment/wallet/topups', withAuth(
    function($request) use ($freePaymentController) {
        return $freePaymentController->getTopupRequests($request);
    },
    $authMiddleware
));
