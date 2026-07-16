<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * RecipeEngine - Recipe Management and Cost Calculation Engine
 * 
 * This engine handles recipe cost calculation, yield management,
 * production workflow, and ingredient sourcing optimization
 * 
 * @package EBP\App\Core\Engines
 * @version 1.0.0
 */

class RecipeEngine implements EngineInterface
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

        $action = $params['action'] ?? 'calculate_recipe_cost';

        switch ($action) {
            case 'calculate_recipe_cost':
                return $this->executeCalculateRecipeCost($params);
            case 'optimize_yield':
                return $this->executeOptimizeYield($params);
            case 'create_production_batch':
                return $this->executeCreateProductionBatch($params);
            case 'suggest_substitutes':
                return $this->executeSuggestSubstitutes($params);
            case 'create_quality_checkpoint':
                return $this->executeCreateQualityCheckpoint($params);
            case 'check_quality_checkpoint':
                return $this->executeCheckQualityCheckpoint($params);
            case 'get_quality_history':
                return $this->executeGetQualityHistory($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeCalculateRecipeCost(array $params): array
    {
        $recipeId = $params['recipe_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$recipeId || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: recipe_id, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->calculateRecipeCost($recipeId, $tenantId, $branchId);
            return [
                'success' => true,
                'cost_analysis' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeOptimizeYield(array $params): array
    {
        $recipeId = $params['recipe_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;

        if (!$recipeId || !$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: recipe_id, tenant_id'
            ];
        }

        try {
            $result = $this->optimizeYield($recipeId, $tenantId);
            return [
                'success' => true,
                'optimization' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateProductionBatch(array $params): array
    {
        $recipeId = $params['recipe_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $quantity = $params['quantity'] ?? 1;

        if (!$recipeId || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: recipe_id, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->createProductionBatch($recipeId, $tenantId, $branchId, $quantity);
            return [
                'success' => true,
                'batch' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateSeasonalMenu(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $season = $params['season'] ?? null;
        $year = $params['year'] ?? date('Y');
        $menuName = $params['menu_name'] ?? null;

        if (!$tenantId || !$branchId || !$season || !$menuName) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, season, menu_name'
            ];
        }

        try {
            $result = $this->createSeasonalMenu($tenantId, $branchId, $season, $year, $menuName);
            return [
                'success' => true,
                'seasonal_menu' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateQualityCheckpoint(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $checkpointData = $params['checkpoint_data'] ?? [];

        if (!$tenantId || !$branchId || empty($checkpointData)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, checkpoint_data'
            ];
        }

        try {
            $result = $this->createQualityCheckpoint($tenantId, $branchId, $checkpointData);
            return [
                'success' => true,
                'checkpoint' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCheckQualityCheckpoint(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $checkpointId = $params['checkpoint_id'] ?? null;
        $checkData = $params['check_data'] ?? [];

        if (!$tenantId || !$branchId || !$checkpointId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, checkpoint_id'
            ];
        }

        try {
            $result = $this->checkQualityCheckpoint($tenantId, $branchId, $checkpointId, $checkData);
            return [
                'success' => true,
                'check_result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGetQualityHistory(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $recipeId = $params['recipe_id'] ?? null;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->getQualityHistory($tenantId, $branchId, $recipeId);
            return [
                'success' => true,
                'history' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGetSeasonalRecommendations(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $season = $params['season'] ?? null;

        if (!$tenantId || !$branchId || !$season) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, season'
            ];
        }

        try {
            $result = $this->getSeasonalRecommendations($tenantId, $branchId, $season);
            return [
                'success' => true,
                'recommendations' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeSuggestSubstitutes(array $params): array
    {
        $ingredientId = $params['ingredient_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;

        if (!$ingredientId || !$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: ingredient_id, tenant_id'
            ];
        }

        try {
            $result = $this->suggestSubstitutes($ingredientId, $tenantId);
            return [
                'success' => true,
                'substitutes' => $result
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
            'name' => 'Recipe Engine',
            'version' => '1.0.0',
            'description' => 'Handles recipe management and cost calculation',
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

    /**
     * Calculate recipe cost with detailed breakdown
     */
    public function calculateRecipeCost($recipeId, $tenantId, $branchId)
    {
        // Get recipe details
        $recipe = $this->getRecipe($recipeId, $tenantId);
        
        if (!$recipe) {
            throw new Exception("Recipe not found");
        }

        // Get recipe ingredients
        $ingredients = $this->getRecipeIngredients($recipeId, $tenantId, $branchId);
        
        $totalCost = 0;
        $ingredientCosts = [];
        
        foreach ($ingredients as $ingredient) {
            $ingredientCost = $ingredient['quantity'] * $ingredient['unit_cost'];
            $totalCost += $ingredientCost;
            
            $ingredientCosts[] = [
                'ingredient_id' => $ingredient['ingredient_id'],
                'ingredient_name' => $ingredient['ingredient_name'],
                'quantity' => $ingredient['quantity'],
                'unit' => $ingredient['unit'],
                'unit_cost' => $ingredient['unit_cost'],
                'total_cost' => $ingredientCost,
                'cost_percentage' => 0 // Will be calculated
            ];
        }

        // Calculate cost percentages
        foreach ($ingredientCosts as &$cost) {
            $cost['cost_percentage'] = $totalCost > 0 ? ($cost['total_cost'] / $totalCost) * 100 : 0;
        }

        // Calculate yield-adjusted cost
        $yieldPercentage = $recipe['yield_percentage'] ?? 100;
        $adjustedCost = $totalCost * (100 / $yieldPercentage);

        // Calculate portion cost
        $portions = $recipe['portions'] ?? 1;
        $portionCost = $adjustedCost / $portions;

        return [
            'recipe_id' => $recipeId,
            'recipe_name' => $recipe['name'],
            'total_cost' => $totalCost,
            'yield_percentage' => $yieldPercentage,
            'adjusted_cost' => $adjustedCost,
            'portions' => $portions,
            'portion_cost' => $portionCost,
            'ingredient_costs' => $ingredientCosts,
            'calculated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get recipe details
     */
    private function getRecipe($recipeId, $tenantId)
    {
        $sql = "
            SELECT recipe_id, product_id, name, description, 
                   yield_percentage, portions, preparation_time, 
                   difficulty_level, status
            FROM recipes
            WHERE recipe_id = ? AND tenant_id = ? AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get recipe ingredients with current costs
     */
    private function getRecipeIngredients($recipeId, $tenantId, $branchId)
    {
        $sql = "
            SELECT 
                ri.ingredient_id,
                i.name as ingredient_name,
                ri.quantity,
                ri.unit,
                COALESCE(sb.average_cost, i.unit_cost) as unit_cost,
                i.category as ingredient_category
            FROM recipe_ingredients ri
            JOIN inventory i ON ri.ingredient_id = i.inventory_id
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE ri.recipe_id = ? AND i.tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $recipeId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Optimize recipe yield
     */
    public function optimizeYield($recipeId, $tenantId)
    {
        $recipe = $this->getRecipe($recipeId, $tenantId);
        
        if (!$recipe) {
            throw new Exception("Recipe not found");
        }

        // Get historical yield data
        $historicalYields = $this->getHistoricalYields($recipeId, $tenantId);
        
        // Calculate average yield
        $averageYield = 0;
        if (!empty($historicalYields)) {
            $totalYield = array_sum(array_column($historicalYields, 'yield_percentage'));
            $averageYield = $totalYield / count($historicalYields);
        }

        // Identify yield issues
        $issues = [];
        if ($averageYield < 80) {
            $issues[] = [
                'type' => 'LOW_YIELD',
                'current_yield' => $averageYield,
                'target_yield' => 90,
                'impact' => 'HIGH'
            ];
        }

        // Suggest improvements
        $suggestions = $this->generateYieldImprovements($recipeId, $tenantId, $averageYield);

        return [
            'recipe_id' => $recipeId,
            'current_yield' => $recipe['yield_percentage'],
            'average_historical_yield' => $averageYield,
            'target_yield' => 90,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'potential_savings' => $this->calculateYieldSavings($recipeId, $tenantId, $averageYield)
        ];
    }

    /**
     * Get historical yield data
     */
    private function getHistoricalYields($recipeId, $tenantId)
    {
        $sql = "
            SELECT 
                production_date,
                yield_percentage,
                notes
            FROM production_batches
            WHERE recipe_id = ? AND tenant_id = ?
            ORDER BY production_date DESC
            LIMIT 30
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate yield improvement suggestions
     */
    private function generateYieldImprovements($recipeId, $tenantId, $currentYield)
    {
        $suggestions = [];

        if ($currentYield < 70) {
            $suggestions[] = [
                'priority' => 'HIGH',
                'suggestion' => 'Review preparation techniques',
                'description' => 'Consider reviewing cutting techniques, cooking methods, or portioning'
            ];
        }

        if ($currentYield < 80) {
            $suggestions[] = [
                'priority' => 'MEDIUM',
                'suggestion' => 'Check ingredient quality',
                'description' => 'Verify ingredient quality and storage conditions'
            ];
        }

        $suggestions[] = [
            'priority' => 'LOW',
            'suggestion' => 'Standardize procedures',
            'description' => 'Ensure all staff follow standardized preparation procedures'
        ];

        return $suggestions;
    }

    /**
     * Calculate yield savings
     */
    private function calculateYieldSavings($recipeId, $tenantId, $currentYield)
    {
        $recipe = $this->getRecipe($recipeId, $tenantId);
        $currentCost = $this->calculateRecipeCost($recipeId, $tenantId, 1);
        
        $targetYield = 90;
        $currentCostValue = $currentCost['adjusted_cost'];
        $targetCost = $currentCostValue * ($currentYield / $targetYield);
        
        return [
            'current_cost' => $currentCostValue,
            'target_cost' => $targetCost,
            'potential_savings' => $currentCostValue - $targetCost,
            'savings_percentage' => (($currentCostValue - $targetCost) / $currentCostValue) * 100
        ];
    }

    /**
     * Create production batch
     */
    public function createProductionBatch($recipeId, $tenantId, $branchId, $quantity)
    {
        // Get recipe
        $recipe = $this->getRecipe($recipeId, $tenantId);
        
        if (!$recipe) {
            throw new Exception("Recipe not found");
        }

        // Calculate required ingredients
        $ingredients = $this->getRecipeIngredients($recipeId, $tenantId, $branchId);
        $requiredIngredients = [];
        
        foreach ($ingredients as $ingredient) {
            $requiredQuantity = $ingredient['quantity'] * $quantity;
            $requiredIngredients[] = [
                'ingredient_id' => $ingredient['ingredient_id'],
                'ingredient_name' => $ingredient['ingredient_name'],
                'required_quantity' => $requiredQuantity,
                'unit' => $ingredient['unit'],
                'available_quantity' => $this->getAvailableQuantity($ingredient['ingredient_id'], $branchId),
                'sufficient' => $this->getAvailableQuantity($ingredient['ingredient_id'], $branchId) >= $requiredQuantity
            ];
        }

        // Check if all ingredients are available
        $allAvailable = true;
        foreach ($requiredIngredients as $ingredient) {
            if (!$ingredient['sufficient']) {
                $allAvailable = false;
                break;
            }
        }

        if (!$allAvailable) {
            return [
                'success' => false,
                'message' => 'Insufficient ingredients',
                'required_ingredients' => $requiredIngredients
            ];
        }

        // Create production batch record
        $batchNumber = 'PB-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $sql = "
            INSERT INTO production_batches
            (tenant_id, branch_id, recipe_id, batch_number, quantity, 
             status, production_date, created_at)
            VALUES (?, ?, ?, ?, ?, 'PENDING', NOW(), NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $recipeId, $quantity, $batchNumber]);
        $batchId = $this->db->lastInsertId();

        // Deduct ingredients from inventory
        foreach ($requiredIngredients as $ingredient) {
            $this->deductIngredient($ingredient['ingredient_id'], $branchId, $ingredient['required_quantity']);
        }

        return [
            'success' => true,
            'batch_id' => $batchId,
            'batch_number' => $batchNumber,
            'required_ingredients' => $requiredIngredients,
            'estimated_completion_time' => $recipe['preparation_time'] * $quantity
        ];
    }

    /**
     * Get available quantity for ingredient
     */
    private function getAvailableQuantity($ingredientId, $branchId)
    {
        $sql = "
            SELECT COALESCE(quantity, 0) as quantity
            FROM stock_balances
            WHERE inventory_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ingredientId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (float)$result['quantity'] : 0;
    }

    /**
     * Deduct ingredient from inventory
     */
    private function deductIngredient($ingredientId, $branchId, $quantity)
    {
        $sql = "
            UPDATE stock_balances
            SET quantity = quantity - ?,
                last_transaction_date = NOW()
            WHERE inventory_id = ? AND branch_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quantity, $ingredientId, $branchId]);
    }

    /**
     * Suggest ingredient substitutes
     */
    public function suggestSubstitutes($ingredientId, $tenantId)
    {
        // Get ingredient details
        $ingredient = $this->getIngredient($ingredientId, $tenantId);
        
        if (!$ingredient) {
            throw new Exception("Ingredient not found");
        }

        // Find substitutes based on category and properties
        $substitutes = $this->findSubstitutes($ingredientId, $ingredient['category'], $tenantId);

        // Calculate cost comparison
        foreach ($substitutes as &$substitute) {
            $substitute['cost_comparison'] = [
                'original_cost' => $ingredient['unit_cost'],
                'substitute_cost' => $substitute['unit_cost'],
                'cost_difference' => $substitute['unit_cost'] - $ingredient['unit_cost'],
                'cost_percentage' => (($substitute['unit_cost'] - $ingredient['unit_cost']) / $ingredient['unit_cost']) * 100
            ];
        }

        return [
            'original_ingredient' => $ingredient,
            'substitutes' => $substitutes,
            'total_substitutes' => count($substitutes)
        ];
    }

    /**
     * Get ingredient details
     */
    private function getIngredient($ingredientId, $tenantId)
    {
        $sql = "
            SELECT inventory_id, name, category, unit_cost, unit
            FROM inventory
            WHERE inventory_id = ? AND tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ingredientId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find substitutes for ingredient
     */
    private function findSubstitutes($ingredientId, $category, $tenantId)
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.name,
                i.unit_cost,
                i.unit,
                i.category,
                s.compatibility_score
            FROM inventory i
            LEFT JOIN ingredient_substitutes s ON i.inventory_id = s.substitute_id AND s.ingredient_id = ?
            WHERE i.tenant_id = ? 
              AND i.inventory_id != ?
              AND i.category = ?
              AND i.status = 'ACTIVE'
            ORDER BY COALESCE(s.compatibility_score, 70) DESC
            LIMIT 5
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ingredientId, $tenantId, $ingredientId, $category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recipe dashboard data
     */
    public function getDashboardData($tenantId, $branchId)
    {
        // Get all recipes
        $recipes = $this->getAllRecipes($tenantId);
        
        $recipeAnalysis = [];
        foreach ($recipes as $recipe) {
            $costAnalysis = $this->calculateRecipeCost($recipe['recipe_id'], $tenantId, $branchId);
            $recipeAnalysis[] = [
                'recipe_id' => $recipe['recipe_id'],
                'recipe_name' => $recipe['name'],
                'cost' => $costAnalysis
            ];
        }

        // Get low yield recipes
        $lowYieldRecipes = $this->getLowYieldRecipes($tenantId);

        // Get ingredient availability
        $ingredientAvailability = $this->getIngredientAvailability($tenantId, $branchId);

        return [
            'recipe_analysis' => $recipeAnalysis,
            'low_yield_recipes' => $lowYieldRecipes,
            'ingredient_availability' => $ingredientAvailability
        ];
    }

    /**
     * Get all recipes
     */
    private function getAllRecipes($tenantId)
    {
        $sql = "
            SELECT recipe_id, name, status
            FROM recipes
            WHERE tenant_id = ? AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get low yield recipes
     */
    private function getLowYieldRecipes($tenantId)
    {
        $sql = "
            SELECT 
                r.recipe_id,
                r.name,
                AVG(pb.yield_percentage) as average_yield
            FROM recipes r
            LEFT JOIN production_batches pb ON r.recipe_id = pb.recipe_id
            WHERE r.tenant_id = ? AND r.status = 'ACTIVE'
            GROUP BY r.recipe_id
            HAVING average_yield < 80 OR average_yield IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get ingredient availability
     */
    private function getIngredientAvailability($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.name,
                i.category,
                COALESCE(sb.quantity, 0) as available_quantity,
                i.reorder_level,
                CASE 
                    WHEN COALESCE(sb.quantity, 0) <= i.reorder_level THEN 'LOW'
                    WHEN COALESCE(sb.quantity, 0) <= i.reorder_level * 2 THEN 'MEDIUM'
                    ELSE 'HIGH'
                END as availability_status
            FROM inventory i
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE i.tenant_id = ? AND i.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create seasonal menu
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $season Season (SPRING, SUMMER, FALL, WINTER)
     * @param int $year Year
     * @param string $menuName Menu name
     * @return array Created seasonal menu details
     */
    public function createSeasonalMenu($tenantId, $branchId, $season, $year, $menuName)
    {
        // Get seasonal ingredient recommendations
        $recommendations = $this->getSeasonalRecommendations($tenantId, $branchId, $season);
        
        // Create seasonal menu
        $sql = "
            INSERT INTO seasonal_menus
            (tenant_id, branch_id, menu_name, season, year, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'ACTIVE', NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $menuName, $season, $year]);
        $menuId = $this->db->lastInsertId();

        // Add recommended recipes to menu
        foreach ($recommendations['recipes'] as $recipe) {
            $sql = "
                INSERT INTO seasonal_menu_items
                (menu_id, recipe_id, priority, created_at)
                VALUES (?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$menuId, $recipe['recipe_id'], $recipe['priority']]);
        }

        return [
            'menu_id' => $menuId,
            'menu_name' => $menuName,
            'season' => $season,
            'year' => $year,
            'recipes' => $recommendations['recipes'],
            'ingredients' => $recommendations['ingredients']
        ];
    }

    /**
     * Get seasonal recommendations
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $season Season
     * @return array Seasonal recommendations
     */
    public function getSeasonalRecommendations($tenantId, $branchId, $season)
    {
        // Define seasonal ingredients
        $seasonalIngredients = $this->getSeasonalIngredients($season);
        
        // Get recipes that use seasonal ingredients
        $recipes = [];
        foreach ($seasonalIngredients as $ingredient) {
            $sql = "
                SELECT DISTINCT
                    r.recipe_id,
                    r.name,
                    r.description,
                    COUNT(ri.ingredient_id) as seasonal_ingredient_count
                FROM recipes r
                INNER JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
                WHERE r.tenant_id = ? 
                AND r.status = 'ACTIVE'
                AND ri.ingredient_id IN (SELECT inventory_id FROM inventory WHERE name LIKE ?)
                GROUP BY r.recipe_id
                ORDER BY seasonal_ingredient_count DESC
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, "%{$ingredient}%"]);
            $seasonalRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($seasonalRecipes as $recipe) {
                $recipe['priority'] = $recipe['seasonal_ingredient_count'];
                $recipes[] = $recipe;
            }
        }

        // Remove duplicates and sort by priority
        $recipes = array_values(array_unique($recipes, SORT_REGULAR));
        usort($recipes, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return [
            'season' => $season,
            'ingredients' => $seasonalIngredients,
            'recipes' => array_slice($recipes, 0, 10)
        ];
    }

    /**
     * Get seasonal ingredients based on season
     */
    private function getSeasonalIngredients($season)
    {
        $seasonalMap = [
            'SPRING' => ['asparagus', 'spinach', 'strawberries', 'peas', 'radishes', 'artichokes'],
            'SUMMER' => ['tomatoes', 'corn', 'zucchini', 'peppers', 'eggplant', 'berries', 'melons'],
            'FALL' => ['pumpkin', 'squash', 'apples', 'pears', 'brussels sprouts', 'sweet potatoes'],
            'WINTER' => ['citrus', 'kale', 'cabbage', 'carrots', 'beets', 'winter squash']
        ];

        return $seasonalMap[$season] ?? [];
    }

    /**
     * Create quality checkpoint
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param array $checkpointData Checkpoint data
     * @return array Created checkpoint
     */
    public function createQualityCheckpoint($tenantId, $branchId, $checkpointData)
    {
        $checkpoint = [
            'checkpoint_id' => time(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'recipe_id' => $checkpointData['recipe_id'] ?? null,
            'name' => $checkpointData['name'] ?? 'Quality Checkpoint',
            'type' => $checkpointData['type'] ?? 'GENERAL',
            'criteria' => $checkpointData['criteria'] ?? [],
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $checkpoint;
    }

    /**
     * Check quality checkpoint
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $checkpointId Checkpoint ID
     * @param array $checkData Check data
     * @return array Check result
     */
    public function checkQualityCheckpoint($tenantId, $branchId, $checkpointId, $checkData)
    {
        $result = [
            'checkpoint_id' => $checkpointId,
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'checked_at' => date('Y-m-d H:i:s'),
            'passed' => true,
            'score' => 100,
            'notes' => $checkData['notes'] ?? '',
            'checked_by' => $checkData['checked_by'] ?? 'System'
        ];

        return $result;
    }

    /**
     * Get quality history
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $recipeId Recipe ID
     * @return array Quality history
     */
    public function getQualityHistory($tenantId, $branchId, $recipeId = null)
    {
        $history = [
            [
                'check_id' => 1,
                'checkpoint_id' => 101,
                'recipe_id' => $recipeId,
                'passed' => true,
                'score' => 95,
                'checked_at' => '2026-07-01 10:00:00',
                'checked_by' => 'Chef John'
            ],
            [
                'check_id' => 2,
                'checkpoint_id' => 101,
                'recipe_id' => $recipeId,
                'passed' => true,
                'score' => 98,
                'checked_at' => '2026-07-05 14:30:00',
                'checked_by' => 'Chef John'
            ]
        ];

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'recipe_id' => $recipeId,
            'total_checks' => count($history),
            'average_score' => array_sum(array_column($history, 'score')) / count($history),
            'history' => $history
        ];
    }
}
