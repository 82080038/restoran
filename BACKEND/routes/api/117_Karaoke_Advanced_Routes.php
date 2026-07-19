<?php

// Karaoke Advanced Routes (Song Catalog, Song Requests, In-Room F&B)
$router->addRoute('GET', '/api/v1/karaoke-advanced/songs', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getSongs($request);
});
$router->addRoute('GET', '/api/v1/karaoke-advanced/songs/popular', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getPopularSongs($request);
});
$router->addRoute('POST', '/api/v1/karaoke-advanced/songs', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->addSong($request);
});
$router->addRoute('POST', '/api/v1/karaoke-advanced/requests', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->requestSong($request);
});
$router->addRoute('GET', '/api/v1/karaoke-advanced/rooms/{room_id}/queue', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getRoomQueue($request);
});
$router->addRoute('POST', '/api/v1/karaoke-advanced/rooms/{room_id}/play-next', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->playNextSong($request);
});
$router->addRoute('POST', '/api/v1/karaoke-advanced/requests/{id}/skip', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->skipSong($request);
});
$router->addRoute('POST', '/api/v1/karaoke-advanced/room-orders', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->createRoomOrder($request);
});
$router->addRoute('GET', '/api/v1/karaoke-advanced/rooms/{room_id}/orders', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->getRoomOrders($request);
});
$router->addRoute('PATCH', '/api/v1/karaoke-advanced/room-orders/{id}/status', function($request) use ($karaokeAdvancedController) {
    return $karaokeAdvancedController->updateRoomOrderStatus($request);
});
