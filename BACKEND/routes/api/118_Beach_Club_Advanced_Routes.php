<?php

// Beach Club Advanced Routes (Seat Map, Weather/Rain Check)
$router->addRoute('GET', '/api/v1/beach-club/seat-map', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getSeatMap($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beach-club/seat-availability', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getSeatAvailability($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beach-club/seat-map', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->addSeat($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/beach-club/seat-map/{id}/position', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->updateSeatPosition($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beach-club/rain-checks', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getRainChecks($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beach-club/rain-checks', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->createRainCheck($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beach-club/rain-checks/{id}/reschedule', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->rescheduleRainCheck($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beach-club/rain-checks/{id}/refund', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->refundRainCheck($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beach-club/weather-policies', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getWeatherPolicies($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beach-club/weather-policies', withAuth(function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->createWeatherPolicy($request);
}, $authMiddleware));
