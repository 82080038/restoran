<?php

if (!class_exists('InventoryAdvancedService')) {
    require_once __DIR__ . '/../Services/InventoryAdvancedService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class InventoryAdvancedController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new InventoryAdvancedService();
    }

    public function repurposeStock($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->repurposeStock($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['repurposing_id' => $result['repurposing_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function zeroCostStockIn($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->zeroCostStockIn($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createStockTransfer($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createStockTransfer($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['transfer_id' => $result['transfer_id'], 'transfer_number' => $result['transfer_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function receiveStockTransfer($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $transferId = $request['params']['id'] ?? null;

        if (!$transferId) {
            Response::error('Transfer ID is required');
            return;
        }

        $result = $this->service->receiveStockTransfer($transferId, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getStockTransfers($request)
    {
        $status = $request['params']['status'] ?? null;

        $result = $this->service->getStockTransfers($user['tenant_id'], $user['branch_id'], $status);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getRepurposingHistory($request)
    {
        $dateFrom = $request['params']['start_date'] ?? null;
        $dateTo = $request['params']['end_date'] ?? null;

        $result = $this->service->getRepurposingHistory($user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
