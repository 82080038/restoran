<?php

// AI Customer Intelligence Routes
$router->addRoute('POST', '/api/v1/ai/customer/analyze', function($request) use ($customerIntelligenceController) {
    return $customerIntelligenceController->analyzeBehavior($request);
});

