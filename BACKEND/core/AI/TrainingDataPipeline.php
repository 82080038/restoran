<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * TrainingDataPipeline - AI Training Data Pipeline
 * 
 * This pipeline collects, processes, and prepares data for AI model training.
 * It handles data extraction from various sources, feature engineering,
 * data validation, and export for model training.
 * 
 * @package EBP\App\Core\AI
 * @version 1.0.0
 */

class TrainingDataPipeline implements EngineInterface
{
    private $db;
    private $initialized = false;
    private $dataDirectory;
    private $config;

    public function __construct($db = null, $config = [])
    {
        $this->config = array_merge([
            'data_directory' => __DIR__ . '/../../data/training/',
            'batch_size' => 1000,
            'max_file_size' => 104857600, // 100MB
            'supported_formats' => ['json', 'csv', 'parquet']
        ], $config);

        $this->dataDirectory = $this->config['data_directory'];

        if ($db) {
            $this->initialize(['db' => $db]);
        }
    }

    public function initialize($dependencies): void
    {
        $this->db = $dependencies['db'] ?? null;
        $this->initialized = !empty($this->db);
        
        // Create data directory if it doesn't exist
        if (!is_dir($this->dataDirectory)) {
            mkdir($this->dataDirectory, 0755, true);
        }
    }

    public function validate(): bool
    {
        return $this->initialized && !empty($this->db) && is_dir($this->dataDirectory);
    }

