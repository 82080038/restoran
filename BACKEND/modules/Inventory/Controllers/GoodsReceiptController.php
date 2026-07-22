<?php

if (!class_exists('GoodsReceiptService')) {
    require_once __DIR__ . '/../Services/GoodsReceiptService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class GoodsReceiptController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new GoodsReceiptService();
    }

    public function create($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createGoodsReceipt($data, $user['user_id'], $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['receipt_id' => $result['receipt_id'], 'receipt_number' => $result['receipt_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function complete($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $receiptId = $request['params']['id'] ?? null;

        $result = $this->service->completeGoodsReceipt($receiptId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $result = $this->service->getGoodsReceipts($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
