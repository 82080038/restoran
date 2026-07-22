<?php

namespace App\Modules\SportsBarAdvanced\Controllers;

use App\Core\Response;
use App\Modules\SportsBarAdvanced\Services\SportsBarAdvancedService;

class SportsBarAdvancedController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new SportsBarAdvancedService();
    }

    public function getTabs($request)
    {
        try {
            $result = $this->service->getTabs($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['status'] ?? null);
            return Response::success($result, 'Bar tabs retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getTab($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $result = $this->service->getTab($id);
            if (!$result) return Response::notFound('Tab not found');
            return Response::success($result, 'Tab retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function openTab($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['opened_by'] = $request['user_id'] ?? null;
            if (empty($data['customer_name'])) {
                return Response::error('customer_name is required', 400);
            }
            return Response::success($this->service->openTab($data), 'Tab opened');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addToTab($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->addToTab($id, $request['body']['items'] ?? [], $request['body']['amount'] ?? 0), 'Items added to tab');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function closeTab($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->closeTab($id, $request['body']['tip_amount'] ?? 0), 'Tab closed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function captureTab($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->captureTab($id), 'Tab captured');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function voidTab($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->voidTab($id, $request['body']['reason'] ?? ''), 'Tab voided');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}
