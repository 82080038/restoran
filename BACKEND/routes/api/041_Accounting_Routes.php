<?php

// Accounting Routes
$router->addRoute('POST', '/api/v1/accounting/journal-entries', withAuth(function($request) use ($accountingController) {
    return $accountingController->createJournalEntry($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/trial-balance', withAuth(function($request) use ($accountingController) {
    return $accountingController->getTrialBalance($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/balance-sheet', withAuth(function($request) use ($accountingController) {
    return $accountingController->getBalanceSheet($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/profit-loss', withAuth(function($request) use ($accountingController) {
    return $accountingController->getProfitLoss($request);
}, $authMiddleware));

