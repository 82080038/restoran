<?php

// Report Routes
$router->addRoute('GET', '/api/v1/reports/sales', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getSalesReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/top-products', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getTopSellingProducts($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/inventory', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getInventoryReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/stock-movement', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getStockMovementReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/kitchen-performance', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getKitchenPerformanceReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/reservations', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getReservationReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/financial', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getFinancialReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/dashboard', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getDashboardSummary($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/profit-loss', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getProfitLossReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/cost-analysis', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getCostAnalysisReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/food-cost', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getFoodCostPercentage($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/sales-by-hour', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getSalesByHour($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/payment-breakdown', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getPaymentMethodBreakdown($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/inventory-usage', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getInventoryUsageReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/labor-cost', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getLaborCostAnalysis($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/tax', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getTaxReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

