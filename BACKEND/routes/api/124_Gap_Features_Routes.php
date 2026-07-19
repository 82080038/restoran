<?php

// Gap Features Routes (7 new features: ID Scan, COGS, E-Signature, Corporate Meals, Drive-Thru, Tasting Menu, Reservation Deposit)

use App\Modules\GapFeatures\Controllers\GapFeaturesController;
use App\Modules\GapFeatures\Services\ExternalIntegrationService;

$gapFeaturesController = new GapFeaturesController();
$externalIntegrationService = new ExternalIntegrationService();

// ==================== SCAN ID / VERIFIKASI USIA ====================
$router->addRoute('POST', '/api/v1/gap/id-scan', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->scanId($request);
});
$router->addRoute('GET', '/api/v1/gap/id-scans', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getIdScans($request);
});
$router->addRoute('GET', '/api/v1/gap/id-scans/stats', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getIdScanStats($request);
});

// ==================== COGS MINUMAN KONSOLIDASI ====================
$router->addRoute('POST', '/api/v1/gap/beverage-cogs/generate', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->generateCogsReport($request);
});
$router->addRoute('GET', '/api/v1/gap/beverage-cogs', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getCogsReport($request);
});

// ==================== KONTRAK E-SIGNATURE ====================
$router->addRoute('POST', '/api/v1/gap/contracts', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createContract($request);
});
$router->addRoute('POST', '/api/v1/gap/contracts/{id}/sign', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->signContract($request);
});
$router->addRoute('GET', '/api/v1/gap/contracts', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getContracts($request);
});

// ==================== LANGGANAN MAKAN KORPORAT ====================
$router->addRoute('POST', '/api/v1/gap/corporate-subscriptions', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createSubscription($request);
});
$router->addRoute('GET', '/api/v1/gap/corporate-subscriptions', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getSubscriptions($request);
});
$router->addRoute('POST', '/api/v1/gap/corporate-subscriptions/deliveries', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->recordDelivery($request);
});
$router->addRoute('GET', '/api/v1/gap/corporate-subscriptions/{id}/deliveries', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getDeliveryHistory($request);
});

// ==================== DRIVE-THRU ====================
$router->addRoute('POST', '/api/v1/gap/drive-thru/sessions', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->startDriveThruSession($request);
});
$router->addRoute('PATCH', '/api/v1/gap/drive-thru/sessions/{id}', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->updateDriveThruStatus($request);
});
$router->addRoute('GET', '/api/v1/gap/drive-thru/stats', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getDriveThruStats($request);
});

// ==================== TASTING MENU ====================
$router->addRoute('POST', '/api/v1/gap/tasting-menus', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createTastingMenu($request);
});
$router->addRoute('POST', '/api/v1/gap/tasting-menus/courses', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->addTastingCourse($request);
});
$router->addRoute('GET', '/api/v1/gap/tasting-menus', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getTastingMenus($request);
});
$router->addRoute('GET', '/api/v1/gap/tasting-menus/{id}', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getTastingMenuDetail($request);
});
$router->addRoute('POST', '/api/v1/gap/tasting-menus/reservations', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createTastingReservation($request);
});

// ==================== DEPOSIT RESERVASI ====================
$router->addRoute('POST', '/api/v1/gap/reservation-deposits', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->createReservationDeposit($request);
});
$router->addRoute('GET', '/api/v1/gap/reservation-deposits', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->getReservationDeposits($request);
});
$router->addRoute('POST', '/api/v1/gap/reservation-deposits/{id}/forfeit', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->forfeitDeposit($request);
});
$router->addRoute('POST', '/api/v1/gap/reservation-deposits/{id}/refund', function($request) use ($gapFeaturesController) {
    return $gapFeaturesController->refundDeposit($request);
});

// ==================== E-WALLET / QRIS ====================
$router->addRoute('GET', '/api/v1/gap/ewallet/providers', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        return \App\Core\Response::success($externalIntegrationService->getEwalletProviders(), 'E-wallet providers retrieved');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});
$router->addRoute('POST', '/api/v1/gap/ewallet/qris', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        $result = $externalIntegrationService->createQrisPayment($request['body']);
        return \App\Core\Response::success($result, 'QRIS payment created');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});
$router->addRoute('POST', '/api/v1/gap/ewallet/pay', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        $result = $externalIntegrationService->processEwalletPayment($request['body']);
        return \App\Core\Response::success($result, 'E-wallet payment processed');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});

// ==================== TICKETING PLATFORM SYNC ====================
$router->addRoute('GET', '/api/v1/gap/ticketing/platforms', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        return \App\Core\Response::success($externalIntegrationService->getTicketingPlatforms(), 'Ticketing platforms retrieved');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});
$router->addRoute('POST', '/api/v1/gap/ticketing/sync', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        $result = $externalIntegrationService->syncTicketSales($request['body']);
        return \App\Core\Response::success($result, 'Ticket sales synced');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});

// ==================== OFFLINE MODE ====================
$router->addRoute('GET', '/api/v1/gap/offline/status', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        return \App\Core\Response::success($externalIntegrationService->getOfflineStatus(), 'Offline status retrieved');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});
$router->addRoute('POST', '/api/v1/gap/offline/sync', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        $result = $externalIntegrationService->syncOfflineQueue($request['body']);
        return \App\Core\Response::success($result, 'Offline queue synced');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});

// ==================== LINE BUSTING ====================
$router->addRoute('GET', '/api/v1/gap/line-bust/stats', function($request) use ($externalIntegrationService) {
    try {
        $request = (new \AuthMiddleware())->handle($request);
        $date = $request['query']['date'] ?? date('Y-m-d');
        $data = $externalIntegrationService->getLineBustStats($request['tenant_id'], $request['branch_id'] ?? null, $date);
        return \App\Core\Response::success($data, 'Line bust stats retrieved');
    } catch (\Exception $e) { return \App\Core\Response::error($e->getMessage(), 500); }
});
