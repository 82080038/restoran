<?php

// Bill Split Routes

// Table groups
$router->addRoute('GET', '/api/v1/bill-split/tables/{id}/groups', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->getTableGroups($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/bill-split/groups', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->createGroup($request);
}, $authMiddleware));
$router->addRoute('PUT', '/api/v1/bill-split/groups/{id}', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->updateGroup($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/bill-split/groups/{id}', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->closeGroup($request);
}, $authMiddleware));

// Bills
$router->addRoute('GET', '/api/v1/bill-split/bills/{id}', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->getBill($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/bill-split/bills/{id}/items', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->assignItemToBill($request);
}, $authMiddleware));
$router->addRoute('DELETE', '/api/v1/bill-split/bills/{id}/items/{item_id}', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->removeItemFromBill($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/bill-split/bills/{id}/split', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->splitBill($request);
}, $authMiddleware));
$router->addRoute('PATCH', '/api/v1/bill-split/bills/{id}/payment', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->markBillPaid($request);
}, $authMiddleware));

// Merge bills
$router->addRoute('POST', '/api/v1/bill-split/merge', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->mergeBills($request);
}, $authMiddleware));

// Table summary
$router->addRoute('GET', '/api/v1/bill-split/tables/{id}/summary', withAuth(function($request) use ($billSplitController) {
    return $billSplitController->getTableSummary($request);
}, $authMiddleware));
