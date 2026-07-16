<?php

// Advanced Operations Routes - KDS, Waitlist, Peak Hour, Course Firing, AYCE, Load Balancing, Performance Monitoring

// ========================================================
// KDS SCREEN ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/kds/screens', function($request) use ($kdsScreenController) {
    return $kdsScreenController->getScreens($request);
});

$router->addRoute('GET', '/api/v1/kds/screens/{id}', function($request) use ($kdsScreenController) {
    return $kdsScreenController->getScreen($request);
});

$router->addRoute('POST', '/api/v1/kds/screens', function($request) use ($kdsScreenController) {
    return $kdsScreenController->createScreen($request);
});

$router->addRoute('PUT', '/api/v1/kds/screens/{id}', function($request) use ($kdsScreenController) {
    return $kdsScreenController->updateScreen($request);
});

$router->addRoute('DELETE', '/api/v1/kds/screens/{id}', function($request) use ($kdsScreenController) {
    return $kdsScreenController->deleteScreen($request);
});

// ========================================================
// KDS ROUTING RULE ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/kds/routing-rules', function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->getRoutingRules($request);
});

$router->addRoute('GET', '/api/v1/kds/routing-rules/{id}', function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->getRoutingRule($request);
});

$router->addRoute('POST', '/api/v1/kds/routing-rules', function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->createRoutingRule($request);
});

$router->addRoute('PUT', '/api/v1/kds/routing-rules/{id}', function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->updateRoutingRule($request);
});

$router->addRoute('DELETE', '/api/v1/kds/routing-rules/{id}', function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->deleteRoutingRule($request);
});

$router->addRoute('POST', '/api/v1/kds/routing-rules/apply', function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->applyRoutingRules($request);
});

// ========================================================
// KDS TICKET ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/kds/tickets', function($request) use ($kdsTicketController) {
    return $kdsTicketController->getTickets($request);
});

$router->addRoute('GET', '/api/v1/kds/tickets/{id}', function($request) use ($kdsTicketController) {
    return $kdsTicketController->getTicket($request);
});

$router->addRoute('POST', '/api/v1/kds/tickets', function($request) use ($kdsTicketController) {
    return $kdsTicketController->createTicket($request);
});

$router->addRoute('PUT', '/api/v1/kds/tickets/{id}/status', function($request) use ($kdsTicketController) {
    return $kdsTicketController->updateTicketStatus($request);
});

$router->addRoute('POST', '/api/v1/kds/tickets/update-urgency', function($request) use ($kdsTicketController) {
    return $kdsTicketController->updateUrgencyLevels($request);
});

$router->addRoute('DELETE', '/api/v1/kds/tickets/{id}', function($request) use ($kdsTicketController) {
    return $kdsTicketController->deleteTicket($request);
});

// ========================================================
// WAITLIST ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/waitlist', function($request) use ($waitlistController) {
    return $waitlistController->getWaitlistEntries($request);
});

$router->addRoute('GET', '/api/v1/waitlist/{id}', function($request) use ($waitlistController) {
    return $waitlistController->getWaitlistEntry($request);
});

$router->addRoute('POST', '/api/v1/waitlist', function($request) use ($waitlistController) {
    return $waitlistController->createWaitlistEntry($request);
});

$router->addRoute('PUT', '/api/v1/waitlist/{id}', function($request) use ($waitlistController) {
    return $waitlistController->updateWaitlistEntry($request);
});

$router->addRoute('POST', '/api/v1/waitlist/{id}/seat', function($request) use ($waitlistController) {
    return $waitlistController->seatGuest($request);
});

$router->addRoute('DELETE', '/api/v1/waitlist/{id}', function($request) use ($waitlistController) {
    return $waitlistController->deleteWaitlistEntry($request);
});

// ========================================================
// PEAK HOUR ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/peak-hours', function($request) use ($peakHourController) {
    return $peakHourController->getPeakHourSchedules($request);
});

$router->addRoute('GET', '/api/v1/operations/peak-hours/current', function($request) use ($peakHourController) {
    return $peakHourController->getCurrentPeakHour($request);
});

$router->addRoute('POST', '/api/v1/operations/peak-hours', function($request) use ($peakHourController) {
    return $peakHourController->createPeakHourSchedule($request);
});

$router->addRoute('PUT', '/api/v1/operations/peak-hours/{id}', function($request) use ($peakHourController) {
    return $peakHourController->updatePeakHourSchedule($request);
});

$router->addRoute('DELETE', '/api/v1/operations/peak-hours/{id}', function($request) use ($peakHourController) {
    return $peakHourController->deletePeakHourSchedule($request);
});

$router->addRoute('GET', '/api/v1/operations/peak-hours/is-peak', function($request) use ($peakHourController) {
    return $peakHourController->isPeakHourNow($request);
});

