<?php

if (!class_exists('AdvancedAIService')) {
    require_once __DIR__ . '/../Services/AdvancedAIService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class AdvancedAIController extends \App\Core\BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdvancedAIService();
    }

    public function analyzeMenu($request)
    {
        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->analyzeMenuEngineering($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['analyzed_products' => $result['analyzed_products']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function optimizeStaff($request)
    {
        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->optimizeStaff($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['optimized_hours' => $result['optimized_hours']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function detectFraud($request)
    {
        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->detectFraud($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['alerts_detected' => $result['alerts_detected']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function generateInsights($request)
    {
        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->generateExecutiveInsights($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['insights_count' => $result['insights_count']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getMenuAnalysis($request)
    {
        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $analysis = $this->service->repository->getMenuEngineering($user['tenant_id'], $user['branch_id'], $date);

        Response::success('Menu analysis retrieved', $analysis);
    }

    public function getFraudAlerts($request)
    {
        $params = $request['params'] ?? [];
        $status = $params['status'] ?? null;

        $alerts = $this->service->repository->getFraudAlerts($user['tenant_id'], $user['branch_id'], $status);

        Response::success('Fraud alerts retrieved', $alerts);
    }

    public function getExecutiveInsights($request)
    {
        $params = $request['params'] ?? [];
        $status = $params['status'] ?? null;

        $insights = $this->service->repository->getExecutiveInsights($user['tenant_id'], $user['branch_id'], $status);

        Response::success('Executive insights retrieved', $insights);
    }
}
