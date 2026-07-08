<?php

namespace App\Modules\CentralKitchen\Controllers;

use App\Modules\CentralKitchen\Services\CentralKitchenService;
use App\Core\Response;

class CentralKitchenController
{
    private $service;

    public function __construct()
    {
        $this->service = new CentralKitchenService();
    }

    /**
     * Create production plan
     * POST /api/v1/central-kitchen/production-plans
     */
    public function createProductionPlan($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createProductionPlan($tenantId, $branchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get production plans
     * GET /api/v1/central-kitchen/production-plans
     */
    public function getProductionPlans($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $plans = $this->service->getProductionPlans($tenantId, $branchId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Get production plan details
     * GET /api/v1/central-kitchen/production-plans/{id}
     */
    public function getProductionPlanDetails($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $planId = $request->id;

        $plan = $this->service->getProductionPlanDetails($planId, $tenantId);

        if (!$plan) {
            return Response::json([
                'success' => false,
                'message' => 'Production plan not found'
            ], 404);
        }

        return Response::json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * Calculate ingredient requirements
     * GET /api/v1/central-kitchen/production-plans/{id}/ingredient-requirements
     */
    public function calculateIngredientRequirements($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $planId = $request->id;

        $requirements = $this->service->calculateIngredientRequirements($planId, $tenantId);

        return Response::json([
            'success' => true,
            'data' => $requirements
        ]);
    }

    /**
     * Standardize recipe across branches
     * POST /api/v1/central-kitchen/recipes/standardize
     */
    public function standardizeRecipe($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->standardizeRecipe($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Track production yield
     * POST /api/v1/central-kitchen/yields
     */
    public function trackYield($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->trackYield($tenantId, $branchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get yield analytics
     * GET /api/v1/central-kitchen/yields
     */
    public function getYieldAnalytics($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $yields = $this->service->getYieldAnalytics($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $yields
        ]);
    }

    /**
     * Create distribution order
     * POST /api/v1/central-kitchen/distributions
     */
    public function createDistributionOrder($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $sourceBranchId = $_SESSION['branch_id'] ?? 2;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createDistributionOrder($tenantId, $sourceBranchId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get distribution orders
     * GET /api/v1/central-kitchen/distributions
     */
    public function getDistributionOrders($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        
        $status = $request->status ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $distributions = $this->service->getDistributionOrders($tenantId, $branchId, $status, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $distributions
        ]);
    }

    /**
     * Update distribution status
     * PUT /api/v1/central-kitchen/distributions/{id}/status
     */
    public function updateDistributionStatus($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;
        $distributionId = $request->id;
        $status = $request->status;

        $result = $this->service->updateDistributionStatus($distributionId, $status, $userId, $tenantId);

        return Response::json($result);
    }

    /**
     * Get central kitchen summary
     * GET /api/v1/central-kitchen/summary
     */
    public function getSummary($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;

        $summary = $this->service->getSummary($tenantId, $branchId);

        return Response::json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
