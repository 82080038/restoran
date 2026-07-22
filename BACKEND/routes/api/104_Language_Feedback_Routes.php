<?php

// Language / i18n Routes
$router->addRoute('GET', '/api/v1/languages', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getLanguages($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/languages/{code}/translations', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getTranslations($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/languages/preference', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getUserPreference($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/languages/preference', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->setUserPreference($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/languages/translations/all', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getAllTranslations($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/languages/translations', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->saveTranslation($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/languages/translations/{id}', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->deleteTranslation($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/languages/contexts', withAuth(function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getContexts($request);
}, $authMiddleware));

// Feedback / Review Routes
$router->addRoute('GET', '/api/v1/feedback/reviews', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getReviews($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/feedback/reviews/{id}', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getReview($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/feedback/reviews', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->createReview($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/feedback/reviews/{id}/status', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->updateReviewStatus($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/feedback/reviews/{id}/respond', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->respondToReview($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/feedback', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getFeedback($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/feedback', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->createFeedback($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/feedback/{id}/status', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->updateFeedbackStatus($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/feedback/statistics', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getStatistics($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/feedback/review-categories', withAuth(function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getReviewCategories($request);
}, $authMiddleware));
