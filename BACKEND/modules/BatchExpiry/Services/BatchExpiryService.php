<?php

namespace App\Modules\BatchExpiry\Services;

use App\Core\Database;
use PDO;

class BatchExpiryService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function receiveBatch($data)
    {
        $sql = "INSERT INTO inventory_batches (tenant_id, branch_id, product_id, inventory_item_id, batch_number, manufacture_date, expiry_date, quantity_received, quantity_remaining, unit, unit_cost, original_price, source, supplier_id)
                VALUES (:tenant_id, :branch_id, :product_id, :inventory_item_id, :batch_number, :manufacture_date, :expiry_date, :quantity_received, :quantity_received, :unit, :unit_cost, :original_price, :source, :supplier_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':product_id' => $data['product_id'],
            ':inventory_item_id' => $data['inventory_item_id'] ?? null,
            ':batch_number' => $data['batch_number'],
            ':manufacture_date' => $data['manufacture_date'] ?? null,
            ':expiry_date' => $data['expiry_date'],
            ':quantity_received' => $data['quantity_received'],
            ':unit' => $data['unit'] ?? 'pcs',
            ':unit_cost' => $data['unit_cost'] ?? 0,
            ':original_price' => $data['original_price'] ?? null,
            ':source' => $data['source'] ?? 'PURCHASE',
            ':supplier_id' => $data['supplier_id'] ?? null,
        ]);
        $batchId = $this->pdo->lastInsertId();

        $this->updateBatchStatus($batchId);
        return ['batch_id' => $batchId];
    }

    public function getBatches($tenantId, $branchId, $status = null, $productId = null, $nearExpiryDays = null)
    {
        $sql = "SELECT ib.*, DATEDIFF(ib.expiry_date, CURDATE()) as days_until_expiry, p.product_name FROM inventory_batches ib
                LEFT JOIN products p ON ib.product_id = p.product_id
                WHERE ib.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND ib.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND ib.status = :status";
            $params[':status'] = $status;
        }
        if ($productId) {
            $sql .= " AND ib.product_id = :product_id";
            $params[':product_id'] = $productId;
        }
        if ($nearExpiryDays !== null) {
            $sql .= " AND DATEDIFF(ib.expiry_date, CURDATE()) <= :days AND DATEDIFF(ib.expiry_date, CURDATE()) >= 0 AND ib.status IN ('FRESH','NEAR_EXPIRY')";
            $params[':days'] = $nearExpiryDays;
        }
        $sql .= " ORDER BY ib.expiry_date ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBatch($batchId)
    {
        $sql = "SELECT * FROM inventory_batches WHERE batch_id = :batch_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':batch_id' => $batchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deductFromBatch($batchId, $quantity)
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) {
            return ['success' => false, 'message' => 'Batch not found'];
        }
        if ($batch['quantity_remaining'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient quantity in batch'];
        }

        $newQty = $batch['quantity_remaining'] - $quantity;
        $status = $newQty <= 0 ? 'SOLD_OUT' : $batch['status'];

        $sql = "UPDATE inventory_batches SET quantity_remaining = :qty, status = :status WHERE batch_id = :batch_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':batch_id' => $batchId, ':qty' => $newQty, ':status' => $status]);

        return ['success' => true, 'remaining' => $newQty, 'status' => $status];
    }

    public function applyDiscount($batchId, $discountPercentage, $discountedPrice, $changedBy, $reason)
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) {
            return ['success' => false, 'message' => 'Batch not found'];
        }

        $sql = "UPDATE inventory_batches SET discount_applied = 1, discount_percentage = :pct, discounted_price = :price, status = 'DISCOUNTED' WHERE batch_id = :batch_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':batch_id' => $batchId,
            ':pct' => $discountPercentage,
            ':price' => $discountedPrice,
        ]);

        $this->logBatchStatus($batchId, $batch['status'], 'DISCOUNTED', $changedBy, $reason, $discountPercentage);

        return ['success' => true, 'discounted_price' => $discountedPrice];
    }

    public function discardBatch($batchId, $changedBy, $reason)
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) {
            return ['success' => false, 'message' => 'Batch not found'];
        }

        $sql = "UPDATE inventory_batches SET status = 'DISCARDED' WHERE batch_id = :batch_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':batch_id' => $batchId]);

        $this->logBatchStatus($batchId, $batch['status'], 'DISCARDED', $changedBy, $reason);

        return ['success' => true];
    }

    public function getNearExpiryBatches($tenantId, $branchId, $days = 7)
    {
        return $this->getBatches($tenantId, $branchId, null, null, $days);
    }

    public function getExpiryDashboard($tenantId, $branchId)
    {
        $fresh = $this->countByStatus($tenantId, $branchId, 'FRESH');
        $nearExpiry = $this->countByStatus($tenantId, $branchId, 'NEAR_EXPIRY');
        $discounted = $this->countByStatus($tenantId, $branchId, 'DISCOUNTED');
        $expired = $this->countByStatus($tenantId, $branchId, 'EXPIRED');
        $discarded = $this->countByStatus($tenantId, $branchId, 'DISCARDED');

        $sql = "SELECT COUNT(*) as cnt, COALESCE(SUM(quantity_remaining * unit_cost), 0) as value_at_risk
                FROM inventory_batches
                WHERE tenant_id = :tenant_id AND DATEDIFF(expiry_date, CURDATE()) <= 3 AND DATEDIFF(expiry_date, CURDATE()) >= 0 AND status IN ('FRESH','NEAR_EXPIRY')";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $risk = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'fresh' => $fresh,
            'near_expiry' => $nearExpiry,
            'discounted' => $discounted,
            'expired' => $expired,
            'discarded' => $discarded,
            'value_at_risk' => $risk['value_at_risk'] ?? 0,
            'items_at_risk' => $risk['cnt'] ?? 0,
        ];
    }

    public function updateAllBatchStatuses($tenantId, $branchId)
    {
        $batches = $this->getBatches($tenantId, $branchId);
        $updated = 0;
        foreach ($batches as $batch) {
            if (in_array($batch['status'], ['SOLD_OUT', 'DISCARDED', 'EXPIRED'])) {
                continue;
            }
            $updated += $this->updateBatchStatus($batch['batch_id']) ? 1 : 0;
        }
        return ['updated' => $updated, 'total_checked' => count($batches)];
    }

    private function updateBatchStatus($batchId)
    {
        $batch = $this->getBatch($batchId);
        if (!$batch) {
            return false;
        }

        $daysUntilExpiry = (int) ((strtotime($batch['expiry_date']) - strtotime(date('Y-m-d'))) / 86400);
        $newStatus = $batch['status'];

        if ($daysUntilExpiry < 0) {
            $newStatus = 'EXPIRED';
        } elseif ($daysUntilExpiry <= 3 && $batch['status'] === 'FRESH') {
            $newStatus = 'NEAR_EXPIRY';
        }

        if ($newStatus !== $batch['status']) {
            $sql = "UPDATE inventory_batches SET status = :status, days_until_expiry = :days WHERE batch_id = :batch_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':batch_id' => $batchId, ':status' => $newStatus, ':days' => $daysUntilExpiry]);
            $this->logBatchStatus($batchId, $batch['status'], $newStatus, null, 'Auto-status update');
            return true;
        }
        return false;
    }

    private function logBatchStatus($batchId, $oldStatus, $newStatus, $changedBy = null, $reason = null, $discountPct = null)
    {
        $sql = "INSERT INTO batch_status_logs (batch_id, old_status, new_status, changed_by, reason, discount_percentage)
                VALUES (:batch_id, :old, :new, :changed_by, :reason, :discount)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':batch_id' => $batchId,
            ':old' => $oldStatus,
            ':new' => $newStatus,
            ':changed_by' => $changedBy,
            ':reason' => $reason,
            ':discount' => $discountPct,
        ]);
    }

    private function countByStatus($tenantId, $branchId, $status)
    {
        $sql = "SELECT COUNT(*) as cnt FROM inventory_batches WHERE tenant_id = :tenant_id AND status = :status";
        $params = [':tenant_id' => $tenantId, ':status' => $status];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    }
}
