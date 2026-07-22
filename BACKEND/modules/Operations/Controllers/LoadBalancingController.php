<?php

namespace App\Modules\Operations\Controllers;

use App\Core\Response;
use App\Modules\Operations\Services\LoadBalancingService;

class LoadBalancingController extends BaseController
{
    private $loadBalancingService;

    public function __construct()
    {
        $this->loadBalancingService = new LoadBalancingService();
    }

    public function recordStationLoad($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'];
            $stationId = $request['station_id'];

            $loadLevel = $this->loadBalancingService->recordStationLoad($tenantId, $branchId, $stationId);
            return Response::success(['load_level' => $loadLevel], 'Station load recorded successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getStationLoadMetrics($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $stationId = $request['station_id'] ?? null;
            $minutes = $request['minutes'] ?? 60;

            $metrics = $this->loadBalancingService->getStationLoadMetrics($tenantId, $branchId, $stationId, $minutes);
            return Response::success($metrics, 'Station load metrics retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getLeastLoadedStation($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $stationType = $request['station_type'] ?? null;

            $station = $this->loadBalancingService->getLeastLoadedStation($tenantId, $branchId, $stationType);
            return Response::success($station, 'Least loaded station retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function recommendReroute($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'];
            $currentStationId = $request['station_id'];

            $recommendation = $this->loadBalancingService->recommendReroute($tenantId, $branchId, $currentStationId);
            return Response::success($recommendation, 'Reroute recommendation retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBottleneckStations($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $bottlenecks = $this->loadBalancingService->getBottleneckStations($tenantId, $branchId);
            return Response::success($bottlenecks, 'Bottleneck stations retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
