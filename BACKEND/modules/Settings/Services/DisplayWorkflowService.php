<?php

/**
 * Display Workflow Service
 * 
 * Manages tenant-specific display workflow configurations
 * for different restaurant styles (Padang, buffet, display-based, etc.)
 * 
 * @package EBP\Modules\Settings\Services
 * @version 1.0.0
 */

class DisplayWorkflowService
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Create a new display workflow configuration
     */
    public function createWorkflowConfig($data, $tenantId, $userId)
    {
        try {
            if (empty($data['config_name']) || empty($data['workflow_type'])) {
                return [
                    'success' => false,
                    'message' => 'Config name and workflow type are required'
                ];
            }

            $sql = "INSERT INTO display_workflow_configurations 
                    (tenant_id, branch_id, workflow_type, config_name, display_mode, 
                     show_out_of_stock, show_low_stock, auto_hide_out_of_stock, 
                     display_categories, display_order, price_display_mode, 
                     allow_customer_selection, require_table_assignment, 
                     kitchen_notification_mode, serving_mode, is_active, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $data['branch_id'] ?? null,
                $data['workflow_type'],
                $data['config_name'],
                $data['display_mode'] ?? 'INDIVIDUAL_ITEMS',
                $data['show_out_of_stock'] ?? 0,
                $data['show_low_stock'] ?? 1,
                $data['auto_hide_out_of_stock'] ?? 0,
                isset($data['display_categories']) ? json_encode($data['display_categories']) : null,
                isset($data['display_order']) ? json_encode($data['display_order']) : null,
                $data['price_display_mode'] ?? 'SHOW_ALL',
                $data['allow_customer_selection'] ?? 1,
                $data['require_table_assignment'] ?? 0,
                $data['kitchen_notification_mode'] ?? 'AUTO',
                $data['serving_mode'] ?? 'STAFF_SERVE',
                $data['is_active'] ?? 1,
                $userId
            ]);

            $configId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Display workflow configuration created successfully',
                'config_id' => $configId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create workflow configuration: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update a display workflow configuration
     */
    public function updateWorkflowConfig($configId, $data, $tenantId, $userId)
    {
        try {
            $config = $this->getWorkflowConfig($configId, $tenantId);
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Configuration not found'
                ];
            }

            $updateFields = [];
            $params = [];

            $allowedFields = [
                'branch_id', 'workflow_type', 'config_name', 'display_mode',
                'show_out_of_stock', 'show_low_stock', 'auto_hide_out_of_stock',
                'price_display_mode', 'allow_customer_selection', 'require_table_assignment',
                'kitchen_notification_mode', 'serving_mode', 'is_active'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            if (isset($data['display_categories'])) {
                $updateFields[] = "display_categories = ?";
                $params[] = json_encode($data['display_categories']);
            }

            if (isset($data['display_order'])) {
                $updateFields[] = "display_order = ?";
                $params[] = json_encode($data['display_order']);
            }

            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }

            $updateFields[] = "updated_by = ?";
            $params[] = $userId;
            $params[] = $configId;

            $sql = "UPDATE display_workflow_configurations SET " . implode(', ', $updateFields) . " WHERE config_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Display workflow configuration updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update workflow configuration: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get a specific workflow configuration
     */
    public function getWorkflowConfig($configId, $tenantId)
    {
        try {
            $sql = "SELECT * FROM display_workflow_configurations 
                    WHERE config_id = ? AND tenant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$configId, $tenantId]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($config) {
                // Decode JSON fields
                $config['display_categories'] = $config['display_categories'] ? json_decode($config['display_categories'], true) : null;
                $config['display_order'] = $config['display_order'] ? json_decode($config['display_order'], true) : null;
            }

            return $config;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all workflow configurations for a tenant
     */
    public function getWorkflowConfigs($tenantId, $branchId = null, $isActive = null)
    {
        try {
            $sql = "SELECT * FROM display_workflow_configurations WHERE tenant_id = ?";
            $params = [$tenantId];

            if ($branchId !== null) {
                $sql .= " AND (branch_id = ? OR branch_id IS NULL)";
                $params[] = $branchId;
            }

            if ($isActive !== null) {
                $sql .= " AND is_active = ?";
                $params[] = $isActive;
            }

            $sql .= " ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Decode JSON fields
            foreach ($configs as &$config) {
                $config['display_categories'] = $config['display_categories'] ? json_decode($config['display_categories'], true) : null;
                $config['display_order'] = $config['display_order'] ? json_decode($config['display_order'], true) : null;
            }

            return [
                'success' => true,
                'message' => 'Workflow configurations retrieved successfully',
                'data' => $configs
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get workflow configurations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get active workflow configuration for a branch
     */
    public function getActiveWorkflowConfig($tenantId, $branchId)
    {
        try {
            // First check if branch has specific config
            $sql = "SELECT dwc.* FROM display_workflow_configurations dwc 
                    JOIN branches b ON dwc.config_id = b.display_workflow_config_id 
                    WHERE dwc.tenant_id = ? AND b.branch_id = ? AND dwc.is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($config) {
                $config['display_categories'] = $config['display_categories'] ? json_decode($config['display_categories'], true) : null;
                $config['display_order'] = $config['display_order'] ? json_decode($config['display_order'], true) : null;
                return $config;
            }

            // Fallback to tenant's standard config
            $sql = "SELECT * FROM display_workflow_configurations 
                    WHERE tenant_id = ? AND workflow_type = 'STANDARD' AND is_active = 1 
                    AND (branch_id IS NULL OR branch_id = ?)
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($config) {
                $config['display_categories'] = $config['display_categories'] ? json_decode($config['display_categories'], true) : null;
                $config['display_order'] = $config['display_order'] ? json_decode($config['display_order'], true) : null;
            }

            return $config;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Assign workflow configuration to a branch
     */
    public function assignToBranch($configId, $branchId, $tenantId)
    {
        try {
            $config = $this->getWorkflowConfig($configId, $tenantId);
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Configuration not found'
                ];
            }

            $sql = "UPDATE branches SET display_workflow_config_id = ? WHERE branch_id = ? AND tenant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$configId, $branchId, $tenantId]);

            return [
                'success' => true,
                'message' => 'Workflow configuration assigned to branch successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to assign configuration to branch: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a workflow configuration
     */
    public function deleteWorkflowConfig($configId, $tenantId)
    {
        try {
            $config = $this->getWorkflowConfig($configId, $tenantId);
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Configuration not found'
                ];
            }

            $sql = "DELETE FROM display_workflow_configurations WHERE config_id = ? AND tenant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$configId, $tenantId]);

            // Remove assignment from branches
            $sql = "UPDATE branches SET display_workflow_config_id = NULL WHERE display_workflow_config_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$configId]);

            return [
                'success' => true,
                'message' => 'Workflow configuration deleted successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete workflow configuration: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get available workflow types
     */
    public function getWorkflowTypes()
    {
        return [
            'STANDARD' => 'Standard POS Workflow',
            'PADANG_DISPLAY' => 'Padang Style Display',
            'BUFFET' => 'Buffet Self-Service',
            'CAFETERIA' => 'Cafeteria Style',
            'FOOD_COURT' => 'Food Court',
            'COUNTER_SERVICE' => 'Counter Service',
            'TABLE_SERVICE' => 'Full Table Service',
            'SELF_SERVICE' => 'Self Service'
        ];
    }

    /**
     * Get available display modes
     */
    public function getDisplayModes()
    {
        return [
            'INDIVIDUAL_ITEMS' => 'Individual Items',
            'GROUPED_DISPLAY' => 'Grouped Display',
            'COMBO_DISPLAY' => 'Combo Display',
            'CATEGORY_DISPLAY' => 'Category Display',
            'PRICE_BASED_DISPLAY' => 'Price Based Display'
        ];
    }

    /**
     * Get available price display modes
     */
    public function getPriceDisplayModes()
    {
        return [
            'SHOW_ALL' => 'Show All Prices',
            'HIDE_PRICES' => 'Hide Prices',
            'SHOW_ON_REQUEST' => 'Show on Request',
            'SHOW_RANGE' => 'Show Price Range'
        ];
    }
}
