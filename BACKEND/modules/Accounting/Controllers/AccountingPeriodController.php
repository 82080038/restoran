<?php

if (!class_exists('AccountingPeriodService')) {
    require_once __DIR__ . '/../Services/AccountingPeriodService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class AccountingPeriodController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new AccountingPeriodService();
    }

    public function createPeriod($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_CREATE');

        $data = $request['body'] ?? [];

        $result = $this->service->createPeriod($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success(['period_id' => $result['period_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getPeriods($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $fiscalYear = $params['fiscal_year'] ?? null;
        $status = $params['status'] ?? null;

        $result = $this->service->getPeriods($user['tenant_id'], $user['branch_id'], $fiscalYear, $status);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCurrentPeriod($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $result = $this->service->getCurrentPeriod($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function closePeriod($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $periodId = $params['id'];

        if (!$periodId) {
            Response::error('Period ID is required');
        }

        $result = $this->service->closePeriod($user['tenant_id'], $user['branch_id'], $periodId, $user['user_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function reopenPeriod($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $periodId = $params['id'];

        if (!$periodId) {
            Response::error('Period ID is required');
        }

        $result = $this->service->reopenPeriod($user['tenant_id'], $user['branch_id'], $periodId, $user['user_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
