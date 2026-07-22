<?php

// WhatsApp Ordering Routes
$router->addRoute('POST', '/api/v1/whatsapp/orders', withAuth(function($request) use ($whatsAppOrderingController) {
    return $whatsAppOrderingController->processOrder($request);
}, $authMiddleware));

