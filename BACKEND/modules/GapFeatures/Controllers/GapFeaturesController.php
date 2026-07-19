<?php

namespace App\Modules\GapFeatures\Controllers;

use App\Core\Response;
use App\Modules\GapFeatures\Services\GapFeaturesService;

class GapFeaturesController
{
    private $service;

    public function __construct()
    {
        $this->service = new GapFeaturesService();
    }

    // ==================== SCAN ID ====================

    public function scanId($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->scanId($data);
            return Response::success($result, 'ID scan completed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getIdScans($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $eventId = $request['query']['event_id'] ?? null;
            $date = $request['query']['date'] ?? null;
            $data = $this->service->getIdScans($request['tenant_id'], $request['branch_id'] ?? null, $eventId, $date);
            return Response::success($data, 'ID scans retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getIdScanStats($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $date = $request['query']['date'] ?? date('Y-m-d');
            $data = $this->service->getIdScanStats($request['tenant_id'], $request['branch_id'] ?? null, $date);
            return Response::success($data, 'ID scan stats retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== COGS MINUMAN ====================

    public function generateCogsReport($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $date = $request['query']['date'] ?? $request['body']['date'] ?? date('Y-m-d');
            $data = $this->service->generateCogsReport($request['tenant_id'], $request['branch_id'] ?? null, $date);
            return Response::success($data, 'COGS report generated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getCogsReport($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $date = $request['query']['date'] ?? date('Y-m-d');
            $data = $this->service->getCogsReport($request['tenant_id'], $request['branch_id'] ?? null, $date);
            return Response::success($data, 'COGS report retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== E-SIGNATURE ====================

    public function createContract($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->createContract($data);
            return Response::success($result, 'Contract created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function signContract($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['body']['signature_id'];
            $sigData = $request['body']['signature_data'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $result = $this->service->signContract($id, $sigData, $ip);
            return Response::success($result, 'Contract signed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getContracts($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $status = $request['query']['status'] ?? null;
            $data = $this->service->getContracts($request['tenant_id'], $request['branch_id'] ?? null, $status);
            return Response::success($data, 'Contracts retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== CORPORATE MEAL SUBSCRIPTION ====================

    public function createSubscription($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->createSubscription($data);
            return Response::success($result, 'Subscription created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getSubscriptions($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $status = $request['query']['status'] ?? null;
            $data = $this->service->getSubscriptions($request['tenant_id'], $request['branch_id'] ?? null, $status);
            return Response::success($data, 'Subscriptions retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function recordDelivery($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->recordDelivery($data);
            return Response::success($result, 'Delivery recorded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getDeliveryHistory($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $subId = $request['params']['id'] ?? $request['query']['subscription_id'];
            $data = $this->service->getDeliveryHistory($subId);
            return Response::success($data, 'Delivery history retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== DRIVE-THRU ====================

    public function startDriveThruSession($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->startDriveThruSession($data);
            return Response::success($result, 'Drive-thru session started');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateDriveThruStatus($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'];
            $status = $request['body']['status'];
            $orderId = $request['body']['order_id'] ?? null;
            $orderTotal = $request['body']['order_total'] ?? null;
            $result = $this->service->updateDriveThruStatus($id, $status, $orderId, $orderTotal);
            return Response::success($result, 'Drive-thru status updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getDriveThruStats($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $date = $request['query']['date'] ?? date('Y-m-d');
            $data = $this->service->getDriveThruStats($request['tenant_id'], $request['branch_id'] ?? null, $date);
            return Response::success($data, 'Drive-thru stats retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== TASTING MENU ====================

    public function createTastingMenu($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->createTastingMenu($data);
            return Response::success($result, 'Tasting menu created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addTastingCourse($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $result = $this->service->addTastingCourse($data);
            return Response::success($result, 'Course added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getTastingMenus($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $this->service->getTastingMenus($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($data, 'Tasting menus retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getTastingMenuDetail($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'];
            $data = $this->service->getTastingMenuDetail($id);
            return Response::success($data, 'Tasting menu detail retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createTastingReservation($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->createTastingReservation($data);
            return Response::success($result, 'Tasting reservation created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== DEPOSIT RESERVASI ====================

    public function createReservationDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $result = $this->service->createReservationDeposit($data);
            return Response::success($result, 'Reservation deposit created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getReservationDeposits($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $status = $request['query']['status'] ?? null;
            $date = $request['query']['date'] ?? null;
            $data = $this->service->getReservationDeposits($request['tenant_id'], $request['branch_id'] ?? null, $status, $date);
            return Response::success($data, 'Reservation deposits retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function forfeitDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'];
            $result = $this->service->forfeitDeposit($id);
            return Response::success($result, 'Deposit forfeited');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function refundDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'];
            $result = $this->service->refundDeposit($id);
            return Response::success($result, 'Deposit refunded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}
