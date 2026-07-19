<?php

namespace App\Modules\EventProfitability\Controllers;

use App\Core\Response;
use App\Modules\EventProfitability\Services\EventProfitabilityService;

class EventProfitabilityController
{
    private $service;

    public function __construct()
    {
        $this->service = new EventProfitabilityService();
    }

    public function getProfitabilityList($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $eventType = $request['query']['event_type'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? null;
            $dateTo = $request['query']['date_to'] ?? null;

            $result = $this->service->getProfitabilityList($tenantId, $branchId, $eventType, $dateFrom, $dateTo);
            return Response::success($result, 'Profitability list retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getProfitability($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $profitabilityId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->getProfitabilityDetail($profitabilityId);
            if (!$result['profitability']) {
                return Response::notFound('Profitability record not found');
            }
            return Response::success($result, 'Profitability detail retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createProfitability($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['event_type']) || empty($data['event_id']) || empty($data['event_name']) || empty($data['event_date'])) {
                return Response::error('event_type, event_id, event_name, and event_date are required', 400);
            }

            $result = $this->service->createProfitability($data);
            return Response::success($result, 'Profitability record created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function addCostItem($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $profitabilityId = $request['params']['id'] ?? $request['id'] ?? null;
            $item = $request['body'];

            if (empty($item['cost_category']) || !isset($item['amount'])) {
                return Response::error('cost_category and amount are required', 400);
            }

            $result = $this->service->addCostItem($profitabilityId, $item);
            return Response::success($result, 'Cost item added successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function finalizeProfitability($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $profitabilityId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->finalizeProfitability($profitabilityId);
            return Response::success($result, 'Profitability finalized successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getSummary($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-01');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d');

            $result = $this->service->getProfitabilitySummary($tenantId, $branchId, $dateFrom, $dateTo);
            return Response::success($result, 'Profitability summary retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
