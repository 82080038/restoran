<?php

// AI Waste Reduction Routes
$router->addRoute('POST', '/api/v1/ai/waste/record', function($request) use ($wasteReductionController) {
    return $wasteReductionController->recordWaste($request);
});
$router->addRoute('GET', '/api/v1/ai/waste/report', function($request) use ($wasteReductionController) {
    return $wasteReductionController->getWasteReport($request);
});

