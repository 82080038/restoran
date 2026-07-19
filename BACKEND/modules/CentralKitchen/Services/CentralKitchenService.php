<?php

namespace App\Modules\CentralKitchen\Services;

use App\Core\Database;
use App\Core\Audit;

class CentralKitchenService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = Audit::getInstance();
    }

    /**
     * Create production plan
     */
    public function createProductionPlan($tenantId, $branchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $planData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'plan_name' => $data->plan_name,
                'plan_description' => $data->plan_description ?? null,
                'production_date' => $data->production_date,
                'status' => 'DRAFT',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO production_plans (tenant_id, branch_id, plan_name, plan_description, production_date, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $planData['tenant_id'],
                $planData['branch_id'],
                $planData['plan_name'],
                $planData['plan_description'],
                $planData['production_date'],
                $planData['status'],
                $planData['created_by']
            ]);

            $planId = $this->db->lastInsertId();

            // Add production items
            if (isset($data->items) && is_array($data->items)) {
                foreach ($data->items as $item) {
                    $this->addProductionItem($planId, $item, $userId);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'production_plan', $planId, 'CREATE', json_encode($planData));

            return [
                'success' => true,
                'message' => 'Production plan created',
                'plan_id' => $planId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create production plan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add production item to plan
     */
    private function addProductionItem($planId, $item, $userId)
    {
        $sql = "INSERT INTO production_plan_items (production_plan_id, recipe_id, quantity, unit, priority, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $planId,
            $item->recipe_id,
            $item->quantity,
            $item->unit,
            $item->priority ?? 'MEDIUM',
            $item->notes ?? null,
            $userId
        ]);
    }

    /**
     * Get production plans
     */
    public function getProductionPlans($tenantId, $branchId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND production_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND production_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT pp.*, 
                    (SELECT COUNT(*) FROM production_plan_items ppi WHERE ppi.production_plan_id = pp.id) as item_count,
                    u.username as created_by_name
                FROM production_plans pp
                LEFT JOIN users u ON pp.created_by = u.id
                {$where}
                ORDER BY pp.production_date DESC, pp.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get production plan details
     */
    public function getProductionPlanDetails($planId, $tenantId)
    {
        $sql = "SELECT pp.*, 
                    u.username as created_by_name
                FROM production_plans pp
                LEFT JOIN users u ON pp.created_by = u.id
                WHERE pp.id = ? AND pp.tenant_id = ?";

        $plan = $this->db->query($sql, [$planId, $tenantId])->fetch();

        if (!$plan) {
            return null;
        }

        // Get production items
        $itemsSql = "SELECT ppi.*, 
                        r.recipe_name,
                        r.cost_per_portion,
                        (ppi.quantity * r.cost_per_portion) as estimated_cost
                    FROM production_plan_items ppi
                    LEFT JOIN recipes r ON ppi.recipe_id = r.id
                    WHERE ppi.production_plan_id = ?
                    ORDER BY ppi.priority DESC, ppi.id";

        $plan['items'] = $this->db->query($itemsSql, [$planId])->fetchAll();

        return $plan;
    }

    /**
     * Calculate ingredient requirements for production plan
     */
    public function calculateIngredientRequirements($planId, $tenantId)
    {
        $sql = "SELECT ppi.recipe_id, ppi.quantity as production_quantity
                FROM production_plan_items ppi
                WHERE ppi.production_plan_id = ?";

        $items = $this->db->query($sql, [$planId])->fetchAll();

        $ingredientRequirements = [];

        foreach ($items as $item) {
            // Get recipe ingredients
            $recipeSql = "SELECT ri.ingredient_id, ri.quantity as recipe_quantity, ri.unit,
                            ii.item_name, ii.current_stock, ii.unit_of_measure
                        FROM recipe_ingredients ri
                        LEFT JOIN inventory_items ii ON ri.ingredient_id = ii.id
                        WHERE ri.recipe_id = ?";

            $ingredients = $this->db->query($recipeSql, [$item['recipe_id']])->fetchAll();

            foreach ($ingredients as $ingredient) {
                $requiredQuantity = $ingredient['recipe_quantity'] * $item['production_quantity'];
                
                if (!isset($ingredientRequirements[$ingredient['ingredient_id']])) {
                    $ingredientRequirements[$ingredient['ingredient_id']] = [
                        'ingredient_id' => $ingredient['ingredient_id'],
                        'item_name' => $ingredient['item_name'],
                        'current_stock' => $ingredient['current_stock'],
                        'unit' => $ingredient['unit'],
                        'required_quantity' => 0,
                        'is_sufficient' => true
                    ];
                }

                $ingredientRequirements[$ingredient['ingredient_id']]['required_quantity'] += $requiredQuantity;
            }
        }

        // Check sufficiency
        foreach ($ingredientRequirements as &$req) {
            $req['is_sufficient'] = $req['current_stock'] >= $req['required_quantity'];
            $req['shortage'] = $req['required_quantity'] - $req['current_stock'];
            if ($req['shortage'] < 0) $req['shortage'] = 0;
        }

        return array_values($ingredientRequirements);
    }

    /**
     * Standardize recipe across branches
     */
    public function standardizeRecipe($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            // Get master recipe
            $masterRecipeSql = "SELECT * FROM recipes WHERE id = ? AND tenant_id = ?";
            $masterRecipe = $this->db->query($masterRecipeSql, [$data->master_recipe_id, $tenantId])->fetch();

            if (!$masterRecipe) {
                throw new Exception('Master recipe not found');
            }

            // Get branches to standardize
            $branches = $data->branch_ids ?? [];

            foreach ($branches as $branchId) {
                // Check if recipe already exists in branch
                $existingSql = "SELECT id FROM recipes WHERE recipe_code = ? AND branch_id = ? AND tenant_id = ?";
                $existing = $this->db->query($existingSql, [$masterRecipe['recipe_code'], $branchId, $tenantId])->fetch();

                if ($existing) {
                    // Update existing recipe
                    $updateSql = "UPDATE recipes SET 
                                    recipe_name = ?,
                                    recipe_description = ?,
                                    preparation_time = ?,
                                    serving_size = ?,
                                    cost_per_portion = ?,
                                    updated_by = ?,
                                    updated_at = NOW()
                                  WHERE id = ?";
                    
                    $this->db->prepare($updateSql)->execute([
                        $masterRecipe['recipe_name'],
                        $masterRecipe['recipe_description'],
                        $masterRecipe['preparation_time'],
                        $masterRecipe['serving_size'],
                        $masterRecipe['cost_per_portion'],
                        $userId,
                        $existing['id']
                    ]);

                    $recipeId = $existing['id'];
                } else {
                    // Create new recipe in branch
                    $insertSql = "INSERT INTO recipes (tenant_id, branch_id, recipe_code, recipe_name, recipe_description, preparation_time, serving_size, cost_per_portion, status, created_by, created_at)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', ?, NOW())";
                    
                    $this->db->prepare($insertSql)->execute([
                        $tenantId,
                        $branchId,
                        $masterRecipe['recipe_code'],
                        $masterRecipe['recipe_name'],
                        $masterRecipe['recipe_description'],
                        $masterRecipe['preparation_time'],
                        $masterRecipe['serving_size'],
                        $masterRecipe['cost_per_portion'],
                        $userId
                    ]);

                    $recipeId = $this->db->lastInsertId();
                }

                // Copy ingredients
                $this->copyRecipeIngredients($masterRecipe['id'], $recipeId, $userId);
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'recipe_standardization', $data->master_recipe_id, 'STANDARDIZE', json_encode($data));

            return [
                'success' => true,
                'message' => 'Recipe standardized across branches',
                'branches_affected' => count($branches)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to standardize recipe: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Copy recipe ingredients
     */
    private function copyRecipeIngredients($sourceRecipeId, $targetRecipeId, $userId)
    {
        // Delete existing ingredients
        $deleteSql = "DELETE FROM recipe_ingredients WHERE recipe_id = ?";
        $this->db->prepare($deleteSql)->execute([$targetRecipeId]);

        // Copy ingredients
        $copySql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit, created_by, created_at)
                    SELECT ?, ingredient_id, quantity, unit, ?, NOW()
                    FROM recipe_ingredients
                    WHERE recipe_id = ?";
        
        $this->db->prepare($copySql)->execute([$targetRecipeId, $userId, $sourceRecipeId]);
    }

    /**
     * Track production yield
     */
    public function trackYield($tenantId, $branchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $yieldData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'production_plan_id' => $data->production_plan_id,
                'recipe_id' => $data->recipe_id,
                'planned_quantity' => $data->planned_quantity,
                'actual_quantity' => $data->actual_quantity,
                'yield_percentage' => ($data->actual_quantity / $data->planned_quantity) * 100,
                'waste_quantity' => $data->waste_quantity ?? 0,
                'variance_reason' => $data->variance_reason ?? null,
                'recorded_by' => $userId
            ];

            $sql = "INSERT INTO production_yields (tenant_id, branch_id, production_plan_id, recipe_id, planned_quantity, actual_quantity, yield_percentage, waste_quantity, variance_reason, recorded_by, recorded_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $yieldData['tenant_id'],
                $yieldData['branch_id'],
                $yieldData['production_plan_id'],
                $yieldData['recipe_id'],
                $yieldData['planned_quantity'],
                $yieldData['actual_quantity'],
                $yieldData['yield_percentage'],
                $yieldData['waste_quantity'],
                $yieldData['variance_reason'],
                $yieldData['recorded_by']
            ]);

            $yieldId = $this->db->lastInsertId();

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'production_yield', $yieldId, 'CREATE', json_encode($yieldData));

            return [
                'success' => true,
                'message' => 'Yield tracked',
                'yield_id' => $yieldId,
                'yield_percentage' => $yieldData['yield_percentage']
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to track yield: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get yield analytics
     */
    public function getYieldAnalytics($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE py.tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND py.branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($dateFrom) {
            $where .= " AND py.recorded_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND py.recorded_at <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT py.*, 
                    r.recipe_name,
                    pp.plan_name,
                    u.username as recorded_by_name
                FROM production_yields py
                LEFT JOIN recipes r ON py.recipe_id = r.id
                LEFT JOIN production_plans pp ON py.production_plan_id = pp.id
                LEFT JOIN users u ON py.recorded_by = u.id
                {$where}
                ORDER BY py.recorded_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Create distribution order
     */
    public function createDistributionOrder($tenantId, $sourceBranchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $distributionData = [
                'tenant_id' => $tenantId,
                'source_branch_id' => $sourceBranchId,
                'destination_branch_id' => $data->destination_branch_id,
                'distribution_date' => $data->distribution_date,
                'status' => 'PENDING',
                'notes' => $data->notes ?? null,
                'created_by' => $userId
            ];

            $sql = "INSERT INTO distribution_orders (tenant_id, source_branch_id, destination_branch_id, distribution_date, status, notes, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $distributionData['tenant_id'],
                $distributionData['source_branch_id'],
                $distributionData['destination_branch_id'],
                $distributionData['distribution_date'],
                $distributionData['status'],
                $distributionData['notes'],
                $distributionData['created_by']
            ]);

            $distributionId = $this->db->lastInsertId();

            // Add distribution items
            if (isset($data->items) && is_array($data->items)) {
                foreach ($data->items as $item) {
                    $this->addDistributionItem($distributionId, $item, $userId);
                }
            }

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $sourceBranchId, $userId, 'distribution_order', $distributionId, 'CREATE', json_encode($distributionData));

            return [
                'success' => true,
                'message' => 'Distribution order created',
                'distribution_id' => $distributionId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create distribution order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add distribution item
     */
    private function addDistributionItem($distributionId, $item, $userId)
    {
        $sql = "INSERT INTO distribution_items (distribution_order_id, product_id, quantity, unit, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $distributionId,
            $item->product_id,
            $item->quantity,
            $item->unit,
            $item->notes ?? null,
            $userId
        ]);
    }

    /**
     * Get distribution orders
     */
    public function getDistributionOrders($tenantId, $branchId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND (source_branch_id = ? OR destination_branch_id = ?)";
            $params[] = $branchId;
            $params[] = $branchId;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND distribution_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND distribution_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT do.*, 
                    b1.branch_name as source_branch_name,
                    b2.branch_name as destination_branch_name,
                    u.username as created_by_name
                FROM distribution_orders do
                LEFT JOIN branches b1 ON do.source_branch_id = b1.id
                LEFT JOIN branches b2 ON do.destination_branch_id = b2.id
                LEFT JOIN users u ON do.created_by = u.id
                {$where}
                ORDER BY do.distribution_date DESC, do.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Update distribution status
     */
    public function updateDistributionStatus($distributionId, $status, $userId, $tenantId)
    {
        try {
            $updateData = ['status' => $status];
            
            if ($status === 'IN_TRANSIT') {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'DELIVERED') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
            }

            $sql = "UPDATE distribution_orders SET status = ?, updated_by = ?, updated_at = NOW()";
            $params = [$status, $userId];

            if (isset($updateData['shipped_at'])) {
                $sql .= ", shipped_at = ?";
                $params[] = $updateData['shipped_at'];
            }

            if (isset($updateData['delivered_at'])) {
                $sql .= ", delivered_at = ?";
                $params[] = $updateData['delivered_at'];
            }

            $sql .= " WHERE id = ? AND tenant_id = ?";
            $params[] = $distributionId;
            $params[] = $tenantId;

            $this->db->prepare($sql)->execute($params);

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'distribution_order', $distributionId, 'UPDATE_STATUS', json_encode(['status' => $status]));

            return [
                'success' => true,
                'message' => 'Distribution status updated'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get central kitchen summary
     */
    public function getSummary($tenantId, $branchId)
    {
        // Active production plans
        $activePlansSql = "SELECT COUNT(*) as count FROM production_plans WHERE tenant_id = ? AND branch_id = ? AND status IN ('DRAFT', 'IN_PROGRESS')";
        $activePlans = $this->db->query($activePlansSql, [$tenantId, $branchId])->fetch();

        // Today's production
        $todaySql = "SELECT COUNT(*) as count FROM production_plans WHERE tenant_id = ? AND branch_id = ? AND production_date = CURDATE()";
        $todayProduction = $this->db->query($todaySql, [$tenantId, $branchId])->fetch();

        // Pending distributions
        $pendingDistSql = "SELECT COUNT(*) as count FROM distribution_orders WHERE tenant_id = ? AND source_branch_id = ? AND status = 'PENDING'";
        $pendingDistributions = $this->db->query($pendingDistSql, [$tenantId, $branchId])->fetch();

        // Average yield (last 30 days)
        $yieldSql = "SELECT AVG(yield_percentage) as avg_yield FROM production_yields WHERE tenant_id = ? AND branch_id = ? AND recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $avgYield = $this->db->query($yieldSql, [$tenantId, $branchId])->fetch();

        return [
            'active_production_plans' => $activePlans['count'] ?? 0,
            'today_production_count' => $todayProduction['count'] ?? 0,
            'pending_distributions' => $pendingDistributions['count'] ?? 0,
            'average_yield_percentage' => round($avgYield['avg_yield'] ?? 0, 2)
        ];
    }
}
