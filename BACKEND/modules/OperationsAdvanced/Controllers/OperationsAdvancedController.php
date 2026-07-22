<?php

namespace App\Modules\OperationsAdvanced\Controllers;

use App\Core\Response;
use App\Modules\OperationsAdvanced\Services\OperationsAdvancedService;

class OperationsAdvancedController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new OperationsAdvancedService();
    }

    // ==================== 86-ING ====================

    public function get86Items($request)
    {
        try {
            $result = $this->service->get86Items($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result, '86-ed items retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function set86Status($request)
    {
        try {
            $data = $request['body'];
            $branchId = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['product_id']) || empty($branchId)) return Response::error('product_id and branch_id are required', 400);
            $result = $this->service->set86Status(
                $request['tenant_id'], $branchId,
                $data['product_id'], $data['reason'] ?? '', $request['user_id'] ?? null,
                $data['expected_restock_date'] ?? null
            );
            if (!$result['success']) return Response::error($result['message'], 404);
            return Response::success($result, 'Item 86-ed successfully');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function restockItem($request)
    {
        try {
            $productId = $request['product_id'] ?? $request['params']['product_id'] ?? $request['body']['product_id'] ?? null;
            $branchId = $request['branch_id'] ?? $request['body']['branch_id'] ?? null;
            if (empty($productId) || empty($branchId)) return Response::error('product_id and branch_id are required', 400);
            $result = $this->service->restockItem($request['tenant_id'], $branchId, $productId, $request['user_id'] ?? null);
            if (!$result['success']) return Response::error($result['message'], 404);
            return Response::success($result, 'Item restocked');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== CUSTOM ORDERS ====================

    public function getCustomOrders($request)
    {
        try {
            $result = $this->service->getCustomOrders($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['status'] ?? null);
            return Response::success($result, 'Custom orders retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createCustomOrder($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['customer_name']) || empty($data['product_name'])) {
                return Response::error('customer_name and product_name are required', 400);
            }
            return Response::success($this->service->createCustomOrder($data), 'Custom order created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateCustomOrderStatus($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $status = $request['body']['status'] ?? null;
            if (!$status) return Response::error('status is required', 400);
            return Response::success($this->service->updateCustomOrderStatus($id, $status), 'Custom order status updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== DELIVERY ROUTING ====================

    public function getRoutes($request)
    {
        try {
            $result = $this->service->getRoutes($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['date'] ?? null, $request['query']['status'] ?? null);
            return Response::success($result, 'Delivery routes retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getRoute($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $result = $this->service->getRouteDetail($id);
            if (!$result['route']) return Response::notFound('Route not found');
            return Response::success($result, 'Route retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createRoute($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['route_date'])) return Response::error('route_date is required', 400);
            return Response::success($this->service->createRoute($data), 'Route created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addRouteStop($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $stop = $request['body'];
            if (empty($stop['delivery_address'])) return Response::error('delivery_address is required', 400);
            return Response::success($this->service->addRouteStop($id, $stop), 'Stop added to route');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function startRoute($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->startRoute($id), 'Route started');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function completeRoute($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->completeRoute($id), 'Route completed');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateStopStatus($request)
    {
        try {
            $id = $request['params']['stop_id'] ?? $request['id'] ?? null;
            $data = $request['body'];
            return Response::success($this->service->updateStopStatus($id, $data['status'] ?? 'DELIVERED', $data['proof_photo_path'] ?? null, $data['signature_path'] ?? null, $data['failure_reason'] ?? null), 'Stop status updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== LEAD PIPELINE ====================

    public function getLeads($request)
    {
        try {
            $result = $this->service->getLeads($request['tenant_id'], $request['branch_id'] ?? null, $request['query']['stage'] ?? null, $request['query']['assigned_to'] ?? null);
            return Response::success($result, 'Leads retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createLead($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['client_name'])) return Response::error('client_name is required', 400);
            return Response::success($this->service->createLead($data), 'Lead created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateLeadStage($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $stage = $request['body']['stage'] ?? null;
            $prob = $request['body']['probability_pct'] ?? null;
            if (!$stage) return Response::error('stage is required', 400);
            return Response::success($this->service->updateLeadStage($id, $stage, $prob), 'Lead stage updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getLeadPipelineSummary($request)
    {
        try {
            $result = $this->service->getLeadPipelineSummary($request['tenant_id'], $request['branch_id'] ?? null);
            return Response::success($result, 'Pipeline summary retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    // ==================== ALLERGEN TRACKING ====================

    public function getAllergenInfo($request)
    {
        try {
            $productId = $request['params']['product_id'] ?? $request['query']['product_id'] ?? null;
            $result = $this->service->getAllergenInfo($request['tenant_id'], $productId);
            return Response::success($result ?: [], 'Allergen info retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function setAllergenInfo($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            if (empty($data['product_id'])) return Response::error('product_id is required', 400);
            return Response::success($this->service->setAllergenInfo($data), 'Allergen info saved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function filterByDietaryTag($request)
    {
        try {
            $tag = $request['params']['tag'] ?? $request['query']['tag'] ?? null;
            if (!$tag) return Response::error('tag is required', 400);
            $result = $this->service->filterByDietaryTag($request['tenant_id'], $tag);
            return Response::success($result, 'Dietary filter results retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}
