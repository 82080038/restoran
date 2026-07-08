<?php

namespace App\Modules\Franchise\Controllers;

use App\Modules\Franchise\Services\AdvancedFranchiseService;
use App\Core\Response;

class AdvancedFranchiseController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdvancedFranchiseService();
    }

    /**
     * Create compliance checklist
     * POST /api/v1/franchise/compliance-checklists
     */
    public function createComplianceChecklist($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createComplianceChecklist($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Record compliance audit
     * POST /api/v1/franchise/compliance-audits
     */
    public function recordComplianceAudit($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->recordComplianceAudit($tenantId, $branchId, $request->franchisee_id ?? null, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get compliance audits
     * GET /api/v1/franchise/compliance-audits
     */
    public function getComplianceAudits($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $franchiseeId = $request->franchisee_id ?? null;
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $audits = $this->service->getComplianceAudits($tenantId, $franchiseeId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $audits
        ]);
    }

    /**
     * Generate franchise report
     * GET /api/v1/franchise/performance-report
     */
    public function generateFranchiseReport($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $franchiseeId = $request->franchisee_id ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $report = $this->service->generateFranchiseReport($tenantId, $franchiseeId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get franchise summary
     * GET /api/v1/franchise/summary
     */
    public function getSummary($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $summary = $this->service->getSummary($tenantId);

        return Response::json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
