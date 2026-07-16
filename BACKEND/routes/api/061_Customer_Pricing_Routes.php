<?php

// Customer Pricing Routes
$router->addRoute('POST', '/api/v1/crm/customer-pricing', function($request) use ($customerPricingController) {
    return $customerPricingController->setCustomerPrice($request);
});
$router->addRoute('GET', '/api/v1/crm/customer-pricing', function($request) use ($customerPricingController) {
    return $customerPricingController->getCustomerPrice($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/pricing', function($request) use ($customerPricingController) {
    return $customerPricingController->getCustomerPricings($request);
});

