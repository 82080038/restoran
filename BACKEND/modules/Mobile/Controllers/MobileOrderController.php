<?php

if (!class_exists('MobileOrderService')) {
    require_once __DIR__ . '/../Services/MobileOrderService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';


class MobileOrderController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new MobileOrderService();
    }

    public function getMenu($request)
    {
        $result = $this->service->getMobileMenu($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getQuickOrder($request)
    {
        $productId = $request['id'] ?? null;

        if (!$productId) {
            Response::error('Product ID is required');
            return;
        }

        $result = $this->service->getQuickOrder($user['tenant_id'], $user['branch_id'], $productId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
