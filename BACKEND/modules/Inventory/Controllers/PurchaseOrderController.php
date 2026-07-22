<?php

if (!class_exists('PurchaseOrderService')) {
    require_once __DIR__ . '/../Services/PurchaseOrderService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class PurchaseOrderController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new PurchaseOrderService();
    }

    public function create($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createPurchaseOrder($data, $user['user_id'], $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['po_id' => $result['po_id'], 'po_number' => $result['po_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function approve($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $poId = $request['params']['id'] ?? null;

        $result = $this->service->approvePurchaseOrder($poId, $user['user_id'], $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $result = $this->service->getPurchaseOrders($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
