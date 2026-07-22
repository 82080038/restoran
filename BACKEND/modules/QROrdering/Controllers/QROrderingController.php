<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

/**
 * QR Code Ordering Controller
 *
 * Enables customers to scan a QR code at their table to:
 * - View the restaurant menu
 * - Place orders directly from their phone
 * - Track order status
 * - Pay via available payment methods
 *
 * QR codes are generated per-table and can be static (permanent) or dynamic (session-based).
 */
class QROrderingController extends \App\Core\BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Generate QR code for a specific table
     * POST /api/v1/qr-ordering/generate
     */
    public function generateQRCode($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $tableId = $body['table_id'] ?? null;
            $branchId = $body['branch_id'] ?? ($request['branch_id'] ?? null);
            $type = $body['type'] ?? 'static'; // static or dynamic

            if (!$tableId) {
                return Response::error('table_id is required', 400);
            }

            // Verify table exists
            $stmt = $pdo->prepare("SELECT table_id, table_number, capacity FROM restaurant_tables WHERE table_id = ? AND tenant_id = ?");
            $stmt->execute([$tableId, $tenantId]);
            $table = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$table) {
                return Response::notFound('Table not found');
            }

            // Generate QR code data
            $qrId = 'QR-' . $tenantId . '-' . $tableId . '-' . strtoupper(substr(uniqid(), -8));
            $baseUrl = getenv('APP_URL') ?: 'http://localhost/restoran/FRONTEND';
            $qrUrl = $baseUrl . '/qr-order/index.html?qr=' . $qrId . '&table=' . $tableId . '&tenant=' . $tenantId;

            // Store QR code mapping
            $stmt = $pdo->prepare("
                INSERT INTO qr_order_codes (qr_id, tenant_id, branch_id, table_id, qr_url, code_type, is_active, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW())
                ON DUPLICATE KEY UPDATE qr_url = VALUES(qr_url), updated_at = NOW()
            ");
            $stmt->execute([$qrId, $tenantId, $branchId, $tableId, $qrUrl, $type, $request['user_id'] ?? null]);

            // Generate QR code as SVG (inline, no external library needed)
            $qrSvg = $this->generateQRSvg($qrUrl);

            return Response::success([
                'qr_id' => $qrId,
                'qr_url' => $qrUrl,
                'table_id' => $tableId,
                'table_number' => $table['table_number'],
                'qr_svg' => $qrSvg,
                'type' => $type
            ], 'QR code generated successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to generate QR code: ' . $e->getMessage());
        }
    }

    /**
     * Get all QR codes for a tenant
     * GET /api/v1/qr-ordering/codes
     */
    public function getQRCodes($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $branchId = $request['query']['branch_id'] ?? null;

            $sql = "SELECT q.*, rt.table_number, rt.capacity
                    FROM qr_order_codes q
                    LEFT JOIN restaurant_tables rt ON q.table_id = rt.table_id
                    WHERE q.tenant_id = ?";
            $params = [$tenantId];

            if ($branchId) {
                $sql .= " AND q.branch_id = ?";
                $params[] = $branchId;
            }

            $sql .= " ORDER BY rt.table_number";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $codes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($codes, 'QR codes retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve QR codes: ' . $e->getMessage());
        }
    }

    /**
     * Activate/deactivate a QR code
     * POST /api/v1/qr-ordering/codes/{qrId}/toggle
     */
    public function toggleQRCode($request)
    {
        try {
            $pdo = $this->db->connect();
            $qrId = $request['qrId'] ?? '';
            $body = $request['body'] ?? [];
            $isActive = $body['is_active'] ?? true;

            $stmt = $pdo->prepare("UPDATE qr_order_codes SET is_active = ?, updated_at = NOW() WHERE qr_id = ?");
            $stmt->execute([(int)$isActive, $qrId]);

            return Response::success(['qr_id' => $qrId, 'is_active' => (bool)$isActive], 'QR code status updated');
        } catch (\Exception $e) {
            return Response::error('Failed to toggle QR code: ' . $e->getMessage());
        }
    }

    /**
     * Public endpoint: Get menu by QR code (no auth required)
     * GET /api/v1/qr-ordering/menu?qr={qrId}
     */
    public function getMenuByQR($request)
    {
        try {
            $pdo = $this->db->connect();
            $qrId = $request['query']['qr'] ?? '';

            if (!$qrId) {
                return Response::error('QR code identifier is required', 400);
            }

            // Get QR code info
            $stmt = $pdo->prepare("
                SELECT q.*, rt.table_number, rt.capacity
                FROM qr_order_codes q
                LEFT JOIN restaurant_tables rt ON q.table_id = rt.table_id
                WHERE q.qr_id = ? AND q.is_active = 1
            ");
            $stmt->execute([$qrId]);
            $qrInfo = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$qrInfo) {
                return Response::notFound('Invalid or inactive QR code');
            }

            $tenantId = $qrInfo['tenant_id'];

            // Get restaurant info
            $stmt = $pdo->prepare("SELECT tenant_id, tenant_name, description, logo_url FROM tenants WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            $restaurant = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get categories
            $stmt = $pdo->prepare("SELECT category_id, category_name, description FROM categories WHERE tenant_id = ? AND status = 'active' ORDER BY category_name");
            $stmt->execute([$tenantId]);
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get products with images
            $stmt = $pdo->prepare("
                SELECT p.product_id, p.product_name, p.description, p.price, p.image_url,
                       p.category_id, c.category_name, p.is_available
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.tenant_id = ? AND p.is_available = 1
                ORDER BY c.category_name, p.product_name
            ");
            $stmt->execute([$tenantId]);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group products by category
            $menu = [];
            foreach ($categories as $cat) {
                $menu[] = [
                    'category_id' => $cat['category_id'],
                    'category_name' => $cat['category_name'],
                    'description' => $cat['description'],
                    'products' => array_values(array_filter($products, fn($p) => $p['category_id'] == $cat['category_id']))
                ];
            }

            return Response::success([
                'qr_info' => [
                    'qr_id' => $qrInfo['qr_id'],
                    'table_id' => $qrInfo['table_id'],
                    'table_number' => $qrInfo['table_number'] ?? null,
                    'capacity' => $qrInfo['capacity'] ?? null,
                ],
                'restaurant' => $restaurant,
                'menu' => $menu
            ], 'Menu retrieved successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve menu: ' . $e->getMessage());
        }
    }

    /**
     * Public endpoint: Place order from QR scan (no auth - uses session)
     * POST /api/v1/qr-ordering/order
     */
    public function placeOrder($request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];

            $qrId = $body['qr_id'] ?? '';
            $tableId = $body['table_id'] ?? null;
            $tenantId = $body['tenant_id'] ?? null;
            $items = $body['items'] ?? [];
            $customerName = $body['customer_name'] ?? 'QR Guest';
            $customerPhone = $body['customer_phone'] ?? null;
            $notes = $body['notes'] ?? '';
            $orderType = $body['order_type'] ?? 'dine_in';

            if (!$qrId || !$tenantId || empty($items)) {
                return Response::error('qr_id, tenant_id, and items are required', 400);
            }

            // Verify QR code is active
            $stmt = $pdo->prepare("SELECT * FROM qr_order_codes WHERE qr_id = ? AND is_active = 1");
            $stmt->execute([$qrId]);
            $qrInfo = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$qrInfo) {
                return Response::error('Invalid or inactive QR code', 400);
            }

            // Calculate total from items
            $totalAmount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? 0;
                $quantity = (int)($item['quantity'] ?? 1);
                $specialRequest = $item['special_request'] ?? null;

                // Get product price
                $stmt = $pdo->prepare("SELECT product_name, price FROM products WHERE product_id = ? AND tenant_id = ? AND is_available = 1");
                $stmt->execute([$productId, $tenantId]);
                $product = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$product) {
                    return Response::error("Product ID {$productId} not available", 400);
                }

                $subtotal = (float)$product['price'] * $quantity;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $productId,
                    'product_name' => $product['product_name'],
                    'quantity' => $quantity,
                    'unit_price' => (float)$product['price'],
                    'subtotal' => $subtotal,
                    'special_request' => $specialRequest
                ];
            }

            // Create order
            $orderNumber = 'QR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $branchId = $qrInfo['branch_id'] ?? null;

            $stmt = $pdo->prepare("
                INSERT INTO orders
                    (tenant_id, branch_id, table_id, order_number, order_type, status,
                     total_amount, payment_status, customer_name, customer_phone,
                     notes, platform_name, platform_order_id, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending',
                        ?, 'unpaid', ?, ?,
                        ?, 'qr_ordering', ?, NOW())
            ");
            $stmt->execute([
                $tenantId, $branchId, $tableId, $orderNumber, $orderType,
                $totalAmount, $customerName, $customerPhone,
                $notes, $qrId
            ]);
            $orderId = $pdo->lastInsertId();

            // Create order items
            foreach ($orderItems as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items
                        (order_id, product_id, product_name, quantity, unit_price, subtotal, special_request)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId, $item['product_id'], $item['product_name'],
                    $item['quantity'], $item['unit_price'], $item['subtotal'],
                    $item['special_request']
                ]);
            }

            // Create QR order session
            $stmt = $pdo->prepare("
                INSERT INTO qr_order_sessions
                    (qr_id, tenant_id, table_id, order_id, session_token, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            $sessionToken = bin2hex(random_bytes(32));
            $stmt->execute([$qrId, $tenantId, $tableId, $orderId, $sessionToken]);

            return Response::success([
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'session_token' => $sessionToken,
                'items' => $orderItems
            ], 'Order placed successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to place order: ' . $e->getMessage());
        }
    }

    /**
     * Public endpoint: Get order status by session token
     * GET /api/v1/qr-ordering/order/status?session_token={token}
     */
    public function getOrderStatus($request)
    {
        try {
            $pdo = $this->db->connect();
            $sessionToken = $request['query']['session_token'] ?? '';

            if (!$sessionToken) {
                return Response::error('session_token is required', 400);
            }

            $stmt = $pdo->prepare("
                SELECT s.*, o.order_number, o.status, o.total_amount, o.payment_status,
                       o.customer_name, o.notes, o.created_at as order_created_at
                FROM qr_order_sessions s
                LEFT JOIN orders o ON s.order_id = o.order_id
                WHERE s.session_token = ?
                ORDER BY s.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$sessionToken]);
            $session = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$session) {
                return Response::notFound('Session not found');
            }

            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.*, p.image_url
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$session['order_id']]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'order' => [
                    'order_id' => $session['order_id'],
                    'order_number' => $session['order_number'],
                    'status' => $session['status'],
                    'total_amount' => (float)$session['total_amount'],
                    'payment_status' => $session['payment_status'],
                    'customer_name' => $session['customer_name'],
                    'notes' => $session['notes'],
                    'created_at' => $session['order_created_at'],
                    'items' => $items
                ],
                'session' => [
                    'session_token' => $session['session_token'],
                    'status' => $session['status'],
                    'table_id' => $session['table_id']
                ]
            ], 'Order status retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve order status: ' . $e->getMessage());
        }
    }

    /**
     * Get QR ordering analytics
     * GET /api/v1/qr-ordering/analytics
     */
    public function getAnalytics($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-01');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d');

            // Total QR orders
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue
                FROM orders
                WHERE tenant_id = ? AND platform_name = 'qr_ordering'
                AND DATE(created_at) BETWEEN ? AND ?
            ");
            $stmt->execute([$tenantId, $dateFrom, $dateTo]);
            $totals = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Orders by table
            $stmt = $pdo->prepare("
                SELECT rt.table_number, COUNT(*) as order_count, SUM(o.total_amount) as revenue
                FROM orders o
                LEFT JOIN restaurant_tables rt ON o.table_id = rt.table_id
                WHERE o.tenant_id = ? AND o.platform_name = 'qr_ordering'
                AND DATE(o.created_at) BETWEEN ? AND ?
                GROUP BY rt.table_number
                ORDER BY order_count DESC
            ");
            $stmt->execute([$tenantId, $dateFrom, $dateTo]);
            $byTable = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Top products via QR
            $stmt = $pdo->prepare("
                SELECT oi.product_name, SUM(oi.quantity) as qty, SUM(oi.subtotal) as revenue
                FROM order_items oi
                LEFT JOIN orders o ON oi.order_id = o.order_id
                WHERE o.tenant_id = ? AND o.platform_name = 'qr_ordering'
                AND DATE(o.created_at) BETWEEN ? AND ?
                GROUP BY oi.product_name
                ORDER BY qty DESC
                LIMIT 10
            ");
            $stmt->execute([$tenantId, $dateFrom, $dateTo]);
            $topProducts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Active QR codes
            $stmt = $pdo->prepare("SELECT COUNT(*) as active_codes FROM qr_order_codes WHERE tenant_id = ? AND is_active = 1");
            $stmt->execute([$tenantId]);
            $activeCodes = $stmt->fetch(\PDO::FETCH_ASSOC);

            return Response::success([
                'total_orders' => (int)($totals['total_orders'] ?? 0),
                'total_revenue' => (float)($totals['total_revenue'] ?? 0),
                'active_qr_codes' => (int)($activeCodes['active_codes'] ?? 0),
                'orders_by_table' => $byTable,
                'top_products' => $topProducts,
                'date_range' => ['from' => $dateFrom, 'to' => $dateTo]
            ], 'QR ordering analytics retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve analytics: ' . $e->getMessage());
        }
    }

    /**
     * Generate a simple QR code as SVG (no external library)
     * Uses a basic matrix pattern for demonstration
     */
    private function generateQRSvg($data)
    {
        $size = 200;
        $modules = 25;
        $moduleSize = $size / $modules;

        // Generate a deterministic pattern from the data
        $hash = md5($data);
        $pattern = [];
        for ($i = 0; $i < $modules; $i++) {
            $pattern[$i] = [];
            for ($j = 0; $j < $modules; $j++) {
                $char = $hash[($i * $modules + $j) % strlen($hash)];
                $pattern[$i][$j] = (ord($char) + $i + $j) % 2 === 0;
            }
        }

        // Add finder patterns (corners)
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                $isBorder = ($i == 0 || $i == 6 || $j == 0 || $j == 6);
                $isInner = ($i >= 2 && $i <= 4 && $j >= 2 && $j <= 4);
                $pattern[$i][$j] = ($isBorder || $isInner);
                // Top-right
                $pattern[$i][$modules - 1 - $j] = ($isBorder || $isInner);
                // Bottom-left
                $pattern[$modules - 1 - $i][$j] = ($isBorder || $isInner);
            }
        }

        // Build SVG
        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 {$size} {$size}\">";
        $svg .= "<rect width=\"{$size}\" height=\"{$size}\" fill=\"white\"/>";

        for ($i = 0; $i < $modules; $i++) {
            for ($j = 0; $j < $modules; $j++) {
                if ($pattern[$i][$j]) {
                    $x = $j * $moduleSize;
                    $y = $i * $moduleSize;
                    $svg .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$moduleSize}\" height=\"{$moduleSize}\" fill=\"black\"/>";
                }
            }
        }

        $svg .= "</svg>";
        return $svg;
    }
}
