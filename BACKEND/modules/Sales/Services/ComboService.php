<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

use PDO;

class ComboService
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
     * Create new combo
     */
    public function createCombo($input, $userId, $tenantId)
    {
        try {
            $this->db->beginTransaction();

            // Insert combo
            $sql = "INSERT INTO menu_combos (tenant_id, combo_code, combo_name, description, combo_price, 
                    discount_amount, is_active, start_date, end_date, image_url, display_order, created_by)
                    VALUES (:tenant_id, :combo_code, :combo_name, :description, :combo_price, 
                    :discount_amount, :is_active, :start_date, :end_date, :image_url, :display_order, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $tenantId,
                ':combo_code' => $input['combo_code'],
                ':combo_name' => $input['combo_name'],
                ':description' => $input['description'] ?? null,
                ':combo_price' => $input['combo_price'],
                ':discount_amount' => $input['discount_amount'] ?? 0,
                ':is_active' => $input['is_active'] ?? true,
                ':start_date' => $input['start_date'] ?? null,
                ':end_date' => $input['end_date'] ?? null,
                ':image_url' => $input['image_url'] ?? null,
                ':display_order' => $input['display_order'] ?? 0,
                ':created_by' => $userId
            ]);

            $comboId = $this->db->lastInsertId();

            // Insert combo items
            if (!empty($input['items'])) {
                $itemSql = "INSERT INTO menu_combo_items (combo_id, menu_id, quantity, is_required, max_quantity, min_quantity)
                           VALUES (:combo_id, :menu_id, :quantity, :is_required, :max_quantity, :min_quantity)";
                $itemStmt = $this->db->prepare($itemSql);

                foreach ($input['items'] as $item) {
                    $itemStmt->execute([
                        ':combo_id' => $comboId,
                        ':menu_id' => $item['menu_id'],
                        ':quantity' => $item['quantity'] ?? 1,
                        ':is_required' => $item['is_required'] ?? true,
                        ':max_quantity' => $item['max_quantity'] ?? 1,
                        ':min_quantity' => $item['min_quantity'] ?? 1
                    ]);
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Combo created successfully',
                'data' => ['combo_id' => $comboId]
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create combo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all combos for tenant
     */
    public function getCombos($tenantId, $isActive = null)
    {
        try {
            $sql = "SELECT mc.*, 
                   (SELECT GROUP_CONCAT(CONCAT(m.menu_name, ' (', mci.quantity, ')') SEPARATOR ', ')
                    FROM menu_combo_items mci
                    JOIN products m ON mci.menu_id = m.product_id
                    WHERE mci.combo_id = mc.combo_id) as items_summary
                   FROM menu_combos mc
                   WHERE mc.tenant_id = :tenant_id";
            
            $params = [':tenant_id' => $tenantId];

            if ($isActive !== null) {
                $sql .= " AND mc.is_active = :is_active";
                $params[':is_active'] = $isActive ? 1 : 0;
            }

            $sql .= " AND (mc.deleted_at IS NULL) 
                     ORDER BY mc.display_order ASC, mc.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get detailed items for each combo
            foreach ($combos as &$combo) {
                $combo['items'] = $this->getComboItems($combo['combo_id']);
                unset($combo['items_summary']);
            }

            return [
                'success' => true,
                'data' => $combos
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get combos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get specific combo
     */
    public function getCombo($comboId, $tenantId)
    {
        try {
            $sql = "SELECT * FROM menu_combos 
                   WHERE combo_id = :combo_id AND tenant_id = :tenant_id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':combo_id' => $comboId,
                ':tenant_id' => $tenantId
            ]);
            $combo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$combo) {
                return [
                    'success' => false,
                    'message' => 'Combo not found'
                ];
            }

            $combo['items'] = $this->getComboItems($comboId);

            return [
                'success' => true,
                'data' => $combo
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get combo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get combo items
     */
    private function getComboItems($comboId)
    {
        try {
            $sql = "SELECT mci.*, p.name as menu_name, p.price as menu_price 
                   FROM menu_combo_items mci
                   JOIN products p ON mci.menu_id = p.product_id
                   WHERE mci.combo_id = :combo_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':combo_id' => $comboId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update combo
     */
    public function updateCombo($comboId, $input, $tenantId)
    {
        try {
            $this->db->beginTransaction();

            // Update combo
            $sql = "UPDATE menu_combos 
                   SET combo_code = :combo_code, combo_name = :combo_name, 
                       description = :description, combo_price = :combo_price,
                       discount_amount = :discount_amount, is_active = :is_active,
                       start_date = :start_date, end_date = :end_date,
                       image_url = :image_url, display_order = :display_order,
                       updated_at = CURRENT_TIMESTAMP
                   WHERE combo_id = :combo_id AND tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':combo_code' => $input['combo_code'],
                ':combo_name' => $input['combo_name'],
                ':description' => $input['description'] ?? null,
                ':combo_price' => $input['combo_price'],
                ':discount_amount' => $input['discount_amount'] ?? 0,
                ':is_active' => $input['is_active'] ?? true,
                ':start_date' => $input['start_date'] ?? null,
                ':end_date' => $input['end_date'] ?? null,
                ':image_url' => $input['image_url'] ?? null,
                ':display_order' => $input['display_order'] ?? 0,
                ':combo_id' => $comboId,
                ':tenant_id' => $tenantId
            ]);

            // Update combo items
            if (!empty($input['items'])) {
                // Delete existing items
                $this->db->prepare("DELETE FROM menu_combo_items WHERE combo_id = :combo_id")
                          ->execute([':combo_id' => $comboId]);

                // Insert new items
                $itemSql = "INSERT INTO menu_combo_items (combo_id, menu_id, quantity, is_required, max_quantity, min_quantity)
                           VALUES (:combo_id, :menu_id, :quantity, :is_required, :max_quantity, :min_quantity)";
                $itemStmt = $this->db->prepare($itemSql);

                foreach ($input['items'] as $item) {
                    $itemStmt->execute([
                        ':combo_id' => $comboId,
                        ':menu_id' => $item['menu_id'],
                        ':quantity' => $item['quantity'] ?? 1,
                        ':is_required' => $item['is_required'] ?? true,
                        ':max_quantity' => $item['max_quantity'] ?? 1,
                        ':min_quantity' => $item['min_quantity'] ?? 1
                    ]);
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Combo updated successfully',
                'data' => ['combo_id' => $comboId]
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update combo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete combo (soft delete)
     */
    public function deleteCombo($comboId, $tenantId)
    {
        try {
            $sql = "UPDATE menu_combos 
                   SET deleted_at = CURRENT_TIMESTAMP 
                   WHERE combo_id = :combo_id AND tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':combo_id' => $comboId,
                ':tenant_id' => $tenantId
            ]);

            return [
                'success' => true,
                'message' => 'Combo deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete combo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate combo price for order
     */
    public function calculateComboPrice($comboId, $quantities, $tenantId)
    {
        try {
            // Get combo details
            $combo = $this->getCombo($comboId, $tenantId);
            if (!$combo['success']) {
                return $combo;
            }

            $comboData = $combo['data'];
            $totalRegularPrice = 0;
            $itemDetails = [];

            // Calculate regular price of individual items
            foreach ($comboData['items'] as $item) {
                $quantity = $quantities[$item['menu_id']] ?? $item['quantity'];
                $itemTotal = $item['menu_price'] * $quantity;
                $totalRegularPrice += $itemTotal;

                $itemDetails[] = [
                    'menu_id' => $item['menu_id'],
                    'menu_name' => $item['menu_name'],
                    'quantity' => $quantity,
                    'unit_price' => $item['menu_price'],
                    'subtotal' => $itemTotal
                ];
            }

            $comboPrice = $comboData['combo_price'];
            $discountAmount = $totalRegularPrice - $comboPrice;
            $discountPercentage = $totalRegularPrice > 0 ? ($discountAmount / $totalRegularPrice) * 100 : 0;

            return [
                'success' => true,
                'data' => [
                    'combo_id' => $comboId,
                    'combo_name' => $comboData['combo_name'],
                    'combo_price' => $comboPrice,
                    'regular_price' => $totalRegularPrice,
                    'discount_amount' => $discountAmount,
                    'discount_percentage' => round($discountPercentage, 2),
                    'items' => $itemDetails
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate combo price: ' . $e->getMessage()
            ];
        }
    }
}
