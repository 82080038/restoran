<?php

// Venue Advanced Routes (Dynamic Pricing, Membership, QR Scan, Occupancy, Karaoke Calendar, Overtime, Holds, Comp List)

// Dynamic Pricing
$router->addRoute('GET', '/api/v1/venue/dynamic-pricing/rules', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getPricingRules($request);
});
$router->addRoute('POST', '/api/v1/venue/dynamic-pricing/rules', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->createPricingRule($request);
});
$router->addRoute('POST', '/api/v1/venue/dynamic-pricing/calculate', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->calculatePrice($request);
});

// Membership
$router->addRoute('GET', '/api/v1/venue/memberships', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getMemberships($request);
});
$router->addRoute('POST', '/api/v1/venue/memberships', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->createMembership($request);
});
$router->addRoute('POST', '/api/v1/venue/memberships/{id}/earn', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->earnPoints($request);
});
$router->addRoute('POST', '/api/v1/venue/memberships/{id}/redeem', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->redeemPoints($request);
});

// QR Ticket Scanning
$router->addRoute('POST', '/api/v1/venue/qr-scan', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->scanTicket($request);
});
$router->addRoute('GET', '/api/v1/venue/qr-scan/stats/{event_id}', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getScanStats($request);
});

// Occupancy
$router->addRoute('GET', '/api/v1/venue/occupancy', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getOccupancy($request);
});
$router->addRoute('POST', '/api/v1/venue/occupancy/entry', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->recordEntry($request);
});
$router->addRoute('POST', '/api/v1/venue/occupancy/exit', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->recordExit($request);
});
$router->addRoute('POST', '/api/v1/venue/occupancy/capacity', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->setMaxCapacity($request);
});

// Karaoke Room Calendar
$router->addRoute('GET', '/api/v1/venue/karaoke/calendar/{room_id}', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getRoomCalendar($request);
});
$router->addRoute('POST', '/api/v1/venue/karaoke/calendar', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->addCalendarBlock($request);
});

// Karaoke Overtime
$router->addRoute('POST', '/api/v1/venue/karaoke/overtime/calculate', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->calculateOvertime($request);
});
$router->addRoute('POST', '/api/v1/venue/karaoke/overtime/{id}/waive', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->waiveOvertime($request);
});

// Holds Calendar
$router->addRoute('GET', '/api/v1/venue/holds', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getHolds($request);
});
$router->addRoute('POST', '/api/v1/venue/holds', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->addHold($request);
});
$router->addRoute('POST', '/api/v1/venue/holds/{id}/release', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->releaseHold($request);
});
$router->addRoute('POST', '/api/v1/venue/holds/{id}/confirm', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->confirmHold($request);
});

// Comp / Guest List
$router->addRoute('GET', '/api/v1/venue/comp-list/{event_id}', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->getCompList($request);
});
$router->addRoute('POST', '/api/v1/venue/comp-list', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->addCompGuest($request);
});
$router->addRoute('POST', '/api/v1/venue/comp-list/{id}/checkin', function($request) use ($venueAdvancedController) {
    return $venueAdvancedController->checkInCompGuest($request);
});
