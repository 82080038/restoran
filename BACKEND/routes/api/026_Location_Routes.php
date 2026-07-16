<?php

// Location Routes
$router->addRoute('POST', '/api/v1/location/nearby-branches', function($request) use ($locationController) {
    return $locationController->findNearbyBranches($request);
});
$router->addRoute('POST', '/api/v1/location/detect-branch', function($request) use ($locationController) {
    return $locationController->detectNearbyBranch($request);
});
$router->addRoute('POST', '/api/v1/location/branches/{id}/delivery-check', function($request) use ($locationController) {
    return $locationController->checkDeliveryAvailability($request);
});
$router->addRoute('GET', '/api/v1/location/branches/{id}', function($request) use ($locationController) {
    return $locationController->getBranchLocation($request);
});
$router->addRoute('PUT', '/api/v1/location/branches/{id}', function($request) use ($locationController) {
    return $locationController->updateBranchLocation($request);
});

