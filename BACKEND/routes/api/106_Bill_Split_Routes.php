<?php

// Bill Split Routes

// Table groups
$router->addRoute('GET', '/api/v1/bill-split/tables/{id}/groups', function($request) use ($billSplitController) {
    return $billSplitController->getTableGroups($request);
});
$router->addRoute('POST', '/api/v1/bill-split/groups', function($request) use ($billSplitController) {
    return $billSplitController->createGroup($request);
});
$router->addRoute('PUT', '/api/v1/bill-split/groups/{id}', function($request) use ($billSplitController) {
    return $billSplitController->updateGroup($request);
});
$router->addRoute('DELETE', '/api/v1/bill-split/groups/{id}', function($request) use ($billSplitController) {
    return $billSplitController->closeGroup($request);
});

// Bills
$router->addRoute('GET', '/api/v1/bill-split/bills/{id}', function($request) use ($billSplitController) {
    return $billSplitController->getBill($request);
});
$router->addRoute('POST', '/api/v1/bill-split/bills/{id}/items', function($request) use ($billSplitController) {
    return $billSplitController->assignItemToBill($request);
});
$router->addRoute('DELETE', '/api/v1/bill-split/bills/{id}/items/{item_id}', function($request) use ($billSplitController) {
    return $billSplitController->removeItemFromBill($request);
});
$router->addRoute('POST', '/api/v1/bill-split/bills/{id}/split', function($request) use ($billSplitController) {
    return $billSplitController->splitBill($request);
});
$router->addRoute('PATCH', '/api/v1/bill-split/bills/{id}/payment', function($request) use ($billSplitController) {
    return $billSplitController->markBillPaid($request);
});

// Merge bills
$router->addRoute('POST', '/api/v1/bill-split/merge', function($request) use ($billSplitController) {
    return $billSplitController->mergeBills($request);
});

// Table summary
$router->addRoute('GET', '/api/v1/bill-split/tables/{id}/summary', function($request) use ($billSplitController) {
    return $billSplitController->getTableSummary($request);
});
