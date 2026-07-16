<?php

namespace App\Modules\KDS\Controllers;

use App\Core\Response;
use App\Modules\KDS\Services\KDSRoutingRuleService;

class KDSRoutingRuleController
{
    private $routingRuleService;

    public function __construct()
    {
        $this->routingRuleService = new KDSRoutingRuleService();
    }

    public function getRoutingRules($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $tenantId = $request['tenant_id'];
            $branchId = $request['branch_id'] ?? null;

            $rules = $this->routingRuleService->getRoutingRules($tenantId, $branchId);
            return Response::success($rules, 'KDS routing rules retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function getRoutingRule($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $ruleId = $request['id'];
            $tenantId = $request['tenant_id'];

            $rule = $this->routingRuleService->getRoutingRule($ruleId, $tenantId);
            if (!$rule) {
                return Response::error('KDS routing rule not found', 404);
            }
            return Response::success($rule, 'KDS routing rule retrieved successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function createRoutingRule($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            
            $required = ['tenant_id', 'branch_id', 'rule_name', 'target_station_id'];
            foreach ($required as $field) {
                if (!isset($request[$field])) {
                    return Response::error("Missing required field: $field", 400);
                }
            }

            $ruleId = $this->routingRuleService->createRoutingRule($request);
            return Response::success(['rule_id' => $ruleId], 'KDS routing rule created successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function updateRoutingRule($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $ruleId = $request['id'];
            $tenantId = $request['tenant_id'];

            $rule = $this->routingRuleService->getRoutingRule($ruleId, $tenantId);
            if (!$rule) {
                return Response::error('KDS routing rule not found', 404);
            }

            $this->routingRuleService->updateRoutingRule($ruleId, $tenantId, $request);
            return Response::success([], 'KDS routing rule updated successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function deleteRoutingRule($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $ruleId = $request['id'];
            $tenantId = $request['tenant_id'];

            $rule = $this->routingRuleService->getRoutingRule($ruleId, $tenantId);
            if (!$rule) {
                return Response::error('KDS routing rule not found', 404);
            }

            $this->routingRuleService->deleteRoutingRule($ruleId, $tenantId);
            return Response::success([], 'KDS routing rule deleted successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function applyRoutingRules($request)
    {
        try {
            $request = (new \AuthMiddleware())->handle($request);
            $orderId = $request['order_id'];
            $diningOption = $request['dining_option'] ?? 'DINE_IN';

            $appliedRules = $this->routingRuleService->applyRoutingRules($orderId, $diningOption);
            return Response::success($appliedRules, 'Routing rules applied successfully');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
