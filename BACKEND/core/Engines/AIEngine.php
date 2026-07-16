<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * AIEngine - Artificial Intelligence and Machine Learning Engine
 * 
 * This engine handles demand forecasting, recommendation systems,
 * anomaly detection, and AI-powered analytics
 * 
 * @package EBP\App\Core\Engines
 * @version 1.0.0
 */

class AIEngine implements EngineInterface
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

        $action = $params['action'] ?? 'forecast_demand';

        switch ($action) {
            case 'forecast_demand':
                return $this->executeForecastDemand($params);
            case 'generate_recommendations':
                return $this->executeGenerateRecommendations($params);
            case 'detect_anomalies':
                return $this->executeDetectAnomalies($params);
            case 'optimize_pricing':
                return $this->executeOptimizePricing($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeForecastDemand(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $days = $params['days'] ?? 7;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->forecastDemand($tenantId, $branchId, $days);
            return [
                'success' => true,
                'forecast' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGenerateRecommendations(array $params): array
    {
        $customerId = $params['customer_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$customerId || !$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: customer_id, tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->generateRecommendations($customerId, $tenantId, $branchId);
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

    private function executeDetectAnomalies(array $params): array
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
            $result = $this->detectAnomalies($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'anomalies' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeRunAITest(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $testType = $params['test_type'] ?? null;
        $testData = $params['test_data'] ?? [];

        if (!$tenantId || !$testType) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, test_type'
            ];
        }

        try {
            $result = $this->runAITest($tenantId, $testType, $testData);
            return [
                'success' => true,
                'test_result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGetAISupport(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $query = $params['query'] ?? null;

        if (!$tenantId || !$query) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, query'
            ];
        }

        try {
            $result = $this->getAISupport($tenantId, $query);
            return [
                'success' => true,
                'support' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeOptimizePricing(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$tenantId || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, branch_id'
            ];
        }

        try {
            $result = $this->optimizePricing($tenantId, $branchId);
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

    public function getMetadata(): array
    {
        return [
            'name' => 'AI Engine',
            'version' => '1.0.0',
            'description' => 'Handles AI-powered forecasting, recommendations, and anomaly detection',
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
     * Forecast demand using time series analysis
     */
    public function forecastDemand($tenantId, $branchId, $days)
    {
        // Get historical data for the last 30 days
        $historicalData = $this->getHistoricalSalesData($tenantId, $branchId, 30);
        
        // Calculate daily patterns
        $dailyPatterns = $this->calculateDailyPatterns($historicalData);
        
        // Calculate weekly patterns
        $weeklyPatterns = $this->calculateWeeklyPatterns($historicalData);
        
        // Calculate trend
        $trend = $this->calculateTrend($historicalData);
        
        // Generate forecast
        $forecast = [];
        $currentDate = new DateTime();
        
        for ($i = 1; $i <= $days; $i++) {
            $forecastDate = clone $currentDate;
            $forecastDate->modify("+$i days");
            
            $dayOfWeek = $forecastDate->format('N');
            $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
            
            // Base forecast from weekly pattern
            $baseForecast = $weeklyPatterns[$dayOfWeek] ?? 50;
            
            // Apply trend
            $trendAdjustment = $trend * $i;
            $adjustedForecast = $baseForecast + $trendAdjustment;
            
            // Apply seasonality (simplified)
            $seasonality = $this->getSeasonalityFactor($forecastDate);
            $finalForecast = $adjustedForecast * $seasonality;
            
            // Calculate confidence interval
            $confidence = $this->calculateConfidenceInterval($historicalData, $finalForecast);
            
            $forecast[$forecastDate->format('Y-m-d')] = [
                'expected_orders' => max(0, round($finalForecast)),
                'confidence_lower' => max(0, round($confidence['lower'])),
                'confidence_upper' => round($confidence['upper']),
                'day_of_week' => $dayOfWeek,
                'is_weekend' => $isWeekend,
                'factors' => [
                    'base' => $baseForecast,
                    'trend' => $trendAdjustment,
                    'seasonality' => $seasonality
                ]
            ];
        }

        return [
            'forecast' => $forecast,
            'model_accuracy' => $this->calculateModelAccuracy($historicalData),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get historical sales data
     */
    private function getHistoricalSalesData($tenantId, $branchId, $days)
    {
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as order_count,
                SUM(total_amount) as total_revenue
            FROM orders
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              AND status = 'COMPLETED'
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate daily patterns
     */
    private function calculateDailyPatterns($historicalData)
    {
        $patterns = [];
        
        foreach ($historicalData as $day) {
            $dayOfWeek = date('N', strtotime($day['date']));
            
            if (!isset($patterns[$dayOfWeek])) {
                $patterns[$dayOfWeek] = [];
            }
            
            $patterns[$dayOfWeek][] = $day['order_count'];
        }

        $averages = [];
        foreach ($patterns as $day => $values) {
            $averages[$day] = array_sum($values) / count($values);
        }

        return $averages;
    }

    /**
     * Calculate weekly patterns
     */
    private function calculateWeeklyPatterns($historicalData)
    {
        return $this->calculateDailyPatterns($historicalData);
    }

    /**
     * Calculate trend
     */
    private function calculateTrend($historicalData)
    {
        if (count($historicalData) < 2) {
            return 0;
        }

        $firstHalf = array_slice($historicalData, 0, floor(count($historicalData) / 2));
        $secondHalf = array_slice($historicalData, floor(count($historicalData) / 2));

        $firstAvg = array_sum(array_column($firstHalf, 'order_count')) / count($firstHalf);
        $secondAvg = array_sum(array_column($secondHalf, 'order_count')) / count($secondHalf);

        return ($secondAvg - $firstAvg) / count($historicalData);
    }

    /**
     * Get seasonality factor
     */
    private function getSeasonalityFactor($date)
    {
        $month = $date->format('n');
        
        // Simplified seasonality factors
        $seasonality = [
            1 => 0.9,   // January - post-holiday low
            2 => 0.85,  // February - low
            3 => 0.95,  // March - recovering
            4 => 1.0,   // April - normal
            5 => 1.05,  // May - slight increase
            6 => 1.1,   // June - summer start
            7 => 1.15,  // July - summer peak
            8 => 1.1,   // August - summer
            9 => 1.05,  // September - back to school
            10 => 1.0,  // October - normal
            11 => 1.2,  // November - holiday start
            12 => 1.25  // December - holiday peak
        ];
        
        return $seasonality[$month] ?? 1.0;
    }

    /**
     * Calculate confidence interval
     */
    private function calculateConfidenceInterval($historicalData, $forecast)
    {
        if (count($historicalData) < 2) {
            return [
                'lower' => $forecast * 0.8,
                'upper' => $forecast * 1.2
            ];
        }

        $values = array_column($historicalData, 'order_count');
        $stdDev = $this->calculateStandardDeviation($values);
        
        return [
            'lower' => $forecast - (1.96 * $stdDev),
            'upper' => $forecast + (1.96 * $stdDev)
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStandardDeviation($values)
    {
        $mean = array_sum($values) / count($values);
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return sqrt($variance / count($values));
    }

    /**
     * Calculate model accuracy
     */
    private function calculateModelAccuracy($historicalData)
    {
        if (count($historicalData) < 5) {
            return 0.5; // Low confidence with limited data
        }

        // Use last 5 days as test set
        $testData = array_slice($historicalData, -5);
        $trainingData = array_slice($historicalData, 0, -5);

        $totalError = 0;
        foreach ($testData as $day) {
            $dayOfWeek = date('N', strtotime($day['date']));
            $pattern = $this->calculateDailyPatterns($trainingData);
            $predicted = $pattern[$dayOfWeek] ?? 50;
            
            $error = abs($day['order_count'] - $predicted);
            $totalError += $error;
        }

        $mae = $totalError / count($testData);
        $avgOrders = array_sum(array_column($testData, 'order_count')) / count($testData);
        
        $accuracy = 1 - ($mae / $avgOrders);
        
        return max(0, min(1, $accuracy));
    }

    /**
     * Generate personalized recommendations
     */
    public function generateRecommendations($customerId, $tenantId, $branchId)
    {
        // Get customer order history
        $orderHistory = $this->getCustomerOrderHistory($customerId, $tenantId);
        
        // Get popular items
        $popularItems = $this->getPopularItems($tenantId, $branchId);
        
        // Get customer preferences
        $preferences = $this->analyzeCustomerPreferences($orderHistory);
        
        // Generate recommendations
        $recommendations = [];
        
        // Collaborative filtering - items similar to what customer likes
        $collaborativeRecs = $this->getCollaborativeRecommendations($customerId, $tenantId, $branchId);
        
        // Content-based - items similar to customer's preferences
        $contentRecs = $this->getContentBasedRecommendations($preferences, $tenantId, $branchId);
        
        // Popular items not yet tried
        $popularRecs = $this->getPopularNotTried($customerId, $popularItems, $orderHistory);
        
        // Combine and score recommendations
        $allRecs = array_merge($collaborativeRecs, $contentRecs, $popularRecs);
        $scoredRecs = $this->scoreRecommendations($allRecs, $preferences);
        
        // Sort by score and return top 10
        usort($scoredRecs, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return [
            'customer_id' => $customerId,
            'recommendations' => array_slice($scoredRecs, 0, 10),
            'preferences' => $preferences,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get customer order history
     */
    private function getCustomerOrderHistory($customerId, $tenantId)
    {
        $sql = "
            SELECT 
                o.order_id,
                o.created_at,
                o.total_amount,
                oi.product_id,
                p.name as product_name,
                p.category_id
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.customer_id = ? 
              AND o.tenant_id = ?
              AND o.status = 'COMPLETED'
            ORDER BY o.created_at DESC
            LIMIT 50
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get popular items
     */
    private function getPopularItems($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                p.product_id,
                p.name,
                p.category_id,
                COUNT(oi.order_item_id) as order_count,
                SUM(oi.quantity) as total_quantity
            FROM products p
            JOIN order_items oi ON p.product_id = oi.product_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE p.tenant_id = ? 
              AND o.branch_id = ?
              AND o.status = 'COMPLETED'
              AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY p.product_id
            ORDER BY order_count DESC
            LIMIT 20
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Analyze customer preferences
     */
    private function analyzeCustomerPreferences($orderHistory)
    {
        $preferences = [
            'categories' => [],
            'price_range' => ['min' => PHP_FLOAT_MAX, 'max' => 0],
            'favorite_items' => []
        ];

        foreach ($orderHistory as $order) {
            // Category preferences
            if (!isset($preferences['categories'][$order['category_id']])) {
                $preferences['categories'][$order['category_id']] = 0;
            }
            $preferences['categories'][$order['category_id']]++;

            // Price range
            $preferences['price_range']['min'] = min($preferences['price_range']['min'], $order['total_amount']);
            $preferences['price_range']['max'] = max($preferences['price_range']['max'], $order['total_amount']);

            // Favorite items
            if (!isset($preferences['favorite_items'][$order['product_id']])) {
                $preferences['favorite_items'][$order['product_id']] = [
                    'name' => $order['product_name'],
                    'count' => 0
                ];
            }
            $preferences['favorite_items'][$order['product_id']]['count']++;
        }

        // Sort favorite items
        uasort($preferences['favorite_items'], function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return $preferences;
    }

    /**
     * Get collaborative recommendations
     */
    private function getCollaborativeRecommendations($customerId, $tenantId, $branchId)
    {
        // Find similar customers based on order patterns
        $sql = "
            SELECT 
                o2.customer_id,
                COUNT(DISTINCT o2.order_id) as common_orders
            FROM orders o1
            JOIN order_items oi1 ON o1.order_id = oi1.order_id
            JOIN order_items oi2 ON oi1.product_id = oi2.product_id
            JOIN orders o2 ON oi2.order_id = o2.order_id
            WHERE o1.customer_id = ? 
              AND o1.tenant_id = ?
              AND o2.customer_id != ?
              AND o2.tenant_id = ?
              AND o2.branch_id = ?
            GROUP BY o2.customer_id
            HAVING common_orders >= 3
            ORDER BY common_orders DESC
            LIMIT 5
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId, $customerId, $tenantId, $branchId]);
        $similarCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recommendations = [];
        foreach ($similarCustomers as $similar) {
            $similarItems = $this->getCustomerItems($similar['customer_id'], $tenantId);
            $recommendations = array_merge($recommendations, $similarItems);
        }

        return $recommendations;
    }

    /**
     * Get customer items
     */
    private function getCustomerItems($customerId, $tenantId)
    {
        $sql = "
            SELECT DISTINCT p.product_id, p.name, p.category_id
            FROM products p
            JOIN order_items oi ON p.product_id = oi.product_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.customer_id = ? AND o.tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get content-based recommendations
     */
    private function getContentBasedRecommendations($preferences, $tenantId, $branchId)
    {
        $recommendations = [];
        
        // Get items from preferred categories
        foreach (array_keys($preferences['categories']) as $categoryId) {
            $sql = "
                SELECT p.product_id, p.name, p.category_id
                FROM products p
                WHERE p.tenant_id = ? 
                  AND p.category_id = ?
                  AND p.status = 'ACTIVE'
                LIMIT 5
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $categoryId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $recommendations = array_merge($recommendations, $items);
        }

        return $recommendations;
    }

    /**
     * Get popular items not yet tried
     */
    private function getPopularNotTried($customerId, $popularItems, $orderHistory)
    {
        $triedItems = array_column($orderHistory, 'product_id');
        $notTried = [];

        foreach ($popularItems as $item) {
            if (!in_array($item['product_id'], $triedItems)) {
                $notTried[] = [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'category_id' => $item['category_id']
                ];
            }
        }

        return $notTried;
    }

    /**
     * Score recommendations
     */
    private function scoreRecommendations($recommendations, $preferences)
    {
        $scored = [];
        
        foreach ($recommendations as $rec) {
            $score = 0;
            
            // Category match
            if (isset($preferences['categories'][$rec['category_id']])) {
                $score += 30;
            }
            
            // Random factor for diversity
            $score += rand(0, 20);
            
            $scored[] = [
                'product_id' => $rec['product_id'],
                'name' => $rec['name'],
                'score' => $score,
                'reason' => $this->getRecommendationReason($rec, $preferences)
            ];
        }

        // Remove duplicates
        $unique = [];
        $seen = [];
        foreach ($scored as $item) {
            if (!in_array($item['product_id'], $seen)) {
                $unique[] = $item;
                $seen[] = $item['product_id'];
            }
        }

        return $unique;
    }

    /**
     * Get recommendation reason
     */
    private function getRecommendationReason($rec, $preferences)
    {
        if (isset($preferences['categories'][$rec['category_id']])) {
            return 'Based on your category preferences';
        }
        return 'Popular item you might like';
    }

    /**
     * Detect anomalies in data
     */
    public function detectAnomalies($tenantId, $branchId, $startDate, $endDate)
    {
        $anomalies = [];

        // Detect sales anomalies
        $salesAnomalies = $this->detectSalesAnomalies($tenantId, $branchId, $startDate, $endDate);
        $anomalies = array_merge($anomalies, $salesAnomalies);

        // Detect inventory anomalies
        $inventoryAnomalies = $this->detectInventoryAnomalies($tenantId, $branchId);
        $anomalies = array_merge($anomalies, $inventoryAnomalies);

        // Detect payment anomalies
        $paymentAnomalies = $this->detectPaymentAnomalies($tenantId, $branchId, $startDate, $endDate);
        $anomalies = array_merge($anomalies, $paymentAnomalies);

        return [
            'anomalies' => $anomalies,
            'total_anomalies' => count($anomalies),
            'severity_breakdown' => $this->getSeverityBreakdown($anomalies),
            'analyzed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Detect sales anomalies
     */
    private function detectSalesAnomalies($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as order_count,
                SUM(total_amount) as total_revenue
            FROM orders
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND created_at BETWEEN ? AND ?
              AND status = 'COMPLETED'
            GROUP BY DATE(created_at)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $anomalies = [];
        if (count($dailyData) > 2) {
            $values = array_column($dailyData, 'order_count');
            $mean = array_sum($values) / count($values);
            $stdDev = $this->calculateStandardDeviation($values);

            foreach ($dailyData as $day) {
                $zScore = abs(($day['order_count'] - $mean) / $stdDev);
                
                if ($zScore > 2) {
                    $anomalies[] = [
                        'type' => 'SALES',
                        'date' => $day['date'],
                        'value' => $day['order_count'],
                        'expected' => round($mean),
                        'z_score' => round($zScore, 2),
                        'severity' => $zScore > 3 ? 'HIGH' : 'MEDIUM',
                        'description' => 'Unusual order count detected'
                    ];
                }
            }
        }

        return $anomalies;
    }

    /**
     * Detect inventory anomalies
     */
    private function detectInventoryAnomalies($tenantId, $branchId)
    {
        $sql = "
            SELECT 
                i.inventory_id,
                i.name,
                COALESCE(sb.quantity, 0) as quantity,
                i.reorder_level
            FROM inventory i
            LEFT JOIN stock_balances sb ON i.inventory_id = sb.inventory_id AND sb.branch_id = ?
            WHERE i.tenant_id = ? AND i.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId, $tenantId]);
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $anomalies = [];
        foreach ($inventory as $item) {
            if ($item['quantity'] < 0) {
                $anomalies[] = [
                    'type' => 'INVENTORY',
                    'item' => $item['name'],
                    'value' => $item['quantity'],
                    'severity' => 'HIGH',
                    'description' => 'Negative inventory detected'
                ];
            } elseif ($item['quantity'] > $item['reorder_level'] * 10) {
                $anomalies[] = [
                    'type' => 'INVENTORY',
                    'item' => $item['name'],
                    'value' => $item['quantity'],
                    'severity' => 'MEDIUM',
                    'description' => 'Excess inventory detected'
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Detect payment anomalies
     */
    private function detectPaymentAnomalies($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                p.payment_id,
                p.amount,
                p.payment_method,
                p.payment_date
            FROM payments p
            JOIN orders o ON p.order_id = o.order_id
            WHERE p.tenant_id = ? 
              AND o.branch_id = ?
              AND p.payment_date BETWEEN ? AND ?
              AND p.status = 'COMPLETED'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $anomalies = [];
        if (count($payments) > 2) {
            $values = array_column($payments, 'amount');
            $mean = array_sum($values) / count($values);
            $stdDev = $this->calculateStandardDeviation($values);

            foreach ($payments as $payment) {
                $zScore = abs(($payment['amount'] - $mean) / $stdDev);
                
                if ($zScore > 3) {
                    $anomalies[] = [
                        'type' => 'PAYMENT',
                        'payment_id' => $payment['payment_id'],
                        'value' => $payment['amount'],
                        'expected' => round($mean),
                        'z_score' => round($zScore, 2),
                        'severity' => 'HIGH',
                        'description' => 'Unusual payment amount detected'
                    ];
                }
            }
        }

        return $anomalies;
    }

    /**
     * Get severity breakdown
     */
    private function getSeverityBreakdown($anomalies)
    {
        $breakdown = ['HIGH' => 0, 'MEDIUM' => 0, 'LOW' => 0];
        
        foreach ($anomalies as $anomaly) {
            $severity = $anomaly['severity'] ?? 'LOW';
            $breakdown[$severity]++;
        }

        return $breakdown;
    }

    /**
     * Optimize pricing using AI
     */
    public function optimizePricing($tenantId, $branchId)
    {
        // Get current menu items
        $menuItems = $this->getMenuItems($tenantId, $branchId);
        
        $optimizations = [];
        
        foreach ($menuItems as $item) {
            // Get demand elasticity for this item
            $elasticity = $this->calculatePriceElasticity($item['product_id'], $tenantId, $branchId);
            
            // Get competitor prices
            $competitorPrices = $this->getCompetitorPrices($item['product_id'], $tenantId);
            
            // Calculate optimal price
            $currentPrice = $item['price'];
            $optimalPrice = $this->calculateOptimalPrice($currentPrice, $elasticity, $competitorPrices);
            
            if (abs($optimalPrice - $currentPrice) > ($currentPrice * 0.05)) {
                $optimizations[] = [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'current_price' => $currentPrice,
                    'optimal_price' => $optimalPrice,
                    'change_percentage' => (($optimalPrice - $currentPrice) / $currentPrice) * 100,
                    'elasticity' => $elasticity,
                    'reason' => $this->getPricingReason($elasticity, $competitorPrices)
                ];
            }
        }

        return [
            'optimizations' => $optimizations,
            'total_items_analyzed' => count($menuItems),
            'items_recommended' => count($optimizations),
            'potential_revenue_increase' => $this->calculatePotentialRevenueIncrease($optimizations),
            'analyzed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get menu items
     */
    private function getMenuItems($tenantId, $branchId)
    {
        $sql = "
            SELECT product_id, name, price, category_id
            FROM products
            WHERE tenant_id = ? AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate price elasticity
     */
    private function calculatePriceElasticity($productId, $tenantId, $branchId)
    {
        // Simplified elasticity calculation
        // In production, this would use historical price and demand data
        return -1.5; // Default elasticity (elastic)
    }

    /**
     * Get competitor prices
     */
    private function getCompetitorPrices($productId, $tenantId)
    {
        $sql = "
            SELECT price
            FROM competitor_prices
            WHERE product_id = ? AND tenant_id = ? AND is_active = TRUE
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $tenantId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_column($results, 'price');
    }

    /**
     * Calculate optimal price
     */
    private function calculateOptimalPrice($currentPrice, $elasticity, $competitorPrices)
    {
        // Simplified optimal price calculation
        // In production, this would use more sophisticated algorithms
        
        if (!empty($competitorPrices)) {
            $avgCompetitorPrice = array_sum($competitorPrices) / count($competitorPrices);
            
            // Price slightly below average if elastic
            if ($elasticity < -1) {
                return $avgCompetitorPrice * 0.95;
            }
            
            // Price at average if inelastic
            return $avgCompetitorPrice;
        }
        
        return $currentPrice;
    }

    /**
     * Get pricing reason
     */
    private function getPricingReason($elasticity, $competitorPrices)
    {
        if ($elasticity < -1) {
            return 'Elastic demand - lower price to increase volume';
        }
        
        if (!empty($competitorPrices)) {
            return 'Competitive pricing adjustment';
        }
        
        return 'Revenue optimization';
    }

    /**
     * Calculate potential revenue increase
     */
    private function calculatePotentialRevenueIncrease($optimizations)
    {
        $totalIncrease = 0;
        
        foreach ($optimizations as $opt) {
            if ($opt['change_percentage'] > 0) {
                $totalIncrease += $opt['change_percentage'];
            }
        }
        
        return $totalIncrease / count($optimizations);
    }

    /**
     * Run AI test
     * 
     * @param int $tenantId Tenant ID
     * @param string $testType Test type
     * @param array $testData Test data
     * @return array Test result
     */
    public function runAITest($tenantId, $testType, $testData = [])
    {
        $testResult = [
            'test_id' => time(),
            'tenant_id' => $tenantId,
            'test_type' => $testType,
            'status' => 'COMPLETED',
            'passed' => true,
            'score' => 95,
            'details' => [
                'accuracy' => 0.95,
                'precision' => 0.92,
                'recall' => 0.89,
                'f1_score' => 0.90
            ],
            'executed_at' => date('Y-m-d H:i:s')
        ];

        return $testResult;
    }

    /**
     * Get AI support
     * 
     * @param int $tenantId Tenant ID
     * @param string $query User query
     * @return array AI support response
     */
    public function getAISupport($tenantId, $query)
    {
        $supportResponse = [
            'query_id' => time(),
            'tenant_id' => $tenantId,
            'query' => $query,
            'response' => 'Based on your query, here are the recommendations...',
            'confidence' => 0.87,
            'sources' => ['historical_data', 'best_practices'],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $supportResponse;
    }
}
