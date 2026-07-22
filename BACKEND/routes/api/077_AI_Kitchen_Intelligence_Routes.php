<?php

// AI Kitchen Intelligence Routes
$router->addRoute('POST', '/api/v1/ai/kitchen/analyze', withAuth(function($request) use ($kitchenIntelligenceController) {
    return $kitchenIntelligenceController->analyzePerformance($request);
}, $authMiddleware));

