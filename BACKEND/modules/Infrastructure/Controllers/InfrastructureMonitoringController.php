<?php

namespace App\Modules\Infrastructure\Controllers;

use App\Modules\Infrastructure\Services\InfrastructureMonitoringService;
use App\Core\Response;

class InfrastructureMonitoringController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new InfrastructureMonitoringService();
    }

    /**
     * Record performance metrics
     * POST /api/v1/infrastructure/performance-metrics
     */
    public function recordPerformanceMetrics($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $result = $this->service->recordPerformanceMetrics($tenantId, $request);

        return Response::json($result);
    }

    /**
     * Get performance metrics
     * GET /api/v1/infrastructure/performance-metrics
     */
    public function getPerformanceMetrics($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $serverId = $request->server_id ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $metrics = $this->service->getPerformanceMetrics($tenantId, $serverId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get infrastructure alerts
     * GET /api/v1/infrastructure/alerts
     */
    public function getAlerts($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $status = $request->status ?? null;

        $alerts = $this->service->getAlerts($tenantId, $status);

        return Response::json([
            'success' => true,
            'data' => $alerts
        ]);
    }

    /**
     * Get performance report
     * GET /api/v1/infrastructure/performance-report
     */
    public function getPerformanceReport($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $report = $this->service->getPerformanceReport($tenantId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get infrastructure summary
     * GET /api/v1/infrastructure/summary
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
