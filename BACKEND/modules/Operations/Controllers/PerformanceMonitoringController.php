<?php

namespace App\Modules\Operations\Controllers;

use App\Core\Response;
use App\Modules\Operations\Services\PerformanceMonitoringService;

class PerformanceMonitoringController extends BaseController
{
    private $performanceService;

    public function __construct()
    {
        $this->performanceService = new PerformanceMonitoringService();
    }

    public function getPerformanceMetrics($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $date = $request['date'] ?? null;
            $stationId = $request['station_id'] ?? null;

            $metrics = $this->performanceService->getPerformanceMetrics($tenantId, $branchId, $date, $stationId);
            return Response::success($metrics, 'Performance metrics retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function recordOrderTiming($request)
    {
        try {
            $required = ['order_id', 'tenant_id', 'branch_id', 'order_placed_at'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $this->performanceService->recordOrderTiming(
                $request['order_id'],
                $request['tenant_id'],
                $request['branch_id'],
                $request['station_id'] ?? null,
                $request['order_placed_at'],
                $request['sent_to_kitchen_at'] ?? null,
                $request['prep_started_at'] ?? null,
                $request['ready_at'] ?? null,
                $request['served_at'] ?? null,
                $request['estimated_time'] ?? null
            );
            return Response::success([], 'Order timing recorded successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function calculateHourlyMetrics($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'];
            $date = $request['date'] ?? date('Y-m-d');
            $hour = $request['hour'] ?? date('H');
            $stationId = $request['station_id'] ?? null;

            $this->performanceService->calculateHourlyMetrics($tenantId, $branchId, $date, $hour, $stationId);
            return Response::success([], 'Hourly metrics calculated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBottlenecks($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $days = $request['days'] ?? 7;

            $bottlenecks = $this->performanceService->getBottlenecks($tenantId, $branchId, $days);
            return Response::success($bottlenecks, 'Bottlenecks retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getPerformanceSummary($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $days = $request['days'] ?? 30;

            $summary = $this->performanceService->getPerformanceSummary($tenantId, $branchId, $days);
            return Response::success($summary, 'Performance summary retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
