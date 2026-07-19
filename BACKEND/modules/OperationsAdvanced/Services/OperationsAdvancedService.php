<?php

namespace App\Modules\OperationsAdvanced\Services;

use App\Core\Database;
use PDO;

class OperationsAdvancedService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== 86-ING ====================

    public function set86Status($tenantId, $branchId, $productId, $reason, $userId, $expectedRestockDate = null)
    {
        $sql = "INSERT INTO item_86_status (tenant_id, branch_id, product_id, is_86ed, reason, 86ed_by, 86ed_at, expected_restock_date)
                VALUES (:tenant_id, :branch_id, :product_id, 1, :reason, :user, NOW(), :restock)
                ON DUPLICATE KEY UPDATE is_86ed = 1, reason = :reason2, 86ed_by = :user2, 86ed_at = NOW(), expected_restock_date = :restock2";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId, ':branch_id' => $branchId, ':product_id' => $productId,
            ':reason' => $reason, ':user' => $userId, ':restock' => $expectedRestockDate,
            ':reason2' => $reason, ':user2' => $userId, ':restock2' => $expectedRestockDate,
        ]);
        return ['success' => true];
    }

    public function restockItem($tenantId, $branchId, $productId, $userId)
    {
        $sql = "UPDATE item_86_status SET is_86ed = 0, restocked_by = :user, restocked_at = NOW(), reason = NULL WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId, ':product_id' => $productId, ':user' => $userId]);
        return ['success' => true];
    }

    public function get86Items($tenantId, $branchId)
    {
        $sql = "SELECT i86.*, p.name as product_name FROM item_86_status i86
                LEFT JOIN products p ON i86.product_id = p.product_id
                WHERE i86.tenant_id = :tenant_id AND i86.is_86ed = 1";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND i86.branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isItem86ed($tenantId, $branchId, $productId)
    {
        $sql = "SELECT is_86ed FROM item_86_status WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId, ':product_id' => $productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (bool)$result['is_86ed'] : false;
    }

    // ==================== CUSTOM ORDERS ====================

    public function createCustomOrder($data)
    {
        $orderNumber = 'CO-' . date('Ymd') . '-' . substr(uniqid(), -4);
        $total = ($data['unit_price'] ?? 0) * ($data['quantity'] ?? 1) + ($data['delivery_fee'] ?? 0);
        $sql = "INSERT INTO custom_orders (tenant_id, branch_id, order_number, customer_name, customer_phone, customer_email, product_name, product_description, specifications, quantity, unit_price, total_price, deposit_required, pickup_date, pickup_time, delivery_address, delivery_fee, fulfillment_type, notes)
                VALUES (:tenant_id, :branch_id, :order_number, :name, :phone, :email, :product_name, :desc, :specs, :qty, :price, :total, :deposit, :pickup_date, :pickup_time, :addr, :delivery_fee, :fulfillment, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'],
            ':order_number' => $orderNumber, ':name' => $data['customer_name'],
            ':phone' => $data['customer_phone'] ?? null, ':email' => $data['customer_email'] ?? null,
            ':product_name' => $data['product_name'], ':desc' => $data['product_description'] ?? null,
            ':specs' => isset($data['specifications']) ? json_encode($data['specifications']) : null,
            ':qty' => $data['quantity'] ?? 1, ':price' => $data['unit_price'] ?? 0,
            ':total' => $total, ':deposit' => $data['deposit_required'] ?? 0,
            ':pickup_date' => $data['pickup_date'] ?? null, ':pickup_time' => $data['pickup_time'] ?? null,
            ':addr' => $data['delivery_address'] ?? null, ':delivery_fee' => $data['delivery_fee'] ?? 0,
            ':fulfillment' => $data['fulfillment_type'] ?? 'PICKUP', ':notes' => $data['notes'] ?? null,
        ]);
        return ['custom_order_id' => $this->pdo->lastInsertId(), 'order_number' => $orderNumber, 'total_price' => $total];
    }

    public function getCustomOrders($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM custom_orders WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY pickup_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateCustomOrderStatus($orderId, $status)
    {
        $sql = "UPDATE custom_orders SET status = :status WHERE custom_order_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $orderId, ':status' => $status]);
        return ['success' => true];
    }

    // ==================== DELIVERY ROUTING ====================

    public function createRoute($data)
    {
        $sql = "INSERT INTO delivery_routes (tenant_id, branch_id, route_date, driver_name, driver_phone, vehicle, total_stops, estimated_duration_minutes)
                VALUES (:tenant_id, :branch_id, :route_date, :driver, :phone, :vehicle, :stops, :duration)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'],
            ':route_date' => $data['route_date'], ':driver' => $data['driver_name'] ?? null,
            ':phone' => $data['driver_phone'] ?? null, ':vehicle' => $data['vehicle'] ?? null,
            ':stops' => $data['total_stops'] ?? 0, ':duration' => $data['estimated_duration_minutes'] ?? null,
        ]);
        return ['route_id' => $this->pdo->lastInsertId()];
    }

    public function addRouteStop($routeId, $stop)
    {
        $sql = "SELECT COALESCE(MAX(stop_sequence), 0) + 1 as next_seq FROM delivery_route_stops WHERE route_id = :route_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':route_id' => $routeId]);
        $nextSeq = $stmt->fetch(PDO::FETCH_ASSOC)['next_seq'];

        $sql = "INSERT INTO delivery_route_stops (route_id, order_id, stop_sequence, customer_name, delivery_address, contact_phone, items_summary, amount)
                VALUES (:route_id, :order_id, :seq, :name, :addr, :phone, :items, :amount)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':route_id' => $routeId, ':order_id' => $stop['order_id'] ?? null,
            ':seq' => $nextSeq, ':name' => $stop['customer_name'] ?? null,
            ':addr' => $stop['delivery_address'], ':phone' => $stop['contact_phone'] ?? null,
            ':items' => $stop['items_summary'] ?? null, ':amount' => $stop['amount'] ?? 0,
        ]);

        $sql = "UPDATE delivery_routes SET total_stops = total_stops + 1 WHERE route_id = :route_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':route_id' => $routeId]);

        return ['stop_id' => $this->pdo->lastInsertId(), 'sequence' => $nextSeq];
    }

    public function getRoutes($tenantId, $branchId, $date = null, $status = null)
    {
        $sql = "SELECT * FROM delivery_routes WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($date) { $sql .= " AND route_date = :date"; $params[':date'] = $date; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY route_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRouteDetail($routeId)
    {
        $sql = "SELECT * FROM delivery_routes WHERE route_id = :route_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':route_id' => $routeId]);
        $route = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM delivery_route_stops WHERE route_id = :route_id ORDER BY stop_sequence";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':route_id' => $routeId]);
        $stops = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['route' => $route, 'stops' => $stops];
    }

    public function updateStopStatus($stopId, $status, $proofPhoto = null, $signature = null, $failureReason = null)
    {
        $deliveredAt = $status === 'DELIVERED' ? ', delivered_at = NOW()' : '';
        $arrivedAt = $status === 'REACHED' || $status === 'DELIVERED' ? ', arrived_at = NOW()' : '';
        $sql = "UPDATE delivery_route_stops SET status = :status, proof_photo_path = :proof, signature_path = :sig, failure_reason = :reason$deliveredAt$arrivedAt WHERE stop_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $stopId, ':status' => $status, ':proof' => $proofPhoto, ':sig' => $signature, ':reason' => $failureReason]);
        return ['success' => true];
    }

    public function startRoute($routeId)
    {
        $sql = "UPDATE delivery_routes SET status = 'IN_PROGRESS', actual_start_time = NOW() WHERE route_id = :route_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':route_id' => $routeId]);
        return ['success' => true];
    }

    public function completeRoute($routeId)
    {
        $sql = "UPDATE delivery_routes SET status = 'COMPLETED', actual_end_time = NOW() WHERE route_id = :route_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':route_id' => $routeId]);
        return ['success' => true];
    }

    // ==================== LEAD PIPELINE ====================

    public function createLead($data)
    {
        $leadNumber = 'LEAD-' . date('Ymd') . '-' . substr(uniqid(), -4);
        $sql = "INSERT INTO catering_lead_pipeline (tenant_id, branch_id, lead_number, lead_source, client_name, client_company, client_phone, client_email, event_type, event_date, guest_count, estimated_value, assigned_to, notes, next_follow_up)
                VALUES (:tenant_id, :branch_id, :lead_number, :source, :name, :company, :phone, :email, :event_type, :event_date, :guests, :value, :assigned, :notes, :follow_up)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'],
            ':lead_number' => $leadNumber, ':source' => $data['lead_source'] ?? null,
            ':name' => $data['client_name'], ':company' => $data['client_company'] ?? null,
            ':phone' => $data['client_phone'] ?? null, ':email' => $data['client_email'] ?? null,
            ':event_type' => $data['event_type'] ?? null, ':event_date' => $data['event_date'] ?? null,
            ':guests' => $data['guest_count'] ?? null, ':value' => $data['estimated_value'] ?? 0,
            ':assigned' => $data['assigned_to'] ?? null, ':notes' => $data['notes'] ?? null,
            ':follow_up' => $data['next_follow_up'] ?? null,
        ]);
        return ['lead_id' => $this->pdo->lastInsertId(), 'lead_number' => $leadNumber];
    }

    public function updateLeadStage($leadId, $stage, $probabilityPct = null)
    {
        $sql = "UPDATE catering_lead_pipeline SET stage = :stage, stage_updated_at = NOW()";
        $params = [':lead_id' => $leadId, ':stage' => $stage];
        if ($probabilityPct !== null) {
            $sql .= ", probability_pct = :prob";
            $params[':prob'] = $probabilityPct;
        }
        $sql .= " WHERE lead_id = :lead_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['success' => true];
    }

    public function getLeads($tenantId, $branchId, $stage = null, $assignedTo = null)
    {
        $sql = "SELECT * FROM catering_lead_pipeline WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($stage) { $sql .= " AND stage = :stage"; $params[':stage'] = $stage; }
        if ($assignedTo) { $sql .= " AND assigned_to = :assigned"; $params[':assigned'] = $assignedTo; }
        $sql .= " ORDER BY stage_updated_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLeadPipelineSummary($tenantId, $branchId)
    {
        $stages = ['INQUIRY', 'QUALIFIED', 'PROPOSAL_SENT', 'NEGOTIATION', 'BOOKED', 'COMPLETED', 'LOST'];
        $summary = [];
        foreach ($stages as $stage) {
            $sql = "SELECT COUNT(*) as cnt, COALESCE(SUM(estimated_value), 0) as total_value FROM catering_lead_pipeline WHERE tenant_id = :tenant_id AND stage = :stage";
            $params = [':tenant_id' => $tenantId, ':stage' => $stage];
            if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $summary[$stage] = ['count' => (int)$row['cnt'], 'total_value' => (float)$row['total_value']];
        }
        return $summary;
    }

    // ==================== ALLERGEN TRACKING ====================

    public function setAllergenInfo($data)
    {
        $allergens = $data['allergens'] ?? [];
        $dietaryTags = $data['dietary_tags'] ?? [];

        $sql = "INSERT INTO allergen_tracking (tenant_id, product_id, allergens, dietary_tags, contains_gluten, contains_dairy, contains_nuts, contains_eggs, contains_soy, contains_shellfish, contains_fish, contains_sesame, is_vegetarian, is_vegan, is_halal, is_kosher, certification_body, certification_number, certification_expiry)
                VALUES (:tenant_id, :product_id, :allergens, :dietary, :gluten, :dairy, :nuts, :eggs, :soy, :shellfish, :fish, :sesame, :veg, :vegan, :halal, :kosher, :cert_body, :cert_num, :cert_expiry)
                ON DUPLICATE KEY UPDATE allergens = :allergens2, dietary_tags = :dietary2, contains_gluten = :gluten2, contains_dairy = :dairy2, contains_nuts = :nuts2, contains_eggs = :eggs2, contains_soy = :soy2, contains_shellfish = :shellfish2, contains_fish = :fish2, contains_sesame = :sesame2, is_vegetarian = :veg2, is_vegan = :vegan2, is_halal = :halal2, is_kosher = :kosher2, certification_body = :cert_body2, certification_number = :cert_num2, certification_expiry = :cert_expiry2";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':tenant_id' => $data['tenant_id'], ':product_id' => $data['product_id'],
            ':allergens' => json_encode($allergens), ':dietary' => json_encode($dietaryTags),
            ':gluten' => in_array('gluten', $allergens) ? 1 : 0,
            ':dairy' => in_array('dairy', $allergens) ? 1 : 0,
            ':nuts' => in_array('nuts', $allergens) ? 1 : 0,
            ':eggs' => in_array('eggs', $allergens) ? 1 : 0,
            ':soy' => in_array('soy', $allergens) ? 1 : 0,
            ':shellfish' => in_array('shellfish', $allergens) ? 1 : 0,
            ':fish' => in_array('fish', $allergens) ? 1 : 0,
            ':sesame' => in_array('sesame', $allergens) ? 1 : 0,
            ':veg' => in_array('vegetarian', $dietaryTags) ? 1 : 0,
            ':vegan' => in_array('vegan', $dietaryTags) ? 1 : 0,
            ':halal' => in_array('halal', $dietaryTags) ? 1 : 0,
            ':kosher' => in_array('kosher', $dietaryTags) ? 1 : 0,
            ':cert_body' => $data['certification_body'] ?? null,
            ':cert_num' => $data['certification_number'] ?? null,
            ':cert_expiry' => $data['certification_expiry'] ?? null,
        ];
        $dupParams = [];
        foreach ($params as $k => $v) { $dupParams[$k . '2'] = $v; }
        $stmt->execute(array_merge($params, $dupParams));
        return ['success' => true];
    }

    public function getAllergenInfo($tenantId, $productId)
    {
        $sql = "SELECT * FROM allergen_tracking WHERE tenant_id = :tenant_id AND product_id = :product_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':product_id' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllergenInfoBatch($tenantId, $productIds)
    {
        if (empty($productIds)) return [];
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $sql = "SELECT * FROM allergen_tracking WHERE tenant_id = ? AND product_id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge([$tenantId], $productIds));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterByDietaryTag($tenantId, $tag)
    {
        $columnMap = [
            'vegetarian' => 'is_vegetarian', 'vegan' => 'is_vegan',
            'halal' => 'is_halal', 'kosher' => 'is_kosher',
        ];
        $col = $columnMap[$tag] ?? null;
        if (!$col) return [];

        $sql = "SELECT at.*, p.name as product_name FROM allergen_tracking at
                LEFT JOIN products p ON at.product_id = p.product_id
                WHERE at.tenant_id = :tenant_id AND at.$col = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
