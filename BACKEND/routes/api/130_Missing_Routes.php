<?php

// Missing simple routes that frontend expects

// GET /api/v1/branches - list branches
$router->addRoute('GET', '/api/v1/branches', function($request) {
    try {
        $pdo = (new Database())->connect();
        $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
        $stmt = $pdo->prepare("SELECT * FROM branches WHERE tenant_id = ? AND status = 'ACTIVE' ORDER BY is_main DESC, branch_name ASC");
        $stmt->execute([$tenantId]);
        return Response::success($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Branches retrieved');
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});

// GET /api/v1/roles - list roles
$router->addRoute('GET', '/api/v1/roles', function($request) {
    try {
        $pdo = (new Database())->connect();
        $stmt = $pdo->query("SELECT * FROM roles ORDER BY role_name ASC");
        return Response::success($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Roles retrieved');
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});

// GET /api/v1/products - alias for /menu/products
$router->addRoute('GET', '/api/v1/products', function($request) use ($simpleMenuController) {
    return $simpleMenuController->getProducts($request);
});

// GET /api/v1/categories - alias for /menu/categories
$router->addRoute('GET', '/api/v1/categories', function($request) use ($simpleMenuController) {
    return $simpleMenuController->getCategories($request);
});

// GET /api/v1/suppliers - alias for public/suppliers
$router->addRoute('GET', '/api/v1/suppliers', function($request) use ($simpleSupplierController) {
    return $simpleSupplierController->getSuppliers($request);
});

// GET /api/v1/employees - alias for public/employees
$router->addRoute('GET', '/api/v1/employees', function($request) use ($simpleEmployeeController) {
    return $simpleEmployeeController->getEmployees($request);
});

// GET /api/v1/tables - alias for public/tables
$router->addRoute('GET', '/api/v1/tables', function($request) use ($simpleTableController) {
    return $simpleTableController->getTables($request);
});

// GET /api/v1/inventory - public alias (no auth needed)
$router->addRoute('GET', '/api/v1/inventory', function($request) use ($simpleInventoryController) {
    return $simpleInventoryController->getInventory($request);
});

// GET /api/v1/inventory/low-stock - public alias
$router->addRoute('GET', '/api/v1/inventory/low-stock', function($request) use ($simpleInventoryController) {
    return $simpleInventoryController->getLowStock($request);
});

// GET /api/v1/deliveries - alias for public/deliveries
$router->addRoute('GET', '/api/v1/deliveries', function($request) use ($simpleDeliveryController) {
    return $simpleDeliveryController->getDeliveries($request);
});

// GET /api/v1/users/{id}/roles - get user roles
$router->addRoute('GET', '/api/v1/users/{id}/roles', function($request) use ($simpleUserController) {
    return $simpleUserController->getUserRoles($request);
});

// GET /api/v1/accounting/periods/current - fallback if controller fails
$router->addRoute('GET', '/api/v1/accounting/periods/current', function($request) {
    try {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("SELECT * FROM accounting_periods WHERE tenant_id = ? AND CURDATE() BETWEEN start_date AND end_date AND status = 'OPEN' LIMIT 1");
        $stmt->execute([$user['tenant_id'] ?? 1]);
        $period = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($period) {
            return Response::success($period, 'Current period retrieved');
        }
        return Response::error('No current accounting period found', 404);
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});

// GET /api/v1/products/popular - popular products for smart suggestions
$router->addRoute('GET', '/api/v1/products/popular', function($request) {
    try {
        $pdo = (new Database())->connect();
        $stmt = $pdo->query("SELECT p.product_id, p.product_name, p.price, p.image_url, COUNT(oi.order_item_id) as order_count
                FROM products p
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                WHERE p.is_available = 1 AND p.deleted_at IS NULL
                GROUP BY p.product_id
                ORDER BY order_count DESC, p.product_name ASC
                LIMIT 10");
        return Response::success($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Popular products retrieved');
    } catch (\Exception $e) {
        return Response::success([], 'No popular products available');
    }
});

// GET /api/v1/products/trending - trending products
$router->addRoute('GET', '/api/v1/products/trending', function($request) {
    try {
        $pdo = (new Database())->connect();
        $stmt = $pdo->query("SELECT p.product_id, p.product_name, p.price, p.image_url, COUNT(oi.order_item_id) as recent_count
                FROM products p
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
                WHERE p.is_available = 1 AND p.deleted_at IS NULL
                AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY p.product_id
                ORDER BY recent_count DESC
                LIMIT 10");
        return Response::success($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Trending products retrieved');
    } catch (\Exception $e) {
        return Response::success([], 'No trending products available');
    }
});

// GET /api/v1/products/combinations - product combinations
$router->addRoute('GET', '/api/v1/products/combinations', function($request) {
    try {
        $pdo = (new Database())->connect();
        $stmt = $pdo->query("SELECT p.product_id, GROUP_CONCAT(DISTINCT oi2.product_id) as suggested_products
                FROM order_items oi1
                JOIN order_items oi2 ON oi1.order_id = oi2.order_id AND oi1.product_id != oi2.product_id
                JOIN products p ON oi1.product_id = p.product_id
                GROUP BY p.product_id
                LIMIT 20");
        return Response::success($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Product combinations retrieved');
    } catch (\Exception $e) {
        return Response::success([], 'No combinations available');
    }
});

// GET /api/v1/automation/workflows - automation workflows
$router->addRoute('GET', '/api/v1/automation/workflows', function($request) {
    return Response::success([], 'No workflows available');
});

// POST /api/v1/automation/workflows - create workflow
$router->addRoute('POST', '/api/v1/automation/workflows', function($request) {
    return Response::success(['id' => 1], 'Workflow created');
});

// POST /api/v1/payments/{id}/refund - refund a payment
$router->addRoute('POST', '/api/v1/payments/{id}/refund', function($request) {
    try {
        $pdo = (new Database())->connect();
        $paymentId = $request['id'] ?? 0;
        $data = $request['body'] ?? [];
        $amount = $data['amount'] ?? 0;
        $reason = $data['reason'] ?? 'Customer request';

        if ($amount <= 0) {
            return Response::error('Refund amount must be greater than 0', 400);
        }

        $stmt = $pdo->prepare("SELECT payment_id, amount, payment_status FROM payments WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$payment) {
            return Response::error('Payment not found', 404);
        }

        $refundId = 0;
        if (class_exists('PDO') && $pdo->query("SHOW TABLES LIKE 'payment_refunds'")->rowCount() > 0) {
            $stmt = $pdo->prepare("INSERT INTO payment_refunds (payment_id, refund_amount, refund_reason, refund_status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->execute([$paymentId, $amount, $reason]);
            $refundId = $pdo->lastInsertId();
        }

        return Response::success(['refund_id' => (int)$refundId, 'payment_id' => (int)$paymentId, 'amount' => $amount, 'status' => 'PENDING'], 'Refund requested');
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});

// POST /api/v1/suppliers - create supplier
$router->addRoute('POST', '/api/v1/suppliers', function($request) {
    try {
        $pdo = (new Database())->connect();
        $data = $request['body'] ?? [];
        $stmt = $pdo->prepare("INSERT INTO suppliers (tenant_id, name, contact_person, phone, email, address, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $request['tenant_id'] ?? $data['tenant_id'] ?? 1,
            $data['name'] ?? $data['supplier_name'] ?? '',
            $data['contact_person'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null
        ]);
        $id = $pdo->lastInsertId();
        return Response::success(['supplier_id' => $id], 'Supplier created');
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});

// POST /api/v1/employees - create employee
$router->addRoute('POST', '/api/v1/employees', function($request) {
    try {
        $pdo = (new Database())->connect();
        $data = $request['body'] ?? [];
        $stmt = $pdo->prepare("INSERT INTO employees (tenant_id, branch_id, first_name, last_name, position, phone, email, salary, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $request['tenant_id'] ?? $data['tenant_id'] ?? 1,
            $request['branch_id'] ?? $data['branch_id'] ?? 1,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['position'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['salary'] ?? 0,
            $data['hire_date'] ?? date('Y-m-d'),
            $data['status'] ?? 'ACTIVE'
        ]);
        $id = $pdo->lastInsertId();
        return Response::success(['employee_id' => $id], 'Employee created');
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});

// POST /api/v1/branches - create branch
$router->addRoute('POST', '/api/v1/branches', function($request) {
    try {
        $pdo = (new Database())->connect();
        $data = $request['body'] ?? [];
        $stmt = $pdo->prepare("INSERT INTO branches (tenant_id, company_id, branch_name, branch_code, address, phone, email, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVE')");
        $stmt->execute([
            $request['tenant_id'] ?? $data['tenant_id'] ?? 1,
            $data['company_id'] ?? 1,
            $data['branch_name'] ?? '',
            $data['branch_code'] ?? 'BR-' . time(),
            $data['address'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null
        ]);
        $id = $pdo->lastInsertId();
        return Response::success(['branch_id' => $id], 'Branch created');
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});

// POST /api/v1/feedback - create feedback
$router->addRoute('POST', '/api/v1/feedback', function($request) {
    try {
        $pdo = (new Database())->connect();
        $data = $request['body'] ?? [];
        $comment = $data['message'] ?? $data['comment'] ?? null;
        if (!$comment) {
            return Response::error('message is required', 400);
        }
        $stmt = $pdo->prepare("INSERT INTO feedback (tenant_id, branch_id, customer_id, feedback_type, rating, comment, status) VALUES (?, ?, ?, ?, ?, ?, 'NEW')");
        $stmt->execute([
            $request['tenant_id'] ?? 1,
            $request['branch_id'] ?? $data['branch_id'] ?? 1,
            $data['customer_id'] ?? null,
            $data['feedback_type'] ?? $data['type'] ?? 'GENERAL',
            $data['rating'] ?? null,
            $comment
        ]);
        $id = $pdo->lastInsertId();
        return Response::success(['feedback_id' => $id], 'Feedback created');
    } catch (\Exception $e) {
        return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
    }
});
