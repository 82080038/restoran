<?php

// Enterprise Routes
$router->addRoute('POST', '/api/v1/enterprise/shift-schedules', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->createShiftSchedule($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/enterprise/shift-schedules', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->getShiftSchedules($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/enterprise/performance-evaluations', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->createPerformanceEvaluation($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/enterprise/performance-evaluations', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->getPerformanceEvaluations($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/enterprise/cash-flow', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->recordCashFlow($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/enterprise/cash-flow', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->getCashFlow($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/enterprise/budgets', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->createBudget($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/enterprise/budgets', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->getBudgets($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/enterprise/budgets/update-actuals', withAuth(function($request) use ($enterpriseController) {
    return $enterpriseController->updateBudgetActuals($request);
}, $authMiddleware));

