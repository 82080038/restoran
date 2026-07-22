<?php

// Nightclub Advanced Routes (Table Deposits, Bottle Service Inventory, Promoters)
$router->addRoute('GET', '/api/v1/nightclub-advanced/table-deposits', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getTableDeposits($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->createTableDeposit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits/{id}/pay', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->markDepositPaid($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits/{id}/forfeit', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->forfeitDeposit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/table-deposits/{id}/refund', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->refundDeposit($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/nightclub-advanced/bottle-inventory', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getBottleInventory($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/bottle-inventory', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->addBottleInventory($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/bottle-inventory/assign', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->assignBottle($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/bottle-inventory/assignments/{id}/serve', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->serveBottle($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/nightclub-advanced/promoters', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getPromoters($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/promoters', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->createPromoter($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/promoters/{id}/guests', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->addGuestToList($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub-advanced/promoters/guests/{id}/checkin', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->checkInGuest($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/nightclub-advanced/promoters/{id}/guest-list', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getPromoterGuestList($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/nightclub-advanced/promoters/{id}/stats', withAuth(function($request) use ($nightclubAdvancedController) {
    return $nightclubAdvancedController->getPromoterStats($request);
}, $authMiddleware));
