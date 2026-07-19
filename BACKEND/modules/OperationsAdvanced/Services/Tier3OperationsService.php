<?php

namespace App\Modules\OperationsAdvanced\Services;

use App\Core\Database;
use PDO;

class Tier3OperationsService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== AI SALES PREDICTION ====================

    public function getPredictions($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT * FROM ai_sales_predictions WHERE tenant_id = :tenant_id AND prediction_date BETWEEN :date_from AND :date_to";
        $params = [':tenant_id' => $tenantId, ':date_from' => $dateFrom, ':date_to' => $dateTo];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " ORDER BY prediction_date, predicted_hour";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generatePrediction($tenantId, $branchId, $date)
    {
        $sql = "SELECT DAYOFWEEK(:date) as dow, MONTH(:date) as month";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        $dateInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT HOUR(o.created_at) as hr, SUM(o.total_amount) as revenue, COUNT(*) as orders
                FROM orders o
                WHERE o.tenant_id = :tenant_id AND DATE(o.created_at) >= DATE_SUB(:date, INTERVAL 90 DAY)
                AND o.status = 'COMPLETED'
                GROUP BY HOUR(o.created_at), DAYOFWEEK(o.created_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':date' => $date]);
        $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $predictions = [];
        $dow = $dateInfo['dow'];
        $matchingData = array_filter($historicalData, function($d) use ($dow) {
            return true;
        });

        for ($hour = 0; $hour < 24; $hour++) {
            $hourData = array_filter($historicalData, fn($d) => (int)$d['hr'] === $hour);
            if (count($hourData) > 0) {
                $avgRevenue = array_sum(array_column($hourData, 'revenue')) / count($hourData);
                $avgOrders = array_sum(array_column($hourData, 'orders')) / count($hourData);
            } else {
                $avgRevenue = 0;
                $avgOrders = 0;
            }

            $sql = "INSERT INTO ai_sales_predictions (tenant_id, branch_id, prediction_date, predicted_hour, predicted_revenue, predicted_orders, confidence_score, model_version)
                    VALUES (:tenant_id, :branch_id, :date, :hour, :revenue, :orders, :confidence, 'v1.0')";
            $stmt = $this->pdo->prepare($sql);
            $confidence = count($hourData) > 5 ? 75.0 : (count($hourData) > 0 ? 50.0 : 20.0);
            $stmt->execute([
                ':tenant_id' => $tenantId, ':branch_id' => $branchId,
                ':date' => $date, ':hour' => $hour,
                ':revenue' => round($avgRevenue, 2), ':orders' => round($avgOrders),
                ':confidence' => $confidence,
            ]);
            $predictions[] = ['hour' => $hour, 'predicted_revenue' => round($avgRevenue, 2), 'predicted_orders' => round($avgOrders), 'confidence' => $confidence];
        }

        return ['date' => $date, 'predictions' => $predictions];
    }

    // ==================== MULTI-CHANNEL BOOKING SYNC ====================

    public function syncBooking($data)
    {
        $sql = "INSERT INTO booking_channel_sync (tenant_id, branch_id, channel_name, channel_type, external_booking_id, internal_booking_id, sync_status)
                VALUES (:tenant_id, :branch_id, :channel, :channel_type, :external, :internal, 'PENDING')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':channel' => $data['channel_name'], ':channel_type' => $data['channel_type'],
            ':external' => $data['external_booking_id'] ?? null,
            ':internal' => $data['internal_booking_id'] ?? null,
        ]);
        $syncId = $this->pdo->lastInsertId();

        $sql = "UPDATE booking_channel_sync SET sync_status = 'SYNCED', synced_at = NOW() WHERE sync_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $syncId]);

        return ['sync_id' => $syncId, 'sync_status' => 'SYNCED'];
    }

    public function getSyncStatus($tenantId, $branchId)
    {
        $sql = "SELECT channel_name, sync_status, COUNT(*) as count FROM booking_channel_sync WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " GROUP BY channel_name, sync_status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== ORDER THROTTLING ====================

    public function getThrottlingConfig($tenantId, $branchId, $channel = 'ALL')
    {
        $sql = "SELECT * FROM order_throttling_config WHERE tenant_id = :tenant_id AND channel = :channel AND is_active = 1";
        $params = [':tenant_id' => $tenantId, ':channel' => $channel];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setThrottlingConfig($data)
    {
        $sql = "INSERT INTO order_throttling_config (tenant_id, branch_id, channel, max_orders_per_slot, slot_duration_minutes, auto_pause_threshold, is_active)
                VALUES (:tenant_id, :branch_id, :channel, :max_orders, :slot_duration, :auto_pause, 1)
                ON DUPLICATE KEY UPDATE max_orders_per_slot = :max_orders2, slot_duration_minutes = :slot_duration2, auto_pause_threshold = :auto_pause2";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':channel' => $data['channel'] ?? 'ALL',
            ':max_orders' => $data['max_orders_per_slot'] ?? 10,
            ':slot_duration' => $data['slot_duration_minutes'] ?? 15,
            ':auto_pause' => $data['auto_pause_threshold'] ?? 20,
        ];
        $dupParams = [];
        foreach ($params as $k => $v) { $dupParams[$k . '2'] = $v; }
        $stmt->execute(array_merge($params, $dupParams));
        return ['success' => true];
    }

    public function checkThrottle($tenantId, $branchId, $channel = 'ONLINE')
    {
        $config = $this->getThrottlingConfig($tenantId, $branchId, $channel);
        if (!$config) return ['throttled' => false, 'can_accept' => true];

        $now = time();
        $slotStart = strtotime($config['current_slot_start'] ?? 'now');
        $slotEnd = $slotStart + ($config['slot_duration_minutes'] * 60);

        if ($now >= $slotEnd) {
            $sql = "UPDATE order_throttling_config SET current_orders_in_slot = 0, current_slot_start = NOW(), is_paused = 0 WHERE config_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $config['config_id']]);
            $config['current_orders_in_slot'] = 0;
        }

        $canAccept = $config['current_orders_in_slot'] < $config['max_orders_per_slot'] && !$config['is_paused'];
        $estimatedWait = $canAccept ? 0 : ($config['slot_duration_minutes'] - (($now - $slotStart) / 60));

        return [
            'throttled' => !$canAccept,
            'can_accept' => $canAccept,
            'current_orders' => $config['current_orders_in_slot'],
            'max_orders' => $config['max_orders_per_slot'],
            'estimated_wait_minutes' => max(0, round($estimatedWait)),
            'is_paused' => (bool)$config['is_paused'],
        ];
    }

    public function incrementOrderCount($tenantId, $branchId, $channel = 'ONLINE')
    {
        $config = $this->getThrottlingConfig($tenantId, $branchId, $channel);
        if (!$config) return;

        $now = time();
        $slotEnd = strtotime($config['current_slot_start'] ?? 'now') + ($config['slot_duration_minutes'] * 60);

        if ($now >= $slotEnd) {
            $sql = "UPDATE order_throttling_config SET current_orders_in_slot = 1, current_slot_start = NOW() WHERE config_id = :id";
        } else {
            $sql = "UPDATE order_throttling_config SET current_orders_in_slot = current_orders_in_slot + 1 WHERE config_id = :id";
            if ($config['current_orders_in_slot'] + 1 >= $config['auto_pause_threshold']) {
                $sql = "UPDATE order_throttling_config SET current_orders_in_slot = current_orders_in_slot + 1, is_paused = 1 WHERE config_id = :id";
            }
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $config['config_id']]);
    }

    public function pauseThrottling($tenantId, $branchId, $channel = 'ALL')
    {
        $sql = "UPDATE order_throttling_config SET is_paused = 1 WHERE tenant_id = :tenant_id AND channel = :channel";
        $params = [':tenant_id' => $tenantId, ':channel' => $channel];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['success' => true];
    }

    public function resumeThrottling($tenantId, $branchId, $channel = 'ALL')
    {
        $sql = "UPDATE order_throttling_config SET is_paused = 0, current_orders_in_slot = 0, current_slot_start = NOW() WHERE tenant_id = :tenant_id AND channel = :channel";
        $params = [':tenant_id' => $tenantId, ':channel' => $channel];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['success' => true];
    }

    // ==================== AUTO PURCHASE ORDER ====================

    public function createAutoPORule($data)
    {
        $sql = "INSERT INTO auto_po_rules (tenant_id, branch_id, inventory_id, reorder_point, reorder_quantity, preferred_supplier_id, fallback_supplier_id, auto_generate, requires_approval, is_active)
                VALUES (:tenant_id, :branch_id, :inventory_id, :reorder_point, :reorder_qty, :preferred, :fallback, :auto, :approval, 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':inventory_id' => $data['inventory_id'],
            ':reorder_point' => $data['reorder_point'],
            ':reorder_qty' => $data['reorder_quantity'],
            ':preferred' => $data['preferred_supplier_id'] ?? null,
            ':fallback' => $data['fallback_supplier_id'] ?? null,
            ':auto' => $data['auto_generate'] ?? 0,
            ':approval' => $data['requires_approval'] ?? 1,
        ]);
        return ['rule_id' => $this->pdo->lastInsertId()];
    }

    public function checkAndGeneratePOs($tenantId, $branchId)
    {
        $sql = "SELECT r.*, i.name as inventory_name, COALESCE(sb.quantity, 0) as current_qty
                FROM auto_po_rules r
                JOIN inventory i ON r.inventory_id = i.inventory_id
                LEFT JOIN stock_balances sb ON r.inventory_id = sb.inventory_id AND sb.branch_id = r.branch_id
                WHERE r.tenant_id = :tenant_id AND r.is_active = 1 AND COALESCE(sb.quantity, 0) <= r.reorder_point";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND r.branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $generatedPOs = [];
        foreach ($triggers as $trigger) {
            if ($trigger['auto_generate']) {
                $poNumber = 'AUTO-PO-' . date('Ymd') . '-' . substr(uniqid(), -4);
                $sql = "INSERT INTO purchase_orders (tenant_id, branch_id, po_number, supplier_id, status, total_amount, created_by, created_at)
                        VALUES (:tenant_id, :branch_id, :po_number, :supplier_id, 'DRAFT', 0, NULL, NOW())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':tenant_id' => $tenantId, ':branch_id' => $trigger['branch_id'],
                    ':po_number' => $poNumber, ':supplier_id' => $trigger['preferred_supplier_id'],
                ]);
                $poId = $this->pdo->lastInsertId();
                $generatedPOs[] = ['po_id' => $poId, 'po_number' => $poNumber, 'inventory' => $trigger['inventory_name'], 'quantity' => $trigger['reorder_quantity']];

                $sql = "UPDATE auto_po_rules SET last_po_generated_at = NOW() WHERE rule_id = :rid";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':rid' => $trigger['rule_id']]);
            }
        }

        return ['triggered_count' => count($triggers), 'generated_pos' => $generatedPOs];
    }

    // ==================== DAILY PRODUCTION PLANNING ====================

    public function createProductionPlan($data)
    {
        $sql = "INSERT INTO daily_production_plans (tenant_id, branch_id, plan_date, product_id, product_name, planned_quantity)
                VALUES (:tenant_id, :branch_id, :plan_date, :product_id, :product_name, :planned_qty)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':plan_date' => $data['plan_date'],
            ':product_id' => $data['product_id'],
            ':product_name' => $data['product_name'] ?? null,
            ':planned_qty' => $data['planned_quantity'],
        ]);
        return ['plan_id' => $this->pdo->lastInsertId()];
    }

    public function getProductionPlans($tenantId, $branchId, $date)
    {
        $sql = "SELECT * FROM daily_production_plans WHERE tenant_id = :tenant_id AND plan_date = :date";
        $params = [':tenant_id' => $tenantId, ':date' => $date];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " ORDER BY product_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProductionPlan($planId, $data)
    {
        $fields = [];
        $params = [':id' => $planId];
        if (isset($data['produced_quantity'])) { $fields[] = 'produced_quantity = :produced'; $params[':produced'] = $data['produced_quantity']; }
        if (isset($data['sold_quantity'])) { $fields[] = 'sold_quantity = :sold'; $params[':sold'] = $data['sold_quantity']; }
        if (isset($data['wasted_quantity'])) { $fields[] = 'wasted_quantity = :wasted'; $params[':wasted'] = $data['wasted_quantity']; }
        if (isset($data['status'])) { $fields[] = 'status = :status'; $params[':status'] = $data['status']; }
        if (isset($data['production_start'])) { $fields[] = 'production_start = :start'; $params[':start'] = $data['production_start']; }
        if (isset($data['production_end'])) { $fields[] = 'production_end = :end'; $params[':end'] = $data['production_end']; }

        if (empty($fields)) return ['success' => false, 'message' => 'No fields to update'];

        $fields[] = 'remaining_quantity = GREATEST(produced_quantity - sold_quantity - wasted_quantity, 0)';
        $sql = "UPDATE daily_production_plans SET " . implode(', ', $fields) . " WHERE plan_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['success' => true];
    }

    // ==================== SERVICE SPEED METRICS ====================

    public function recordServiceMetric($data)
    {
        $receivedAt = $data['order_received_at'];
        $startedAt = $data['order_started_at'] ?? null;
        $readyAt = $data['order_ready_at'] ?? null;
        $servedAt = $data['order_served_at'] ?? null;

        $prepSeconds = $startedAt && $readyAt ? strtotime($readyAt) - strtotime($startedAt) : null;
        $serviceSeconds = $receivedAt && $servedAt ? strtotime($servedAt) - strtotime($receivedAt) : null;

        $sql = "INSERT INTO service_speed_metrics (tenant_id, branch_id, order_id, metric_date, metric_hour, order_received_at, order_started_at, order_ready_at, order_served_at, total_prep_seconds, total_service_seconds, order_type, items_count)
                VALUES (:tenant_id, :branch_id, :order_id, :metric_date, :metric_hour, :received, :started, :ready, :served, :prep, :service, :type, :items)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':order_id' => $data['order_id'] ?? null,
            ':metric_date' => date('Y-m-d', strtotime($receivedAt)),
            ':metric_hour' => (int)date('H', strtotime($receivedAt)),
            ':received' => $receivedAt, ':started' => $startedAt, ':ready' => $readyAt, ':served' => $servedAt,
            ':prep' => $prepSeconds, ':service' => $serviceSeconds,
            ':type' => $data['order_type'] ?? 'DINE_IN',
            ':items' => $data['items_count'] ?? null,
        ]);
        return ['metric_id' => $this->pdo->lastInsertId(), 'prep_seconds' => $prepSeconds, 'service_seconds' => $serviceSeconds];
    }

    public function getServiceSpeedReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT metric_date, metric_hour, order_type,
                    AVG(total_prep_seconds) as avg_prep, AVG(total_service_seconds) as avg_service,
                    MAX(total_service_seconds) as max_service, MIN(total_service_seconds) as min_service,
                    COUNT(*) as order_count
                FROM service_speed_metrics
                WHERE tenant_id = :tenant_id AND metric_date BETWEEN :date_from AND :date_to";
        $params = [':tenant_id' => $tenantId, ':date_from' => $dateFrom, ':date_to' => $dateTo];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " GROUP BY metric_date, metric_hour, order_type ORDER BY metric_date, metric_hour";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
