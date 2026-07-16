<?php

// Report Export Routes
$router->addRoute('GET', '/api/v1/reports/export/{type}/{format}', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->exportReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

