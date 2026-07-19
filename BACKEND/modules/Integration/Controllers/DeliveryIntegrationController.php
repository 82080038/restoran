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
class DeliveryIntegrationController
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
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;

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
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
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
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
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

            // Call platform API (simulated)
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

            // Verify webhook signature (in production)
            $signature = $request['headers']['X-Platform-Signature'] ?? '';
            // TODO: Verify signature with platform secret

            $eventType = $body['event_type'] ?? '';
            $orderData = $body['order_data'] ?? [];

            switch ($eventType) {
                case 'order.created':
                    return $this->handleNewOrder($platform, $orderData);
                case 'order.updated':
                    return $this->handleOrderUpdate($platform, $orderData);
                case 'order.cancelled':
                    return $this->handleOrderCancellation($platform, $orderData);
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
    private function handleNewOrder($platform, $orderData)
    {
        $pdo = $this->db->connect();

        // Map platform order to internal order
        $tenantId = $orderData['tenant_id'] ?? 1;
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
    private function handleOrderUpdate($platform, $orderData)
    {
        $pdo = $this->db->connect();
        $platformOrderId = $orderData['platform_order_id'] ?? '';
        $newStatus = $orderData['status'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE orders SET status = ?, updated_at = NOW()
            WHERE platform_order_id = ? AND platform_name = ?
        ");
        $stmt->execute([$newStatus, $platformOrderId, $platform]);

        return Response::success([], 'Order updated successfully');
    }

    /**
     * Handle order cancellation from delivery platform
     */
    private function handleOrderCancellation($platform, $orderData)
    {
        $pdo = $this->db->connect();
        $platformOrderId = $orderData['platform_order_id'] ?? '';
        $reason = $orderData['cancellation_reason'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE orders SET status = 'cancelled', notes = CONCAT(notes, ' [Cancelled: ', ?, ']'), updated_at = NOW()
            WHERE platform_order_id = ? AND platform_name = ?
        ");
        $stmt->execute([$reason, $platformOrderId, $platform]);

        return Response::success([], 'Order cancelled successfully');
    }

    /**
     * Get sync logs
     * GET /api/v1/integrations/delivery/{platform}/logs
     */
    public function getSyncLogs($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
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
     * Call platform API (simulated - implement actual API calls in production)
     */
    private function callPlatformApi($config, $endpoint, $data)
    {
        // In production, implement actual API calls to each platform:
        // - GoFood: https://api.gojek.com/v2/gofood/
        // - GrabFood: https://api.grab.com/grabfood/v1/
        // - ShopeeFood: https://api.shopee.com/food/v1/
        // - Uber Eats: https://api.uber.com/eats/v1/

        // For now, simulate success
        error_log("Delivery API call to {$config['platform_name']}/{$endpoint}");
        return ['success' => true, 'message' => 'API call simulated'];
    }
}
