<?php

// Misc Features Routes (Tier 4: Coat Check, Karaoke Score, Equipment, Radius Clause, Group Booking, Wine, Waiter Button, Entertainer Rotation)

// Coat Check
$router->addRoute('POST', '/api/v1/misc/coat-check/checkin', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->checkInCoat($request);
});
$router->addRoute('POST', '/api/v1/misc/coat-check/{id}/checkout', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->checkOutCoat($request);
});
$router->addRoute('GET', '/api/v1/misc/coat-check', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getCoatCheckItems($request);
});
$router->addRoute('GET', '/api/v1/misc/coat-check/stats', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getCoatCheckStats($request);
});

// Karaoke Score
$router->addRoute('POST', '/api/v1/misc/karaoke-scores', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->recordScore($request);
});
$router->addRoute('GET', '/api/v1/misc/karaoke-scores/high', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getHighScores($request);
});

// Equipment Tracking
$router->addRoute('GET', '/api/v1/misc/equipment', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getEquipment($request);
});
$router->addRoute('POST', '/api/v1/misc/equipment', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addEquipment($request);
});
$router->addRoute('POST', '/api/v1/misc/equipment/{id}/assign', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->assignEquipment($request);
});
$router->addRoute('POST', '/api/v1/misc/equipment/assignments/{id}/return', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->returnEquipment($request);
});

// Radius Clause
$router->addRoute('POST', '/api/v1/misc/radius-clause/check', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->checkRadiusClause($request);
});

// Social Group Booking
$router->addRoute('POST', '/api/v1/misc/group-bookings', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->createGroupBooking($request);
});
$router->addRoute('GET', '/api/v1/misc/group-bookings/{id}', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getGroupBooking($request);
});
$router->addRoute('POST', '/api/v1/misc/group-bookings/{id}/members', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addGroupMember($request);
});
$router->addRoute('POST', '/api/v1/misc/group-bookings/members/{member_id}/pay', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->payShare($request);
});

// Wine Pairing
$router->addRoute('GET', '/api/v1/misc/wines', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getWines($request);
});
$router->addRoute('POST', '/api/v1/misc/wines', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addWine($request);
});
$router->addRoute('POST', '/api/v1/misc/wine-pairings', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addPairingSuggestion($request);
});
$router->addRoute('GET', '/api/v1/misc/wine-pairings/{product_id}', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getPairingsForProduct($request);
});

// Waiter Button
$router->addRoute('POST', '/api/v1/misc/waiter-button/press', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->recordPress($request);
});
$router->addRoute('POST', '/api/v1/misc/waiter-button/{id}/respond', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->respondToPress($request);
});
$router->addRoute('GET', '/api/v1/misc/waiter-button/stats', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getWaiterButtonStats($request);
});

// Entertainer Rotation
$router->addRoute('POST', '/api/v1/misc/entertainer-rotation', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addRotationSlot($request);
});
$router->addRoute('GET', '/api/v1/misc/entertainer-rotation/{event_id}', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getRotationSchedule($request);
});
$router->addRoute('PATCH', '/api/v1/misc/entertainer-rotation/{id}/status', function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->updateRotationStatus($request);
});
