<?php

if (!class_exists('SupplierService')) {
    require_once __DIR__ . '/../Services/SupplierService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class SupplierController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new SupplierService();
    }

    public function create($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createSupplier($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['supplier_id' => $result['supplier_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $result = $this->service->getSuppliers($user['tenant_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function update($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $supplierId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->updateSupplier($supplierId, $data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function delete($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $supplierId = $request['params']['id'] ?? null;

        $result = $this->service->deleteSupplier($supplierId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
