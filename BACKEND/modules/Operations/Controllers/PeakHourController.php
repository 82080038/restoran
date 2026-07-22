<?php

namespace App\Modules\Operations\Controllers;

use App\Core\Response;
use App\Modules\Operations\Services\PeakHourService;

class PeakHourController extends BaseController
{
    private $peakHourService;

    public function __construct()
    {
        $this->peakHourService = new PeakHourService();
    }

    public function getPeakHourSchedules($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $schedules = $this->peakHourService->getPeakHourSchedules($tenantId, $branchId);
            return Response::success($schedules, 'Peak hour schedules retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getCurrentPeakHour($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $peakHour = $this->peakHourService->getCurrentPeakHour($tenantId, $branchId);
            return Response::success($peakHour, 'Current peak hour retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createPeakHourSchedule($request)
    {
        try {
            $required = ['tenant_id', 'branch_id', 'day_of_week', 'start_time', 'end_time'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $scheduleId = $this->peakHourService->createPeakHourSchedule($request);
            return Response::success(['schedule_id' => $scheduleId], 'Peak hour schedule created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updatePeakHourSchedule($request)
    {
        try {
            $scheduleId = $request['id'];
            $tenantId = $request['tenant_id'];

            $this->peakHourService->updatePeakHourSchedule($scheduleId, $tenantId, $request);
            return Response::success([], 'Peak hour schedule updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deletePeakHourSchedule($request)
    {
        try {
            $scheduleId = $request['id'];
            $tenantId = $request['tenant_id'];

            $this->peakHourService->deletePeakHourSchedule($scheduleId, $tenantId);
            return Response::success([], 'Peak hour schedule deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function isPeakHourNow($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $isPeakHour = $this->peakHourService->isPeakHourNow($tenantId, $branchId);
            return Response::success(['is_peak_hour' => $isPeakHour], 'Peak hour status retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
