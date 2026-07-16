<?php

// Staff Scheduling Routes
$router->addRoute('POST', '/api/v1/staff-scheduling/shifts', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->createShift($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/staff-scheduling/shifts', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->getShifts($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/staff-scheduling/schedules', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->createSchedule($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/staff-scheduling/schedules', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->getSchedules($request);
    },
    $authMiddleware
));

