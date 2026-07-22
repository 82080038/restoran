<?php

// BEO & Event Proposal Routes (Catering)
$router->addRoute('GET', '/api/v1/event-proposals', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->getProposals($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/event-proposals/{id}', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->getProposal($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/event-proposals', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->createProposal($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/event-proposals/{id}/status', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->updateProposalStatus($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/event-proposals/{id}/deposit', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->recordDeposit($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/event-proposals/{id}/convert-beo', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->convertToBEO($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beos', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->getBEOs($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/beos/{id}', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->getBEO($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beos/{id}/items', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->addBEOItem($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/beos/items/{item_id}/complete', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->completeBEOItem($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/beos/{id}/status', withAuth(function($request) use ($eventProposalController) {
    return $eventProposalController->updateBEOStatus($request);
}, $authMiddleware));
