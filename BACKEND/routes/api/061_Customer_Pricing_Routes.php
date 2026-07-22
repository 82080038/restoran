<?php

// Customer Pricing Routes
$router->addRoute('POST', '/api/v1/crm/customer-pricing', withAuth(function($request) use ($customerPricingController) {
    return $customerPricingController->setCustomerPrice($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/customer-pricing', withAuth(function($request) use ($customerPricingController) {
    return $customerPricingController->getCustomerPrice($request);
}, $authMiddleware));
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/pricing', withAuth(function($request) use ($customerPricingController) {
    return $customerPricingController->getCustomerPricings($request);
}, $authMiddleware));

