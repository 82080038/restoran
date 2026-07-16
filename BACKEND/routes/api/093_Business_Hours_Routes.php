<?php

// Business Hours Routes
$router->addRoute('POST', '/api/v1/business-hours', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->setHours($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/business-hours', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->getHours($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/business-hours/check', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->checkOpen($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/business-hours/special', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->createSpecialSchedule($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/business-hours/special', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->getSpecialSchedules($request);
    },
    $authMiddleware
));

$router->addRoute('DELETE', '/api/v1/business-hours/special/{schedule_id}', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->deleteSpecialSchedule($request);
    },
    $authMiddleware
));

