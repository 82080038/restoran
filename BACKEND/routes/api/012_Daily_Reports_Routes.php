<?php

// Daily Reports Routes
$router->addRoute('GET', '/api/v1/daily-reports/sales', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getSalesReport($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/table-turnover', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getTableTurnover($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/server-performance', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getServerPerformance($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/peak-hours', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getPeakHours($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/comprehensive', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getComprehensive($request);
    },
    $authMiddleware
));

