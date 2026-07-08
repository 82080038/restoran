<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\APIMarketplaceService;
use App\Core\Response;

class APIMarketplaceController
{
    private $service;

    public function __construct()
    {
        $this->service = new APIMarketplaceService();
    }

    /**
     * Generate API key
     * POST /api/v1/api-marketplace/keys
     */
    public function generateAPIKey($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->generateAPIKey($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get API keys
     * GET /api/v1/api-marketplace/keys
     */
    public function getAPIKeys($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $status = $request->status ?? null;

        $keys = $this->service->getAPIKeys($tenantId, $status);

        return Response::json([
            'success' => true,
            'data' => $keys
        ]);
    }

    /**
     * Revoke API key
     * DELETE /api/v1/api-marketplace/keys/{id}
     */
    public function revokeAPIKey($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;
        $keyId = $request->id;

        $result = $this->service->revokeAPIKey($keyId, $tenantId, $userId);

        return Response::json($result);
    }

    /**
     * Create webhook
     * POST /api/v1/api-marketplace/webhooks
     */
    public function createWebhook($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createWebhook($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get webhooks
     * GET /api/v1/api-marketplace/webhooks
     */
    public function getWebhooks($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $status = $request->status ?? null;

        $webhooks = $this->service->getWebhooks($tenantId, $status);

        return Response::json([
            'success' => true,
            'data' => $webhooks
        ]);
    }

    /**
     * Get API analytics
     * GET /api/v1/api-marketplace/analytics
     */
    public function getAPIAnalytics($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $analytics = $this->service->getAPIAnalytics($tenantId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get API summary
     * GET /api/v1/api-marketplace/summary
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
