<?php

// Entertainment Module Routes (Karaoke Bar, Beach Club, Live Music Venue)

// Dashboard Stats (requires business_type query param)
$router->addRoute('GET', '/api/v1/entertainment/dashboard', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getDashboardStats($request);
}, $authMiddleware));

// Revenue Report (requires business_type query param)
$router->addRoute('GET', '/api/v1/entertainment/revenue-report', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getRevenueReport($request);
}, $authMiddleware));

// ==================== KARAOKE BAR ====================
$router->addRoute('GET', '/api/v1/entertainment/karaoke/rooms', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getKaraokeRooms($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/karaoke/rooms', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createKaraokeRoom($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/entertainment/karaoke/reservations', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getKaraokeReservations($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/karaoke/reservations', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createKaraokeReservation($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/karaoke/reservations/{id}/check-in', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->checkInKaraoke($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/karaoke/reservations/{id}/check-out', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->checkOutKaraoke($request);
}, $authMiddleware));

// ==================== BEACH CLUB ====================
$router->addRoute('GET', '/api/v1/entertainment/beach/cabanas', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getBeachCabanas($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/beach/cabanas', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createBeachCabana($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/entertainment/beach/reservations', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getBeachReservations($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/beach/reservations', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createBeachReservation($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/entertainment/beach/events', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getBeachEvents($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/beach/events', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createBeachEvent($request);
}, $authMiddleware));

// ==================== LIVE MUSIC VENUE ====================
$router->addRoute('GET', '/api/v1/entertainment/concerts', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getConcerts($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/concerts', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createConcert($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/entertainment/seating-sections', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getSeatingSections($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/seating-sections', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createSeatingSection($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/entertainment/tickets', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->getConcertTickets($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/tickets', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->createConcertTicket($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/entertainment/tickets/{id}/check-in', withAuth(function($request) use ($entertainmentController) {
    return $entertainmentController->checkInConcertTicket($request);
}, $authMiddleware));
