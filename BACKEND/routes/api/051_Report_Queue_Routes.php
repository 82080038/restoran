<?php

// Report Queue Routes
if (!class_exists('ReportQueueService')) {
    require_once __DIR__ . '/../../core/ReportQueueService.php';
}
$reportQueueService = new ReportQueueService();

$router->addRoute('POST', '/api/v1/accounting/reports/enqueue', withAuth(function($request) use ($reportQueueService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $data = $request['body'] ?? [];
    $result = $reportQueueService->enqueueReport($user['tenant_id'], $user['branch_id'], $user['user_id'], $data['report_type'], $data['report_name'], $data['parameters'] ?? [], $data['priority'] ?? 0);
    if ($result['success']) {
        Response::success($result, $result['message']);
    } else {
        Response::error($result['message']);
    }
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/accounting/reports/jobs', withAuth(function($request) use ($reportQueueService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $jobs = $reportQueueService->getUserReportJobs($user['user_id']);
    Response::success($jobs, 'Report jobs retrieved successfully');
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/accounting/reports/jobs/{id}', withAuth(function($request) use ($reportQueueService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $jobId = $request['params']['id'];
    $job = $reportQueueService->getReportJob($jobId);
    Response::success($job, 'Report job retrieved successfully');
}, $authMiddleware));

