<?php

// Reservation Routes
$router->addRoute('GET', '/api/v1/reservations', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->getReservations($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reservations/date/{date}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->getReservationsByDate($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reservations/{id}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->getReservation($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reservations/check-availability', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->checkAvailability($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/reservations', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->createReservation($request);
    },
    'RESERVATION_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/reservations/{id}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->updateReservation($request);
    },
    'RESERVATION_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/reservations/{id}/status', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->updateReservationStatus($request);
    },
    'RESERVATION_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/reservations/{id}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->deleteReservation($request);
    },
    'RESERVATION_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

