<?php

// BEO & Event Proposal Routes (Catering)
$router->addRoute('GET', '/api/v1/event-proposals', function($request) use ($eventProposalController) {
    return $eventProposalController->getProposals($request);
});
$router->addRoute('GET', '/api/v1/event-proposals/{id}', function($request) use ($eventProposalController) {
    return $eventProposalController->getProposal($request);
});
$router->addRoute('POST', '/api/v1/event-proposals', function($request) use ($eventProposalController) {
    return $eventProposalController->createProposal($request);
});
$router->addRoute('PATCH', '/api/v1/event-proposals/{id}/status', function($request) use ($eventProposalController) {
    return $eventProposalController->updateProposalStatus($request);
});
$router->addRoute('POST', '/api/v1/event-proposals/{id}/deposit', function($request) use ($eventProposalController) {
    return $eventProposalController->recordDeposit($request);
});
$router->addRoute('POST', '/api/v1/event-proposals/{id}/convert-beo', function($request) use ($eventProposalController) {
    return $eventProposalController->convertToBEO($request);
});
$router->addRoute('GET', '/api/v1/beos', function($request) use ($eventProposalController) {
    return $eventProposalController->getBEOs($request);
});
$router->addRoute('GET', '/api/v1/beos/{id}', function($request) use ($eventProposalController) {
    return $eventProposalController->getBEO($request);
});
$router->addRoute('POST', '/api/v1/beos/{id}/items', function($request) use ($eventProposalController) {
    return $eventProposalController->addBEOItem($request);
});
$router->addRoute('POST', '/api/v1/beos/items/{item_id}/complete', function($request) use ($eventProposalController) {
    return $eventProposalController->completeBEOItem($request);
});
$router->addRoute('PATCH', '/api/v1/beos/{id}/status', function($request) use ($eventProposalController) {
    return $eventProposalController->updateBEOStatus($request);
});
