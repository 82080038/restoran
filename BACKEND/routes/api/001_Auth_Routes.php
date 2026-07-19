<?php

// Auth Routes
$router->addRoute('POST', '/api/v1/auth/login', function($request) use ($authController) {
    // Rate limit: 10 login attempts per minute per IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateLimit = new \App\Core\RateLimitMiddleware();
    if (!$rateLimit->check('login_' . $ip, 10, 60)) {
        return \App\Core\Response::error('Too many login attempts. Please try again later.', 429);
    }
    return $authController->login($request);
});

// Token refresh (requires valid token)
$router->addRoute('POST', '/api/v1/auth/refresh', function($request) use ($authController) {
    return $authController->refresh($request);
});

// Logout (requires valid token for audit logging)
$router->addRoute('POST', '/api/v1/auth/logout', function($request) use ($authController) {
    return $authController->logout($request);
});

// Change password (requires valid token)
$router->addRoute('POST', '/api/v1/auth/change-password', function($request) use ($authController) {
    return $authController->changePassword($request);
});

// Get current user info (requires valid token)
$router->addRoute('GET', '/api/v1/auth/me', function($request) {
    try {
        $payload = AuthMiddleware::handle($request);
        Response::success([
            'user' => [
                'id' => $payload['user_id'],
                'username' => $payload['username'],
                'tenant_id' => $payload['tenant_id'],
                'branch_id' => $payload['branch_id'],
                'role' => $payload['role'],
                'level' => $payload['level'],
                'is_platform_owner' => (bool) $payload['is_platform_owner']
            ]
        ], 'User info retrieved');
    } catch (\Throwable $e) {
        Response::error('Authentication required', 401);
    }
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

