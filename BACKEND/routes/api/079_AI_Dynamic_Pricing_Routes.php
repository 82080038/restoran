<?php

// AI Dynamic Pricing Routes
$router->addRoute('POST', '/api/v1/ai/pricing/generate', withAuth(function($request) use ($dynamicPricingController) {
    return $dynamicPricingController->generatePricing($request);
}, $authMiddleware));

