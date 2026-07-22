<?php

// AI Smart Procurement Routes
$router->addRoute('POST', '/api/v1/ai/procurement/recommendations', withAuth(function($request) use ($smartProcurementController) {
    return $smartProcurementController->generateRecommendation($request);
}, $authMiddleware));

