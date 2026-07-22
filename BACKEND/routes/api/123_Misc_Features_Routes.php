<?php

// Misc Features Routes (Tier 4: Coat Check, Karaoke Score, Equipment, Radius Clause, Group Booking, Wine, Waiter Button, Entertainer Rotation)

// Coat Check
$router->addRoute('POST', '/api/v1/misc/coat-check/checkin', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->checkInCoat($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/coat-check/{id}/checkout', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->checkOutCoat($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/misc/coat-check', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getCoatCheckItems($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/misc/coat-check/stats', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getCoatCheckStats($request);
}, $authMiddleware));

// Karaoke Score
$router->addRoute('POST', '/api/v1/misc/karaoke-scores', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->recordScore($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/misc/karaoke-scores/high', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getHighScores($request);
}, $authMiddleware));

// Equipment Tracking
$router->addRoute('GET', '/api/v1/misc/equipment', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getEquipment($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/equipment', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addEquipment($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/equipment/{id}/assign', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->assignEquipment($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/equipment/assignments/{id}/return', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->returnEquipment($request);
}, $authMiddleware));

// Radius Clause
$router->addRoute('POST', '/api/v1/misc/radius-clause/check', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->checkRadiusClause($request);
}, $authMiddleware));

// Social Group Booking
$router->addRoute('POST', '/api/v1/misc/group-bookings', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->createGroupBooking($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/misc/group-bookings/{id}', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getGroupBooking($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/group-bookings/{id}/members', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addGroupMember($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/group-bookings/members/{member_id}/pay', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->payShare($request);
}, $authMiddleware));

// Wine Pairing
$router->addRoute('GET', '/api/v1/misc/wines', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getWines($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/wines', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addWine($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/wine-pairings', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addPairingSuggestion($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/misc/wine-pairings/{product_id}', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getPairingsForProduct($request);
}, $authMiddleware));

// Waiter Button
$router->addRoute('POST', '/api/v1/misc/waiter-button/press', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->recordPress($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/misc/waiter-button/{id}/respond', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->respondToPress($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/misc/waiter-button/stats', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getWaiterButtonStats($request);
}, $authMiddleware));

// Entertainer Rotation
$router->addRoute('POST', '/api/v1/misc/entertainer-rotation', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->addRotationSlot($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/misc/entertainer-rotation/{event_id}', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->getRotationSchedule($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/misc/entertainer-rotation/{id}/status', withAuth(function($request) use ($miscFeaturesController) {
    return $miscFeaturesController->updateRotationStatus($request);
}, $authMiddleware));
