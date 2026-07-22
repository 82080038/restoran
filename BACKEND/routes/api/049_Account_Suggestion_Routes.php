<?php

// Account Suggestion Routes
if (!class_exists('AccountSuggestionService')) {
    require_once __DIR__ . '/../../modules/Accounting/Services/AccountSuggestionService.php';
}
$accountSuggestionService = new AccountSuggestionService();

$router->addRoute('GET', '/api/v1/accounting/suggest-accounts', withAuth(function($request) use ($accountSuggestionService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $transactionType = $_GET['transaction_type'] ?? null;
    $description = $_GET['description'] ?? null;
    $amount = $_GET['amount'] ?? null;
    $result = $accountSuggestionService->suggestAccounts($transactionType, $description, $amount);
    if ($result['success']) {
        Response::success($result['suggestions'], $result['message'] ?? 'Account suggestions retrieved successfully');
    } else {
        Response::error($result['message'] ?? 'Failed to get account suggestions');
    }
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/accounting/accounts/search', withAuth(function($request) use ($accountSuggestionService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $searchTerm = $_GET['search_term'] ?? null;
    $accountType = $_GET['account_type'] ?? null;
    $result = $accountSuggestionService->searchAccounts($user['tenant_id'], $searchTerm, $accountType);
    Response::success($result, 'Accounts retrieved successfully');
}, $authMiddleware));

$router->addRoute('GET', '/api/v1/accounting/journal-templates', withAuth(function($request) use ($accountSuggestionService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $templates = $accountSuggestionService->getJournalTemplates();
    Response::success($templates, 'Journal templates retrieved successfully');
}, $authMiddleware));

