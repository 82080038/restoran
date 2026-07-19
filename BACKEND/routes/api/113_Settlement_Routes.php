<?php

// Settlement System Routes (Live Music Venue)
$router->addRoute('GET', '/api/v1/settlements/deals', function($request) use ($settlementController) {
    return $settlementController->getDeals($request);
});
$router->addRoute('POST', '/api/v1/settlements/deals', function($request) use ($settlementController) {
    return $settlementController->createDeal($request);
});
$router->addRoute('POST', '/api/v1/settlements/deals/{id}/sign', function($request) use ($settlementController) {
    return $settlementController->signDeal($request);
});
$router->addRoute('GET', '/api/v1/settlements', function($request) use ($settlementController) {
    return $settlementController->getSettlements($request);
});
$router->addRoute('GET', '/api/v1/settlements/{id}', function($request) use ($settlementController) {
    return $settlementController->getSettlement($request);
});
$router->addRoute('POST', '/api/v1/settlements', function($request) use ($settlementController) {
    return $settlementController->createSettlement($request);
});
$router->addRoute('POST', '/api/v1/settlements/{id}/items', function($request) use ($settlementController) {
    return $settlementController->addSettlementItem($request);
});
$router->addRoute('POST', '/api/v1/settlements/{id}/finalize', function($request) use ($settlementController) {
    return $settlementController->finalizeSettlement($request);
});
$router->addRoute('POST', '/api/v1/settlements/{id}/paid', function($request) use ($settlementController) {
    return $settlementController->markSettlementPaid($request);
});
$router->addRoute('GET', '/api/v1/settlements/advancing/{concert_id}', function($request) use ($settlementController) {
    return $settlementController->getAdvancingSheet($request);
});
$router->addRoute('POST', '/api/v1/settlements/advancing', function($request) use ($settlementController) {
    return $settlementController->createAdvancingSheet($request);
});
$router->addRoute('POST', '/api/v1/settlements/advancing/{id}/confirm', function($request) use ($settlementController) {
    return $settlementController->confirmAdvancingSheet($request);
});
