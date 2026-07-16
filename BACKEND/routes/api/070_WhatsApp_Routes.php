<?php

// WhatsApp Routes
$router->addRoute('POST', '/api/v1/whatsapp/settings', function($request) use ($whatsappController) {
    return $whatsappController->saveSettings($request);
});
$router->addRoute('GET', '/api/v1/whatsapp/settings', function($request) use ($whatsappController) {
    return $whatsappController->getSettings($request);
});
$router->addRoute('POST', '/api/v1/whatsapp/send', function($request) use ($whatsappController) {
    return $whatsappController->sendMessage($request);
});
$router->addRoute('POST', '/api/v1/whatsapp/reports/{type}/send', function($request) use ($whatsappController) {
    return $whatsappController->sendReport($request);
});
$router->addRoute('POST', '/api/v1/whatsapp/report-schedules', function($request) use ($whatsappController) {
    return $whatsappController->createReportSchedule($request);
});
$router->addRoute('GET', '/api/v1/whatsapp/report-schedules', function($request) use ($whatsappController) {
    return $whatsappController->getReportSchedules($request);
});
$router->addRoute('GET', '/api/v1/whatsapp/message-logs', function($request) use ($whatsappController) {
    return $whatsappController->getMessageLogs($request);
});

