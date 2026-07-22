<?php

// Goods Receipt Routes
$router->addRoute('POST', '/api/v1/inventory/goods-receipts', withAuth(function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->create($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/inventory/goods-receipts', withAuth(function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->getAll($request);
}, $authMiddleware));
$router->addRoute('POST', '/api/v1/inventory/goods-receipts/{id}/complete', withAuth(function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->complete($request);
}, $authMiddleware));

