<?php

// HACCP Compliance Routes
$router->addRoute('POST', '/api/v1/haccp/ccps', withAuth(
    function($request) use ($haccpController) {
        return $haccpController->createCCP($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/haccp/ccps', withAuth(
    function($request) use ($haccpController) {
        return $haccpController->getCCPs($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/haccp/monitoring', withAuth(
    function($request) use ($haccpController) {
        return $haccpController->recordMonitoring($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/haccp/monitoring', withAuth(
    function($request) use ($haccpController) {
        return $haccpController->getMonitoringRecords($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/haccp/report', withAuth(
    function($request) use ($haccpController) {
        return $haccpController->generateHACCPReport($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/haccp/summary', withAuth(
    function($request) use ($haccpController) {
        return $haccpController->getSummary($request);
    },
    $authMiddleware
));

