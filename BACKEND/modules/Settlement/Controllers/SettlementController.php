<?php

namespace App\Modules\Settlement\Controllers;

use App\Core\Response;
use App\Modules\Settlement\Services\SettlementService;

class SettlementController
{
    private $service;

    public function __construct()
    {
        $this->service = new SettlementService();
    }

    public function getDeals($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $result = $this->service->getDeals($tenantId, $branchId, $status);
            return Response::success($result, 'Artist deals retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createDeal($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['artist_name']) || empty($data['deal_type'])) {
                return Response::error('artist_name and deal_type are required', 400);
            }

            $result = $this->service->createDeal($data);
            return Response::success($result, 'Artist deal created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function signDeal($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $dealId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->signDeal($dealId);
            return Response::success($result, 'Deal signed successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getSettlements($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $result = $this->service->getSettlements($tenantId, $branchId, $status);
            return Response::success($result, 'Settlements retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getSettlement($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $settlementId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->getSettlementDetail($settlementId);
            if (!$result['settlement']) {
                return Response::notFound('Settlement not found');
            }
            return Response::success($result, 'Settlement retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createSettlement($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['settlement_type']) || empty($data['settlement_date'])) {
                return Response::error('settlement_type and settlement_date are required', 400);
            }

            $result = $this->service->createSettlement($data);
            return Response::success($result, 'Settlement created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function addSettlementItem($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $settlementId = $request['params']['id'] ?? $request['id'] ?? null;
            $item = $request['body'];

            if (empty($item['item_type']) || !isset($item['amount'])) {
                return Response::error('item_type and amount are required', 400);
            }

            $result = $this->service->addSettlementItem($settlementId, $item);
            return Response::success($result, 'Settlement item added successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function finalizeSettlement($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $settlementId = $request['params']['id'] ?? $request['id'] ?? null;
            $finalizedBy = $request['user_id'] ?? null;

            $result = $this->service->finalizeSettlement($settlementId, $finalizedBy);
            return Response::success($result, 'Settlement finalized successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function markSettlementPaid($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $settlementId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->markSettlementPaid($settlementId);
            return Response::success($result, 'Settlement marked as paid');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getAdvancingSheet($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $concertId = $request['params']['concert_id'] ?? $request['query']['concert_id'] ?? null;

            $result = $this->service->getAdvancingSheet($concertId);
            if (!$result) {
                return Response::notFound('Advancing sheet not found');
            }
            return Response::success($result, 'Advancing sheet retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createAdvancingSheet($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['concert_id'])) {
                return Response::error('concert_id is required', 400);
            }

            $result = $this->service->createAdvancingSheet($data);
            return Response::success($result, 'Advancing sheet created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function confirmAdvancingSheet($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $sheetId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->confirmAdvancingSheet($sheetId);
            return Response::success($result, 'Advancing sheet confirmed successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
