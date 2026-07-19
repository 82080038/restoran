<?php

namespace App\Modules\OperationsAdvanced\Controllers;

use App\Core\Response;
use App\Modules\OperationsAdvanced\Services\Tier3OperationsService;

class Tier3OperationsController
{
    private $service;

    public function __construct()
    {
        $this->service = new Tier3OperationsService();
    }

    // ==================== AI SALES PREDICTION ====================

    public function getPredictions($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getPredictions(
                $request['tenant_id'], $request['branch_id'] ?? null,
                $request['query']['date_from'] ?? date('Y-m-d'),
                $request['query']['date_to'] ?? date('Y-m-d', strtotime('+7 days'))
            );
            return Response::success($result, 'Predictions retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function generatePrediction($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $date = $request['body']['date'] ?? $request['query']['date'] ?? date('Y-m-d');
            $result = $this->service->generatePrediction($request['tenant_id'], $request['branch_id'] ?? null, $date);
            return Response::success($result, 'Predictions generated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== MULTI-CHANNEL BOOKING SYNC ====================

    public function syncBooking($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['channel_name']) || empty($data['channel_type'])) {
                return Response::error('channel_name and channel_type are required', 400);
            }
            return Response::success($this->service->syncBooking($data), 'Booking synced');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getSyncStatus($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getSyncStatus($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result, 'Sync status retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== ORDER THROTTLING ====================

    public function checkThrottle($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $channel = $request['query']['channel'] ?? 'ONLINE';
            $result = $this->service->checkThrottle($request['tenant_id'], $request['branch_id'] ?? null, $channel);
            return Response::success($result, 'Throttle status retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function setThrottlingConfig($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            return Response::success($this->service->setThrottlingConfig($data), 'Throttling config set');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function pauseThrottling($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $channel = $request['body']['channel'] ?? $request['query']['channel'] ?? 'ALL';
            return Response::success($this->service->pauseThrottling($request['tenant_id'], $request['branch_id'] ?? null, $channel), 'Throttling paused');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function resumeThrottling($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $channel = $request['body']['channel'] ?? $request['query']['channel'] ?? 'ALL';
            return Response::success($this->service->resumeThrottling($request['tenant_id'], $request['branch_id'] ?? null, $channel), 'Throttling resumed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== AUTO PURCHASE ORDER ====================

    public function createAutoPORule($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['inventory_id']) || !isset($data['reorder_point'])) {
                return Response::error('inventory_id and reorder_point are required', 400);
            }
            return Response::success($this->service->createAutoPORule($data), 'Auto PO rule created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function checkAndGeneratePOs($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->checkAndGeneratePOs($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result, 'PO check completed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== DAILY PRODUCTION PLANNING ====================

    public function getProductionPlans($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $date = $request['query']['date'] ?? date('Y-m-d');
            $result = $this->service->getProductionPlans($request['tenant_id'], $request['branch_id'] ?? null, $date);
            return Response::success($result, 'Production plans retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createProductionPlan($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['plan_date']) || empty($data['product_id']) || !isset($data['planned_quantity'])) {
                return Response::error('plan_date, product_id, and planned_quantity are required', 400);
            }
            return Response::success($this->service->createProductionPlan($data), 'Production plan created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateProductionPlan($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $data = $request['body'];
            return Response::success($this->service->updateProductionPlan($id, $data), 'Production plan updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== SERVICE SPEED METRICS ====================

    public function recordServiceMetric($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['order_received_at'])) {
                return Response::error('order_received_at is required', 400);
            }
            return Response::success($this->service->recordServiceMetric($data), 'Service metric recorded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getServiceSpeedReport($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getServiceSpeedReport(
                $request['tenant_id'], $request['branch_id'] ?? null,
                $request['query']['date_from'] ?? date('Y-m-d'),
                $request['query']['date_to'] ?? date('Y-m-d')
            );
            return Response::success($result, 'Service speed report retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}
