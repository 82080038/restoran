<?php

// WhatsApp Routes
$router->addRoute('POST', '/api/v1/whatsapp/settings', withAuth(function($request) use ($whatsappController) {
    return $whatsappController->saveSettings($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/whatsapp/settings', withAuth(function($request) use ($whatsappController) {
    return $whatsappController->getSettings($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/whatsapp/send', withAuth(function($request) use ($whatsappController) {
    return $whatsappController->sendMessage($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/whatsapp/reports/{type}/send', withAuth(function($request) use ($whatsappController) {
    return $whatsappController->sendReport($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/whatsapp/report-schedules', withAuth(function($request) use ($whatsappController) {
    return $whatsappController->createReportSchedule($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/whatsapp/report-schedules', withAuth(function($request) use ($whatsappController) {
    return $whatsappController->getReportSchedules($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/whatsapp/message-logs', withAuth(function($request) use ($whatsappController) {
    return $whatsappController->getMessageLogs($request);
}, $authMiddleware));

