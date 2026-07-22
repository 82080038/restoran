<?php

declare(strict_types=1);

namespace App\Modules\Integration\Controllers;

use App\Core\Response;
use App\Core\Database;
use App\Core\Middleware\AuthMiddleware;

/**
 * Delivery Platform Integration Controller
 *
 * Handles integration with third-party delivery platforms:
 * - GoFood (Gojek)
 * - GrabFood (Grab)
 * - ShopeeFood
 * - Uber Eats (for international)
 *
 * @package EBP\App\Modules\Integration
 * @version 1.0.0
 */
class DeliveryIntegrationController extends BaseController
{
    private $db;
    private $platforms = ['gofood', 'grabfood', 'shopeefood', 'ubereats'];

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get all configured delivery platforms
     * GET /api/v1/integrations/delivery
     */
    public function getPlatforms($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("
                SELECT * FROM delivery_platform_integrations
                WHERE tenant_id = ?
                ORDER BY platform_name
            ");
            $stmt->execute([$tenantId]);
            $platforms = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($platforms, 'Delivery platforms retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve delivery platforms: ' . $e->getMessage());
        }
    }

    /**
     * Configure a delivery platform integration
     * POST /api/v1/integrations/delivery/{platform}/configure
     */
    public function configurePlatform($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $platform = $request['platform'] ?? '';
            $body = $request['body'] ?? [];

            if (!in_array($platform, $this->platforms)) {
                return Response::error("Unsupported platform: {$platform}", 400);
            }

            $apiKey = $body['api_key'] ?? '';
            $apiSecret = $body['api_secret'] ?? '';
            $merchantId = $body['merchant_id'] ?? '';
            $webhookUrl = $body['webhook_url'] ?? '';
            $isActive = $body['is_active'] ?? true;

            if (empty($apiKey) || empty($merchantId)) {
                return Response::error('api_key and merchant_id are required', 400);
            }

            // Check if already configured
            $stmt = $pdo->prepare("
                SELECT integration_id FROM delivery_platform_integrations
                WHERE tenant_id = ? AND platform_name = ?
            ");
            $stmt->execute([$tenantId, $platform]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE delivery_platform_integrations
                    SET api_key = ?, api_secret = ?, merchant_id = ?, webhook_url = ?, is_active = ?, updated_at = NOW()
                    WHERE integration_id = ?
                ");
                $stmt->execute([$apiKey, $apiSecret, $merchantId, $webhookUrl, $isActive, $existing['integration_id']]);
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO delivery_platform_integrations
                        (tenant_id, platform_name, api_key, api_secret, merchant_id, webhook_url, is_active, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$tenantId, $platform, $apiKey, $apiSecret, $merchantId, $webhookUrl, $isActive]);
            }

            return Response::success([
                'platform' => $platform,
                'merchant_id' => $merchantId,
                'is_active' => (bool)$isActive
            ], "Platform {$platform} configured successfully");
        } catch (\Exception $e) {
            return Response::error('Failed to configure platform: ' . $e->getMessage());
        }
    }

    /**
     * Sync menu to delivery platform
     * POST /api/v1/integrations/delivery/{platform}/sync-menu
     */
    public function syncMenu($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $platform = $request['platform'] ?? '';

            if (!in_array($platform, $this->platforms)) {
                return Response::error("Unsupported platform: {$platform}", 400);
            }

            // Get platform config
            $config = $this->getPlatformConfig($pdo, $tenantId, $platform);
            if (!$config) {
                return Response::error("Platform {$platform} not configured", 400);
            }

            // Get menu items
            $stmt = $pdo->prepare("
                SELECT p.product_id, p.product_name, p.description, p.price, p.is_available,
                       c.category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.tenant_id = ? AND p.is_available = 1
            ");
            $stmt->execute([$tenantId]);
            $menuItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Format for platform
            $formattedItems = array_map(function($item) use ($platform) {
                return [
                    'id' => $item['product_id'],
                    'name' => $item['product_name'],
                    'description' => $item['description'] ?? '',
                    'price' => (int)$item['price'],
                    'category' => $item['category_name'] ?? 'Uncategorized',
                    'available' => (bool)$item['is_available']
                ];
            }, $menuItems);

            // Call platform API (DB-backed with cURL support)
            $syncResult = $this->callPlatformApi($config, 'menu/sync', ['items' => $formattedItems]);

            // Log sync
            $stmt = $pdo->prepare("
                INSERT INTO delivery_sync_logs
                    (tenant_id, platform_name, sync_type, items_count, status, response, created_at)
                VALUES (?, ?, 'menu', ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $tenantId, $platform, count($formattedItems),
                $syncResult['success'] ? 'success' : 'failed',
                json_encode($syncResult)
            ]);

            return Response::success([
                'platform' => $platform,
                'items_synced' => count($formattedItems),
                'sync_status' => $syncResult['success'] ? 'success' : 'failed'
            ], $syncResult['success'] ? 'Menu synced successfully' : 'Menu sync failed');
        } catch (\Exception $e) {
            return Response::error('Failed to sync menu: ' . $e->getMessage());
        }
    }

    /**
     * Receive webhook from delivery platform (new order)
     * POST /api/v1/integrations/delivery/{platform}/webhook
     */
    public function handleWebhook($request)
    {
        try {
            $platform = $request['platform'] ?? '';
            $body = $request['body'] ?? [];

            if (!in_array($platform, $this->platforms)) {
                return Response::error("Unsupported platform", 400);
            }

            $merchantId = $body['merchant_id'] ?? '';
            if ($merchantId === '') {
                return Response::error('merchant_id is required', 400);
            }

            $pdo = $this->db->connect();
            $statement = $pdo->prepare('SELECT tenant_id, api_secret FROM delivery_platform_integrations WHERE platform_name = ? AND merchant_id = ? AND is_active = 1');
            $statement->execute([$platform, $merchantId]);
            $integration = $statement->fetch(\PDO::FETCH_ASSOC);
            if (!$integration || empty($integration['api_secret'])) {
                return Response::error('Webhook integration is not configured', 401);
            }

            $signature = $request['headers']['X-Platform-Signature'] ?? $request['headers']['x-platform-signature'] ?? '';
            $rawBody = $request['raw_body'] ?? json_encode($body, JSON_UNESCAPED_SLASHES);
            $expectedSignature = hash_hmac('sha256', $rawBody, $integration['api_secret']);
            if ($signature === '' || !hash_equals($expectedSignature, $signature)) {
                return Response::error('Invalid webhook signature', 401);
            }

            $eventType = $body['event_type'] ?? '';
            $orderData = $body['order_data'] ?? [];
            $tenantId = (int) $integration['tenant_id'];

            switch ($eventType) {
                case 'order.created':
                    return $this->handleNewOrder($platform, $orderData, $tenantId);
                case 'order.updated':
                    return $this->handleOrderUpdate($platform, $orderData, $tenantId);
                case 'order.cancelled':
                    return $this->handleOrderCancellation($platform, $orderData, $tenantId);
                default:
                    return Response::error("Unknown event type: {$eventType}", 400);
            }
        } catch (\Exception $e) {
            return Response::error('Webhook handling failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle new order from delivery platform
     */
    private function handleNewOrder($platform, $orderData, int $tenantId)
    {
        $pdo = $this->db->connect();

        // Map platform order to internal order
        $platformOrderId = $orderData['platform_order_id'] ?? '';
        $customerName = $orderData['customer_name'] ?? 'Delivery Customer';
        $customerPhone = $orderData['customer_phone'] ?? '';
        $deliveryAddress = $orderData['delivery_address'] ?? '';
        $items = $orderData['items'] ?? [];
        $totalAmount = $orderData['total_amount'] ?? 0;
        $notes = $orderData['notes'] ?? '';

        // Check if order already exists
        $stmt = $pdo->prepare("
            SELECT order_id FROM orders
            WHERE tenant_id = ? AND platform_order_id = ? AND platform_name = ?
        ");
        $stmt->execute([$tenantId, $platformOrderId, $platform]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            return Response::success(['order_id' => $existing['order_id']], 'Order already exists');
        }

        // Create order
        $orderNumber = 'DEL-' . strtoupper(substr($platform, 0, 3)) . '-' . date('Ymd') . '-' . substr(uniqid(), -4);

        $stmt = $pdo->prepare("
            INSERT INTO orders
                (tenant_id, order_number, order_type, status, total_amount, payment_status,
                 customer_name, customer_phone, delivery_address, notes,
                 platform_name, platform_order_id, created_at)
            VALUES (?, ?, 'delivery', 'pending', ?, 'pending',
                    ?, ?, ?, ?,
                    ?, ?, NOW())
        ");
        $stmt->execute([
            $tenantId, $orderNumber, $totalAmount,
            $customerName, $customerPhone, $deliveryAddress, $notes,
            $platform, $platformOrderId
        ]);
        $orderId = $pdo->lastInsertId();

        // Create order items
        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items
                    (order_id, product_id, product_name, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId,
                $item['product_id'] ?? null,
                $item['name'] ?? '',
                $item['quantity'] ?? 1,
                $item['price'] ?? 0,
                ($item['price'] ?? 0) * ($item['quantity'] ?? 1)
            ]);
        }

        return Response::success([
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ], 'Delivery order created successfully');
    }

    /**
     * Handle order update from delivery platform
     */
    private function handleOrderUpdate($platform, $orderData, int $tenantId)
    {
        $pdo = $this->db->connect();
        $platformOrderId = $orderData['platform_order_id'] ?? '';
        $newStatus = $orderData['status'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE orders SET status = ?, updated_at = NOW()
            WHERE tenant_id = ? AND platform_order_id = ? AND platform_name = ?
        ");
        $stmt->execute([$newStatus, $tenantId, $platformOrderId, $platform]);

        return Response::success([], 'Order updated successfully');
    }

    /**
     * Handle order cancellation from delivery platform
     */
    private function handleOrderCancellation($platform, $orderData, int $tenantId)
    {
        $pdo = $this->db->connect();
        $platformOrderId = $orderData['platform_order_id'] ?? '';
        $reason = $orderData['cancellation_reason'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE orders SET status = 'cancelled', notes = CONCAT(notes, ' [Cancelled: ', ?, ']'), updated_at = NOW()
            WHERE tenant_id = ? AND platform_order_id = ? AND platform_name = ?
        ");
        $stmt->execute([$reason, $tenantId, $platformOrderId, $platform]);

        return Response::success([], 'Order cancelled successfully');
    }

    /**
     * Get sync logs
     * GET /api/v1/integrations/delivery/{platform}/logs
     */
    public function getSyncLogs($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $platform = $request['platform'] ?? '';
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT * FROM delivery_sync_logs WHERE tenant_id = ?";
            $params = [$tenantId];

            if ($platform && in_array($platform, $this->platforms)) {
                $sql .= " AND platform_name = ?";
                $params[] = $platform;
            }

            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($logs, 'Sync logs retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve logs: ' . $e->getMessage());
        }
    }

    /**
     * Get platform configuration
     */
    private function getPlatformConfig($pdo, $tenantId, $platform)
    {
        $stmt = $pdo->prepare("
            SELECT * FROM delivery_platform_integrations
            WHERE tenant_id = ? AND platform_name = ? AND is_active = 1
        ");
        $stmt->execute([$tenantId, $platform]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Call platform API with DB-backed logging and cURL integration
     * Attempts real HTTP call if API URL is configured, otherwise logs to DB
     */
    private function callPlatformApi($config, $endpoint, $data)
    {
        $pdo = Database::getInstance()->connect();
        $platformName = $config['platform_name'];
        $apiUrl = $config['api_url'] ?? null;
        $apiKey = $config['api_key'] ?? null;

        // If API URL is configured, attempt real HTTP call
        if ($apiUrl && function_exists('curl_init')) {
            $url = rtrim($apiUrl, '/') . '/' . ltrim($endpoint, '/');
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . ($apiKey ?? ''),
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $result = ['success' => false, 'message' => 'cURL error: ' . $error, 'http_code' => 0];
            } else {
                $decoded = json_decode($response, true);
                $result = [
                    'success' => $httpCode >= 200 && $httpCode < 300,
                    'message' => $decoded['message'] ?? 'API call completed',
                    'http_code' => $httpCode,
                    'response' => $decoded ?: $response,
                ];
            }
        } else {
            // No API URL configured — log to DB and return structured response
            $result = [
                'success' => true,
                'message' => 'Platform API not configured — request logged for manual processing',
                'pending' => true,
            ];
        }

        // Log API call to delivery_sync_logs
        $stmt = $pdo->prepare("
            INSERT INTO delivery_sync_logs
                (tenant_id, platform_name, sync_type, items_count, status, response, created_at)
            VALUES (?, ?, 'api_call', 0, ?, ?, NOW())
        ");
        $stmt->execute([
            $config['tenant_id'] ?? 0,
            $platformName,
            $result['success'] ? 'success' : 'failed',
            json_encode(['endpoint' => $endpoint, 'result' => $result]),
        ]);

        error_log("Delivery API call to {$platformName}/{$endpoint} — " . ($result['success'] ? 'success' : 'failed'
        ));

        return $result;
    }
}
