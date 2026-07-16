<?php

// Holiday Routes
$router->addRoute('POST', '/api/v1/holidays', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->create($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/holidays', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->getHolidays($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/holidays/{holiday_id}', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->update($request);
    },
    $authMiddleware
));

$router->addRoute('DELETE', '/api/v1/holidays/{holiday_id}', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->delete($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/holidays/check', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->checkHoliday($request);
    },
    $authMiddleware
));

