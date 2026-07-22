<?php

// Advanced Operations Routes - KDS, Waitlist, Peak Hour, Course Firing, AYCE, Load Balancing, Performance Monitoring

// ========================================================
// KDS SCREEN ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/kds/screens', withAuth(function($request) use ($kdsScreenController) {
    return $kdsScreenController->getScreens($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/kds/screens/{id}', withAuth(function($request) use ($kdsScreenController) {
    return $kdsScreenController->getScreen($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/kds/screens', withAuth(function($request) use ($kdsScreenController) {
    return $kdsScreenController->createScreen($request);
}, $authMiddleware));

$router->addRoute('PUT', '/api/v1/kds/screens/{id}', withAuth(function($request) use ($kdsScreenController) {
    return $kdsScreenController->updateScreen($request);
}, $authMiddleware));

$router->addRoute('DELETE', '/api/v1/kds/screens/{id}', withAuth(function($request) use ($kdsScreenController) {
    return $kdsScreenController->deleteScreen($request);
}, $authMiddleware));

// ========================================================
// KDS ROUTING RULE ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/kds/routing-rules', withAuth(function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->getRoutingRules($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/kds/routing-rules/{id}', withAuth(function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->getRoutingRule($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/kds/routing-rules', withAuth(function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->createRoutingRule($request);
}, $authMiddleware));

$router->addRoute('PUT', '/api/v1/kds/routing-rules/{id}', withAuth(function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->updateRoutingRule($request);
}, $authMiddleware));

$router->addRoute('DELETE', '/api/v1/kds/routing-rules/{id}', withAuth(function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->deleteRoutingRule($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/kds/routing-rules/apply', withAuth(function($request) use ($kdsRoutingRuleController) {
    return $kdsRoutingRuleController->applyRoutingRules($request);
}, $authMiddleware));

// ========================================================
// KDS TICKET ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/kds/tickets', withAuth(function($request) use ($kdsTicketController) {
    return $kdsTicketController->getTickets($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/kds/tickets/{id}', withAuth(function($request) use ($kdsTicketController) {
    return $kdsTicketController->getTicket($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/kds/tickets', withAuth(function($request) use ($kdsTicketController) {
    return $kdsTicketController->createTicket($request);
}, $authMiddleware));

$router->addRoute('PUT', '/api/v1/kds/tickets/{id}/status', withAuth(function($request) use ($kdsTicketController) {
    return $kdsTicketController->updateTicketStatus($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/kds/tickets/update-urgency', withAuth(function($request) use ($kdsTicketController) {
    return $kdsTicketController->updateUrgencyLevels($request);
}, $authMiddleware));

$router->addRoute('DELETE', '/api/v1/kds/tickets/{id}', withAuth(function($request) use ($kdsTicketController) {
    return $kdsTicketController->deleteTicket($request);
}, $authMiddleware));

// ========================================================
// WAITLIST ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/waitlist', withAuth(function($request) use ($waitlistController) {
    return $waitlistController->getWaitlistEntries($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/waitlist/{id}', withAuth(function($request) use ($waitlistController) {
    return $waitlistController->getWaitlistEntry($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/waitlist', withAuth(function($request) use ($waitlistController) {
    return $waitlistController->createWaitlistEntry($request);
}, $authMiddleware));

$router->addRoute('PUT', '/api/v1/waitlist/{id}', withAuth(function($request) use ($waitlistController) {
    return $waitlistController->updateWaitlistEntry($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/waitlist/{id}/seat', withAuth(function($request) use ($waitlistController) {
    return $waitlistController->seatGuest($request);
}, $authMiddleware));

$router->addRoute('DELETE', '/api/v1/waitlist/{id}', withAuth(function($request) use ($waitlistController) {
    return $waitlistController->deleteWaitlistEntry($request);
}, $authMiddleware));

// ========================================================
// PEAK HOUR ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/peak-hours', withAuth(function($request) use ($peakHourController) {
    return $peakHourController->getPeakHourSchedules($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/peak-hours/current', withAuth(function($request) use ($peakHourController) {
    return $peakHourController->getCurrentPeakHour($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/peak-hours', withAuth(function($request) use ($peakHourController) {
    return $peakHourController->createPeakHourSchedule($request);
}, $authMiddleware));

