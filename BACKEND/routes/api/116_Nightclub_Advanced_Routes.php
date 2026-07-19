<?php

// Nightclub Advanced Routes (Table Deposits, Bottle Service Inventory, Promoters)
$router->addRoute('GET', '/api/v1/nightclub-advanced/table-deposits', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getTableDeposits($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->createTableDeposit($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits/{id}/pay', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->markDepositPaid($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits/{id}/forfeit', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->forfeitDeposit($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits/{id}/refund', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->refundDeposit($request);
});
$router->addRoute('GET', '/api/v1/nightclub-advanced/bottle-inventory', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getBottleInventory($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/bottle-inventory', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->addBottleInventory($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/bottle-inventory/assign', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->assignBottle($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/bottle-inventory/assignments/{id}/serve', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->serveBottle($request);
});
$router->addRoute('GET', '/api/v1/nightclub-advanced/promoters', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getPromoters($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/promoters', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->createPromoter($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/promoters/{id}/guests', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->addGuestToList($request);
});
$router->addRoute('POST', '/api/v1/nightclub-advanced/promoters/guests/{id}/checkin', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->checkInGuest($request);
});
$router->addRoute('GET', '/api/v1/nightclub-advanced/promoters/{id}/guest-list', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getPromoterGuestList($request);
});
$router->addRoute('GET', '/api/v1/nightclub-advanced/promoters/{id}/stats', function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getPromoterStats($request);
});
