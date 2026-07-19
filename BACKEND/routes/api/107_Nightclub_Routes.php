<?php

// Nightclub / Discotheque Module Routes

// Dashboard Stats
$router->addRoute('GET', '/api/v1/nightclub/dashboard', function($request) use ($nightclubController) {
    return $nightclubController->getDashboardStats($request);
});

// Revenue Report (by date range, per-event breakdown)
$router->addRoute('GET', '/api/v1/nightclub/revenue-report', function($request) use ($nightclubController) {
    return $nightclubController->getRevenueReport($request);
});

// Events (DJ / Entertainment Schedule)
$router->addRoute('GET', '/api/v1/nightclub/events', function($request) use ($nightclubController) {
    return $nightclubController->getEvents($request);
});
$router->addRoute('GET', '/api/v1/nightclub/events/{id}', function($request) use ($nightclubController) {
    return $nightclubController->getEvent($request);
});
$router->addRoute('POST', '/api/v1/nightclub/events', function($request) use ($nightclubController) {
    return $nightclubController->createEvent($request);
});
$router->addRoute('PUT', '/api/v1/nightclub/events/{id}', function($request) use ($nightclubController) {
    return $nightclubController->updateEvent($request);
});
$router->addRoute('DELETE', '/api/v1/nightclub/events/{id}', function($request) use ($nightclubController) {
    return $nightclubController->deleteEvent($request);
});

// Entrance Fees (Cover Charge Config)
$router->addRoute('GET', '/api/v1/nightclub/entrance-fees', function($request) use ($nightclubController) {
    return $nightclubController->getEntranceFees($request);
});
$router->addRoute('POST', '/api/v1/nightclub/entrance-fees', function($request) use ($nightclubController) {
    return $nightclubController->createEntranceFee($request);
});
$router->addRoute('PUT', '/api/v1/nightclub/entrance-fees/{id}', function($request) use ($nightclubController) {
    return $nightclubController->updateEntranceFee($request);
});
$router->addRoute('DELETE', '/api/v1/nightclub/entrance-fees/{id}', function($request) use ($nightclubController) {
    return $nightclubController->deleteEntranceFee($request);
});

// Entrance Tickets (Sales & Check-in)
$router->addRoute('GET', '/api/v1/nightclub/entrance-tickets', function($request) use ($nightclubController) {
    return $nightclubController->getEntranceTickets($request);
});
$router->addRoute('POST', '/api/v1/nightclub/entrance-tickets', function($request) use ($nightclubController) {
    return $nightclubController->sellEntranceTicket($request);
});
$router->addRoute('POST', '/api/v1/nightclub/entrance-tickets/{id}/check-in', function($request) use ($nightclubController) {
    return $nightclubController->checkInTicket($request);
});

// Guest List
$router->addRoute('GET', '/api/v1/nightclub/guest-list', function($request) use ($nightclubController) {
    return $nightclubController->getGuestList($request);
});
$router->addRoute('POST', '/api/v1/nightclub/guest-list', function($request) use ($nightclubController) {
    return $nightclubController->addGuestListEntry($request);
});
$router->addRoute('PUT', '/api/v1/nightclub/guest-list/{id}', function($request) use ($nightclubController) {
    return $nightclubController->updateGuestListEntry($request);
});
$router->addRoute('POST', '/api/v1/nightclub/guest-list/{id}/check-in', function($request) use ($nightclubController) {
    return $nightclubController->checkInGuest($request);
});
$router->addRoute('DELETE', '/api/v1/nightclub/guest-list/{id}', function($request) use ($nightclubController) {
    return $nightclubController->deleteGuestListEntry($request);
});

// Bottle Service
$router->addRoute('GET', '/api/v1/nightclub/bottle-service', function($request) use ($nightclubController) {
    return $nightclubController->getBottleServiceReservations($request);
});
$router->addRoute('POST', '/api/v1/nightclub/bottle-service', function($request) use ($nightclubController) {
    return $nightclubController->createBottleServiceReservation($request);
});
$router->addRoute('PUT', '/api/v1/nightclub/bottle-service/{id}', function($request) use ($nightclubController) {
    return $nightclubController->updateBottleServiceReservation($request);
});
$router->addRoute('DELETE', '/api/v1/nightclub/bottle-service/{id}', function($request) use ($nightclubController) {
    return $nightclubController->deleteBottleServiceReservation($request);
});

// Table Reservations (VIP Booth)
$router->addRoute('GET', '/api/v1/nightclub/table-reservations', function($request) use ($nightclubController) {
    return $nightclubController->getTableReservations($request);
});
$router->addRoute('POST', '/api/v1/nightclub/table-reservations', function($request) use ($nightclubController) {
    return $nightclubController->createTableReservation($request);
});
$router->addRoute('PUT', '/api/v1/nightclub/table-reservations/{id}', function($request) use ($nightclubController) {
    return $nightclubController->updateTableReservation($request);
});
$router->addRoute('DELETE', '/api/v1/nightclub/table-reservations/{id}', function($request) use ($nightclubController) {
    return $nightclubController->deleteTableReservation($request);
});
