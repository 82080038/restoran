<?php

// Quality Compliance Routes
$router->addRoute('POST', '/api/v1/quality/compliance-checks', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->createComplianceCheck($request);
});
$router->addRoute('GET', '/api/v1/quality/compliance-report', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->getComplianceReport($request);
});
$router->addRoute('POST', '/api/v1/quality/food-safety-protocols', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->addFoodSafetyProtocol($request);
});
$router->addRoute('GET', '/api/v1/quality/food-safety-protocols', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->getFoodSafetyProtocols($request);
});

// Loyalty Routes - Points
$router->addRoute('GET', '/api/v1/loyalty/points', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getPoints($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/points/award', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->awardPoints($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/points/redeem', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->redeemPoints($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));

// Loyalty Routes - Rewards
$router->addRoute('GET', '/api/v1/loyalty/rewards', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getRewards($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/rewards/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getReward($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/rewards', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->createReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/loyalty/rewards/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->updateReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/loyalty/rewards/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->deleteReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/rewards/{id}/redeem', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->redeemReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));

// Loyalty Routes - Customer Loyalty
$router->addRoute('GET', '/api/v1/loyalty/customers', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getCustomerLoyalty($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/customers/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getCustomerLoyaltyByCustomer($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/customers/enroll', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->enrollCustomer($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/customers/top', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getTopCustomers($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/customers/tier/{tier}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getCustomersByTier($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Feature Toggle Module
if (!class_exists('FeatureToggleController')) {
    require_once __DIR__ . '/../../core/FeatureToggleController.php';
}

