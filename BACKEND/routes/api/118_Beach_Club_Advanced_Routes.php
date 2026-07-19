<?php

// Beach Club Advanced Routes (Seat Map, Weather/Rain Check)
$router->addRoute('GET', '/api/v1/beach-club/seat-map', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getSeatMap($request);
});
$router->addRoute('GET', '/api/v1/beach-club/seat-availability', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getSeatAvailability($request);
});
$router->addRoute('POST', '/api/v1/beach-club/seat-map', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->addSeat($request);
});
$router->addRoute('PATCH', '/api/v1/beach-club/seat-map/{id}/position', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->updateSeatPosition($request);
});
$router->addRoute('GET', '/api/v1/beach-club/rain-checks', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getRainChecks($request);
});
$router->addRoute('POST', '/api/v1/beach-club/rain-checks', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->createRainCheck($request);
});
$router->addRoute('POST', '/api/v1/beach-club/rain-checks/{id}/reschedule', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->rescheduleRainCheck($request);
});
$router->addRoute('POST', '/api/v1/beach-club/rain-checks/{id}/refund', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->refundRainCheck($request);
});
$router->addRoute('GET', '/api/v1/beach-club/weather-policies', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->getWeatherPolicies($request);
});
$router->addRoute('POST', '/api/v1/beach-club/weather-policies', function($request) use ($beachClubAdvancedController) {
    return $beachClubAdvancedController->createWeatherPolicy($request);
});