$router->addRoute('PUT', '/api/v1/operations/peak-hours/{id}', withAuth(function($request) use ($peakHourController) {
    return $peakHourController->updatePeakHourSchedule($request);
}, $authMiddleware));

$router->addRoute('DELETE', '/api/v1/operations/peak-hours/{id}', withAuth(function($request) use ($peakHourController) {
    return $peakHourController->deletePeakHourSchedule($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/peak-hours/is-peak', withAuth(function($request) use ($peakHourController) {
    return $peakHourController->isPeakHourNow($request);
}, $authMiddleware));

// ========================================================
// COURSE FIRING ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/courses', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->getCourseSequences($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/courses', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->createCourseSequence($request);
}, $authMiddleware));

$router->addRoute('PUT', '/api/v1/operations/courses/{id}', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->updateCourseSequence($request);
}, $authMiddleware));

$router->addRoute('DELETE', '/api/v1/operations/courses/{id}', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->deleteCourseSequence($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/orders/{order_id}/courses', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->createOrderCourses($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/order-courses/{id}/fire', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->fireCourse($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/order-courses/{id}/complete', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->completeCourse($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/orders/{order_id}/courses', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->getOrderCourses($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/courses/auto-fire', withAuth(function($request) use ($courseFiringController) {
    return $courseFiringController->checkAutoFireCourses($request);
}, $authMiddleware));

// ========================================================
// AYCE ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/ayce/sessions', withAuth(function($request) use ($ayceController) {
    return $ayceController->getAYCESessions($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/ayce/sessions/{id}', withAuth(function($request) use ($ayceController) {
    return $ayceController->getAYCESession($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/ayce/sessions', withAuth(function($request) use ($ayceController) {
    return $ayceController->createAYCESession($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/ayce/reorders', withAuth(function($request) use ($ayceController) {
    return $ayceController->createAYCEReorder($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/ayce/reorders/{id}/send-to-kitchen', withAuth(function($request) use ($ayceController) {
    return $ayceController->sendReorderToKitchen($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/ayce/reorders/{id}/complete', withAuth(function($request) use ($ayceController) {
    return $ayceController->completeReorder($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/ayce/sessions/{id}/reorders', withAuth(function($request) use ($ayceController) {
    return $ayceController->getSessionReorders($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/ayce/sessions/{id}/end', withAuth(function($request) use ($ayceController) {
    return $ayceController->endSession($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/ayce/check-timeouts', withAuth(function($request) use ($ayceController) {
    return $ayceController->checkSessionTimeouts($request);
}, $authMiddleware));

// ========================================================
// LOAD BALANCING ROUTES
// ========================================================
$router->addRoute('POST', '/api/v1/operations/load-balancing/record', withAuth(function($request) use ($loadBalancingController) {
    return $loadBalancingController->recordStationLoad($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/load-balancing/metrics', withAuth(function($request) use ($loadBalancingController) {
    return $loadBalancingController->getStationLoadMetrics($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/load-balancing/least-loaded', withAuth(function($request) use ($loadBalancingController) {
    return $loadBalancingController->getLeastLoadedStation($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/load-balancing/recommend-reroute', withAuth(function($request) use ($loadBalancingController) {
    return $loadBalancingController->recommendReroute($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/load-balancing/bottlenecks', withAuth(function($request) use ($loadBalancingController) {
    return $loadBalancingController->getBottleneckStations($request);
}, $authMiddleware));

// ========================================================
// PERFORMANCE MONITORING ROUTES
// ========================================================
$router->addRoute('GET', '/api/v1/operations/performance/metrics', withAuth(function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->getPerformanceMetrics($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/performance/order-timing', withAuth(function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->recordOrderTiming($request);
}, $authMiddleware));

$router->addRoute('POST', '/api/v1/operations/performance/calculate-hourly', withAuth(function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->calculateHourlyMetrics($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/performance/bottlenecks', withAuth(function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->getBottlenecks($request);
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/operations/performance/summary', withAuth(function($request) use ($performanceMonitoringController) {
    return $performanceMonitoringController->getPerformanceSummary($request);
}, $authMiddleware));
