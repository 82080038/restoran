<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('WeightBasedPricingService')) {
    require_once __DIR__ . '/../Services/WeightBasedPricingService.php';
}

class WeightBasedPricingController
{
    private $service;

    public function __construct()
    {
        $this->service = new WeightBasedPricingService();
    }

    /**
     * Calculate weight-based price
     */
    public function calculatePrice()
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error('Invalid input data');
                return;
            }

            $productId = $input['product_id'] ?? null;
            $actualWeight = $input['actual_weight'] ?? null;

            if (!$productId || $actualWeight === null) {
                Response::error('Product ID and actual weight are required');
                return;
            }

            $result = $this->service->calculateWeightBasedPrice($productId, $actualWeight, $user['tenant_id']);

            if ($result['success']) {
                Response::success($result['data'], 'Price calculated successfully');
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Price calculation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get available inventory items
     */
    public function getInventoryItems($request)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $inventoryId = $request['query']['inventory_id'] ?? null;

            if (!$inventoryId) {
                Response::error('Inventory ID is required');
                return;
            }

            $result = $this->service->getAvailableInventoryItems($inventoryId, $user['branch_id']);

            if ($result['success']) {
                Response::success($result['data'], 'Inventory items retrieved successfully');
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Failed to get inventory items: ' . $e->getMessage());
        }
    }

    /**
     * Get product pricing configuration
     */
    public function getPricingConfig($request)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $productId = $request['query']['product_id'] ?? null;

            if (!$productId) {
                Response::error('Product ID is required');
                return;
            }

            $result = $this->service->getProductPricingConfig($productId, $user['tenant_id']);

            if ($result['success']) {
                Response::success($result['data'], 'Pricing config retrieved successfully');
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Failed to get pricing config: ' . $e->getMessage());
        }
    }

    /**
     * Update product pricing configuration
     */
    public function updatePricingConfig()
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error('Invalid input data');
                return;
            }

            $productId = $input['product_id'] ?? null;
            $pricingConfig = $input['pricing_config'] ?? [];

            if (!$productId) {
                Response::error('Product ID is required');
                return;
            }

            $result = $this->service->updateProductPricingConfig($productId, $pricingConfig, $user['tenant_id']);

            if ($result['success']) {
                Response::success([], $result['message']);
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Failed to update pricing config: ' . $e->getMessage());
        }
    }

    /**
     * Reserve inventory item
     */
    public function reserveItem()
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error('Invalid input data');
                return;
            }

            $itemId = $input['item_id'] ?? null;
            $orderId = $input['order_id'] ?? null;

            if (!$itemId) {
                Response::error('Item ID is required');
                return;
            }

            $result = $this->service->reserveInventoryItem($itemId, $orderId);

            if ($result['success']) {
                Response::success([], $result['message']);
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Failed to reserve item: ' . $e->getMessage());
        }
    }

    /**
     * Mark inventory item as sold
     */
    public function markAsSold()
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();

            $input = json_decode(file_get_contents("php://input"), true);

            if (!$input) {
                Response::error('Invalid input data');
                return;
            }

            $itemId = $input['item_id'] ?? null;

            if (!$itemId) {
                Response::error('Item ID is required');
                return;
            }

            $result = $this->service->markInventoryItemAsSold($itemId);

            if ($result['success']) {
                Response::success([], $result['message']);
            } else {
                Response::error($result['message']);
            }
        } catch (Exception $e) {
            Response::error('Failed to mark item as sold: ' . $e->getMessage());
        }
    }
}
