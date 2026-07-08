<?php

namespace App\Modules\MultiBranch\Controllers;

use App\Modules\MultiBranch\Services\MultiBranchService;
use App\Core\Response;

class MultiBranchController
{
    private $service;

    public function __construct()
    {
        $this->service = new MultiBranchService();
    }

    /**
     * Create stock transfer
     * POST /api/v1/multi-branch/stock-transfers
     */
    public function createStockTransfer($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $fromBranchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createStockTransfer($tenantId, $fromBranchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get stock transfers
     * GET /api/v1/multi-branch/stock-transfers
     */
    public function getStockTransfers($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $transfers = $this->service->getStockTransfers($tenantId, $branchId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $transfers
        ]);
    }

    /**
     * Update transfer status
     * PUT /api/v1/multi-branch/stock-transfers/{id}/status
     */
    public function updateTransferStatus($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;
        $transferId = $request->id;
        $status = $request->status;

        $result = $this->service->updateTransferStatus($transferId, $status, $userId, $tenantId);

        return Response::json($result);
    }

    /**
     * Create centralized purchase
     * POST /api/v1/multi-branch/centralized-purchases
     */
    public function createCentralizedPurchase($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createCentralizedPurchase($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get branch performance
     * GET /api/v1/multi-branch/branch-performance
     */
    public function getBranchPerformance($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $performance = $this->service->getBranchPerformance($tenantId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $performance
        ]);
    }

    /**
     * Standardize pricing
     * POST /api/v1/multi-branch/standardize-pricing
     */
    public function standardizePricing($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->standardizePricing($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get multi-branch summary
     * GET /api/v1/multi-branch/summary
     */
    public function getSummary($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $summary = $this->service->getSummary($tenantId);

        return Response::json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
