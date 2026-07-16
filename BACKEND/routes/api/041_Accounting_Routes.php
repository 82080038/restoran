<?php

// Accounting Routes
$router->addRoute('POST', '/api/v1/accounting/journal-entries', function($request) use ($accountingController) {
    return $accountingController->createJournalEntry($request);
});
$router->addRoute('GET', '/api/v1/accounting/trial-balance', function($request) use ($accountingController) {
    return $accountingController->getTrialBalance($request);
});
$router->addRoute('GET', '/api/v1/accounting/balance-sheet', function($request) use ($accountingController) {
    return $accountingController->getBalanceSheet($request);
});
$router->addRoute('GET', '/api/v1/accounting/profit-loss', function($request) use ($accountingController) {
    return $accountingController->getProfitLoss($request);
});

