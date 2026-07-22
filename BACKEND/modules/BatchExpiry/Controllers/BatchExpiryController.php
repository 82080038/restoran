<?php

namespace App\Modules\BatchExpiry\Controllers;

use App\Core\Response;
use App\Modules\BatchExpiry\Services\BatchExpiryService;

class BatchExpiryController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new BatchExpiryService();
    }

    public function getBatches($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $status = $request['query']['status'] ?? null;
            $productId = $request['query']['product_id'] ?? null;
            $nearExpiryDays = $request['query']['near_expiry_days'] ?? null;

            $result = $this->service->getBatches($tenantId, $branchId, $status, $productId, $nearExpiryDays !== null ? (int)$nearExpiryDays : null);
            return Response::success($result, 'Batches retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getBatch($request)
    {
        try {
            $batchId = $request['params']['id'] ?? $request['id'] ?? null;

            $result = $this->service->getBatch($batchId);
            if (!$result) {
                return Response::notFound('Batch not found');
            }
            return Response::success($result, 'Batch retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function receiveBatch($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;

            if (empty($data['batch_number']) || empty($data['expiry_date']) || empty($data['product_id'])) {
                return Response::error('batch_number, expiry_date, and product_id are required', 400);
            }

            $result = $this->service->receiveBatch($data);
            return Response::success($result, 'Batch received successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deductFromBatch($request)
    {
        try {
            $batchId = $request['params']['id'] ?? $request['id'] ?? null;
            $quantity = $request['body']['quantity'] ?? null;

            if ($quantity === null) {
                return Response::error('quantity is required', 400);
            }

            $result = $this->service->deductFromBatch($batchId, $quantity);
            return Response::success($result, 'Batch deducted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function applyDiscount($request)
    {
        try {
            $batchId = $request['params']['id'] ?? $request['id'] ?? null;
            $discountPct = $request['body']['discount_percentage'] ?? null;
            $discountedPrice = $request['body']['discounted_price'] ?? null;
            $reason = $request['body']['reason'] ?? '';
            $changedBy = $request['user_id'] ?? null;

            if ($discountPct === null || $discountedPrice === null) {
                return Response::error('discount_percentage and discounted_price are required', 400);
            }

            $result = $this->service->applyDiscount($batchId, $discountPct, $discountedPrice, $changedBy, $reason);
            return Response::success($result, 'Discount applied successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function discardBatch($request)
    {
        try {
            $batchId = $request['params']['id'] ?? $request['id'] ?? null;
            $reason = $request['body']['reason'] ?? '';
            $changedBy = $request['user_id'] ?? null;

            $result = $this->service->discardBatch($batchId, $changedBy, $reason);
            return Response::success($result, 'Batch discarded successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getNearExpiry($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;
            $days = (int)($request['query']['days'] ?? 7);

            $result = $this->service->getNearExpiryBatches($tenantId, $branchId, $days);
            return Response::success($result, 'Near-expiry batches retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getExpiryDashboard($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $result = $this->service->getExpiryDashboard($tenantId, $branchId);
            return Response::success($result, 'Expiry dashboard retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateBatchStatuses($request)
    {
        try {
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $result = $this->service->updateAllBatchStatuses($tenantId, $branchId);
            return Response::success($result, 'Batch statuses updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
