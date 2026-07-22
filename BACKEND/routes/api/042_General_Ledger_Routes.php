<?php

// General Ledger Routes
$router->addRoute('GET', '/api/v1/accounting/general-ledger', withAuth(function($request) use ($generalLedgerController) {
    return $generalLedgerController->getLedger($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/general-ledger/accounts/{id}/balance', withAuth(function($request) use ($generalLedgerController) {
    return $generalLedgerController->getAccountBalance($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/cash-flow', withAuth(function($request) use ($generalLedgerController) {
    return $generalLedgerController->getCashFlowStatement($request);
}, $authMiddleware));

