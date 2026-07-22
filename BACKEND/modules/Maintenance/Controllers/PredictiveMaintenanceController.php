<?php

if (!class_exists('PredictiveMaintenanceService')) {
    require_once __DIR__ . '/../Services/PredictiveMaintenanceService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class PredictiveMaintenanceController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new PredictiveMaintenanceService();
    }

    public function predictNeeds($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $result = $this->service->predictMaintenanceNeeds($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
