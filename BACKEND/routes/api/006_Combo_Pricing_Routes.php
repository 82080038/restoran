<?php

// Combo Pricing Routes
$router->addRoute('POST', '/api/v1/sales/combos', function($request) use ($comboController) {
    return $comboController->create($request);
});
$router->addRoute('GET', '/api/v1/sales/combos', function($request) use ($comboController) {
    return $comboController->getAll($request);
});
$router->addRoute('GET', '/api/v1/sales/combos/{id}', function($request) use ($comboController) {
    return $comboController->get($request);
});
$router->addRoute('PUT', '/api/v1/sales/combos/{id}', function($request) use ($comboController) {
    return $comboController->update($request);
});
$router->addRoute('DELETE', '/api/v1/sales/combos/{id}', function($request) use ($comboController) {
    return $comboController->delete($request);
});
$router->addRoute('POST', '/api/v1/sales/combos/calculate-price', function($request) use ($comboController) {
    return $comboController->calculatePrice($request);
});

