<?php

// Enterprise Routes
$router->addRoute('POST', '/api/v1/enterprise/shift-schedules', function($request) use ($enterpriseController) {
    return $enterpriseController->createShiftSchedule($request);
});
$router->addRoute('GET', '/api/v1/enterprise/shift-schedules', function($request) use ($enterpriseController) {
    return $enterpriseController->getShiftSchedules($request);
});
$router->addRoute('POST', '/api/v1/enterprise/performance-evaluations', function($request) use ($enterpriseController) {
    return $enterpriseController->createPerformanceEvaluation($request);
});
$router->addRoute('GET', '/api/v1/enterprise/performance-evaluations', function($request) use ($enterpriseController) {
    return $enterpriseController->getPerformanceEvaluations($request);
});
$router->addRoute('POST', '/api/v1/enterprise/cash-flow', function($request) use ($enterpriseController) {
    return $enterpriseController->recordCashFlow($request);
});
$router->addRoute('GET', '/api/v1/enterprise/cash-flow', function($request) use ($enterpriseController) {
    return $enterpriseController->getCashFlow($request);
});
$router->addRoute('POST', '/api/v1/enterprise/budgets', function($request) use ($enterpriseController) {
    return $enterpriseController->createBudget($request);
});
$router->addRoute('GET', '/api/v1/enterprise/budgets', function($request) use ($enterpriseController) {
    return $enterpriseController->getBudgets($request);
});
$router->addRoute('POST', '/api/v1/enterprise/budgets/update-actuals', function($request) use ($enterpriseController) {
    return $enterpriseController->updateBudgetActuals($request);
});

