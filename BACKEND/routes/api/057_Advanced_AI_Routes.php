<?php

// Advanced AI Routes
$router->addRoute('POST', '/api/v1/ai/menu-engineering', function($request) use ($advancedAIController) {
    return $advancedAIController->analyzeMenu($request);
});
$router->addRoute('GET', '/api/v1/ai/menu-engineering', function($request) use ($advancedAIController) {
    return $advancedAIController->getMenuAnalysis($request);
});
$router->addRoute('POST', '/api/v1/ai/staff-optimization', function($request) use ($advancedAIController) {
    return $advancedAIController->optimizeStaff($request);
});
$router->addRoute('POST', '/api/v1/ai/fraud-detection', function($request) use ($advancedAIController) {
    return $advancedAIController->detectFraud($request);
});
$router->addRoute('GET', '/api/v1/ai/fraud-alerts', function($request) use ($advancedAIController) {
    return $advancedAIController->getFraudAlerts($request);
});
$router->addRoute('POST', '/api/v1/ai/executive-insights', function($request) use ($advancedAIController) {
    return $advancedAIController->generateInsights($request);
});
$router->addRoute('GET', '/api/v1/ai/executive-insights', function($request) use ($advancedAIController) {
    return $advancedAIController->getExecutiveInsights($request);
});

