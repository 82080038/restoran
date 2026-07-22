<?php

// Nightclub / Discotheque Module Routes

// Dashboard Stats
$router->addRoute('GET', '/api/v1/nightclub/dashboard', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getDashboardStats($request);
}, $authMiddleware));

// Revenue Report (by date range, per-event breakdown)
$router->addRoute('GET', '/api/v1/nightclub/revenue-report', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getRevenueReport($request);
}, $authMiddleware));

// Events (DJ / Entertainment Schedule)
$router->addRoute('GET', '/api/v1/nightclub/events', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getEvents($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/nightclub/events/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getEvent($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/events', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->createEvent($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/nightclub/events/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->updateEvent($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/nightclub/events/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->deleteEvent($request);
}, $authMiddleware));

// Entrance Fees (Cover Charge Config)
$router->addRoute('GET', '/api/v1/nightclub/entrance-fees', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getEntranceFees($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/entrance-fees', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->createEntranceFee($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/nightclub/entrance-fees/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->updateEntranceFee($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/nightclub/entrance-fees/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->deleteEntranceFee($request);
}, $authMiddleware));

// Entrance Tickets (Sales & Check-in)
$router->addRoute('GET', '/api/v1/nightclub/entrance-tickets', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getEntranceTickets($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/entrance-tickets', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->sellEntranceTicket($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/entrance-tickets/{id}/check-in', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->checkInTicket($request);
}, $authMiddleware));

// Guest List
$router->addRoute('GET', '/api/v1/nightclub/guest-list', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getGuestList($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/guest-list', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->addGuestListEntry($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/nightclub/guest-list/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->updateGuestListEntry($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/guest-list/{id}/check-in', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->checkInGuest($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/nightclub/guest-list/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->deleteGuestListEntry($request);
}, $authMiddleware));

// Bottle Service
$router->addRoute('GET', '/api/v1/nightclub/bottle-service', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getBottleServiceReservations($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/bottle-service', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->createBottleServiceReservation($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/nightclub/bottle-service/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->updateBottleServiceReservation($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/nightclub/bottle-service/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->deleteBottleServiceReservation($request);
}, $authMiddleware));

// Table Reservations (VIP Booth)
$router->addRoute('GET', '/api/v1/nightclub/table-reservations', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->getTableReservations($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/nightclub/table-reservations', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->createTableReservation($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/nightclub/table-reservations/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->updateTableReservation($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/nightclub/table-reservations/{id}', withAuth(function($request) use ($nightclubController) {
    return $nightclubController->deleteTableReservation($request);
}, $authMiddleware));
