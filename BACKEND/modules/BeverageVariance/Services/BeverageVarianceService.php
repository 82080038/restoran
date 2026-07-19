<?php

namespace App\Modules\BeverageVariance\Services;

use App\Core\Database;
use PDO;

class BeverageVarianceService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function createBarCount($data)
    {
        $sql = "INSERT INTO bar_counts (tenant_id, branch_id, count_type, count_date, shift_name, zone, counted_by, status)
                VALUES (:tenant_id, :branch_id, :count_type, :count_date, :shift_name, :zone, :counted_by, 'DRAFT')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':count_type' => $data['count_type'],
            ':count_date' => $data['count_date'],
            ':shift_name' => $data['shift_name'] ?? null,
            ':zone' => $data['zone'] ?? 'MAIN_BAR',
            ':counted_by' => $data['counted_by'],
        ]);
        $countId = $this->pdo->lastInsertId();

        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->addBarCountItem($countId, $item);
            }
        }
        return ['count_id' => $countId];
    }

    public function addBarCountItem($countId, $item)
    {
        $expected = ($item['opening_bottles'] ?? 0) + ($item['received_bottles'] ?? 0) - ($item['sold_pos'] ?? 0);
        $variance = ($item['counted_bottles'] ?? 0) + ($item['counted_partial'] ?? 0) - $expected;
        $varianceCost = $variance * ($item['unit_cost'] ?? 0);
        $variancePct = $expected > 0 ? round(($variance / $expected) * 100, 2) : 0;

        $sql = "INSERT INTO bar_count_items (count_id, product_id, product_name, unit, opening_bottles, opening_partial, received_bottles, sold_pos, expected_bottles, counted_bottles, counted_partial, variance_bottles, variance_cost, variance_pct, notes)
                VALUES (:count_id, :product_id, :product_name, :unit, :opening_bottles, :opening_partial, :received_bottles, :sold_pos, :expected_bottles, :counted_bottles, :counted_partial, :variance_bottles, :variance_cost, :variance_pct, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':count_id' => $countId,
            ':product_id' => $item['product_id'],
            ':product_name' => $item['product_name'] ?? '',
            ':unit' => $item['unit'] ?? 'bottle',
            ':opening_bottles' => $item['opening_bottles'] ?? 0,
            ':opening_partial' => $item['opening_partial'] ?? 0,
            ':received_bottles' => $item['received_bottles'] ?? 0,
            ':sold_pos' => $item['sold_pos'] ?? 0,
            ':expected_bottles' => $expected,
            ':counted_bottles' => $item['counted_bottles'] ?? 0,
            ':counted_partial' => $item['counted_partial'] ?? 0,
            ':variance_bottles' => $variance,
            ':variance_cost' => $varianceCost,
            ':variance_pct' => $variancePct,
            ':notes' => $item['notes'] ?? null,
        ]);
        return ['item_id' => $this->pdo->lastInsertId(), 'variance' => $variance, 'variance_cost' => $varianceCost];
    }

    public function submitBarCount($countId, $verifiedBy = null)
    {
        $status = $verifiedBy ? 'VERIFIED' : 'SUBMITTED';
        $sql = "UPDATE bar_counts SET status = :status" . ($verifiedBy ? ", verified_by = :verified_by" : "") . " WHERE count_id = :count_id";
        $params = [':count_id' => $countId, ':status' => $status];
        if ($verifiedBy) {
            $params[':verified_by'] = $verifiedBy;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['success' => true, 'status' => $status];
    }

    public function getBarCounts($tenantId, $branchId, $dateFrom = null, $dateTo = null, $countType = null)
    {
        $sql = "SELECT * FROM bar_counts WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($dateFrom) {
            $sql .= " AND count_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND count_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        if ($countType) {
            $sql .= " AND count_type = :count_type";
            $params[':count_type'] = $countType;
        }
        $sql .= " ORDER BY count_date DESC, created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBarCountDetail($countId)
    {
        $sql = "SELECT * FROM bar_counts WHERE count_id = :count_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':count_id' => $countId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM bar_count_items WHERE count_id = :count_id ORDER BY product_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':count_id' => $countId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['count' => $count, 'items' => $items];
    }

    public function generateVarianceReport($tenantId, $branchId, $periodStart, $periodEnd, $generatedBy)
    {
        $sql = "SELECT bci.* FROM bar_count_items bci
                JOIN bar_counts bc ON bci.count_id = bc.count_id
                WHERE bc.tenant_id = :tenant_id AND bc.count_date BETWEEN :start AND :end";
        $params = [':tenant_id' => $tenantId, ':start' => $periodStart, ':end' => $periodEnd];
        if ($branchId) {
            $sql .= " AND bc.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalExpected = array_sum(array_column($items, 'variance_cost'));
        $itemsWithVariance = count(array_filter($items, fn($i) => abs($i['variance_bottles']) > 0.01));

        $sql = "INSERT INTO variance_reports (tenant_id, branch_id, report_date, period_start, period_end, total_variance_cost, items_with_variance, status, generated_by)
                VALUES (:tenant_id, :branch_id, CURDATE(), :start, :end, :total_variance, :items_count, 'DRAFT', :generated_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':start' => $periodStart,
            ':end' => $periodEnd,
            ':total_variance' => $totalExpected,
            ':items_count' => $itemsWithVariance,
            ':generated_by' => $generatedBy,
        ]);
        $reportId = $this->pdo->lastInsertId();

        return ['report_id' => $reportId, 'total_variance_cost' => $totalExpected, 'items_with_variance' => $itemsWithVariance];
    }

    public function getVarianceReports($tenantId, $branchId)
    {
        $sql = "SELECT * FROM variance_reports WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $sql .= " ORDER BY report_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== KEG TRACKING ====================

    public function receiveKeg($data)
    {
        $sql = "INSERT INTO keg_tracking (tenant_id, branch_id, product_id, keg_number, tap_handle, size_liters, received_date, full_weight_kg, empty_weight_kg, current_weight_kg, theoretical_remaining_liters, status)
                VALUES (:tenant_id, :branch_id, :product_id, :keg_number, :tap_handle, :size_liters, CURDATE(), :full_weight, :empty_weight, :full_weight, :size_liters, 'INVENTORY')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':product_id' => $data['product_id'],
            ':keg_number' => $data['keg_number'] ?? null,
            ':tap_handle' => $data['tap_handle'] ?? null,
            ':size_liters' => $data['size_liters'] ?? 50.00,
            ':full_weight' => $data['full_weight_kg'] ?? 0,
            ':empty_weight' => $data['empty_weight_kg'] ?? 0,
        ]);
        return ['keg_id' => $this->pdo->lastInsertId()];
    }

    public function tapKeg($kegId, $tapHandle)
    {
        $sql = "UPDATE keg_tracking SET status = 'TAPPED', tapped_date = CURDATE(), tap_handle = :tap_handle WHERE keg_id = :keg_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':keg_id' => $kegId, ':tap_handle' => $tapHandle]);
        return ['success' => true];
    }

    public function updateKegWeight($kegId, $currentWeightKg)
    {
        $keg = $this->getKeg($kegId);
        if (!$keg) {
            return ['success' => false, 'message' => 'Keg not found'];
        }

        $emptyWeight = $keg['empty_weight_kg'] ?? 0;
        $fullWeight = $keg['full_weight_kg'] ?? 0;
        $sizeLiters = $keg['size_liters'] ?? 50;

        $actualRemaining = $fullWeight > $emptyWeight
            ? (($currentWeightKg - $emptyWeight) / ($fullWeight - $emptyWeight)) * $sizeLiters
            : 0;

        $variance = $actualRemaining - ($keg['theoretical_remaining_liters'] ?? 0);
        $varianceCost = $variance * ($keg['unit_cost_per_liter'] ?? 0);

        $sql = "UPDATE keg_tracking SET current_weight_kg = :weight, actual_pours_liters = :actual, variance_liters = :variance, variance_cost = :variance_cost WHERE keg_id = :keg_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':keg_id' => $kegId,
            ':weight' => $currentWeightKg,
            ':actual' => $sizeLiters - $actualRemaining,
            ':variance' => $variance,
            ':variance_cost' => $varianceCost,
        ]);

        $status = $actualRemaining <= 0.5 ? 'EMPTY' : 'TAPPED';
        if ($status === 'EMPTY') {
            $sql = "UPDATE keg_tracking SET status = 'EMPTY', emptied_date = CURDATE() WHERE keg_id = :keg_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':keg_id' => $kegId]);
        }

        return ['success' => true, 'remaining_liters' => round($actualRemaining, 2), 'variance_liters' => round($variance, 2)];
    }

    public function getKegs($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM keg_tracking WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY received_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKeg($kegId)
    {
        $sql = "SELECT * FROM keg_tracking WHERE keg_id = :keg_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':keg_id' => $kegId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
