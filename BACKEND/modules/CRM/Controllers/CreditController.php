<?php

if (!class_exists('CreditService')) {
    require_once __DIR__ . '/../Services/CreditService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';




class CreditController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new CreditService();
    }

    public function createCredit($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createCredit($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['credit_id' => $result['credit_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function payCredit($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $creditId = $request['params']['id'] ?? null;
        $amount = $request['body']['amount'] ?? null;

        if (!$creditId || !$amount) {
            Response::error(Messages::CRM_CREDIT_ID_REQUIRED);
            return;
        }

        $result = $this->service->payCredit($creditId, $amount, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCustomerCredits($request)
    {
        $customerId = $request['params']['customer_id'] ?? null;

        if (!$customerId) {
            Response::error(Messages::CRM_CUSTOMER_ID_REQUIRED);
            return;
        }

        $result = $this->service->getCustomerCredits($user['tenant_id'], $user['branch_id'], $customerId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getOverdueCredits($request)
    {
        $result = $this->service->getOverdueCredits($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
