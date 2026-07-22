<?php

// Bonus Routes
$router->addRoute('POST', '/api/v1/hr/bonuses', withAuth(function($request) use ($bonusController) {
    return $bonusController->createBonus($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/hr/bonuses/{id}/approve', withAuth(function($request) use ($bonusController) {
    return $bonusController->approveBonus($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/hr/bonuses/{id}/pay', withAuth(function($request) use ($bonusController) {
    return $bonusController->payBonus($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/bonuses', withAuth(function($request) use ($bonusController) {
    return $bonusController->getEmployeeBonuses($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/hr/bonuses/pending', withAuth(function($request) use ($bonusController) {
    return $bonusController->getPendingBonuses($request);
}, $authMiddleware));

