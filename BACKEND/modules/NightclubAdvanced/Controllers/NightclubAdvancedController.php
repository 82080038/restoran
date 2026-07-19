<?php

namespace App\Modules\NightclubAdvanced\Controllers;

use App\Core\Response;
use App\Modules\NightclubAdvanced\Services\NightclubAdvancedService;

class NightclubAdvancedController
{
    private $service;

    public function __construct()
    {
        $this->service = new NightclubAdvancedService();
    }

    public function getTableDeposits($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getTableDeposits(
                $request['tenant_id'], $request['branch_id'] ?? null,
                $request['query']['status'] ?? null, $request['query']['event_date'] ?? null
            );
            return Response::success($result, 'Table deposits retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createTableDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['customer_name']) || empty($data['event_date']) || !isset($data['deposit_amount'])) {
                return Response::error('customer_name, event_date, and deposit_amount are required', 400);
            }
            return Response::success($this->service->createTableDeposit($data), 'Table deposit created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function markDepositPaid($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->markDepositPaid($id, $request['body']['payment_method'] ?? null, $request['body']['payment_ref'] ?? null), 'Deposit marked as paid');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function forfeitDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->forfeitDeposit($id, $request['body']['reason'] ?? ''), 'Deposit forfeited');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function refundDeposit($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->refundDeposit($id, $request['body']['reason'] ?? ''), 'Deposit refunded');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getBottleInventory($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getBottleInventory($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['status'] ?? null);
            return Response::success($result, 'Bottle inventory retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addBottleInventory($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['bottle_name']) || empty($data['product_id'])) {
                return Response::error('bottle_name and product_id are required', 400);
            }
            return Response::success($this->service->addBottleInventory($data), 'Bottle inventory added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function assignBottle($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['assigned_by'] = $request['user_id'] ?? null;
            if (empty($data['bottle_inv_id'])) {
                return Response::error('bottle_inv_id is required', 400);
            }
            return Response::success($this->service->assignBottle($data), 'Bottle assigned');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function serveBottle($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->serveBottle($id), 'Bottle served');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getPromoters($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $result = $this->service->getPromoters($request['tenant_id'], $request['branch_id'] ?? null, ($request['query']['active'] ?? '0') === '1');
            return Response::success($result, 'Promoters retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createPromoter($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['promoter_name'])) {
                return Response::error('promoter_name is required', 400);
            }
            return Response::success($this->service->createPromoter($data), 'Promoter created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addGuestToList($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['promoter_id']) || empty($data['event_id']) || empty($data['guest_name'])) {
                return Response::error('promoter_id, event_id, and guest_name are required', 400);
            }
            return Response::success($this->service->addGuestToPromoterList($data), 'Guest added to promoter list');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function checkInGuest($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->checkInPromoterGuest($id), 'Guest checked in');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getPromoterGuestList($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $promoterId = $request['params']['promoter_id'] ?? $request['query']['promoter_id'] ?? null;
            $eventId = $request['params']['event_id'] ?? $request['query']['event_id'] ?? null;
            return Response::success($this->service->getPromoterGuestList($promoterId, $eventId), 'Promoter guest list retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getPromoterStats($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->getPromoterStats($id), 'Promoter stats retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}
