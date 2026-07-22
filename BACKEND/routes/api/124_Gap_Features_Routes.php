<?php

// Gap Features Routes (7 new features: ID Scan, COGS, E-Signature, Corporate Meals, Drive-Thru, Tasting Menu, Reservation Deposit)

use App\Modules\GapFeatures\Controllers\GapFeaturesController;
use App\Modules\GapFeatures\Services\ExternalIntegrationService;

$gapFeaturesController = new GapFeaturesController();
$externalIntegrationService = new ExternalIntegrationService();

// ==================== SCAN ID / VERIFIKASI USIA ====================
$router->addRoute('POST', '/api/v1/gap/id-scan', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->scanId($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/id-scans', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getIdScans($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/id-scans/stats', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getIdScanStats($request);
}, $authMiddleware));

// ==================== COGS MINUMAN KONSOLIDASI ====================
$router->addRoute('POST', '/api/v1/gap/beverage-cogs/generate', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->generateCogsReport($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/beverage-cogs', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getCogsReport($request);
}, $authMiddleware));

// ==================== KONTRAK E-SIGNATURE ====================
$router->addRoute('POST', '/api/v1/gap/contracts', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createContract($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/contracts/{id}/sign', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->signContract($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/contracts', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getContracts($request);
}, $authMiddleware));

// ==================== LANGGANAN MAKAN KORPORAT ====================
$router->addRoute('POST', '/api/v1/gap/corporate-subscriptions', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createSubscription($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/corporate-subscriptions', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getSubscriptions($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/corporate-subscriptions/deliveries', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->recordDelivery($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/corporate-subscriptions/{id}/deliveries', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getDeliveryHistory($request);
}, $authMiddleware));

// ==================== DRIVE-THRU ====================
$router->addRoute('POST', '/api/v1/gap/drive-thru/sessions', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->startDriveThruSession($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/gap/drive-thru/sessions/{id}', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->updateDriveThruStatus($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/drive-thru/stats', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getDriveThruStats($request);
}, $authMiddleware));

// ==================== TASTING MENU ====================
$router->addRoute('POST', '/api/v1/gap/tasting-menus', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createTastingMenu($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/tasting-menus/courses', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->addTastingCourse($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/tasting-menus', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getTastingMenus($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/tasting-menus/{id}', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getTastingMenuDetail($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/tasting-menus/reservations', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createTastingReservation($request);
}, $authMiddleware));

// ==================== DEPOSIT RESERVASI ====================
$router->addRoute('POST', '/api/v1/gap/reservation-deposits', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createReservationDeposit($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/gap/reservation-deposits', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getReservationDeposits($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/reservation-deposits/{id}/forfeit', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->forfeitDeposit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/reservation-deposits/{id}/refund', withAuth(function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->refundDeposit($request);
}, $authMiddleware));

// ==================== E-WALLET / QRIS ====================
$router->addRoute('GET', '/api/v1/gap/ewallet/providers', withAuth(function($request) use ($externalIntegrationService) {
    return \App\Core\Response::success($externalIntegrationService->getEwalletProviders(), 'E-wallet providers retrieved');
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/ewallet/qris', withAuth(function($request) use ($externalIntegrationService) {
    $result = $externalIntegrationService->createQrisPayment($request['body']);
    return \App\Core\Response::success($result, 'QRIS payment created');
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/ewallet/pay', withAuth(function($request) use ($externalIntegrationService) {
    $result = $externalIntegrationService->processEwalletPayment($request['body']);
    return \App\Core\Response::success($result, 'E-wallet payment processed');
}, $authMiddleware));

// ==================== TICKETING PLATFORM SYNC ====================
$router->addRoute('GET', '/api/v1/gap/ticketing/platforms', withAuth(function($request) use ($externalIntegrationService) {
    return \App\Core\Response::success($externalIntegrationService->getTicketingPlatforms(), 'Ticketing platforms retrieved');
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/ticketing/sync', withAuth(function($request) use ($externalIntegrationService) {
    $result = $externalIntegrationService->syncTicketSales($request['body']);
    return \App\Core\Response::success($result, 'Ticket sales synced');
}, $authMiddleware));

// ==================== OFFLINE MODE ====================
$router->addRoute('GET', '/api/v1/gap/offline/status', withAuth(function($request) use ($externalIntegrationService) {
    return \App\Core\Response::success($externalIntegrationService->getOfflineStatus(), 'Offline status retrieved');
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/gap/offline/sync', withAuth(function($request) use ($externalIntegrationService) {
    $result = $externalIntegrationService->syncOfflineQueue($request['body']);
    return \App\Core\Response::success($result, 'Offline queue synced');
}, $authMiddleware));

// ==================== LINE BUSTING ====================
$router->addRoute('GET', '/api/v1/gap/line-bust/stats', withAuth(function($request) use ($externalIntegrationService) {
    $date = $request['query']['date'] ?? date('Y-m-d');
    $data = $externalIntegrationService->getLineBustStats($request['tenant_id'], $request['branch_id'] ?? null, $date);
    return \App\Core\Response::success($data, 'Line bust stats retrieved');
}, $authMiddleware));
