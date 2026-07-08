<?php

namespace App\Modules\Quality\Controllers;

use App\Modules\Quality\Services\QualityControlService;
use App\Core\Response;

class QualityControlController
{
    private $service;

    public function __construct()
    {
        $this->service = new QualityControlService();
    }

    /**
     * Create quality check
     * POST /api/v1/quality/checks
     */
    public function createQualityCheck($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createQualityCheck($tenantId, $branchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get quality checks
     * GET /api/v1/quality/checks
     */
    public function getQualityChecks($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $checkType = $request->check_type ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $checks = $this->service->getQualityChecks($tenantId, $branchId, $checkType, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $checks
        ]);
    }

    /**
     * Get non-conformances
     * GET /api/v1/quality/non-conformances
     */
    public function getNonConformances($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $status = $request->status ?? null;

        $ncs = $this->service->getNonConformances($tenantId, $branchId, $status);

        return Response::json([
            'success' => true,
            'data' => $ncs
        ]);
    }

    /**
     * Update non-conformance status
     * PUT /api/v1/quality/non-conformances/{id}/status
     */
    public function updateNonConformanceStatus($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;
        $ncId = $request->id;
        $status = $request->status;

        $result = $this->service->updateNonConformanceStatus($ncId, $status, $userId, $tenantId);

        return Response::json($result);
    }

    /**
     * Get quality metrics
     * GET /api/v1/quality/metrics
     */
    public function getQualityMetrics($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $metrics = $this->service->getQualityMetrics($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get quality summary
     * GET /api/v1/quality/summary
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
