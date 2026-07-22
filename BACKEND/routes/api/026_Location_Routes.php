<?php

// Location Routes
$router->addRoute('POST', '/api/v1/location/nearby-branches', withAuth(function($request) use ($locationController) {
    return $locationController->findNearbyBranches($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/location/detect-branch', withAuth(function($request) use ($locationController) {
    return $locationController->detectNearbyBranch($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/location/branches/{id}/delivery-check', withAuth(function($request) use ($locationController) {
    return $locationController->checkDeliveryAvailability($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/location/branches/{id}', withAuth(function($request) use ($locationController) {
    return $locationController->getBranchLocation($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/location/branches/{id}', withAuth(function($request) use ($locationController) {
    return $locationController->updateBranchLocation($request);
}, $authMiddleware));