// ========================================================
// COURSE FIRING ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/courses', function($request) use ($courseFiringController) {
    return $courseFiringController->getCourseSequences($request);
});

$router->addRoute('POST', '/api/v1/operations/courses', function($request) use ($courseFiringController) {
    return $courseFiringController->createCourseSequence($request);
});

$router->addRoute('PUT', '/api/v1/operations/courses/{id}', function($request) use ($courseFiringController) {
    return $courseFiringController->updateCourseSequence($request);
});

$router->addRoute('DELETE', '/api/v1/operations/courses/{id}', function($request) use ($courseFiringController) {
    return $courseFiringController->deleteCourseSequence($request);
});

$router->addRoute('POST', '/api/v1/operations/orders/{order_id}/courses', function($request) use ($courseFiringController) {
    return $courseFiringController->createOrderCourses($request);
});

$router->addRoute('POST', '/api/v1/operations/order-courses/{id}/fire', function($request) use ($courseFiringController) {
    return $courseFiringController->fireCourse($request);
});

$router->addRoute('POST', '/api/v1/operations/order-courses/{id}/complete', function($request) use ($courseFiringController) {
    return $courseFiringController->completeCourse($request);
});

$router->addRoute('GET', '/api/v1/operations/orders/{order_id}/courses', function($request) use ($courseFiringController) {
    return $courseFiringController->getOrderCourses($request);
});

$router->addRoute('POST', '/api/v1/operations/courses/auto-fire', function($request) use ($courseFiringController) {
    return $courseFiringController->checkAutoFireCourses($request);
});

// ========================================================
// AYCE ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/ayce/sessions', function($request) use ($ayceController) {
    return $ayceController->getAYCESessions($request);
});

$router->addRoute('GET', '/api/v1/operations/ayce/sessions/{id}', function($request) use ($ayceController) {
    return $ayceController->getAYCESession($request);
});

$router->addRoute('POST', '/api/v1/operations/ayce/sessions', function($request) use ($ayceController) {
    return $ayceController->createAYCESession($request);
});

$router->addRoute('POST', '/api/v1/operations/ayce/reorders', function($request) use ($ayceController) {
    return $ayceController->createAYCEReorder($request);
});

$router->addRoute('POST', '/api/v1/operations/ayce/reorders/{id}/send-to-kitchen', function($request) use ($ayceController) {
    return $ayceController->sendReorderToKitchen($request);
});

$router->addRoute('POST', '/api/v1/operations/ayce/reorders/{id}/complete', function($request) use ($ayceController) {
    return $ayceController->completeReorder($request);
});

$router->addRoute('GET', '/api/v1/operations/ayce/sessions/{id}/reorders', function($request) use ($ayceController) {
    return $ayceController->getSessionReorders($request);
});

$router->addRoute('POST', '/api/v1/operations/ayce/sessions/{id}/end', function($request) use ($ayceController) {
    return $ayceController->endSession($request);
});

$router->addRoute('POST', '/api/v1/operations/ayce/check-timeouts', function($request) use ($ayceController) {
    return $ayceController->checkSessionTimeouts($request);
});

// ========================================================
// LOAD BALANCING ROUTES
// ========================================================
$router->addRoute('POST', '/api/v1/operations/load-balancing/record', function($request) use ($loadBalancingController) {
    return $loadBalancingController->recordStationLoad($request);
});

$router->addRoute('GET', '/api/v1/operations/load-balancing/metrics', function($request) use ($loadBalancingController) {
    return $loadBalancingController->getStationLoadMetrics($request);
});

$router->addRoute('GET', '/api/v1/operations/load-balancing/least-loaded', function($request) use ($loadBalancingController) {
    return $loadBalancingController->getLeastLoadedStation($request);
});

$router->addRoute('GET', '/api/v1/operations/load-balancing/recommend-reroute', function($request) use ($loadBalancingController) {
    return $loadBalancingController->recommendReroute($request);
});

$router->addRoute('GET', '/api/v1/operations/load-balancing/bottlenecks', function($request) use ($loadBalancingController) {
    return $loadBalancingController->getBottleneckStations($request);
});

// ========================================================
// PERFORMANCE MONITORING ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/performance/metrics', function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->getPerformanceMetrics($request);
});

$router->addRoute('POST', '/api/v1/operations/performance/order-timing', function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->recordOrderTiming($request);
});

$router->addRoute('POST', '/api/v1/operations/performance/calculate-hourly', function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->calculateHourlyMetrics($request);
});

$router->addRoute('GET', '/api/v1/operations/performance/bottlenecks', function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->getBottlenecks($request);
});

$router->addRoute('GET', '/api/v1/operations/performance/summary', function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->getPerformanceSummary($request);
});
