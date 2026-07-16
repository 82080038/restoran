<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

use PDO;

class WeightBasedPricingService
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Calculate price based on weight
     */
    public function calculateWeightBasedPrice($productId, $actualWeight, $tenantId)
    {
        try {
            // Get product pricing information
            $sql = "SELECT product_id, name, pricing_type, price, unit_price_per_kg, unit_price_per_unit, unit
                   FROM products 
                   WHERE product_id = :product_id AND tenant_id = :tenant_id AND status = 'ACTIVE'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':product_id' => $productId,
                ':tenant_id' => $tenantId
            ]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            $calculatedPrice = 0;
            $pricingDetails = [];

            switch ($product['pricing_type']) {
                case 'WEIGHT_BASED':
                    if ($product['unit_price_per_kg'] && $actualWeight > 0) {
                        $calculatedPrice = $actualWeight * $product['unit_price_per_kg'];
                        $pricingDetails = [
                            'pricing_type' => 'WEIGHT_BASED',
                            'unit_price_per_kg' => $product['unit_price_per_kg'],
                            'actual_weight' => $actualWeight,
                            'calculation' => "{$actualWeight} kg × {$product['unit_price_per_kg']} = {$calculatedPrice}"
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Weight-based pricing requires unit_price_per_kg and actual_weight'
                        ];
                    }
                    break;

                case 'UNIT_BASED':
                    if ($product['unit_price_per_unit'] && $actualWeight > 0) {
                        $calculatedPrice = $actualWeight * $product['unit_price_per_unit'];
                        $pricingDetails = [
                            'pricing_type' => 'UNIT_BASED',
                            'unit_price_per_unit' => $product['unit_price_per_unit'],
                            'actual_quantity' => $actualWeight,
                            'calculation' => "{$actualWeight} units × {$product['unit_price_per_unit']} = {$calculatedPrice}"
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Unit-based pricing requires unit_price_per_unit and actual_quantity'
                        ];
                    }
                    break;

                case 'FIXED':
                default:
                    $calculatedPrice = $product['price'];
                    $pricingDetails = [
                        'pricing_type' => 'FIXED',
                        'fixed_price' => $product['price'],
                        'calculation' => "Fixed price: {$product['price']}"
                    ];
                    break;
            }

            return [
                'success' => true,
                'data' => [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['name'],
                    'calculated_price' => round($calculatedPrice, 2),
                    'pricing_details' => $pricingDetails
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate weight-based price: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get available inventory items with specific weights
     */
    public function getAvailableInventoryItems($inventoryId, $branchId)
    {
        try {
            $sql = "SELECT item_id, item_code, weight, unit_cost, calculated_cost, status, received_date, expiry_date
                   FROM inventory_items
                   WHERE inventory_id = :inventory_id 
                   AND branch_id = :branch_id 
                   AND status = 'AVAILABLE'
                   AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                   ORDER BY received_date ASC, expiry_date ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':inventory_id' => $inventoryId,
                ':branch_id' => $branchId
            ]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get inventory items: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reserve inventory item for order
     */
    public function reserveInventoryItem($itemId, $orderId)
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE inventory_items 
                   SET status = 'RESERVED'
                   WHERE item_id = :item_id AND status = 'AVAILABLE'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':item_id' => $itemId]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Item not available for reservation'
                ];
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Item reserved successfully'
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to reserve item: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mark inventory item as sold
     */
    public function markInventoryItemAsSold($itemId)
    {
        try {
            $sql = "UPDATE inventory_items 
                   SET status = 'SOLD'
                   WHERE item_id = :item_id AND status = 'RESERVED'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':item_id' => $itemId]);

            return [
                'success' => true,
                'message' => 'Item marked as sold successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to mark item as sold: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get product pricing configuration
     */
    public function getProductPricingConfig($productId, $tenantId)
    {
        try {
            $sql = "SELECT product_id, name, pricing_type, price, unit_price_per_kg, unit_price_per_unit, unit
                   FROM products 
                   WHERE product_id = :product_id AND tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':product_id' => $productId,
                ':tenant_id' => $tenantId
            ]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['name'],
                    'pricing_type' => $product['pricing_type'],
                    'price' => $product['price'],
                    'unit_price_per_kg' => $product['unit_price_per_kg'],
                    'unit_price_per_unit' => $product['unit_price_per_unit'],
                    'unit' => $product['unit']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get product pricing config: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update product pricing configuration
     */
    public function updateProductPricingConfig($productId, $pricingConfig, $tenantId)
    {
        try {
            $sql = "UPDATE products 
                   SET pricing_type = :pricing_type,
                       price = :price,
                       unit_price_per_kg = :unit_price_per_kg,
                       unit_price_per_unit = :unit_price_per_unit,
                       unit = :unit,
                       updated_at = CURRENT_TIMESTAMP
                   WHERE product_id = :product_id AND tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':pricing_type' => $pricingConfig['pricing_type'] ?? 'FIXED',
                ':price' => $pricingConfig['price'] ?? null,
                ':unit_price_per_kg' => $pricingConfig['unit_price_per_kg'] ?? null,
                ':unit_price_per_unit' => $pricingConfig['unit_price_per_unit'] ?? null,
                ':unit' => $pricingConfig['unit'] ?? 'PCS',
                ':product_id' => $productId,
                ':tenant_id' => $tenantId
            ]);

            return [
                'success' => true,
                'message' => 'Product pricing configuration updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update product pricing config: ' . $e->getMessage()
            ];
        }
    }
}
