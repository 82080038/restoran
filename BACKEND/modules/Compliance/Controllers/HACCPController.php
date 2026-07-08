<?php

namespace App\Modules\Compliance\Controllers;

use App\Modules\Compliance\Services\HACCPService;
use App\Core\Response;

class HACCPController
{
    private $service;

    public function __construct()
    {
        $this->service = new HACCPService();
    }

    /**
     * Create critical control point
     * POST /api/v1/haccp/ccps
     */
    public function createCCP($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createCCP($tenantId, $branchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get critical control points
     * GET /api/v1/haccp/ccps
     */
    public function getCCPs($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $status = $request->status ?? null;

        $ccps = $this->service->getCCPs($tenantId, $branchId, $status);

        return Response::json([
            'success' => true,
            'data' => $ccps
        ]);
    }

    /**
     * Record monitoring
     * POST /api/v1/haccp/monitoring
     */
    public function recordMonitoring($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->recordMonitoring($tenantId, $branchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get monitoring records
     * GET /api/v1/haccp/monitoring
     */
    public function getMonitoringRecords($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $ccpId = $request->ccp_id ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $records = $this->service->getMonitoringRecords($tenantId, $branchId, $ccpId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $records
        ]);
    }

    /**
     * Generate HACCP report
     * GET /api/v1/haccp/report
     */
    public function generateHACCPReport($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $report = $this->service->generateHACCPReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get HACCP summary
     * GET /api/v1/haccp/summary
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
