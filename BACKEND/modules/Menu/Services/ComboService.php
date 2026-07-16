<?php

if (!class_exists('ComboRepository')) {
    require_once __DIR__ . '/../Repositories/ComboRepository.php';
}


class ComboService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new ComboRepository();
        $this->db = db();
    }

    public function createCombo($data, $tenantId)
    {
        try {
            if (empty($data['combo_code']) || empty($data['combo_name']) || empty($data['combo_type'])) {
                return [
                    'success' => false,
                    'message' => 'Combo code, name, and type are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $comboId = $this->repository->createCombo($data);

            // Create combo groups
            if (!empty($data['groups'])) {
                foreach ($data['groups'] as $group) {
                    $group['combo_id'] = $comboId;
                    $groupId = $this->repository->createComboGroup($group);

                    // Create combo items
                    if (!empty($group['items'])) {
                        foreach ($group['items'] as $item) {
                            $item['combo_group_id'] = $groupId;
                            $this->repository->createComboItem($item);
                        }
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Combo created successfully',
                'combo_id' => $comboId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create combo: ' . $e->getMessage()
            ];
        }
    }

    public function getCombos($tenantId)
    {
        try {
            $combos = $this->repository->getCombosByTenant($tenantId);
            
            return [
                'success' => true,
                'message' => 'Combos retrieved successfully',
                'data' => $combos
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get combos: ' . $e->getMessage()
            ];
        }
    }

    public function calculateComboPrice($comboId, $selections, $productCondition = 'REGULAR')
    {
        try {
            $combo = $this->repository->getComboById($comboId);
            if (!$combo) {
                return [
                    'success' => false,
                    'message' => 'Combo not found'
                ];
            }

            $groups = $this->repository->getComboGroups($comboId);
            $totalIndividualPrice = 0;
            $selectedItems = [];

            foreach ($groups as $group) {
                $items = $this->repository->getComboItems($group['combo_group_id']);
                $selectedCount = 0;

                foreach ($items as $item) {
                    if (isset($selections[$group['combo_group_id']]) && 
                        in_array($item['combo_item_id'], $selections[$group['combo_group_id']])) {
                        $product = $this->getProductPrice($item['product_id'], $productCondition);
                        $totalIndividualPrice += $product['price'];
                        $selectedItems[] = array_merge($item, [
                            'price' => $product['price'],
                            'price_type' => $product['price_type'],
                            'condition' => $productCondition
                        ]);
                        $selectedCount++;
                    }
                }

                // Validate selection count
                if ($selectedCount < $group['min_selections'] || $selectedCount > $group['max_selections']) {
                    return [
                        'success' => false,
                        'message' => "Invalid selection for group: {$group['group_name']}"
                    ];
                }
            }

            // Calculate combo price
            $comboPrice = $combo['base_price'];
            
            if ($combo['combo_type'] === 'BUNDLE') {
                // Bundle: discount on total
                if ($combo['discount_percentage'] > 0) {
                    $comboPrice = $totalIndividualPrice * (1 - $combo['discount_percentage'] / 100);
                } elseif ($combo['discount_amount'] > 0) {
                    $comboPrice = $totalIndividualPrice - $combo['discount_amount'];
                }
            }

            $savings = $totalIndividualPrice - $comboPrice;

            return [
                'success' => true,
                'message' => 'Price calculated successfully',
                'data' => [
                    'combo_price' => $comboPrice,
                    'individual_price' => $totalIndividualPrice,
                    'savings' => $savings,
                    'savings_percentage' => $totalIndividualPrice > 0 ? ($savings / $totalIndividualPrice) * 100 : 0,
                    'product_condition' => $productCondition,
                    'selected_items' => $selectedItems
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate price: ' . $e->getMessage()
            ];
        }
    }

    private function getProductPrice($productId, $condition = 'REGULAR')
    {
        // Try to get condition-specific price first
        $sql = "SELECT price, price_type FROM product_prices 
                WHERE product_id = ? AND price_type = ? AND is_active = 1 
                AND (valid_from IS NULL OR valid_from <= CURDATE()) 
                AND (valid_until IS NULL OR valid_until >= CURDATE())
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $condition]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result;
        }

        // Fallback to regular price
        $sql = "SELECT price, 'REGULAR' as price_type FROM products WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result;
        }

        // Final fallback to product_prices regular type
        $sql = "SELECT price, price_type FROM product_prices 
                WHERE product_id = ? AND price_type = 'REGULAR' AND is_active = 1 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get available product conditions for a product
     */
    public function getProductConditions($productId)
    {
        try {
            $sql = "SELECT DISTINCT price_type FROM product_prices 
                    WHERE product_id = ? AND is_active = 1 
                    AND (valid_from IS NULL OR valid_from <= CURDATE()) 
                    AND (valid_until IS NULL OR valid_until >= CURDATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
            $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $conditionTypes = array_column($conditions, 'price_type');

            // Always include REGULAR as default
            if (!in_array('REGULAR', $conditionTypes)) {
                $conditionTypes[] = 'REGULAR';
            }

            return [
                'success' => true,
                'message' => 'Product conditions retrieved successfully',
                'data' => $conditionTypes
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get product conditions: ' . $e->getMessage()
            ];
        }
    }
}
