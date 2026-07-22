<?php

// Budget Management Routes
$router->addRoute('POST', '/api/v1/accounting/budgets', withAuth(function($request) use ($budgetController) {
    return $budgetController->createBudget($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/budgets', withAuth(function($request) use ($budgetController) {
    return $budgetController->getBudgets($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/budgets/{id}', withAuth(function($request) use ($budgetController) {
    return $budgetController->getBudget($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/budgets/items', withAuth(function($request) use ($budgetController) {
    return $budgetController->addBudgetItem($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/accounting/budgets/{id}/approve', withAuth(function($request) use ($budgetController) {
    return $budgetController->approveBudget($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/accounting/budgets/{id}/variance', withAuth(function($request) use ($budgetController) {
    return $budgetController->getBudgetVariance($request);
}, $authMiddleware));

