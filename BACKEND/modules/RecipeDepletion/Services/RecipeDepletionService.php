<?php

namespace App\Modules\RecipeDepletion\Services;

use App\Core\Database;
use PDO;

class RecipeDepletionService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function depleteFromOrder($tenantId, $branchId, $orderId, $productId, $quantitySold, $unit = 'portion')
    {
        $recipe = $this->getRecipeForProduct($tenantId, $branchId, $productId);
        if (!$recipe) {
            return ['success' => false, 'message' => 'No recipe found for product'];
        }

        $ingredients = $this->getRecipeIngredients($recipe['recipe_id']);
        if (empty($ingredients)) {
            return ['success' => false, 'message' => 'No ingredients in recipe'];
        }

        $logs = [];
        $totalCost = 0;

        foreach ($ingredients as $ingredient) {
            $depletionQty = $ingredient['quantity'] * $quantitySold;
            $depletionCost = $depletionQty * $ingredient['cost_per_unit'];

            $this->deductInventory($tenantId, $branchId, $ingredient['inventory_item_id'], $depletionQty);

            $sql = "INSERT INTO recipe_depletion_logs (tenant_id, branch_id, order_id, product_id, recipe_id, quantity_sold, unit, ingredient_inventory_item_id, ingredient_name, depletion_quantity, depletion_unit, depletion_cost)
                    VALUES (:tenant_id, :branch_id, :order_id, :product_id, :recipe_id, :quantity_sold, :unit, :ingredient_id, :ingredient_name, :depletion_qty, :depletion_unit, :depletion_cost)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $tenantId,
                ':branch_id' => $branchId,
                ':order_id' => $orderId,
                ':product_id' => $productId,
                ':recipe_id' => $recipe['recipe_id'],
                ':quantity_sold' => $quantitySold,
                ':unit' => $unit,
                ':ingredient_id' => $ingredient['inventory_item_id'],
                ':ingredient_name' => $ingredient['ingredient_name'] ?? '',
                ':depletion_qty' => $depletionQty,
                ':depletion_unit' => $ingredient['unit'],
                ':depletion_cost' => $depletionCost,
            ]);

            $logs[] = [
                'ingredient' => $ingredient['ingredient_name'] ?? '',
                'quantity' => $depletionQty,
                'cost' => $depletionCost,
            ];
            $totalCost += $depletionCost;
        }

        return ['success' => true, 'logs' => $logs, 'total_cost' => $totalCost];
    }

    public function getDepletionLogs($tenantId, $branchId, $dateFrom = null, $dateTo = null, $productId = null)
    {
        $sql = "SELECT rdl.*, p.product_name FROM recipe_depletion_logs rdl
                LEFT JOIN products p ON rdl.product_id = p.product_id
                WHERE rdl.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND rdl.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($dateFrom) {
            $sql .= " AND rdl.depletion_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND rdl.depletion_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        if ($productId) {
            $sql .= " AND rdl.product_id = :product_id";
            $params[':product_id'] = $productId;
        }
        $sql .= " ORDER BY rdl.depletion_date DESC LIMIT 500";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDepletionSummary($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT product_id, ingredient_inventory_item_id, ingredient_name,
                    SUM(depletion_quantity) as total_depleted,
                    SUM(depletion_cost) as total_cost,
                    COUNT(*) as log_count
                FROM recipe_depletion_logs
                WHERE tenant_id = :tenant_id AND depletion_date BETWEEN :date_from AND :date_to";
        $params = [':tenant_id' => $tenantId, ':date_from' => $dateFrom, ':date_to' => $dateTo];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $sql .= " GROUP BY product_id, ingredient_inventory_item_id ORDER BY total_cost DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createProductionBatch($data)
    {
        $sql = "INSERT INTO production_batches (tenant_id, branch_id, recipe_id, product_id, batch_code, planned_quantity, unit, status, produced_by)
                VALUES (:tenant_id, :branch_id, :recipe_id, :product_id, :batch_code, :planned_quantity, :unit, 'PLANNED', :produced_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':recipe_id' => $data['recipe_id'],
            ':product_id' => $data['product_id'] ?? null,
            ':batch_code' => $data['batch_code'] ?? 'BATCH-' . date('YmdHis'),
            ':planned_quantity' => $data['planned_quantity'],
            ':unit' => $data['unit'] ?? 'portion',
            ':produced_by' => $data['produced_by'] ?? null,
        ]);
        return ['batch_id' => $this->pdo->lastInsertId()];
    }

    public function completeProductionBatch($batchId, $data)
    {
        $batch = $this->getProductionBatch($batchId);
        if (!$batch) {
            return ['success' => false, 'message' => 'Batch not found'];
        }

        $ingredients = $this->getRecipeIngredients($batch['recipe_id']);
        $consumed = [];
        $totalIngredientCost = 0;

        foreach ($ingredients as $ing) {
            $qty = $ing['quantity'] * ($data['actual_quantity'] ?? $batch['planned_quantity']);
            $cost = $qty * $ing['cost_per_unit'];
            $consumed[] = ['ingredient' => $ing['ingredient_name'] ?? '', 'quantity' => $qty, 'cost' => $cost];
            $totalIngredientCost += $cost;
            $this->deductInventory($batch['tenant_id'], $batch['branch_id'], $ing['inventory_item_id'], $qty);
        }

        $laborCost = $data['labor_cost'] ?? 0;
        $overheadCost = $data['overhead_cost'] ?? 0;
        $totalCost = $totalIngredientCost + $laborCost + $overheadCost;
        $unitCost = ($data['actual_quantity'] ?? $batch['planned_quantity']) > 0
            ? $totalCost / ($data['actual_quantity'] ?? $batch['planned_quantity']) : 0;

        $sql = "UPDATE production_batches SET
                    actual_quantity = :actual_quantity,
                    ingredients_consumed = :consumed,
                    total_ingredient_cost = :ingredient_cost,
                    labor_cost = :labor_cost,
                    overhead_cost = :overhead_cost,
                    total_cost = :total_cost,
                    unit_cost = :unit_cost,
                    status = 'COMPLETED',
                    produced_at = NOW(),
                    notes = :notes
                WHERE batch_id = :batch_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':batch_id' => $batchId,
            ':actual_quantity' => $data['actual_quantity'] ?? $batch['planned_quantity'],
            ':consumed' => json_encode($consumed),
            ':ingredient_cost' => $totalIngredientCost,
            ':labor_cost' => $laborCost,
            ':overhead_cost' => $overheadCost,
            ':total_cost' => $totalCost,
            ':unit_cost' => $unitCost,
            ':notes' => $data['notes'] ?? null,
        ]);

        return ['batch_id' => $batchId, 'total_cost' => $totalCost, 'unit_cost' => $unitCost, 'consumed' => $consumed];
    }

    public function getProductionBatches($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT pb.*, r.recipe_name FROM production_batches pb
                LEFT JOIN recipes r ON pb.recipe_id = r.recipe_id
                WHERE pb.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND pb.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND pb.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY pb.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductionBatch($batchId)
    {
        $sql = "SELECT * FROM production_batches WHERE batch_id = :batch_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':batch_id' => $batchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getRecipeForProduct($tenantId, $branchId, $productId)
    {
        $sql = "SELECT * FROM recipes WHERE tenant_id = :tenant_id AND product_id = :product_id AND status = 'ACTIVE' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getRecipeIngredients($recipeId)
    {
        $sql = "SELECT ri.*, i.item_name as ingredient_name FROM recipe_ingredients ri
                LEFT JOIN inventory_items i ON ri.inventory_item_id = i.inventory_item_id
                WHERE ri.recipe_id = :recipe_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':recipe_id' => $recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function deductInventory($tenantId, $branchId, $inventoryItemId, $quantity)
    {
        $sql = "UPDATE inventory SET quantity_on_hand = quantity_on_hand - :qty
                WHERE tenant_id = :tenant_id AND inventory_id = :inventory_id";
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
        }
        $stmt = $this->pdo->prepare($sql);
        $params = [':tenant_id' => $tenantId, ':inventory_id' => $inventoryItemId, ':qty' => $quantity];
        if ($branchId) {
            $params[':branch_id'] = $branchId;
        }
        $stmt->execute($params);
    }
}
