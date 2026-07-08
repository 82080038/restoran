<?php

namespace App\Modules\Order\Services;

use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Models\OrderModifier;
use App\Modules\Order\Models\KitchenOrder;
use App\Modules\Order\Models\TableSession;
use App\Core\Database;

class OrderService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get orders
     */
    public function getOrders($restaurantId, $status, $tableId, $dateFrom, $dateTo, $page, $limit)
    {
        $orderModel = new Order();
        return $orderModel->getPaginated($restaurantId, $status, $tableId, $dateFrom, $dateTo, $page, $limit);
    }

    /**
     * Get single order
     */
    public function getOrder($id, $restaurantId)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if ($order) {
            // Get order items
            $itemModel = new OrderItem();
            $order['items'] = $itemModel->getByOrderId($id);
            
            // Get order status history
            $history = $this->getOrderStatusHistory($id);
            $order['status_history'] = $history;
        }
        
        return $order;
    }

    /**
     * Create order with complete workflow
     */
    public function createOrder($restaurantId, $userId, $data)
    {
        // Validate order before creation
        $validation = $this->validateOrderBeforeCreation($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation['errors']];
        }

        $orderModel = new Order();
        
        $orderData = [
            'restaurant_id' => $restaurantId,
            'table_id' => $data->table_id ?? null,
            'order_type' => $data->order_type,
            'order_status' => 'pending',
            'order_channel' => $data->order_channel ?? 'pos',
            'customer_id' => $data->customer_id ?? null,
            'customer_name' => $data->customer_name ?? null,
            'customer_phone' => $data->customer_phone ?? null,
            'customer_email' => $data->customer_email ?? null,
            'customer_address' => $data->customer_address ?? null,
            'order_date' => date('Y-m-d H:i:s'),
            'estimated_time' => $data->estimated_time ?? null,
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'service_charge' => 0.00,
            'discount_amount' => 0.00,
            'delivery_fee' => $data->delivery_fee ?? 0.00,
            'total_amount' => 0.00,
            'paid_amount' => 0.00,
            'payment_status' => 'unpaid',
            'created_by' => $userId,
            'special_instructions' => $data->special_instructions ?? null,
            'external_order_id' => $data->external_order_id ?? null,
            'external_source' => $data->external_source ?? null
        ];
        
        $orderId = $orderModel->create($orderData);
        
        if (!$orderId) {
            return ['success' => false, 'message' => 'Failed to create order'];
        }
        
        // Add order items with inventory check
        if (isset($data->items) && is_array($data->items)) {
            foreach ($data->items as $item) {
                $this->addOrderItem($orderId, $restaurantId, $item);
            }
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($orderId);
        
        // Create table session if dine-in
        if ($data->order_type === 'dine_in' && isset($data->table_id)) {
            $this->ensureTableSession($restaurantId, $userId, $data->table_id, $orderId);
        }
        
        // Send to kitchen if applicable
        if ($data->order_type !== 'online') {
            $this->sendToKitchen($orderId);
        }
        
        // Trigger real-time notification
        $this->triggerOrderNotification($orderId, 'created');
        
        // Log order creation
        $this->logOrderActivity($orderId, $userId, 'order_created', 'Order created successfully');
        
        return ['success' => true, 'message' => 'Order created successfully', 'order_id' => $orderId];
    }

    /**
     * Validate order before creation
     */
    private function validateOrderBeforeCreation($data)
    {
        $errors = [];
        
        // Validate order type
        if (!isset($data->order_type)) {
            $errors[] = 'Order type is required';
        } elseif (!in_array($data->order_type, ['dine_in', 'takeaway', 'delivery', 'online'])) {
            $errors[] = 'Invalid order type';
        }
        
        // Validate items
        if (!isset($data->items) || !is_array($data->items) || empty($data->items)) {
            $errors[] = 'Order must have at least one item';
        }
        
        // Validate table for dine-in
        if (isset($data->order_type) && $data->order_type === 'dine_in' && !isset($data->table_id)) {
            $errors[] = 'Table ID is required for dine-in orders';
        }
        
        // Validate delivery address for delivery orders
        if (isset($data->order_type) && $data->order_type === 'delivery' && !isset($data->customer_address)) {
            $errors[] = 'Delivery address is required for delivery orders';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Process payment for order
     */
    public function processPayment($orderId, $restaurantId, $paymentData)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        if ($order['payment_status'] === 'paid') {
            return ['success' => false, 'message' => 'Order already paid'];
        }
        
        $paymentAmount = $paymentData['amount'] ?? $order['total_amount'];
        $paymentMethod = $paymentData['payment_method'] ?? 'cash';
        
        // Validate payment amount
        if ($paymentAmount < $order['total_amount']) {
            return ['success' => false, 'message' => 'Insufficient payment amount'];
        }
        
        // Update order payment status
        $updated = $orderModel->update($orderId, [
            'payment_status' => 'paid',
            'paid_amount' => $paymentAmount,
            'payment_method' => $paymentMethod,
            'payment_date' => date('Y-m-d H:i:s')
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to process payment'];
        }
        
        // Log payment
        $this->logPayment($orderId, $paymentAmount, $paymentMethod);
        
        // Trigger notification
        $this->triggerOrderNotification($orderId, 'payment_received');
        
        // If order is ready, auto-complete it
        if ($order['order_status'] === 'ready') {
            $this->completeOrder($orderId, $restaurantId, $paymentData['processed_by'] ?? null);
        }
        
        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment_amount' => $paymentAmount,
            'change_amount' => $paymentAmount - $order['total_amount']
        ];
    }

    /**
     * Log payment
     */
    private function logPayment($orderId, $amount, $method)
    {
        $sql = "INSERT INTO order_payments (order_id, payment_amount, payment_method, payment_date, status)
                VALUES (?, ?, ?, NOW(), 'completed')";
        
        $this->db->query($sql, [$orderId, $amount, $method]);
    }

    /**
     * Complete order with full workflow
     */
    public function completeOrder($orderId, $restaurantId, $userId)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        if ($order['order_status'] === 'completed') {
            return ['success' => false, 'message' => 'Order already completed'];
        }
        
        if ($order['payment_status'] !== 'paid') {
            return ['success' => false, 'message' => 'Order must be paid before completion'];
        }
        
        // Update order status
        $updated = $orderModel->update($orderId, [
            'order_status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to complete order'];
        }
        
        // Deduct inventory
        $this->deductInventoryForOrder($orderId);
        
        // Award loyalty points if customer exists
        if ($order['customer_id']) {
            $this->awardLoyaltyPoints($orderId, $order['customer_id'], $restaurantId);
        }
        
        // Close table session if dine-in
        if ($order['order_type'] === 'dine_in' && $order['table_id']) {
            $this->closeTableSessionForOrder($orderId, $order['table_id']);
        }
        
        // Trigger notification
        $this->triggerOrderNotification($orderId, 'completed');
        
        // Log activity
        $this->logOrderActivity($orderId, $userId, 'order_completed', 'Order completed successfully');
        
        return ['success' => true, 'message' => 'Order completed successfully'];
    }

    /**
     * Deduct inventory for order
     */
    private function deductInventoryForOrder($orderId)
    {
        $itemModel = new OrderItem();
        $items = $itemModel->getByOrderId($orderId);
        
        foreach ($items as $item) {
            // Get recipe for menu item
            $recipeSql = "SELECT * FROM recipe_details WHERE menu_item_id = ?";
            $recipeItems = $this->db->query($recipeSql, [$item['menu_item_id']])->fetchAll();
            
            foreach ($recipeItems as $recipeItem) {
                $quantityNeeded = $recipeItem['quantity'] * $item['quantity'];
                
                // Deduct from inventory
                $deductSql = "UPDATE inventory_items 
                              SET current_stock = current_stock - ? 
                              WHERE inventory_id = ?";
                $this->db->query($deductSql, [$quantityNeeded, $recipeItem['item_id']]);
                
                // Log inventory movement
                $this->logInventoryMovement(
                    $recipeItem['item_id'],
                    $orderId,
                    'deducted',
                    $quantityNeeded,
                    'Order fulfillment'
                );
            }
        }
    }

    /**
     * Log inventory movement
     */
    private function logInventoryMovement($inventoryId, $orderId, $movementType, $quantity, $reason)
    {
        $sql = "INSERT INTO inventory_movements 
                (inventory_id, reference_id, movement_type, quantity, reason, movement_date)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [$inventoryId, $orderId, $movementType, $quantity, $reason]);
    }

    /**
     * Award loyalty points for order
     */
    private function awardLoyaltyPoints($orderId, $customerId, $restaurantId)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId);
        
        if (!$order || $order['total_amount'] <= 0) {
            return;
        }
        
        // Calculate points (1 point per 1000 currency units)
        $points = floor($order['total_amount'] / 1000);
        
        if ($points > 0) {
            // Award points
            $sql = "INSERT INTO loyalty_transactions 
                    (customer_id, tenant_id, points_earned, transaction_type, reference_id, created_at)
                    VALUES (?, ?, ?, 'EARNED', ?, NOW())";
            
            $this->db->query($sql, [$customerId, $restaurantId, $points, $orderId]);
            
            // Update customer balance
            $sql = "UPDATE loyalty_members 
                    SET points_balance = points_balance + ?, last_activity_at = NOW()
                    WHERE customer_id = ? AND tenant_id = ?";
            
            $this->db->query($sql, [$points, $customerId, $restaurantId]);
        }
    }

    /**
     * Close table session for order
     */
    private function closeTableSessionForOrder($orderId, $tableId)
    {
        $sessionModel = new TableSession();
        $activeSession = $sessionModel->getActiveByTable(null, $tableId);
        
        if ($activeSession) {
            $this->closeTableSession($activeSession['id'], null);
        }
    }

    /**
     * Trigger order notification
     */
    private function triggerOrderNotification($orderId, $eventType)
    {
        $sql = "INSERT INTO order_notifications 
                (order_id, notification_type, message, created_at, status)
                VALUES (?, ?, ?, NOW(), 'pending')";
        
        $messages = [
            'created' => 'New order received',
            'payment_received' => 'Payment received for order',
            'completed' => 'Order completed successfully',
            'cancelled' => 'Order cancelled'
        ];
        
        $message = $messages[$eventType] ?? 'Order updated';
        
        $this->db->query($sql, [$orderId, $eventType, $message]);
    }

    /**
     * Log order activity
     */
    private function logOrderActivity($orderId, $userId, $activityType, $description)
    {
        $sql = "INSERT INTO order_activity_log 
                (order_id, user_id, activity_type, description, activity_time)
                VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [$orderId, $userId, $activityType, $description]);
    }

    /**
     * Update order
     */
    public function updateOrder($id, $restaurantId, $data)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        $updateData = [];
        
        if (isset($data->customer_name)) {
            $updateData['customer_name'] = $data->customer_name;
        }
        if (isset($data->customer_phone)) {
            $updateData['customer_phone'] = $data->customer_phone;
        }
        if (isset($data->special_instructions)) {
            $updateData['special_instructions'] = $data->special_instructions;
        }
        if (isset($data->estimated_time)) {
            $updateData['estimated_time'] = $data->estimated_time;
        }
        if (isset($data->table_id)) {
            $updateData['table_id'] = $data->table_id;
        }
        
        $updated = $orderModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update order'];
        }
        
        return ['success' => true, 'message' => 'Order updated successfully'];
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($id, $restaurantId, $userId, $newStatus, $notes = null)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        $oldStatus = $order['order_status'];
        
        // Update order status
        $updateData = [
            'order_status' => $newStatus
        ];
        
        // Set timestamps based on status
        switch ($newStatus) {
            case 'confirmed':
                $updateData['confirmed_at'] = date('Y-m-d H:i:s');
                $updateData['confirmed_by'] = $userId;
                break;
            case 'preparing':
                $updateData['started_at'] = date('Y-m-d H:i:s');
                break;
            case 'ready':
                $updateData['ready_at'] = date('Y-m-d H:i:s');
                break;
            case 'served':
                $updateData['served_at'] = date('Y-m-d H:i:s');
                $updateData['served_by'] = $userId;
                break;
            case 'completed':
                $updateData['completed_at'] = date('Y-m-d H:i:s');
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        $updated = $orderModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update order status'];
        }
        
        // Log status change
        $this->logOrderStatusChange($id, $oldStatus, $newStatus, $userId, $notes);
        
        return ['success' => true, 'message' => 'Order status updated successfully'];
    }

    /**
     * Cancel order
     */
    public function cancelOrder($id, $restaurantId, $userId, $reason = null)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($id, $restaurantId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        if ($order['order_status'] === 'completed' || $order['order_status'] === 'cancelled') {
            return ['success' => false, 'message' => 'Cannot cancel this order'];
        }
        
        $updated = $orderModel->update($id, [
            'order_status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $reason
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to cancel order'];
        }
        
        // Log status change
        $this->logOrderStatusChange($id, $order['order_status'], 'cancelled', $userId, $reason);
        
        return ['success' => true, 'message' => 'Order cancelled successfully'];
    }

    /**
     * Add order item
     */
    public function addOrderItem($orderId, $restaurantId, $data)
    {
        $itemModel = new OrderItem();
        
        $itemData = [
            'order_id' => $orderId,
            'menu_item_id' => $data->menu_item_id,
            'item_name' => $data->item_name,
            'quantity' => $data->quantity,
            'unit_price' => $data->unit_price,
            'subtotal' => $data->quantity * $data->unit_price,
            'modifiers' => json_encode($data->modifiers ?? []),
            'special_instructions' => $data->special_instructions ?? null,
            'preparation_station' => $data->preparation_station ?? 'kitchen'
        ];
        
        $itemId = $itemModel->create($itemData);
        
        if (!$itemId) {
            return ['success' => false, 'message' => 'Failed to add item'];
        }
        
        // Add modifiers if any
        if (isset($data->modifiers) && is_array($data->modifiers)) {
            foreach ($data->modifiers as $modifier) {
                $this->addOrderModifier($itemId, $modifier);
            }
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($orderId);
        
        return ['success' => true, 'message' => 'Item added successfully', 'item_id' => $itemId];
    }

    /**
     * Update order item
     */
    public function updateOrderItem($itemId, $restaurantId, $data)
    {
        $itemModel = new OrderItem();
        $item = $itemModel->findById($itemId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $updateData = [];
        
        if (isset($data->quantity)) {
            $updateData['quantity'] = $data->quantity;
        }
        if (isset($data->special_instructions)) {
            $updateData['special_instructions'] = $data->special_instructions;
        }
        
        if (!empty($updateData)) {
            $itemModel->update($itemId, $updateData);
        }
        
        // Recalculate item subtotal
        if (isset($data->quantity)) {
            $itemModel->update($itemId, [
                'subtotal' => $data->quantity * $item['unit_price']
            ]);
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($item['order_id']);
        
        return ['success' => true, 'message' => 'Item updated successfully'];
    }

    /**
     * Remove order item
     */
    public function removeOrderItem($itemId, $restaurantId)
    {
        $itemModel = new OrderItem();
        $item = $itemModel->findById($itemId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $orderId = $item['order_id'];
        $deleted = $itemModel->delete($itemId);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to remove item'];
        }
        
        // Recalculate order totals
        $this->recalculateOrderTotals($orderId);
        
        return ['success' => true, 'message' => 'Item removed successfully'];
    }

    /**
     * Add order modifier
     */
    private function addOrderModifier($orderItemId, $data)
    {
        $modifierModel = new OrderModifier();
        
        $modifierData = [
            'order_item_id' => $orderItemId,
            'modifier_id' => $data->modifier_id,
            'modifier_name' => $data->modifier_name,
            'modifier_type' => $data->modifier_type,
            'price_adjustment' => $data->price_adjustment ?? 0.00
        ];
        
        return $modifierModel->create($modifierData);
    }

    /**
     * Recalculate order totals
     */
    private function recalculateOrderTotals($orderId)
    {
        $itemModel = new OrderItem();
        $orderModel = new Order();
        
        // Get all items for the order
        $items = $itemModel->getByOrderId($orderId);
        
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['subtotal'];
        }
        
        // Calculate tax (assuming 10% for now)
        $taxAmount = $subtotal * 0.10;
        
        // Calculate service charge (assuming 5% for dine-in)
        $order = $orderModel->findById($orderId);
        $serviceCharge = ($order['order_type'] === 'dine_in') ? $subtotal * 0.05 : 0;
        
        $totalAmount = $subtotal + $taxAmount + $serviceCharge + $order['delivery_fee'] - $order['discount_amount'];
        
        // Update order
        $orderModel->update($orderId, [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'service_charge' => $serviceCharge,
            'total_amount' => $totalAmount
        ]);
    }

    /**
     * Send to kitchen
     */
    private function sendToKitchen($orderId)
    {
        $itemModel = new OrderItem();
        $kitchenModel = new KitchenOrder();
        
        $items = $itemModel->getByOrderId($orderId);
        
        foreach ($items as $item) {
            $kitchenModel->create([
                'order_id' => $orderId,
                'order_item_id' => $item['id'],
                'station' => $item['preparation_station'],
                'display_order' => 0,
                'status' => 'pending',
                'sent_to_kitchen_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get kitchen orders
     */
    public function getKitchenOrders($restaurantId, $station, $status)
    {
        $kitchenModel = new KitchenOrder();
        return $kitchenModel->getByRestaurant($restaurantId, $station, $status);
    }

    /**
     * Update kitchen order status
     */
    public function updateKitchenOrderStatus($id, $restaurantId, $userId, $newStatus)
    {
        $kitchenModel = new KitchenOrder();
        $kitchenOrder = $kitchenModel->findById($id);
        
        if (!$kitchenOrder) {
            return ['success' => false, 'message' => 'Kitchen order not found'];
        }
        
        $updateData = ['status' => $newStatus];
        
        switch ($newStatus) {
            case 'in_progress':
                $updateData['started_at'] = date('Y-m-d H:i:s');
                break;
            case 'ready':
                $updateData['ready_at'] = date('Y-m-d H:i:s');
                break;
            case 'served':
                $updateData['served_at'] = date('Y-m-d H:i:s');
                $updateData['prepared_by'] = $userId;
                break;
        }
        
        $updated = $kitchenModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update kitchen order'];
        }
        
        // Update order item status
        $itemModel = new OrderItem();
        $itemModel->update($kitchenOrder['order_item_id'], [
            'preparation_status' => $newStatus
        ]);
        
        return ['success' => true, 'message' => 'Kitchen order updated successfully'];
    }

    /**
     * Get table sessions
     */
    public function getTableSessions($restaurantId, $tableId, $status)
    {
        $sessionModel = new TableSession();
        return $sessionModel->getByRestaurant($restaurantId, $tableId, $status);
    }

    /**
     * Create table session
     */
    public function createTableSession($restaurantId, $userId, $data)
    {
        $sessionModel = new TableSession();
        
        $sessionData = [
            'restaurant_id' => $restaurantId,
            'table_id' => $data->table_id,
            'session_number' => $this->generateSessionNumber($restaurantId, $data->table_id),
            'session_status' => 'active',
            'started_at' => date('Y-m-d H:i:s'),
            'customer_count' => $data->customer_count ?? 0,
            'customer_id' => $data->customer_id ?? null,
            'server_id' => $userId,
            'notes' => $data->notes ?? null
        ];
        
        $sessionId = $sessionModel->create($sessionData);
        
        if (!$sessionId) {
            return ['success' => false, 'message' => 'Failed to create table session'];
        }
        
        return ['success' => true, 'message' => 'Table session created', 'session_id' => $sessionId];
    }

    /**
     * Generate session number
     */
    private function generateSessionNumber($restaurantId, $tableId)
    {
        $sql = "SELECT COUNT(*) as count FROM table_sessions 
                WHERE restaurant_id = ? AND table_id = ? AND DATE(started_at) = CURDATE()";
        $result = $this->db->query($sql, [$restaurantId, $tableId])->fetch();
        $count = $result['count'] ?? 0;
        
        return 'SES' . date('Ymd') . '-' . $tableId . '-' . ($count + 1);
    }

    /**
     * Ensure table session exists
     */
    private function ensureTableSession($restaurantId, $userId, $tableId, $orderId)
    {
        $sessionModel = new TableSession();
        
        // Check if active session exists
        $activeSession = $sessionModel->getActiveByTable($restaurantId, $tableId);
        
        if (!$activeSession) {
            // Create new session
            $this->createTableSession($restaurantId, $userId, [
                'table_id' => $tableId,
                'customer_count' => 0
            ]);
        }
    }

    /**
     * Close table session
     */
    public function closeTableSession($id, $restaurantId)
    {
        $sessionModel = new TableSession();
        $session = $sessionModel->findById($id, $restaurantId);
        
        if (!$session) {
            return ['success' => false, 'message' => 'Session not found'];
        }
        
        $endedAt = date('Y-m-d H:i:s');
        $duration = strtotime($endedAt) - strtotime($session['started_at']);
        $durationMinutes = round($duration / 60);
        
        $updated = $sessionModel->update($id, [
            'session_status' => 'completed',
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to close session'];
        }
        
        return ['success' => true, 'message' => 'Session closed successfully'];
    }

    /**
     * Get order status history
     */
    private function getOrderStatusHistory($orderId)
    {
        $sql = "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY changed_at ASC";
        return $this->db->query($sql, [$orderId])->fetchAll();
    }

    /**
     * Log order status change
     */
    private function logOrderStatusChange($orderId, $oldStatus, $newStatus, $userId, $notes = null)
    {
        $sql = "INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, changed_at, notes)
                VALUES (?, ?, ?, ?, NOW(), ?)";
        
        $this->db->query($sql, [$orderId, $oldStatus, $newStatus, $userId, $notes]);
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId, $dateFrom, $dateTo)
    {
        $orderModel = new Order();
        
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        // Total orders
        $sql = "SELECT COUNT(*) as total FROM orders {$where}";
        $totalOrders = $this->db->query($sql, $params)->fetch()['total'] ?? 0;
        
        // Total revenue
        $sql = "SELECT SUM(total_amount) as revenue FROM orders {$where} WHERE order_status != 'cancelled'";
        $totalRevenue = $this->db->query($sql, $params)->fetch()['revenue'] ?? 0;
        
        // Orders by status
        $sql = "SELECT order_status, COUNT(*) as count FROM orders {$where} GROUP BY order_status";
        $ordersByStatus = $this->db->query($sql, $params)->fetchAll();
        
        // Average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $avgOrderValue,
            'orders_by_status' => $ordersByStatus
        ];
    }
}
