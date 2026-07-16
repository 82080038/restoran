<?php

// General Ledger Routes
$router->addRoute('GET', '/api/v1/accounting/general-ledger', function($request) use ($generalLedgerController) {
    return $generalLedgerController->getLedger($request);
});
$router->addRoute('GET', '/api/v1/accounting/general-ledger/accounts/{id}/balance', function($request) use ($generalLedgerController) {
    return $generalLedgerController->getAccountBalance($request);
});
$router->addRoute('GET', '/api/v1/accounting/cash-flow', function($request) use ($generalLedgerController) {
    return $generalLedgerController->getCashFlowStatement($request);
});