    public function execute(array $params): array
    {
        if (!$this->validate()) {
            return [
                'success' => false,
                'message' => 'Pipeline not properly initialized'
            ];
        }

        $action = $params['action'] ?? 'extract';

        switch ($action) {
            case 'extract':
                return $this->executeExtract($params);
            case 'transform':
                return $this->executeTransform($params);
            case 'validate':
                return $this->executeValidate($params);
            case 'export':
                return $this->executeExport($params);
            case 'run_pipeline':
                return $this->executeRunPipeline($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeExtract(array $params): array
    {
        $dataSource = $params['data_source'] ?? 'orders';
        $tenantId = $params['tenant_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        try {
            $result = $this->extractData($dataSource, $tenantId, $startDate, $endDate);
            return [
                'success' => true,
                'extracted' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeTransform(array $params): array
    {
        $inputFile = $params['input_file'] ?? null;
        $transformType = $params['transform_type'] ?? 'feature_engineering';

        try {
            $result = $this->transformData($inputFile, $transformType);
            return [
                'success' => true,
                'transformed' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeValidate(array $params): array
    {
        $dataFile = $params['data_file'] ?? null;

        try {
            $result = $this->validateData($dataFile);
            return [
                'success' => true,
                'validation' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeExport(array $params): array
    {
        $data = $params['data'] ?? [];
        $format = $params['format'] ?? 'json';
        $filename = $params['filename'] ?? null;

        try {
            $result = $this->exportData($data, $format, $filename);
            return [
                'success' => true,
                'export' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeRunPipeline(array $params): array
    {
        $pipelineConfig = $params['pipeline_config'] ?? [];
        
        try {
            $result = $this->runPipeline($pipelineConfig);
            return [
                'success' => true,
                'pipeline_result' => $result
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
            'name' => 'AI Training Data Pipeline',
            'version' => '1.0.0',
            'description' => 'Collects, processes, and prepares data for AI model training',
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
            'data_directory' => $this->dataDirectory,
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Extract data from various sources
     * 
     * @param string $dataSource Data source type
     * @param int|null $tenantId Tenant ID filter
     * @param string|null $startDate Start date filter
     * @param string|null $endDate End date filter
     * @return array Extracted data
     */
    public function extractData($dataSource, $tenantId = null, $startDate = null, $endDate = null)
    {
        switch ($dataSource) {
            case 'orders':
                return $this->extractOrderData($tenantId, $startDate, $endDate);
            case 'customers':
                return $this->extractCustomerData($tenantId);
            case 'menu':
                return $this->extractMenuData($tenantId);
            case 'inventory':
                return $this->extractInventoryData($tenantId);
            case 'reservations':
                return $this->extractReservationData($tenantId, $startDate, $endDate);
            case 'loyalty':
                return $this->extractLoyaltyData($tenantId);
            case 'sales':
                return $this->extractSalesData($tenantId, $startDate, $endDate);
            default:
                throw new Exception("Unknown data source: {$dataSource}");
        }
    }

    /**
     * Extract order data for training
     */
    private function extractOrderData($tenantId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                o.order_id,
                o.tenant_id,
                o.branch_id,
                o.customer_id,
                o.order_type,
                o.order_status,
                o.total_amount,
                o.tax_amount,
                o.discount_amount,
                o.payment_method,
                o.created_at,
                o.completed_at,
                c.name as customer_name,
                c.email as customer_email,
                c.phone as customer_phone,
                b.branch_name
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.customer_id
            LEFT JOIN branches b ON o.branch_id = b.branch_id
            WHERE 1=1
        ";

        $params = [];
        if ($tenantId) {
            $sql .= " AND o.tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($startDate) {
            $sql .= " AND o.created_at >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND o.created_at <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY o.created_at DESC LIMIT " . $this->config['batch_size'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract customer data for training
     */
    private function extractCustomerData($tenantId)
    {
        $sql = "
            SELECT 
                c.customer_id,
                c.tenant_id,
                c.name,
                c.email,
                c.phone,
                c.date_of_birth,
                c.created_at,
                c.last_order_date,
                c.total_orders,
                c.total_spent,
                c.avg_order_value,
                lm.points_balance,
                lm.tier_level,
                lm.joined_at as loyalty_joined_at
            FROM customers c
            LEFT JOIN loyalty_members lm ON c.customer_id = lm.customer_id
            WHERE 1=1
        ";

        $params = [];
        if ($tenantId) {
            $sql .= " AND c.tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql .= " LIMIT " . $this->config['batch_size'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract menu data for training
     */
    private function extractMenuData($tenantId)
    {
        $sql = "
            SELECT 
                m.menu_item_id,
                m.tenant_id,
                m.branch_id,
                m.item_name,
                m.category_id,
                m.price,
                m.cost,
                m.description,
                m.is_available,
                m.preparation_time,
                m.created_at,
                mc.category_name,
                COUNT(oi.order_item_id) as order_count,
                SUM(oi.quantity) as total_quantity_sold,
                AVG(oi.quantity) as avg_quantity_per_order
            FROM menu_items m
            LEFT JOIN menu_categories mc ON m.category_id = mc.category_id
            LEFT JOIN order_items oi ON m.menu_item_id = oi.menu_item_id
            WHERE 1=1
        ";

        $params = [];
        if ($tenantId) {
            $sql .= " AND m.tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql .= " GROUP BY m.menu_item_id LIMIT " . $this->config['batch_size'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract inventory data for training
     */
    private function extractInventoryData($tenantId)
    {
        $sql = "
            SELECT 
                ii.inventory_id,
                ii.tenant_id,
                ii.item_code,
                ii.item_name,
                ii.category_id,
                ii.unit_cost,
                ii.selling_price,
                ii.current_stock,
                ii.minimum_stock,
                ii.maximum_stock,
                ii.reorder_level,
                ii.expiration_date,
                ic.category_name,
                sb.quantity as branch_stock,
                sb.branch_id
            FROM inventory_items ii
            LEFT JOIN inventory_categories ic ON ii.category_id = ic.category_id
            LEFT JOIN stock_balances sb ON ii.inventory_id = sb.inventory_id
            WHERE 1=1
        ";

        $params = [];
        if ($tenantId) {
            $sql .= " AND ii.tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql .= " LIMIT " . $this->config['batch_size'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract reservation data for training
     */
    private function extractReservationData($tenantId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                r.reservation_id,
                r.tenant_id,
                r.branch_id,
                r.customer_id,
                r.reservation_date,
                r.reservation_time,
                r.party_size,
                r.status,
                r.table_id,
                r.special_requests,
                r.created_at,
                c.name as customer_name,
                rt.table_number
            FROM reservations r
            LEFT JOIN customers c ON r.customer_id = c.customer_id
            LEFT JOIN restaurant_tables rt ON r.table_id = rt.table_id
            WHERE 1=1
        ";

        $params = [];
        if ($tenantId) {
            $sql .= " AND r.tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($startDate) {
            $sql .= " AND r.reservation_date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND r.reservation_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY r.reservation_date DESC LIMIT " . $this->config['batch_size'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract loyalty data for training
     */
    private function extractLoyaltyData($tenantId)
    {
        $sql = "
            SELECT 
                lm.customer_id,
                lm.tenant_id,
                lm.points_balance,
                lm.tier_level,
                lm.joined_at,
                lm.last_activity_at,
                lt.transaction_id,
                lt.points_earned,
                lt.points_used,
                lt.transaction_type,
                lt.created_at as transaction_date,
                c.name as customer_name
            FROM loyalty_members lm
            LEFT JOIN loyalty_transactions lt ON lm.customer_id = lt.customer_id
            LEFT JOIN customers c ON lm.customer_id = c.customer_id
            WHERE 1=1
        ";

        $params = [];
        if ($tenantId) {
            $sql .= " AND lm.tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql .= " ORDER BY lt.created_at DESC LIMIT " . $this->config['batch_size'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract sales data for training
     */
    private function extractSalesData($tenantId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                DATE(o.created_at) as sale_date,
                o.tenant_id,
                o.branch_id,
                COUNT(DISTINCT o.order_id) as total_orders,
                SUM(o.total_amount) as total_revenue,
                SUM(o.tax_amount) as total_tax,
                AVG(o.total_amount) as avg_order_value,
                COUNT(DISTINCT o.customer_id) as unique_customers,
                b.branch_name
            FROM orders o
            LEFT JOIN branches b ON o.branch_id = b.branch_id
            WHERE o.status = 'COMPLETED'
        ";

        $params = [];
        if ($tenantId) {
            $sql .= " AND o.tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($startDate) {
            $sql .= " AND o.created_at >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND o.created_at <= ?";
            $params[] = $endDate;
        }

        $sql .= " GROUP BY DATE(o.created_at), o.tenant_id, o.branch_id ORDER BY sale_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Transform data for training
     * 
     * @param string $inputFile Input file path
     * @param string $transformType Type of transformation
     * @return array Transformed data
     */
    public function transformData($inputFile, $transformType = 'feature_engineering')
    {
        $data = $this->loadDataFile($inputFile);

        switch ($transformType) {
            case 'feature_engineering':
                return $this->performFeatureEngineering($data);
            case 'normalization':
                return $this->normalizeData($data);
            case 'encoding':
                return $this->encodeCategoricalData($data);
            case 'aggregation':
                return $this->aggregateData($data);
            default:
                throw new Exception("Unknown transform type: {$transformType}");
        }
    }

    /**
     * Load data from file
     */
    private function loadDataFile($inputFile)
    {
        if (!file_exists($inputFile)) {
            throw new Exception("Input file not found: {$inputFile}");
        }

        $extension = pathinfo($inputFile, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'json':
                return json_decode(file_get_contents($inputFile), true);
            case 'csv':
                return $this->parseCSV($inputFile);
            default:
                throw new Exception("Unsupported file format: {$extension}");
        }
    }

    /**
     * Parse CSV file
     */
    private function parseCSV($file)
    {
        $data = [];
        $handle = fopen($file, 'r');
        
        if ($handle) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($headers, $row);
            }
            
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * Perform feature engineering
     */
    private function performFeatureEngineering($data)
    {
        $transformed = [];

        foreach ($data as $record) {
            $features = $record;

            // Add derived features
            if (isset($record['total_amount']) && isset($record['total_orders'])) {
                $features['avg_order_value'] = $record['total_amount'] / max(1, $record['total_orders']);
            }

            if (isset($record['created_at'])) {
                $features['day_of_week'] = date('N', strtotime($record['created_at']));
                $features['hour_of_day'] = date('G', strtotime($record['created_at']));
                $features['is_weekend'] = in_array(date('N', strtotime($record['created_at'])), [6, 7]) ? 1 : 0;
            }

            if (isset($record['date_of_birth'])) {
                $features['age'] = $this->calculateAge($record['date_of_birth']);
            }

            $transformed[] = $features;
        }

        return $transformed;
    }

    /**
     * Calculate age from date of birth
     */
    private function calculateAge($dateOfBirth)
    {
        $dob = new DateTime($dateOfBirth);
        $now = new DateTime();
        return $now->diff($dob)->y;
    }

    /**
     * Normalize data
     */
    private function normalizeData($data)
    {
        if (empty($data)) {
            return $data;
        }

        // Get numeric columns
        $numericColumns = $this->getNumericColumns($data);

        // Calculate min/max for each numeric column
        $normalizationParams = [];
        foreach ($numericColumns as $column) {
            $values = array_column($data, $column);
            $normalizationParams[$column] = [
                'min' => min($values),
                'max' => max($values)
            ];
        }

        // Normalize values
        $normalized = [];
        foreach ($data as $record) {
            $normalizedRecord = $record;
            foreach ($numericColumns as $column) {
                if (isset($record[$column]) && $normalizationParams[$column]['max'] > $normalizationParams[$column]['min']) {
                    $normalizedRecord[$column . '_normalized'] = 
                        ($record[$column] - $normalizationParams[$column]['min']) / 
                        ($normalizationParams[$column]['max'] - $normalizationParams[$column]['min']);
                }
            }
            $normalized[] = $normalizedRecord;
        }

        return [
            'data' => $normalized,
            'normalization_params' => $normalizationParams
        ];
    }

    /**
     * Get numeric columns from data
     */
    private function getNumericColumns($data)
    {
        if (empty($data)) {
            return [];
        }

        $numericColumns = [];
        $firstRecord = $data[0];

        foreach ($firstRecord as $column => $value) {
            if (is_numeric($value)) {
                $numericColumns[] = $column;
            }
        }

        return $numericColumns;
    }

    /**
     * Encode categorical data
     */
    private function encodeCategoricalData($data)
    {
        if (empty($data)) {
            return $data;
        }

        // Get categorical columns
        $categoricalColumns = $this->getCategoricalColumns($data);

        // Create encoding maps
        $encodingMaps = [];
        foreach ($categoricalColumns as $column) {
            $uniqueValues = array_unique(array_column($data, $column));
            $encodingMaps[$column] = array_flip($uniqueValues);
        }

        // Apply encoding
        $encoded = [];
        foreach ($data as $record) {
            $encodedRecord = $record;
            foreach ($categoricalColumns as $column) {
                if (isset($record[$column]) && isset($encodingMaps[$column][$record[$column]])) {
                    $encodedRecord[$column . '_encoded'] = $encodingMaps[$column][$record[$column]];
                }
            }
            $encoded[] = $encodedRecord;
        }

        return [
            'data' => $encoded,
            'encoding_maps' => $encodingMaps
        ];
    }

    /**
     * Get categorical columns from data
     */
    private function getCategoricalColumns($data)
    {
        if (empty($data)) {
            return [];
        }

        $categoricalColumns = [];
        $firstRecord = $data[0];

        foreach ($firstRecord as $column => $value) {
            if (!is_numeric($value) && is_string($value)) {
                $categoricalColumns[] = $column;
            }
        }

        return $categoricalColumns;
    }

    /**
     * Aggregate data
     */
    private function aggregateData($data)
    {
        if (empty($data)) {
            return $data;
        }

        // Group by tenant_id if available
        $grouped = [];
        foreach ($data as $record) {
            $groupBy = $record['tenant_id'] ?? 'all';
            if (!isset($grouped[$groupBy])) {
                $grouped[$groupBy] = [];
            }
            $grouped[$groupBy][] = $record;
        }

        // Calculate aggregates
        $aggregated = [];
        foreach ($grouped as $groupId => $records) {
            $aggregate = [
                'group_id' => $groupId,
                'count' => count($records)
            ];

            // Sum numeric columns
            $numericColumns = $this->getNumericColumns($records);
            foreach ($numericColumns as $column) {
                $aggregate[$column . '_sum'] = array_sum(array_column($records, $column));
                $aggregate[$column . '_avg'] = array_sum(array_column($records, $column)) / count($records);
                $aggregate[$column . '_min'] = min(array_column($records, $column));
                $aggregate[$column . '_max'] = max(array_column($records, $column));
            }

            $aggregated[] = $aggregate;
        }

        return $aggregated;
    }

    /**
     * Validate data quality
     * 
     * @param string $dataFile Data file path
     * @return array Validation results
     */
    public function validateData($dataFile)
    {
        $data = $this->loadDataFile($dataFile);

        $validationResults = [
            'total_records' => count($data),
            'missing_values' => [],
            'duplicate_records' => [],
            'outliers' => [],
            'data_types' => [],
            'quality_score' => 0
        ];

        if (empty($data)) {
            return $validationResults;
        }

        // Check for missing values
        $missingValues = $this->checkMissingValues($data);
        $validationResults['missing_values'] = $missingValues;

        // Check for duplicates
        $duplicates = $this->checkDuplicates($data);
        $validationResults['duplicate_records'] = $duplicates;

        // Check for outliers
        $outliers = $this->checkOutliers($data);
        $validationResults['outliers'] = $outliers;

        // Check data types
        $dataTypes = $this->checkDataTypes($data);
        $validationResults['data_types'] = $dataTypes;

        // Calculate quality score
        $qualityScore = $this->calculateQualityScore($missingValues, $duplicates, $outliers);
        $validationResults['quality_score'] = $qualityScore;

        return $validationResults;
    }

    /**
     * Check for missing values
     */
    private function checkMissingValues($data)
    {
        $missing = [];
        $columns = array_keys($data[0]);

        foreach ($columns as $column) {
            $missingCount = 0;
            foreach ($data as $record) {
                if (!isset($record[$column]) || $record[$column] === '' || $record[$column] === null) {
                    $missingCount++;
                }
            }

            if ($missingCount > 0) {
                $missing[$column] = [
                    'count' => $missingCount,
                    'percentage' => ($missingCount / count($data)) * 100
                ];
            }
        }

        return $missing;
    }

    /**
     * Check for duplicate records
     */
    private function checkDuplicates($data)
    {
        $serialized = array_map('json_encode', $data);
        $counts = array_count_values($serialized);
        
        $duplicates = [];
        foreach ($counts as $key => $count) {
            if ($count > 1) {
                $duplicates[] = json_decode($key, true);
            }
        }

        return [
            'count' => count($duplicates),
            'percentage' => (count($duplicates) / count($data)) * 100
        ];
    }

    /**
     * Check for outliers using IQR method
     */
    private function checkOutliers($data)
    {
        $outliers = [];
        $numericColumns = $this->getNumericColumns($data);

        foreach ($numericColumns as $column) {
            $values = array_column($data, $column);
            sort($values);

            $q1 = $values[count($values) * 0.25];
            $q3 = $values[count($values) * 0.75];
            $iqr = $q3 - $q1;

            $lowerBound = $q1 - (1.5 * $iqr);
            $upperBound = $q3 + (1.5 * $iqr);

            $columnOutliers = [];
            foreach ($values as $index => $value) {
                if ($value < $lowerBound || $value > $upperBound) {
                    $columnOutliers[] = [
                        'index' => $index,
                        'value' => $value
                    ];
                }
            }

            if (!empty($columnOutliers)) {
                $outliers[$column] = $columnOutliers;
            }
        }

        return $outliers;
    }

    /**
     * Check data types
     */
    private function checkDataTypes($data)
    {
        $types = [];
        $columns = array_keys($data[0]);

        foreach ($columns as $column) {
            $sampleValue = $data[0][$column] ?? null;
            
            if (is_numeric($sampleValue)) {
                $types[$column] = 'numeric';
            } elseif (is_bool($sampleValue)) {
                $types[$column] = 'boolean';
            } elseif (is_string($sampleValue)) {
                if (strtotime($sampleValue) !== false) {
                    $types[$column] = 'datetime';
                } else {
                    $types[$column] = 'string';
                }
            } else {
                $types[$column] = 'mixed';
            }
        }

        return $types;
    }

    /**
     * Calculate data quality score
     */
    private function calculateQualityScore($missingValues, $duplicates, $outliers)
    {
        $score = 100;

        // Deduct for missing values
        $missingPenalty = 0;
        foreach ($missingValues as $column => $info) {
            $missingPenalty += $info['percentage'];
        }
        $score -= min($missingPenalty, 30);

        // Deduct for duplicates
        $duplicatePenalty = $duplicates['percentage'] ?? 0;
        $score -= min($duplicatePenalty, 20);

        // Deduct for outliers
        $outlierCount = array_sum(array_map('count', $outliers));
        $outlierPenalty = min($outlierCount / 10, 20);
        $score -= $outlierPenalty;

        return max(0, round($score, 2));
    }

    /**
     * Export data to file
     * 
     * @param array $data Data to export
     * @param string $format Export format
     * @param string|null $filename Output filename
     * @return array Export result
     */
    public function exportData($data, $format = 'json', $filename = null)
    {
        if (!$filename) {
            $filename = $this->dataDirectory . 'export_' . date('YmdHis') . '.' . $format;
        }

        switch ($format) {
            case 'json':
                $result = file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
                break;
            case 'csv':
                $result = $this->exportCSV($filename, $data);
                break;
            default:
                throw new Exception("Unsupported export format: {$format}");
        }

        if ($result === false) {
            throw new Exception("Failed to export data to {$filename}");
        }

        return [
            'success' => true,
            'filename' => $filename,
            'records' => count($data),
            'format' => $format,
            'size' => filesize($filename)
        ];
    }

    /**
     * Export data to CSV
     */
    private function exportCSV($filename, $data)
    {
        if (empty($data)) {
            return false;
        }

        $handle = fopen($filename, 'w');
        if (!$handle) {
            return false;
        }

        // Write header
        fputcsv($handle, array_keys($data[0]));

        // Write data
        foreach ($data as $record) {
            fputcsv($handle, $record);
        }

        fclose($handle);
        return true;
    }

    /**
     * Run complete pipeline
     * 
     * @param array $config Pipeline configuration
     * @return array Pipeline results
     */
    public function runPipeline($config)
    {
        $results = [
            'pipeline_id' => 'pipeline_' . time(),
            'started_at' => date('Y-m-d H:i:s'),
            'steps' => []
        ];

        try {
            // Step 1: Extract
            $extractResult = $this->extractData(
                $config['data_source'],
                $config['tenant_id'] ?? null,
                $config['start_date'] ?? null,
                $config['end_date'] ?? null
            );
            $results['steps']['extract'] = [
                'status' => 'completed',
                'records' => count($extractResult)
            ];

            // Step 2: Transform
            $transformResult = $this->transformData(
                $extractResult,
                $config['transform_type'] ?? 'feature_engineering'
            );
            $results['steps']['transform'] = [
                'status' => 'completed',
                'records' => count($transformResult)
            ];

            // Step 3: Validate
            $validationResult = $this->validateData($transformResult);
            $results['steps']['validate'] = [
                'status' => 'completed',
                'quality_score' => $validationResult['quality_score']
            ];

            // Step 4: Export
            $exportResult = $this->exportData(
                $transformResult,
                $config['export_format'] ?? 'json',
                $config['export_filename'] ?? null
            );
            $results['steps']['export'] = [
                'status' => 'completed',
                'filename' => $exportResult['filename'],
                'size' => $exportResult['size']
            ];

            $results['status'] = 'completed';
            $results['completed_at'] = date('Y-m-d H:i:s');
            $results['success'] = true;

        } catch (Exception $e) {
            $results['status'] = 'failed';
            $results['error'] = $e->getMessage();
            $results['completed_at'] = date('Y-m-d H:i:s');
            $results['success'] = false;
        }

        // Log pipeline execution
        $this->logPipelineExecution($results);

        return $results;
    }

    /**
     * Log pipeline execution
     */
    private function logPipelineExecution($results)
    {
        $logFile = $this->dataDirectory . 'pipeline_log.json';
        $logs = [];

        if (file_exists($logFile)) {
            $logs = json_decode(file_get_contents($logFile), true) ?? [];
        }

        $logs[] = $results;
        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
    }

    /**
     * Get pipeline statistics
     * 
     * @return array Pipeline statistics
     */
    public function getPipelineStatistics()
    {
        $logFile = $this->dataDirectory . 'pipeline_log.json';

        if (!file_exists($logFile)) {
            return [
                'total_runs' => 0,
                'successful_runs' => 0,
                'failed_runs' => 0,
                'average_quality_score' => 0
            ];
        }

        $logs = json_decode(file_get_contents($logFile), true);

        $totalRuns = count($logs);
        $successfulRuns = count(array_filter($logs, fn($log) => $log['success'] ?? false));
        $failedRuns = $totalRuns - $successfulRuns;

        $qualityScores = array_filter(array_map(fn($log) => $log['steps']['validate']['quality_score'] ?? 0, $logs));
        $averageQualityScore = !empty($qualityScores) ? array_sum($qualityScores) / count($qualityScores) : 0;

        return [
            'total_runs' => $totalRuns,
            'successful_runs' => $successfulRuns,
            'failed_runs' => $failedRuns,
            'average_quality_score' => round($averageQualityScore, 2),
            'last_run' => end($logs)
        ];
    }
}
