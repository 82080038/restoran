<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * PricingEngine - Menu Pricing and Cost Analysis Engine for RESTAURANT_ERP
 * 
 * This engine handles menu costing calculations, margin optimization,
 * dynamic pricing rules, and price history tracking
 * 
 * @package EBP\App\Core\Engines
 * @version 1.0.0
 */

class PricingEngine implements EngineInterface
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

        $action = $params['action'] ?? 'calculate_menu_item_cost';

        switch ($action) {
            case 'calculate_menu_item_cost':
                return $this->executeCalculateMenuItemCost($params);
            case 'calculate_optimal_price':
                return $this->executeCalculateOptimalPrice($params);
            case 'analyze_menu_performance':
                return $this->executeAnalyzeMenuPerformance($params);
            case 'apply_dynamic_pricing':
                return $this->executeApplyDynamicPricing($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeCalculateMenuItemCost(array $params): array
    {
        $productId = $params['product_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$productId || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: product_id, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->calculateMenuItemCost($productId, $tenantId, $branchId);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCalculateOptimalPrice(array $params): array
    {
        $productId = $params['product_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $targetMargin = $params['target_margin'] ?? 70;

        if (!$productId || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: product_id, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->calculateOptimalPrice($productId, $tenantId, $branchId, $targetMargin);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeAnalyzeMenuPerformance(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$branchId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->analyzeMenuPerformance($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateABTest(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $productId = $params['product_id'] ?? null;
        $testName = $params['test_name'] ?? null;
        $variants = $params['variants'] ?? []; // [{name, price, description, allocation_percentage}]

        if (!$tenantId || !$branchId || !$productId || !$testName || empty($variants)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id, product_id, test_name, variants'
            ];
        }

        try {
            $result = $this->createABTest($tenantId, $branchId, $productId, $testName, $variants);
            return [
                'success' => true,
                'ab_test' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeAnalyzeABTest(array $params): array
    {
        $testId = $params['test_id'] ?? null;

        if (!$testId) {
            return [
                'success' => false,
                'message' => 'Missing required parameter: test_id'
            ];
        }

        try {
            $result = $this->analyzeABTest($testId);
            return [
                'success' => true,
                'analysis' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeApplyDynamicPricing(array $params): array
    {
        $productId = $params['product_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $ruleType = $params['rule_type'] ?? null;
        $ruleParameters = $params['rule_parameters'] ?? [];

        if (!$productId || !$tenantId || !$branchId || !$ruleType) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: product_id, tenant_id, branch_id, rule_type'
            ];
        }

        try {
            $result = $this->applyDynamicPricing($productId, $tenantId, $branchId, $ruleType, $ruleParameters);
            return [
                'success' => true,
                'result' => $result
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
            'name' => 'Pricing Engine',
            'version' => '1.0.0',
            'description' => 'Handles menu pricing and cost analysis',
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
     * Calculate menu item cost based on recipe ingredients
     * 
     * @param int $productId Product ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Cost calculation result
     */
    public function calculateMenuItemCost($productId, $tenantId, $branchId)
    {
        // Get recipe for the product
        $recipe = $this->getRecipe($productId, $tenantId);
        
        if (!$recipe) {
            return [
                'success' => false,
                'message' => 'Recipe not found for this product'
            ];
        }

        // Get recipe ingredients with current costs
        $ingredients = $this->getRecipeIngredients($recipe['recipe_id'], $tenantId, $branchId);
        
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
                'total_cost' => $ingredientCost
            ];
        }

        // Calculate food cost percentage (if selling price exists)
        $foodCostPercentage = 0;
        $margin = 0;
        $grossProfit = 0;
        
        $product = $this->getProduct($productId, $tenantId);
        if ($product && $product['price'] > 0) {
            $foodCostPercentage = ($totalCost / $product['price']) * 100;
            $margin = (($product['price'] - $totalCost) / $product['price']) * 100;
            $grossProfit = $product['price'] - $totalCost;
        }

        return [
            'success' => true,
            'product_id' => $productId,
            'product_name' => $product['name'] ?? 'Unknown',
            'recipe_id' => $recipe['recipe_id'],
            'total_cost' => $totalCost,
            'ingredient_costs' => $ingredientCosts,
            'current_price' => $product['price'] ?? 0,
            'food_cost_percentage' => $foodCostPercentage,
            'margin_percentage' => $margin,
            'gross_profit' => $grossProfit,
            'calculated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calculate optimal selling price based on target margin
     * 
     * @param int $productId Product ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param float $targetMargin Target margin percentage (e.g., 70 for 70%)
     * @return array Pricing recommendation
     */
    public function calculateOptimalPrice($productId, $tenantId, $branchId, $targetMargin = 70)
    {
        $costResult = $this->calculateMenuItemCost($productId, $tenantId, $branchId);
        
        if (!$costResult['success']) {
            return $costResult;
        }

        $totalCost = $costResult['total_cost'];
        
        // Calculate price to achieve target margin
        // Price = Cost / (1 - TargetMargin/100)
        $optimalPrice = $totalCost / (1 - ($targetMargin / 100));
        
        // Round to nearest 100 or 1000 for pricing psychology
        $roundedPrice = $this->roundForPricingPsychology($optimalPrice);
        
        // Calculate actual margin with rounded price
        $actualMargin = (($roundedPrice - $totalCost) / $roundedPrice) * 100;
        
        // Get competitor prices for comparison
        $competitorPrices = $this->getCompetitorPrices($productId, $tenantId);
        
        // Get historical price data
        $priceHistory = $this->getPriceHistory($productId, $tenantId);

        return [
            'success' => true,
            'product_id' => $productId,
            'current_cost' => $totalCost,
            'target_margin' => $targetMargin,
            'optimal_price' => $optimalPrice,
            'recommended_price' => $roundedPrice,
            'actual_margin' => $actualMargin,
            'competitor_prices' => $competitorPrices,
            'price_history' => $priceHistory,
            'calculated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Analyze menu performance and profitability
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Menu performance analysis
     */
    public function analyzeMenuPerformance($tenantId, $branchId, $startDate, $endDate)
    {
        // Get all menu items
        $menuItems = $this->getMenuItems($tenantId, $branchId);
        
        $performanceData = [];
        
        foreach ($menuItems as $item) {
            // Get sales data for this item
            $salesData = $this->getItemSalesData($item['product_id'], $tenantId, $branchId, $startDate, $endDate);
            
            // Calculate cost
            $costData = $this->calculateMenuItemCost($item['product_id'], $tenantId, $branchId);
            
            if ($costData['success'] && $salesData['quantity_sold'] > 0) {
                $totalRevenue = $salesData['quantity_sold'] * $item['price'];
                $totalCost = $salesData['quantity_sold'] * $costData['total_cost'];
                $totalProfit = $totalRevenue - $totalCost;
                
                $performanceData[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'category' => $item['category_name'],
                    'current_price' => $item['price'],
                    'cost_per_unit' => $costData['total_cost'],
                    'food_cost_percentage' => $costData['food_cost_percentage'],
                    'margin_percentage' => $costData['margin_percentage'],
                    'quantity_sold' => $salesData['quantity_sold'],
                    'total_revenue' => $totalRevenue,
                    'total_cost' => $totalCost,
                    'total_profit' => $totalProfit,
                    'profit_per_unit' => $totalProfit / $salesData['quantity_sold']
                ];
            }
        }

        // Sort by total profit descending
        usort($performanceData, function($a, $b) {
            return $b['total_profit'] <=> $a['total_profit'];
        });

        // Calculate category summaries
        $categorySummary = $this->calculateCategorySummary($performanceData);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'item_performance' => $performanceData,
            'category_summary' => $categorySummary,
            'total_items_analyzed' => count($performanceData),
            'analyzed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Apply dynamic pricing rule
     * 
     * @param int $productId Product ID
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $ruleType Rule type (DEMAND_BASED, TIME_BASED, COMPETITOR_BASED)
     * @param array $ruleParameters Rule parameters
     * @return array Dynamic pricing result
     */
    public function applyDynamicPricing($productId, $tenantId, $branchId, $ruleType, $ruleParameters)
    {
        $product = $this->getProduct($productId, $tenantId);
        
        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found'
            ];
        }

        $currentPrice = $product['price'];
        $newPrice = $currentPrice;
        $reason = '';

        switch ($ruleType) {
            case 'DEMAND_BASED':
                // Adjust price based on demand
                $demandMultiplier = $ruleParameters['demand_multiplier'] ?? 1.0;
                $newPrice = $currentPrice * $demandMultiplier;
                $reason = 'Demand-based adjustment';
                break;

            case 'TIME_BASED':
                // Adjust price based on time (happy hour, peak hours, etc.)
                $timeMultiplier = $ruleParameters['time_multiplier'] ?? 1.0;
                $newPrice = $currentPrice * $timeMultiplier;
                $reason = 'Time-based adjustment';
                break;

            case 'COMPETITOR_BASED':
                // Adjust price based on competitor pricing
                $competitorPrices = $this->getCompetitorPrices($productId, $tenantId);
                if (!empty($competitorPrices)) {
                    $avgCompetitorPrice = array_sum($competitorPrices) / count($competitorPrices);
                    $priceAdjustment = $ruleParameters['adjustment_percentage'] ?? 0;
                    $newPrice = $avgCompetitorPrice * (1 + $priceAdjustment / 100);
                    $reason = 'Competitor-based adjustment';
                }
                break;

            default:
                return [
                    'success' => false,
                    'message' => 'Unknown rule type'
                ];
        }

        // Round the new price
        $newPrice = $this->roundForPricingPsychology($newPrice);

        // Log price change
        $this->logPriceChange($productId, $tenantId, $branchId, $currentPrice, $newPrice, $ruleType, $reason);

        return [
            'success' => true,
            'product_id' => $productId,
            'previous_price' => $currentPrice,
            'new_price' => $newPrice,
            'change_percentage' => (($newPrice - $currentPrice) / $currentPrice) * 100,
            'rule_type' => $ruleType,
            'reason' => $reason,
            'applied_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get recipe for a product
     */
    private function getRecipe($productId, $tenantId)
    {
        $sql = "
            SELECT recipe_id, product_id, status
            FROM recipes
            WHERE product_id = ? 
              AND tenant_id = ?
              AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get recipe ingredients with costs
     */
    private function getRecipeIngredients($recipeId, $tenantId, $branchId)
    {
        $sql = "
            SELECT 
                ri.ingredient_id,
                i.name as ingredient_name,
                ri.quantity,
                ri.unit,
                COALESCE(sb.average_cost, i.unit_cost) as unit_cost
            FROM recipe_ingredients ri
            JOIN inventory i ON ri.ingredient_id = i.inventory_id
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE ri.recipe_id = ?
              AND i.tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $recipeId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get product information
     */
    private function getProduct($productId, $tenantId)
    {
        $sql = "
            SELECT product_id, name, price, category_id
            FROM products
            WHERE product_id = ? 
              AND tenant_id = ?
              AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get menu items
     */
    private function getMenuItems($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                p.product_id,
                p.name,
                p.price,
                p.category_id,
                c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.tenant_id = ? 
              AND p.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get item sales data
     */
    private function getItemSalesData($productId, $tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                SUM(oi.quantity) as quantity_sold,
                COUNT(DISTINCT o.order_id) as order_count
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.product_id = ? 
              AND o.tenant_id = ? 
              AND o.branch_id = ?
              AND o.created_at BETWEEN ? AND ?
              AND o.status = 'COMPLETED'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $tenantId, $branchId, $startDate, $endDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get competitor prices
     */
    private function getCompetitorPrices($productId, $tenantId)
    {
        $sql = "
            SELECT price
            FROM competitor_prices
            WHERE product_id = ? 
              AND tenant_id = ?
              AND is_active = TRUE
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $tenantId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_column($results, 'price');
    }

    /**
     * Get price history
     */
    private function getPriceHistory($productId, $tenantId)
    {
        $sql = "
            SELECT 
                old_price,
                new_price,
                change_reason,
                changed_at
            FROM price_history
            WHERE product_id = ? 
              AND tenant_id = ?
            ORDER BY changed_at DESC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Round price for pricing psychology
     */
    private function roundForPricingPsychology($price)
    {
        if ($price < 1000) {
            // Round to nearest 100
            return ceil($price / 100) * 100;
        } elseif ($price < 10000) {
            // Round to nearest 500
            return ceil($price / 500) * 500;
        } else {
            // Round to nearest 1000
            return ceil($price / 1000) * 1000;
        }
    }

    /**
     * Calculate category summary
     */
    private function calculateCategorySummary($performanceData)
    {
        $categories = [];
        
        foreach ($performanceData as $item) {
            $category = $item['category'] ?? 'Uncategorized';
            
            if (!isset($categories[$category])) {
                $categories[$category] = [
                    'total_revenue' => 0,
                    'total_cost' => 0,
                    'total_profit' => 0,
                    'total_quantity' => 0,
                    'item_count' => 0
                ];
            }
            
            $categories[$category]['total_revenue'] += $item['total_revenue'];
            $categories[$category]['total_cost'] += $item['total_cost'];
            $categories[$category]['total_profit'] += $item['total_profit'];
            $categories[$category]['total_quantity'] += $item['quantity_sold'];
            $categories[$category]['item_count']++;
        }

        // Calculate averages
        foreach ($categories as $category => $data) {
            $categories[$category]['average_margin'] = $data['total_revenue'] > 0 
                ? ($data['total_profit'] / $data['total_revenue']) * 100 
                : 0;
        }

        return $categories;
    }

    /**
     * Log price change
     */
    private function logPriceChange($productId, $tenantId, $branchId, $oldPrice, $newPrice, $ruleType, $reason)
    {
        $sql = "
            INSERT INTO price_history
            (product_id, tenant_id, branch_id, old_price, new_price, change_percentage, change_reason, changed_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $changePercentage = (($newPrice - $oldPrice) / $oldPrice) * 100;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $productId,
            $tenantId,
            $branchId,
            $oldPrice,
            $newPrice,
            $changePercentage,
            $reason
        ]);
    }

    /**
     * Get pricing dashboard data
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @return array Dashboard data
     */
    public function getDashboardData($tenantId, $branchId)
    {
        // Get menu items with cost analysis
        $menuItems = $this->getMenuItems($tenantId, $branchId);
        
        $itemsWithCost = [];
        $totalRevenue = 0;
        $totalCost = 0;
        
        foreach ($menuItems as $item) {
            $costData = $this->calculateMenuItemCost($item['product_id'], $tenantId, $branchId);
            
            if ($costData['success']) {
                $itemsWithCost[] = [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'cost' => $costData['total_cost'],
                    'margin' => $costData['margin_percentage'],
                    'food_cost' => $costData['food_cost_percentage']
                ];
                
                // Get recent sales for revenue calculation
                $salesData = $this->getItemSalesData($item['product_id'], $tenantId, $branchId, date('Y-m-01'), date('Y-m-t'));
                $totalRevenue += $salesData['quantity_sold'] * $item['price'];
                $totalCost += $salesData['quantity_sold'] * $costData['total_cost'];
            }
        }

        // Get recent price changes
        $recentPriceChanges = $this->getRecentPriceChanges($tenantId, $branchId);

        return [
            'menu_items' => $itemsWithCost,
            'summary' => [
                'total_items' => count($itemsWithCost),
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalRevenue - $totalCost,
                'average_margin' => $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0
            ],
            'recent_price_changes' => $recentPriceChanges
        ];
    }

    /**
     * Get recent price changes
     */
    private function getRecentPriceChanges($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                ph.product_id,
                p.name as product_name,
                ph.old_price,
                ph.new_price,
                ph.change_percentage,
                ph.change_reason,
                ph.changed_at
            FROM price_history ph
            JOIN products p ON ph.product_id = p.product_id
            WHERE ph.tenant_id = ? 
              AND ph.branch_id = ?
            ORDER BY ph.changed_at DESC
            LIMIT 20
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create A/B test for menu item pricing
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $productId Product ID
     * @param string $testName Test name
     * @param array $variants Test variants
     * @return array Created A/B test details
     */
    public function createABTest($tenantId, $branchId, $productId, $testName, $variants)
    {
        // Validate allocation percentages sum to 100
        $totalAllocation = array_sum(array_column($variants, 'allocation_percentage'));
        if (abs($totalAllocation - 100) > 0.01) {
            throw new Exception('Variant allocation percentages must sum to 100%');
        }

        // Get current product price as baseline
        $sql = "SELECT price FROM products WHERE product_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $tenantId]);
        $currentPrice = $stmt->fetchColumn();

        // Create A/B test
        $sql = "
            INSERT INTO menu_ab_tests
            (tenant_id, branch_id, product_id, test_name, baseline_price, status, start_date, created_at)
            VALUES (?, ?, ?, ?, ?, 'ACTIVE', CURDATE(), NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $productId, $testName, $currentPrice]);
        $testId = $this->db->lastInsertId();

        // Create test variants
        foreach ($variants as $variant) {
            $sql = "
                INSERT INTO menu_ab_test_variants
                (test_id, variant_name, variant_price, variant_description, allocation_percentage, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $testId,
                $variant['name'],
                $variant['price'],
                $variant['description'] ?? '',
                $variant['allocation_percentage']
            ]);
        }

        return [
            'test_id' => $testId,
            'test_name' => $testName,
            'product_id' => $productId,
            'baseline_price' => $currentPrice,
            'variants' => $variants,
            'status' => 'ACTIVE'
        ];
    }

    /**
     * Analyze A/B test results
     * 
     * @param int $testId Test ID
     * @return array A/B test analysis results
     */
    public function analyzeABTest($testId)
    {
        // Get test details
        $sql = "
            SELECT 
                test_id,
                test_name,
                product_id,
                baseline_price,
                start_date,
                status
            FROM menu_ab_tests
            WHERE test_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$testId]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$test) {
            throw new Exception('A/B test not found');
        }

        // Get variants
        $sql = "
            SELECT 
                variant_id,
                variant_name,
                variant_price,
                variant_description,
                allocation_percentage
            FROM menu_ab_test_variants
            WHERE test_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$testId]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Analyze each variant
        $variantAnalysis = [];
        foreach ($variants as $variant) {
            $analysis = $this->analyzeVariant($testId, $variant['variant_id'], $variant['variant_price']);
            $variantAnalysis[] = array_merge($variant, $analysis);
        }

        // Get baseline performance
        $baselineAnalysis = $this->getBaselinePerformance($test['product_id'], $test['start_date']);

        return [
            'test' => $test,
            'variants' => $variantAnalysis,
            'baseline' => $baselineAnalysis,
            'recommendation' => $this->generateABTestRecommendation($variantAnalysis, $baselineAnalysis)
        ];
    }

    /**
     * Analyze individual variant performance
     */
    private function analyzeVariant($testId, $variantId, $variantPrice)
    {
        // Get sales data for this variant
        $sql = "
            SELECT 
                COUNT(DISTINCT o.order_id) as orders,
                SUM(oi.quantity) as quantity_sold,
                SUM(oi.line_total) as revenue
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.product_id = (
                SELECT product_id FROM menu_ab_tests WHERE test_id = ?
            )
            AND oi.unit_price = ?
            AND o.order_date >= (SELECT start_date FROM menu_ab_tests WHERE test_id = ?)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$testId, $variantPrice, $testId]);
        $salesData = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'orders' => $salesData['orders'] ?? 0,
            'quantity_sold' => $salesData['quantity_sold'] ?? 0,
            'revenue' => $salesData['revenue'] ?? 0,
            'average_order_value' => $salesData['orders'] > 0 ? ($salesData['revenue'] / $salesData['orders']) : 0
        ];
    }

    /**
     * Get baseline performance before test
     */
    private function getBaselinePerformance($productId, $startDate)
    {
        // Get sales data for same period before test
        $sql = "
            SELECT 
                COUNT(DISTINCT o.order_id) as orders,
                SUM(oi.quantity) as quantity_sold,
                SUM(oi.line_total) as revenue,
                AVG(oi.unit_price) as avg_price
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.product_id = ?
            AND o.order_date < ?
            AND o.order_date >= DATE_SUB(?, INTERVAL 30 DAY)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $startDate, $startDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Generate recommendation based on A/B test results
     */
    private function generateABTestRecommendation($variantAnalysis, $baseline)
    {
        $bestVariant = null;
        $bestRevenue = 0;

        foreach ($variantAnalysis as $variant) {
            if ($variant['revenue'] > $bestRevenue) {
                $bestRevenue = $variant['revenue'];
                $bestVariant = $variant;
            }
        }

        if (!$bestVariant) {
            return [
                'action' => 'INSUFFICIENT_DATA',
                'message' => 'Not enough data to make recommendation'
            ];
        }

        $baselineRevenue = $baseline['revenue'] ?? 0;
        $improvement = $baselineRevenue > 0 ? (($bestRevenue - $baselineRevenue) / $baselineRevenue) * 100 : 0;

        if ($improvement > 5) {
            return [
                'action' => 'IMPLEMENT_VARIANT',
                'variant_name' => $bestVariant['variant_name'],
                'variant_price' => $bestVariant['variant_price'],
                'improvement_percentage' => $improvement,
                'message' => "Variant shows {$improvement}% improvement over baseline"
            ];
        } else {
            return [
                'action' => 'KEEP_BASELINE',
                'message' => 'No significant improvement over baseline'
            ];
        }
    }
}
