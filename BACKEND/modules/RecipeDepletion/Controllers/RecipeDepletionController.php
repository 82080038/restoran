<?php

namespace App\Modules\RecipeDepletion\Controllers;

use App\Core\Response;
use App\Modules\RecipeDepletion\Services\RecipeDepletionService;

class RecipeDepletionController
{
    private $service;

    public function __construct()
    {
        $this->service = new RecipeDepletionService();
    }

    public function depleteFromOrder($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $data = $request['body'];

            if (empty($data['product_id']) || empty($data['quantity_sold'])) {
                return Response::error('product_id and quantity_sold are required', 400);
            }

            $result = $this->service->depleteFromOrder(
                $tenantId, $branchId,
                $data['order_id'] ?? null,
                $data['product_id'],
                $data['quantity_sold'],
                $data['unit'] ?? 'portion'
            );
            return Response::success($result, 'Recipe depletion processed successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getDepletionLogs($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? null;
            $dateTo = $request['query']['date_to'] ?? null;
            $productId = $request['query']['product_id'] ?? null;

            $result = $this->service->getDepletionLogs($tenantId, $branchId, $dateFrom, $dateTo, $productId);
            return Response::success($result, 'Depletion logs retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getDepletionSummary($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-01');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d');

            $result = $this->service->getDepletionSummary($tenantId, $branchId, $dateFrom, $dateTo);
            return Response::success($result, 'Depletion summary retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getProductionBatches($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;

            $result = $this->service->getProductionBatches($tenantId, $branchId, $status);
            return Response::success($result, 'Production batches retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createProductionBatch($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            $data['produced_by'] = $request['user_id'] ?? null;

            if (empty($data['recipe_id']) || empty($data['planned_quantity'])) {
                return Response::error('recipe_id and planned_quantity are required', 400);
            }

            $result = $this->service->createProductionBatch($data);
            return Response::success($result, 'Production batch created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function completeProductionBatch($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $batchId = $request['params']['id'] ?? $request['id'] ?? null;
            $data = $request['body'];

            $result = $this->service->completeProductionBatch($batchId, $data);
            return Response::success($result, 'Production batch completed successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
