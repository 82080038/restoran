<?php

// Goods Receipt Routes
$router->addRoute('POST', '/api/v1/inventory/goods-receipts', function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/goods-receipts', function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/goods-receipts/{id}/complete', function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->complete($request);
});

