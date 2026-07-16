<?php

// Budget Management Routes
$router->addRoute('POST', '/api/v1/accounting/budgets', function($request) use ($budgetController) {
    return $budgetController->createBudget($request);
});
$router->addRoute('GET', '/api/v1/accounting/budgets', function($request) use ($budgetController) {
    return $budgetController->getBudgets($request);
});
$router->addRoute('GET', '/api/v1/accounting/budgets/{id}', function($request) use ($budgetController) {
    return $budgetController->getBudget($request);
});
$router->addRoute('POST', '/api/v1/accounting/budgets/items', function($request) use ($budgetController) {
    return $budgetController->addBudgetItem($request);
});
$router->addRoute('POST', '/api/v1/accounting/budgets/{id}/approve', function($request) use ($budgetController) {
    return $budgetController->approveBudget($request);
});
$router->addRoute('GET', '/api/v1/accounting/budgets/{id}/variance', function($request) use ($budgetController) {
    return $budgetController->getBudgetVariance($request);
});

