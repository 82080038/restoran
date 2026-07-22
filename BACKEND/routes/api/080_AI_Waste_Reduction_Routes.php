<?php

// AI Waste Reduction Routes
$router->addRoute('POST', '/api/v1/ai/waste/record', withAuth(function($request) use ($wasteReductionController) {
    return $wasteReductionController->recordWaste($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/ai/waste/report', withAuth(function($request) use ($wasteReductionController) {
    return $wasteReductionController->getWasteReport($request);
}, $authMiddleware));

