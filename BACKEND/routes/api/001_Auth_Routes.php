<?php

// Auth Routes
$router->addRoute('POST', '/api/v1/auth/login', function($request) use ($authController) {
    return $authController->login($request);
});

// Public Menu Routes (without authentication)
$router->addRoute('GET', '/api/v1/public/menu/categories', function($request) use ($simpleMenuController) {
    return $simpleMenuController->getCategories($request);
});
$router->addRoute('GET', '/api/v1/public/menu/products', function($request) use ($simpleMenuController) {
    return $simpleMenuController->getProducts($request);
});

// Simple Orders Route (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/orders', function($request) use ($simpleOrderController) {
    return $simpleOrderController->getAll($request);
});

// Simple Users Route (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/users', function($request) use ($simpleUserController) {
    return $simpleUserController->getUsers($request);
});

// Get User Roles (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/users/{id}/roles', function($request) use ($simpleUserController) {
    return $simpleUserController->getUserRoles($request);
});

// Switch Role (without middleware for testing)
$router->addRoute('POST', '/api/v1/public/auth/switch-role', function($request) use ($simpleUserController) {
    return $simpleUserController->switchRole($request);
});

// Get Solo Mode Data (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/solo-mode/dashboard', function($request) use ($simpleUserController) {
    return $simpleUserController->getSoloModeData($request);
});

// Simple Tables Route (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/tables', function($request) use ($simpleTableController) {
    return $simpleTableController->getTables($request);
});

// Simple Inventory Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/inventory', function($request) use ($simpleInventoryController) {
    return $simpleInventoryController->getInventory($request);
});
$router->addRoute('GET', '/api/v1/public/inventory/low-stock', function($request) use ($simpleInventoryController) {
    return $simpleInventoryController->getLowStock($request);
});

// Simple Kitchen Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/kitchen/orders', function($request) use ($simpleKitchenController) {
    return $simpleKitchenController->getKitchenOrders($request);
});

// Simple Reservation Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/reservations', function($request) use ($simpleReservationController) {
    return $simpleReservationController->getReservations($request);
});

// Simple Customer Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/customers', function($request) use ($simpleCustomerController) {
    return $simpleCustomerController->getCustomers($request);
});

// Simple Employee Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/employees', function($request) use ($simpleEmployeeController) {
    return $simpleEmployeeController->getEmployees($request);
});

// Simple Delivery Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/deliveries', function($request) use ($simpleDeliveryController) {
    return $simpleDeliveryController->getDeliveries($request);
});

// Simple Supplier Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/suppliers', function($request) use ($simpleSupplierController) {
    return $simpleSupplierController->getSuppliers($request);
});

