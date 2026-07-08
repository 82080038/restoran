<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleUserController
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Simple endpoint to get users without middleware
    public function getUsers($request = null)
    {
        $sql = "SELECT u.user_id, u.username, u.email, u.full_name, u.status, u.created_at
                FROM users u
                ORDER BY u.username";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($users);
    }

    // Get user roles
    public function getUserRoles($request = null)
    {
        $userId = isset($request['params']['id']) ? intval($request['params']['id']) : 1;

        $sql = "SELECT r.role_id, r.role_code, r.role_name, r.description
                FROM roles r
                JOIN user_roles ur ON r.role_id = ur.role_id
                WHERE ur.user_id = :user_id
                AND r.status = 'ACTIVE'
                ORDER BY r.role_name";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get current role from session or first role
        $currentRole = isset($_SESSION['current_role']) ? $_SESSION['current_role'] : (count($roles) > 0 ? $roles[0]['role_name'] : null);

        Response::success([
            'roles' => $roles,
            'current_role' => $currentRole
        ]);
    }

    // Switch user role
    public function switchRole($request = null)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $newRole = isset($input['role']) ? $input['role'] : null;

        if (!$newRole) {
            Response::error('Role is required', 400);
            return;
        }

        // Validate user has this role
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
        
        $sql = "SELECT r.role_id, r.role_name
                FROM roles r
                JOIN user_roles ur ON r.role_id = ur.role_id
                WHERE ur.user_id = :user_id
                AND r.role_name = :role_name
                AND r.status = 'ACTIVE'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':role_name', $newRole);
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            Response::error('User does not have this role', 403);
            return;
        }

        // Update session
        $_SESSION['current_role'] = $newRole;

        // Get permissions for the new role
        $permissions = $this->getRolePermissions($role['role_id']);

        Response::success([
            'current_role' => $newRole,
            'permissions' => $permissions
        ]);
    }

    // Get solo mode dashboard data
    public function getSoloModeData($request = null)
    {
        $tenantId = isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : 1;
        $branchId = isset($_SESSION['branch_id']) ? $_SESSION['branch_id'] : 1;

        $data = [
            'pos' => $this->getPOSData($tenantId, $branchId),
            'kitchen' => $this->getKitchenData($tenantId, $branchId),
            'tables' => $this->getTablesData($tenantId, $branchId),
            'recent_orders' => $this->getRecentOrders($tenantId, $branchId)
        ];

        Response::success($data);
    }

    // Get POS data
    private function getPOSData($tenantId, $branchId)
    {
        $sql = "SELECT p.product_id, p.product_name, p.price, p.is_available, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.tenant_id = :tenant_id
                AND p.status = 'ACTIVE'
                ORDER BY c.name, p.product_name";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['products' => $products];
    }

    // Get kitchen data
    private function getKitchenData($tenantId, $branchId)
    {
        $sql = "SELECT o.order_id, o.order_number, o.status, 
                oi.product_name, oi.quantity
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.tenant_id = :tenant_id
                AND o.branch_id = :branch_id
                AND o.status IN ('PENDING', 'PREPARING', 'READY')
                ORDER BY o.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId);
        $stmt->bindParam(':branch_id', $branchId);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by order
        $groupedOrders = [];
        foreach ($orders as $order) {
            $orderId = $order['order_id'];
            if (!isset($groupedOrders[$orderId])) {
                $groupedOrders[$orderId] = [
                    'order_id' => $order['order_id'],
                    'order_number' => $order['order_number'],
                    'status' => $order['status'],
                    'items' => []
                ];
            }
            $groupedOrders[$orderId]['items'][] = [
                'product_name' => $order['product_name'],
                'quantity' => $order['quantity']
            ];
        }

        return ['orders' => array_values($groupedOrders)];
    }

    // Get tables data
    private function getTablesData($tenantId, $branchId)
    {
        $sql = "SELECT t.table_id, t.table_number, t.status, t.capacity,
                o.order_number
                FROM restaurant_tables t
                LEFT JOIN orders o ON t.table_id = o.table_id 
                AND o.status IN ('PENDING', 'PREPARING', 'READY', 'SERVED')
                WHERE t.tenant_id = :tenant_id
                AND t.branch_id = :branch_id
                ORDER BY t.table_number";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId);
        $stmt->bindParam(':branch_id', $branchId);
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['tables' => $tables];
    }

    // Get recent orders
    private function getRecentOrders($tenantId, $branchId)
    {
        $sql = "SELECT o.order_id, o.order_number, o.total_amount, o.status, o.created_at
                FROM orders o
                WHERE o.tenant_id = :tenant_id
                AND o.branch_id = :branch_id
                ORDER BY o.created_at DESC
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId);
        $stmt->bindParam(':branch_id', $branchId);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['orders' => $orders];
    }

    // Get role permissions
    private function getRolePermissions($roleId)
    {
        $sql = "SELECT p.permission_code, p.permission_name, p.module, p.action
                FROM permissions p
                JOIN role_permissions rp ON p.permission_id = rp.permission_id
                WHERE rp.role_id = :role_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':role_id', $roleId);
        $stmt->execute();
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $permissions;
    }
}
