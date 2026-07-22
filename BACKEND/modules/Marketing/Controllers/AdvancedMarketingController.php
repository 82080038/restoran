<?php

namespace App\Modules\Marketing\Controllers;

use App\Modules\Marketing\Services\AdvancedMarketingService;
use App\Core\Response;

class AdvancedMarketingController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdvancedMarketingService();
    }

    /**
     * Create customer segment
     * POST /api/v1/marketing/customer-segments
     */
    public function createCustomerSegment($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createCustomerSegment($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Get customer segments
     * GET /api/v1/marketing/customer-segments
     */
    public function getCustomerSegments($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $status = $request->status ?? null;

        $segments = $this->service->getCustomerSegments($tenantId, $status);

        return Response::json([
            'success' => true,
            'data' => $segments
        ]);
    }

    /**
     * Get segment members
     * GET /api/v1/marketing/customer-segments/{id}/members
     */
    public function getSegmentMembers($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $segmentId = $request->id;

        $members = $this->service->getSegmentMembers($segmentId, $tenantId);

        return Response::json([
            'success' => true,
            'data' => $members
        ]);
    }

    /**
     * Create email campaign
     * POST /api/v1/marketing/email-campaigns
     */
    public function createEmailCampaign($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;

        $result = $this->service->createEmailCampaign($tenantId, $userId, $request);

        return Response::json($result);
    }

    /**
     * Send email campaign
     * POST /api/v1/marketing/email-campaigns/{id}/send
     */
    public function sendEmailCampaign($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? 1;
        $campaignId = $request->id;

        $result = $this->service->sendEmailCampaign($campaignId, $tenantId, $userId);

        return Response::json($result);
    }

    /**
     * Get email campaigns
     * GET /api/v1/marketing/email-campaigns
     */
    public function getEmailCampaigns($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $status = $request->status ?? null;

        $campaigns = $this->service->getEmailCampaigns($tenantId, $status);

        return Response::json([
            'success' => true,
            'data' => $campaigns
        ]);
    }

    /**
     * Track email engagement
     * POST /api/v1/marketing/track-email
     */
    public function trackEmailEngagement($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $result = $this->service->trackEmailEngagement($tenantId, $request);

        return Response::json($result);
    }

    /**
     * Get promotion tracking
     * GET /api/v1/marketing/promotion-tracking
     */
    public function getPromotionTracking($request)
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $promotionId = $request->promotion_id ?? null;
        $dateFrom = $request->date_from ?? null;
        $dateTo = $request->date_to ?? null;

        $tracking = $this->service->getPromotionTracking($tenantId, $promotionId, $dateFrom, $dateTo);

        return Response::json([
            'success' => true,
            'data' => $tracking
        ]);
    }

    /**
     * Get marketing summary
     * GET /api/v1/marketing/summary
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
