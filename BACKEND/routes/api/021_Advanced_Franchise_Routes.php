<?php

// Advanced Franchise Routes
$router->addRoute('POST', '/api/v1/franchise/compliance-checklists', withAuth(
    function($request) use ($advancedFranchiseController) {
        return $advancedFranchiseController->createComplianceChecklist($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/franchise/compliance-audits', withAuth(
    function($request) use ($advancedFranchiseController) {
        return $advancedFranchiseController->recordComplianceAudit($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/franchise/compliance-audits', withAuth(
    function($request) use ($advancedFranchiseController) {
        return $advancedFranchiseController->getComplianceAudits($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/franchise/performance-report', withAuth(
    function($request) use ($advancedFranchiseController) {
        return $advancedFranchiseController->generateFranchiseReport($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/franchise/summary', withAuth(
    function($request) use ($advancedFranchiseController) {
        return $advancedFranchiseController->getSummary($request);
    },
    $authMiddleware
));

