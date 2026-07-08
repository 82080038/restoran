<?php

namespace App\Modules\Delivery\Controllers;

use App\Modules\Delivery\Services\AdvancedDeliveryService;
use App\Core\Response;

class AdvancedDeliveryController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdvancedDeliveryService();
    }

    /**
     * Create driver
     * POST /api/v1/delivery/drivers
     */
    public function createDriver($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createDriver($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get drivers
     * GET /api/v1/delivery/drivers
     */
    public function getDrivers($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $status = $request->status ?? null;

        $drivers = $this->service->getDrivers($tenantId, $status);

        return Response::json([
            'success' => true,
            'data' => $drivers
        ]);
    }

    /**
     * Optimize route
     * POST /api/v1/delivery/routes/optimize
     */
    public function optimizeRoute($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->optimizeRoute($tenantId, $branchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get delivery routes
     * GET /api/v1/delivery/routes
     */
    public function getDeliveryRoutes($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $routes = $this->service->getDeliveryRoutes($tenantId, $branchId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $routes
        ]);
    }

    /**
     * Track delivery location
     * POST /api/v1/delivery/tracking
     */
    public function trackDeliveryLocation($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $result = $this->service->trackDeliveryLocation($tenantId, $request);

        return Response::json($result);
    }

    /**
     * Get delivery tracking
     * GET /api/v1/delivery/tracking/{delivery_order_id}
     */
    public function getDeliveryTracking($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $deliveryOrderId = $request->delivery_order_id;

        $tracking = $this->service->getDeliveryTracking($deliveryOrderId, $tenantId);

        return Response::json([
            'success' => true,
            'data' => $tracking
        ]);
    }

    /**
     * Send customer notification
     * POST /api/v1/delivery/notifications
     */
    public function sendCustomerNotification($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->sendCustomerNotification($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get delivery notifications
     * GET /api/v1/delivery/notifications
     */
    public function getDeliveryNotifications($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $deliveryOrderId = $request->delivery_order_id ?? null;
        $status = $request->status ?? null;

        $notifications = $this->service->getDeliveryNotifications($tenantId, $deliveryOrderId, $status);

        return Response::json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get delivery summary
     * GET /api/v1/delivery/summary
     */
    public function getSummary($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;

        $summary = $this->service->getSummary($tenantId, $branchId);

        return Response::json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
