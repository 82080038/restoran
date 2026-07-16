<?php

// Attendance Routes
$router->addRoute('POST', '/api/v1/attendance/check-in', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->checkIn($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/attendance/check-out', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->checkOut($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/attendance', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->getAttendance($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/attendance/break/start', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->startBreak($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/attendance/break/end', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->endBreak($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/attendance/summary', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->getSummary($request);
    },
    $authMiddleware
));

