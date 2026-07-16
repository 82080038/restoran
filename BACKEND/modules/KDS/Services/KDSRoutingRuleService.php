<?php

namespace App\Modules\KDS\Services;

use App\Core\Database;
use PDO;

class KDSRoutingRuleService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getRoutingRules($tenantId, $branchId)
    {
        $sql = "SELECT r.*, st.station_name as target_station_name, st2.station_name as also_station_name 
                FROM kds_routing_rules r 
                LEFT JOIN kitchen_stations st ON r.target_station_id = st.station_id 
                LEFT JOIN kitchen_stations st2 ON r.also_send_to_station_id = st2.station_id 
                WHERE r.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND r.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY r.priority ASC, r.rule_id ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRoutingRule($ruleId, $tenantId)
    {
        $sql = "SELECT r.*, st.station_name as target_station_name, st2.station_name as also_station_name 
                FROM kds_routing_rules r 
                LEFT JOIN kitchen_stations st ON r.target_station_id = st.station_id 
                LEFT JOIN kitchen_stations st2 ON r.also_send_to_station_id = st2.station_id 
                WHERE r.rule_id = :rule_id AND r.tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':rule_id' => $ruleId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    public function createRoutingRule($data)
    {
        $sql = "INSERT INTO kds_routing_rules (tenant_id, branch_id, rule_name, rule_type, condition_value, target_station_id, priority, is_reroute, also_send_to_station_id, apply_to_takeout, apply_to_delivery, apply_to_dinein, is_active) 
                VALUES (:tenant_id, :branch_id, :rule_name, :rule_type, :condition_value, :target_station_id, :priority, :is_reroute, :also_send_to_station_id, :apply_to_takeout, :apply_to_delivery, :apply_to_dinein, :is_active)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':rule_name' => $data['rule_name'],
            ':rule_type' => $data['rule_type'] ?? 'MENU_CATEGORY',
            ':condition_value' => $data['condition_value'] ?? null,
            ':target_station_id' => $data['target_station_id'],
            ':priority' => $data['priority'] ?? 0,
            ':is_reroute' => $data['is_reroute'] ?? 0,
            ':also_send_to_station_id' => $data['also_send_to_station_id'] ?? null,
            ':apply_to_takeout' => $data['apply_to_takeout'] ?? 0,
            ':apply_to_delivery' => $data['apply_to_delivery'] ?? 0,
            ':apply_to_dinein' => $data['apply_to_dinein'] ?? 1,
            ':is_active' => $data['is_active'] ?? 1
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateRoutingRule($ruleId, $tenantId, $data)
    {
        $sql = "UPDATE kds_routing_rules SET rule_name = :rule_name, rule_type = :rule_type, 
                condition_value = :condition_value, target_station_id = :target_station_id, 
                priority = :priority, is_reroute = :is_reroute, also_send_to_station_id = :also_send_to_station_id, 
                apply_to_takeout = :apply_to_takeout, apply_to_delivery = :apply_to_delivery, 
                apply_to_dinein = :apply_to_dinein, is_active = :is_active 
                WHERE rule_id = :rule_id AND tenant_id = :tenant_id";
        
        $params = [
            ':rule_name' => $data['rule_name'],
            ':rule_type' => $data['rule_type'],
            ':condition_value' => $data['condition_value'],
            ':target_station_id' => $data['target_station_id'],
            ':priority' => $data['priority'],
            ':is_reroute' => $data['is_reroute'],
            ':also_send_to_station_id' => $data['also_send_to_station_id'],
            ':apply_to_takeout' => $data['apply_to_takeout'],
            ':apply_to_delivery' => $data['apply_to_delivery'],
            ':apply_to_dinein' => $data['apply_to_dinein'],
            ':is_active' => $data['is_active'],
            ':rule_id' => $ruleId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteRoutingRule($ruleId, $tenantId)
    {
        $sql = "DELETE FROM kds_routing_rules WHERE rule_id = :rule_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':rule_id' => $ruleId, ':tenant_id' => $tenantId]);
    }

    public function applyRoutingRules($orderId, $diningOption)
    {
        // Get order details
        $sql = "SELECT o.*, b.branch_id FROM orders o LEFT JOIN branches b ON o.branch_id = b.branch_id WHERE o.order_id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        $order = $stmt->fetch();
        
        if (!$order) return [];
        
        // Get applicable routing rules
        $sql = "SELECT * FROM kds_routing_rules 
                WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND is_active = 1
                AND (apply_to_dinein = 1 OR apply_to_takeout = 1 OR apply_to_delivery = 1)
                ORDER BY priority ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $order['tenant_id'], ':branch_id' => $order['branch_id']]);
        $rules = $stmt->fetchAll();
        
        $appliedRules = [];
        foreach ($rules as $rule) {
            $apply = false;
            
            // Check dining option
            if ($diningOption === 'DINE_IN' && $rule['apply_to_dinein']) $apply = true;
            if ($diningOption === 'TAKE_OUT' && $rule['apply_to_takeout']) $apply = true;
            if ($diningOption === 'DELIVERY' && $rule['apply_to_delivery']) $apply = true;
            
            if ($apply) {
                $appliedRules[] = [
                    'rule_id' => $rule['rule_id'],
                    'target_station_id' => $rule['target_station_id'],
                    'also_send_to_station_id' => $rule['also_send_to_station_id'],
                    'is_reroute' => $rule['is_reroute']
                ];
            }
        }
        
        return $appliedRules;
    }
}
