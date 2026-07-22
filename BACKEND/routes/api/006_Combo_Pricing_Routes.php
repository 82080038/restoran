<?php

// Combo Pricing Routes
$router->addRoute('POST', '/api/v1/sales/combos', withAuth(function($request) use ($comboController) {
    return $comboController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/sales/combos', withAuth(function($request) use ($comboController) {
    return $comboController->getAll($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/sales/combos/{id}', withAuth(function($request) use ($comboController) {
    return $comboController->get($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/sales/combos/{id}', withAuth(function($request) use ($comboController) {
    return $comboController->update($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/sales/combos/{id}', withAuth(function($request) use ($comboController) {
    return $comboController->delete($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/sales/combos/calculate-price', withAuth(function($request) use ($comboController) {
    return $comboController->calculatePrice($request);
}, $authMiddleware));

