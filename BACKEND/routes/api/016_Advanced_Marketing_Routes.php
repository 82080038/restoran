<?php

// Advanced Marketing Routes
$router->addRoute('POST', '/api/v1/marketing/customer-segments', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->createCustomerSegment($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/marketing/customer-segments', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->getCustomerSegments($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/marketing/customer-segments/{id}/members', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->getSegmentMembers($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/marketing/email-campaigns', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->createEmailCampaign($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/marketing/email-campaigns/{id}/send', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->sendEmailCampaign($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/marketing/email-campaigns', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->getEmailCampaigns($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/marketing/track-email', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->trackEmailEngagement($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/marketing/promotion-tracking', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->getPromotionTracking($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/marketing/summary', withAuth(
    function($request) use ($advancedMarketingController) {
        return $advancedMarketingController->getSummary($request);
    },
    $authMiddleware
));

