<?php

// Settlement System Routes (Live Music Venue)
$router->addRoute('GET', '/api/v1/settlements/deals', withAuth(function($request) use ($settlementController) {
    return $settlementController->getDeals($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements/deals', withAuth(function($request) use ($settlementController) {
    return $settlementController->createDeal($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements/deals/{id}/sign', withAuth(function($request) use ($settlementController) {
    return $settlementController->signDeal($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/settlements', withAuth(function($request) use ($settlementController) {
    return $settlementController->getSettlements($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/settlements/{id}', withAuth(function($request) use ($settlementController) {
    return $settlementController->getSettlement($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements', withAuth(function($request) use ($settlementController) {
    return $settlementController->createSettlement($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements/{id}/items', withAuth(function($request) use ($settlementController) {
    return $settlementController->addSettlementItem($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements/{id}/finalize', withAuth(function($request) use ($settlementController) {
    return $settlementController->finalizeSettlement($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements/{id}/paid', withAuth(function($request) use ($settlementController) {
    return $settlementController->markSettlementPaid($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/settlements/advancing/{concert_id}', withAuth(function($request) use ($settlementController) {
    return $settlementController->getAdvancingSheet($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements/advancing', withAuth(function($request) use ($settlementController) {
    return $settlementController->createAdvancingSheet($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/settlements/advancing/{id}/confirm', withAuth(function($request) use ($settlementController) {
    return $settlementController->confirmAdvancingSheet($request);
}, $authMiddleware));
