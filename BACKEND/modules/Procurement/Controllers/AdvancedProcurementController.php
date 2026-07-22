<?php

namespace App\Modules\Procurement\Controllers;

use App\Modules\Procurement\Services\AdvancedProcurementService;
use App\Core\Response;

class AdvancedProcurementController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdvancedProcurementService();
    }

    /**
     * Generate purchase plan
     * POST /api/v1/procurement/purchase-plans
     */
    public function generatePurchasePlan($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->generatePurchasePlan($tenantId, $branchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get purchase plans
     * GET /api/v1/procurement/purchase-plans
     */
    public function getPurchasePlans($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $plans = $this->service->getPurchasePlans($tenantId, $branchId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Perform three-way match
     * POST /api/v1/procurement/three-way-match
     */
    public function performThreeWayMatch($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->performThreeWayMatch($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get three-way matches
     * GET /api/v1/procurement/three-way-matches
     */
    public function getThreeWayMatches($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $matches = $this->service->getThreeWayMatches($tenantId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $matches
        ]);
    }

    /**
     * Forecast stock
     * POST /api/v1/procurement/stock-forecast
     */
    public function forecastStock($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;

        $result = $this->service->forecastStock($tenantId, $branchId, $request);

        return Response::json($result);
    }

    /**
     * Get procurement summary
     * GET /api/v1/procurement/summary
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
