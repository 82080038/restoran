<?php

// Advanced AI Routes
$router->addRoute('POST', '/api/v1/ai/menu-engineering', withAuth(function($request) use ($advancedAIController) {
    return $advancedAIController->analyzeMenu($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/ai/menu-engineering', withAuth(function($request) use ($advancedAIController) {
    return $advancedAIController->getMenuAnalysis($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/ai/staff-optimization', withAuth(function($request) use ($advancedAIController) {
    return $advancedAIController->optimizeStaff($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/ai/fraud-detection', withAuth(function($request) use ($advancedAIController) {
    return $advancedAIController->detectFraud($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/ai/fraud-alerts', withAuth(function($request) use ($advancedAIController) {
    return $advancedAIController->getFraudAlerts($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/ai/executive-insights', withAuth(function($request) use ($advancedAIController) {
    return $advancedAIController->generateInsights($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/ai/executive-insights', withAuth(function($request) use ($advancedAIController) {
    return $advancedAIController->getExecutiveInsights($request);
}, $authMiddleware));

