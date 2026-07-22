<?php

// Karaoke Advanced Routes (Song Catalog, Song Requests, In-Room F&B)
$router->addRoute('GET', '/api/v1/karaoke-advanced/songs', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getSongs($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/karaoke-advanced/songs/popular', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getPopularSongs($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/karaoke-advanced/songs', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->addSong($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/karaoke-advanced/requests', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->requestSong($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/karaoke-advanced/rooms/{room_id}/queue', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getRoomQueue($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/karaoke-advanced/rooms/{room_id}/play-next', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->playNextSong($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/karaoke-advanced/requests/{id}/skip', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->skipSong($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/karaoke-advanced/room-orders', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->createRoomOrder($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/karaoke-advanced/rooms/{room_id}/orders', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getRoomOrders($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/karaoke-advanced/room-orders/{id}/status', withAuth(function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->updateRoomOrderStatus($request);
}, $authMiddleware));
