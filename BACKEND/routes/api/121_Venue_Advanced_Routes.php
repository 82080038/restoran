<?php

// Venue Advanced Routes (Dynamic Pricing, Membership, QR Scan, Occupancy, Karaoke Calendar, Overtime, Holds, Comp List)

// Dynamic Pricing
$router->addRoute('GET', '/api/v1/venue/dynamic-pricing/rules', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getPricingRules($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/dynamic-pricing/rules', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->createPricingRule($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/dynamic-pricing/calculate', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->calculatePrice($request);
}, $authMiddleware));

// Membership
$router->addRoute('GET', '/api/v1/venue/memberships', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getMemberships($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/memberships', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->createMembership($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/memberships/{id}/earn', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->earnPoints($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/memberships/{id}/redeem', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->redeemPoints($request);
}, $authMiddleware));

// QR Ticket Scanning
$router->addRoute('POST', '/api/v1/venue/qr-scan', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->scanTicket($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/venue/qr-scan/stats/{event_id}', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getScanStats($request);
}, $authMiddleware));

// Occupancy
$router->addRoute('GET', '/api/v1/venue/occupancy', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getOccupancy($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/occupancy/entry', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->recordEntry($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/occupancy/exit', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->recordExit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/occupancy/capacity', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->setMaxCapacity($request);
}, $authMiddleware));

// Karaoke Room Calendar
$router->addRoute('GET', '/api/v1/venue/karaoke/calendar/{room_id}', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getRoomCalendar($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/karaoke/calendar', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->addCalendarBlock($request);
}, $authMiddleware));

// Karaoke Overtime
$router->addRoute('POST', '/api/v1/venue/karaoke/overtime/calculate', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->calculateOvertime($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/karaoke/overtime/{id}/waive', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->waiveOvertime($request);
}, $authMiddleware));

// Holds Calendar
$router->addRoute('GET', '/api/v1/venue/holds', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getHolds($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/holds', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->addHold($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/holds/{id}/release', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->releaseHold($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/holds/{id}/confirm', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->confirmHold($request);
}, $authMiddleware));

// Comp / Guest List
$router->addRoute('GET', '/api/v1/venue/comp-list/{event_id}', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getCompList($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/comp-list', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->addCompGuest($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/venue/comp-list/{id}/checkin', withAuth(function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->checkInCompGuest($request);
}, $authMiddleware));
