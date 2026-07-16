<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

class KitchenEngine implements EngineInterface
{

    private $db;
    private $initialized = false;



    public function __construct($db = null)
    {
        if ($db) {
            $this->initialize(['db' => $db]);
        }
    }

    public function initialize($dependencies): void
    {
        $this->db = $dependencies['db'] ?? null;
        $this->initialized = !empty($this->db);
    }

    public function validate(): bool
    {
        return $this->initialized && !empty($this->db);
    }

    public function execute(array $params): array
    {
        if (!$this->validate()) {
            return [
                'success' => false,
                'message' => 'Engine not properly initialized'
            ];
        }

        $action = $params['action'] ?? 'create_kitchen_order';

        switch ($action) {
            case 'create_kitchen_order':
                return $this->executeCreateKitchenOrder($params);
            case 'split_order':
                return $this->executeSplitOrder($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeSplitOrder(array $params): array
    {
        $orderId = $params['order_id'] ?? null;
        $splitType = $params['split_type'] ?? 'EQUAL'; // EQUAL, CUSTOM, PERCENTAGE
        $splitData = $params['split_data'] ?? []; // For custom splits: [{item_id, quantity, assignee}]

        if (!$orderId) {
            return [
                'success' => false,
                'message' => 'Missing required parameter: order_id'
            ];
        }

        try {
            $result = $this->splitOrder($orderId, $splitType, $splitData);
            return [
                'success' => true,
                'split_orders' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateKitchenOrder(array $params): array
    {
        $orderId = $params['order_id'] ?? null;

        if (!$orderId) {
            return [
                'success' => false,
                'message' => 'Missing required parameter: order_id'
            ];
        }

        try {
            $result = $this->createKitchenOrder($orderId);
            return [
                'success' => $result !== false,
                'kitchen_order_id' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Kitchen Engine',
            'version' => '1.0.0',
            'description' => 'Handles kitchen order creation and management',
            'author' => 'EBP Team',
            'created_at' => '2026-07-08'
        ];
    }

    public function getHealth(): array
    {
        return [
            'status' => $this->validate() ? 'healthy' : 'unhealthy',
            'initialized' => $this->initialized,
            'database_connected' => !empty($this->db),
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }



    public function createKitchenOrder($orderId)
    {
        // Get order details to determine tenant and branch
        $sql = "SELECT tenant_id, branch_id FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return false;
        }
        
        $tenantId = $order['tenant_id'];
        $branchId = $order['branch_id'];
        
        // Generate kitchen order number
        $sql = "SELECT COUNT(*) as count FROM kitchen_orders WHERE tenant_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        $kitchenOrderNumber = 'KIT-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO kitchen_orders
            (tenant_id, branch_id, order_id, kitchen_order_number, status, priority, created_at)
            VALUES (?, ?, ?, ?, 'PENDING', 'NORMAL', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $orderId, $kitchenOrderNumber]);

        $kitchenOrderId = $this->db->lastInsertId();

        // Get order items and insert into kitchen order items
        $sql = "SELECT order_item_id, product_id, quantity FROM order_items WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orderItems as $item) {
            $sql = "
                INSERT INTO kitchen_order_items
                (kitchen_order_id, order_item_id, product_id, quantity, status, created_at)
                VALUES (?, ?, ?, ?, 'PENDING', NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$kitchenOrderId, $item['order_item_id'], $item['product_id'], $item['quantity']]);
        }

        return $kitchenOrderId;
    }

    /**
     * Split order into multiple sub-orders for split bill functionality
     * 
     * @param int $orderId Original order ID
     * @param string $splitType Type of split (EQUAL, CUSTOM, PERCENTAGE)
     * @param array $splitData Split configuration data
     * @return array Split order details
     */
    public function splitOrder($orderId, $splitType, $splitData = [])
    {
        // Get order details
        $sql = "SELECT tenant_id, branch_id, customer_id, table_id FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        $tenantId = $order['tenant_id'];
        $branchId = $order['branch_id'];
        
        // Get order items
        $sql = "SELECT order_item_id, product_id, quantity, unit_price FROM order_items WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($orderItems)) {
            throw new Exception('No items found in order');
        }

        $splitOrders = [];
        
        switch ($splitType) {
            case 'EQUAL':
                // Split equally (assuming 2-way split by default)
                $numSplits = $splitData['num_splits'] ?? 2;
                $itemsPerSplit = [];
                
                foreach ($orderItems as $item) {
                    $quantityPerSplit = $item['quantity'] / $numSplits;
                    for ($i = 0; $i < $numSplits; $i++) {
                        $itemsPerSplit[$i][] = [
                            'product_id' => $item['product_id'],
                            'quantity' => $quantityPerSplit,
                            'unit_price' => $item['unit_price']
                        ];
                    }
                }
                
                for ($i = 0; $i < $numSplits; $i++) {
                    $splitOrders[] = $this->createSplitOrder($tenantId, $branchId, $order, $itemsPerSplit[$i], $orderId, $i + 1);
                }
                break;
                
            case 'CUSTOM':
                // Custom split based on provided data
                $groupedItems = [];
                foreach ($splitData as $split) {
                    $assignee = $split['assignee'] ?? 'Guest';
                    $groupedItems[$assignee] = [];
                }
                
                foreach ($orderItems as $item) {
                    $itemId = $item['order_item_id'];
                    $assignedTo = null;
                    
                    foreach ($splitData as $split) {
                        if (isset($split['items'][$itemId])) {
                            $assignedTo = $split['assignee'] ?? 'Guest';
                            $groupedItems[$assignedTo][] = [
                                'product_id' => $item['product_id'],
                                'quantity' => $split['items'][$itemId],
                                'unit_price' => $item['unit_price']
                            ];
                        }
                    }
                }
                
                foreach ($groupedItems as $assignee => $items) {
                    if (!empty($items)) {
                        $splitOrders[] = $this->createSplitOrder($tenantId, $branchId, $order, $items, $orderId, $assignee);
                    }
                }
                break;
                
            case 'PERCENTAGE':
                // Split by percentage
                $totalSplits = $splitData['splits'] ?? [];
                
                foreach ($totalSplits as $split) {
                    $percentage = $split['percentage'] / 100;
                    $assignee = $split['assignee'] ?? 'Guest';
                    $items = [];
                    
                    foreach ($orderItems as $item) {
                        $items[] = [
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'] * $percentage,
                            'unit_price' => $item['unit_price']
                        ];
                    }
                    
                    $splitOrders[] = $this->createSplitOrder($tenantId, $branchId, $order, $items, $orderId, $assignee);
                }
                break;
                
            default:
                throw new Exception('Invalid split type');
        }
        
        return $splitOrders;
    }

    /**
     * Create a split order
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param array $originalOrder Original order data
     * @param array $items Items for this split
     * @param int $originalOrderId Original order ID
     * @param mixed $splitIdentifier Split identifier (number or name)
     * @return array Created split order details
     */
    private function createSplitOrder($tenantId, $branchId, $originalOrder, $items, $originalOrderId, $splitIdentifier)
    {
        // Generate split order number
        $sql = "SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        $orderNumber = 'SPLIT-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        // Create split order
        $sql = "
            INSERT INTO orders
            (tenant_id, branch_id, customer_id, table_id, order_number, order_date, status, parent_order_id, split_identifier, created_at)
            VALUES (?, ?, ?, ?, ?, NOW(), 'PENDING', ?, ?, NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $originalOrder['customer_id'],
            $originalOrder['table_id'],
            $orderNumber,
            $originalOrderId,
            $splitIdentifier
        ]);
        
        $splitOrderId = $this->db->lastInsertId();
        
        // Add items to split order
        $totalAmount = 0;
        foreach ($items as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $totalAmount += $lineTotal;
            
            $sql = "
                INSERT INTO order_items
                (order_id, product_id, quantity, unit_price, line_total, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$splitOrderId, $item['product_id'], $item['quantity'], $item['unit_price'], $lineTotal]);
        }
        
        // Update order total
        $sql = "UPDATE orders SET total_amount = ? WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$totalAmount, $splitOrderId]);
        
        return [
            'split_order_id' => $splitOrderId,
            'order_number' => $orderNumber,
            'split_identifier' => $splitIdentifier,
            'total_amount' => $totalAmount,
            'items' => $items
        ];
    }

}
