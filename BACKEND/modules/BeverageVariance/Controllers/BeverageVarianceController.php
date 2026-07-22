<?php

namespace App\Modules\BeverageVariance\Controllers;

use App\Core\Response;
use App\Modules\BeverageVariance\Services\BeverageVarianceService;

class BeverageVarianceController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new BeverageVarianceService();
    }

    public function getBarCounts($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? null;
            $dateTo = $request['query']['date_to'] ?? null;
            $countType = $request['query']['count_type'] ?? null;

            $result = $this->service->getBarCounts($tenantId, $branchId, $dateFrom, $dateTo, $countType);
            return Response::success($result, 'Bar counts retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBarCount($request)
    {
        try {
            $countId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->getBarCountDetail($countId);
            if (!$result['count']) {
                return Response::notFound('Bar count not found');
            }
            return Response::success($result, 'Bar count retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createBarCount($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['counted_by'] = $request['user_id'] ?? null;

            if (empty($data['count_type']) || empty($data['count_date'])) {
                return Response::error('count_type and count_date are required', 400);
            }

            $result = $this->service->createBarCount($data);
            return Response::success($result, 'Bar count created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function submitBarCount($request)
    {
        try {
            $countId = $request['params']['id'] ?? $request['id'] ?? null;
            $verifiedBy = $request['query']['verify'] ? ($request['user_id'] ?? null) : null;

            $result = $this->service->submitBarCount($countId, $verifiedBy);
            return Response::success($result, 'Bar count submitted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getVarianceReports($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $result = $this->service->getVarianceReports($tenantId, $branchId);
            return Response::success($result, 'Variance reports retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function generateVarianceReport($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $periodStart = $request['body']['period_start'] ?? $request['query']['period_start'] ?? null;
            $periodEnd = $request['body']['period_end'] ?? $request['query']['period_end'] ?? null;
            $generatedBy = $request['user_id'] ?? null;

            if (!$periodStart || !$periodEnd) {
                return Response::error('period_start and period_end are required', 400);
            }

            $result = $this->service->generateVarianceReport($tenantId, $branchId, $periodStart, $periodEnd, $generatedBy);
            return Response::success($result, 'Variance report generated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getKegs($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $result = $this->service->getKegs($tenantId, $branchId, $status);
            return Response::success($result, 'Kegs retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function receiveKeg($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['product_id'])) {
                return Response::error('product_id is required', 400);
            }

            $result = $this->service->receiveKeg($data);
            return Response::success($result, 'Keg received successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function tapKeg($request)
    {
        try {
            $kegId = $request['params']['id'] ?? $request['id'] ?? null;
            $tapHandle = $request['body']['tap_handle'] ?? null;

            $result = $this->service->tapKeg($kegId, $tapHandle);
            return Response::success($result, 'Keg tapped successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateKegWeight($request)
    {
        try {
            $kegId = $request['params']['id'] ?? $request['id'] ?? null;
            $weight = $request['body']['current_weight_kg'] ?? null;

            if ($weight === null) {
                return Response::error('current_weight_kg is required', 400);
            }

            $result = $this->service->updateKegWeight($kegId, $weight);
            return Response::success($result, 'Keg weight updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
