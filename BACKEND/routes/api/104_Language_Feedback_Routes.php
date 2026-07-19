<?php

// Language / i18n Routes
$router->addRoute('GET', '/api/v1/languages', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getLanguages($request);
});
$router->addRoute('GET', '/api/v1/languages/{code}/translations', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getTranslations($request);
});
$router->addRoute('GET', '/api/v1/languages/preference', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getUserPreference($request);
});
$router->addRoute('POST', '/api/v1/languages/preference', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->setUserPreference($request);
});
$router->addRoute('GET', '/api/v1/languages/translations/all', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getAllTranslations($request);
});
$router->addRoute('POST', '/api/v1/languages/translations', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->saveTranslation($request);
});
$router->addRoute('DELETE', '/api/v1/languages/translations/{id}', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->deleteTranslation($request);
});
$router->addRoute('GET', '/api/v1/languages/contexts', function($request) use ($simpleLanguageController) {
    return $simpleLanguageController->getContexts($request);
});

// Feedback / Review Routes
$router->addRoute('GET', '/api/v1/feedback/reviews', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getReviews($request);
});
$router->addRoute('GET', '/api/v1/feedback/reviews/{id}', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getReview($request);
});
$router->addRoute('POST', '/api/v1/feedback/reviews', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->createReview($request);
});
$router->addRoute('PATCH', '/api/v1/feedback/reviews/{id}/status', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->updateReviewStatus($request);
});
$router->addRoute('POST', '/api/v1/feedback/reviews/{id}/respond', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->respondToReview($request);
});
$router->addRoute('GET', '/api/v1/feedback', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getFeedback($request);
});
$router->addRoute('POST', '/api/v1/feedback', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->createFeedback($request);
});
$router->addRoute('PATCH', '/api/v1/feedback/{id}/status', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->updateFeedbackStatus($request);
});
$router->addRoute('GET', '/api/v1/feedback/statistics', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getStatistics($request);
});
$router->addRoute('GET', '/api/v1/feedback/review-categories', function($request) use ($simpleFeedbackController) {
    return $simpleFeedbackController->getReviewCategories($request);
});
