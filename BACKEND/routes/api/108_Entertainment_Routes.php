<?php

// Entertainment Module Routes (Karaoke Bar, Beach Club, Live Music Venue)

// Dashboard Stats (requires business_type query param)
$router->addRoute('GET', '/api/v1/entertainment/dashboard', function($request) use ($entertainmentController) {
    return $entertainmentController->getDashboardStats($request);
});

// Revenue Report (requires business_type query param)
$router->addRoute('GET', '/api/v1/entertainment/revenue-report', function($request) use ($entertainmentController) {
    return $entertainmentController->getRevenueReport($request);
});

// ==================== KARAOKE BAR ====================
$router->addRoute('GET', '/api/v1/entertainment/karaoke/rooms', function($request) use ($entertainmentController) {
    return $entertainmentController->getKaraokeRooms($request);
});
$router->addRoute('POST', '/api/v1/entertainment/karaoke/rooms', function($request) use ($entertainmentController) {
    return $entertainmentController->createKaraokeRoom($request);
});
$router->addRoute('GET', '/api/v1/entertainment/karaoke/reservations', function($request) use ($entertainmentController) {
    return $entertainmentController->getKaraokeReservations($request);
});
$router->addRoute('POST', '/api/v1/entertainment/karaoke/reservations', function($request) use ($entertainmentController) {
    return $entertainmentController->createKaraokeReservation($request);
});
$router->addRoute('POST', '/api/v1/entertainment/karaoke/reservations/{id}/check-in', function($request) use ($entertainmentController) {
    return $entertainmentController->checkInKaraoke($request);
});
$router->addRoute('POST', '/api/v1/entertainment/karaoke/reservations/{id}/check-out', function($request) use ($entertainmentController) {
    return $entertainmentController->checkOutKaraoke($request);
});

// ==================== BEACH CLUB ====================
$router->addRoute('GET', '/api/v1/entertainment/beach/cabanas', function($request) use ($entertainmentController) {
    return $entertainmentController->getBeachCabanas($request);
});
$router->addRoute('POST', '/api/v1/entertainment/beach/cabanas', function($request) use ($entertainmentController) {
    return $entertainmentController->createBeachCabana($request);
});
$router->addRoute('GET', '/api/v1/entertainment/beach/reservations', function($request) use ($entertainmentController) {
    return $entertainmentController->getBeachReservations($request);
});
$router->addRoute('POST', '/api/v1/entertainment/beach/reservations', function($request) use ($entertainmentController) {
    return $entertainmentController->createBeachReservation($request);
});
$router->addRoute('GET', '/api/v1/entertainment/beach/events', function($request) use ($entertainmentController) {
    return $entertainmentController->getBeachEvents($request);
});
$router->addRoute('POST', '/api/v1/entertainment/beach/events', function($request) use ($entertainmentController) {
    return $entertainmentController->createBeachEvent($request);
});

// ==================== LIVE MUSIC VENUE ====================
$router->addRoute('GET', '/api/v1/entertainment/concerts', function($request) use ($entertainmentController) {
    return $entertainmentController->getConcerts($request);
});
$router->addRoute('POST', '/api/v1/entertainment/concerts', function($request) use ($entertainmentController) {
    return $entertainmentController->createConcert($request);
});
$router->addRoute('GET', '/api/v1/entertainment/seating-sections', function($request) use ($entertainmentController) {
    return $entertainmentController->getSeatingSections($request);
});
$router->addRoute('POST', '/api/v1/entertainment/seating-sections', function($request) use ($entertainmentController) {
    return $entertainmentController->createSeatingSection($request);
});
$router->addRoute('GET', '/api/v1/entertainment/tickets', function($request) use ($entertainmentController) {
    return $entertainmentController->getConcertTickets($request);
});
$router->addRoute('POST', '/api/v1/entertainment/tickets', function($request) use ($entertainmentController) {
    return $entertainmentController->createConcertTicket($request);
});
$router->addRoute('POST', '/api/v1/entertainment/tickets/{id}/check-in', function($request) use ($entertainmentController) {
    return $entertainmentController->checkInConcertTicket($request);
});
