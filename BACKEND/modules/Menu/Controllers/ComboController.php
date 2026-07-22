<?php

if (!class_exists('ComboService')) {
    require_once __DIR__ . '/../Services/ComboService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class ComboController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new ComboService();
    }

    public function create($request)
    {
        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createCombo($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['combo_id' => $result['combo_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $result = $this->service->getCombos($user['tenant_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function calculatePrice($request)
    {
        $comboId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $selections = $data['selections'] ?? [];

        $result = $this->service->calculateComboPrice($comboId, $selections);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
