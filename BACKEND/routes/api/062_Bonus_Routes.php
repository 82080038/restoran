<?php

// Bonus Routes
$router->addRoute('POST', '/api/v1/hr/bonuses', function($request) use ($bonusController) {
    return $bonusController->createBonus($request);
});
$router->addRoute('POST', '/api/v1/hr/bonuses/{id}/approve', function($request) use ($bonusController) {
    return $bonusController->approveBonus($request);
});
$router->addRoute('POST', '/api/v1/hr/bonuses/{id}/pay', function($request) use ($bonusController) {
    return $bonusController->payBonus($request);
});
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/bonuses', function($request) use ($bonusController) {
    return $bonusController->getEmployeeBonuses($request);
});
$router->addRoute('GET', '/api/v1/hr/bonuses/pending', function($request) use ($bonusController) {
    return $bonusController->getPendingBonuses($request);
});

