<?php

namespace App\Modules\HR\Controllers;

use App\Modules\HR\Services\AdvancedHRService;
use App\Core\Response;

class AdvancedHRController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdvancedHRService();
    }

    /**
     * Create multi-location schedule
     * POST /api/v1/hr/multi-location-schedules
     */
    public function createMultiLocationSchedule($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createMultiLocationSchedule($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get multi-location schedules
     * GET /api/v1/hr/multi-location-schedules
     */
    public function getMultiLocationSchedules($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $schedules = $this->service->getMultiLocationSchedules($tenantId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    /**
     * Calculate labor cost
     * GET /api/v1/hr/labor-cost-analysis
     */
    public function calculateLaborCost($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $result = $this->service->calculateLaborCost($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::json($result);
    }

    /**
     * Create training program
     * POST /api/v1/hr/training-programs
     */
    public function createTrainingProgram($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createTrainingProgram($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get training programs
     * GET /api/v1/hr/training-programs
     */
    public function getTrainingPrograms($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        $status = $request->status ?? null;
        $category = $request->category ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $programs = $this->service->getTrainingPrograms($tenantId, $status, $category, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $programs
        ]);
    }

    /**
     * Record training completion
     * POST /api/v1/hr/training-completion
     */
    public function recordTrainingCompletion($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->recordTrainingCompletion($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get staff performance with labor cost
     * GET /api/v1/hr/staff-performance-labor
     */
    public function getStaffPerformanceWithLaborCost($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $result = $this->service->getStaffPerformanceWithLaborCost($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::json($result);
    }

    /**
     * Get HR summary
     * GET /api/v1/hr/summary
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
