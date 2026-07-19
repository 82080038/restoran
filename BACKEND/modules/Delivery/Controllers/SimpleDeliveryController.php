<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleDeliveryController
{
    // Simple endpoint to get deliveries without middleware
    public function getDeliveries($request = null)
    {
        $db = db();

        $sql = "SELECT d.delivery_id, d.order_id, d.customer_name, d.delivery_address, d.customer_phone AS phone, 
                d.delivered_at AS delivery_time, d.delivery_fee, d.delivery_status AS status, d.driver_name
                FROM deliveries d
                WHERE d.delivery_status IN ('pending', 'preparing', 'picked_up', 'on_the_way')
                ORDER BY d.created_at ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($deliveries);
    }
}
