<?php

namespace App\Modules\EventProfitability\Services;

use App\Core\Database;
use PDO;

class EventProfitabilityService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function createProfitability($data)
    {
        $sql = "INSERT INTO event_profitability (tenant_id, branch_id, event_type, event_id, event_name, event_date, ticket_revenue, fnb_revenue, bar_revenue, merch_revenue, other_revenue, cogs, labor_cost, artist_cost, production_cost, marketing_cost, overhead_cost, other_cost, attendance, status)
                VALUES (:tenant_id, :branch_id, :event_type, :event_id, :event_name, :event_date, :ticket_rev, :fnb_rev, :bar_rev, :merch_rev, :other_rev, :cogs, :labor, :artist, :production, :marketing, :overhead, :other_cost, :attendance, 'DRAFT')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':event_type' => $data['event_type'],
            ':event_id' => $data['event_id'],
            ':event_name' => $data['event_name'],
            ':event_date' => $data['event_date'],
            ':ticket_rev' => $data['ticket_revenue'] ?? 0,
            ':fnb_rev' => $data['fnb_revenue'] ?? 0,
            ':bar_rev' => $data['bar_revenue'] ?? 0,
            ':merch_rev' => $data['merch_revenue'] ?? 0,
            ':other_rev' => $data['other_revenue'] ?? 0,
            ':cogs' => $data['cogs'] ?? 0,
            ':labor' => $data['labor_cost'] ?? 0,
            ':artist' => $data['artist_cost'] ?? 0,
            ':production' => $data['production_cost'] ?? 0,
            ':marketing' => $data['marketing_cost'] ?? 0,
            ':overhead' => $data['overhead_cost'] ?? 0,
            ':other_cost' => $data['other_cost'] ?? 0,
            ':attendance' => $data['attendance'] ?? 0,
        ]);
        $profitabilityId = $this->pdo->lastInsertId();
        $this->recalculate($profitabilityId);
        return ['profitability_id' => $profitabilityId];
    }

    public function addCostItem($profitabilityId, $item)
    {
        $sql = "INSERT INTO event_cost_items (profitability_id, cost_category, description, amount, vendor, invoice_number, is_confirmed)
                VALUES (:profitability_id, :category, :description, :amount, :vendor, :invoice, :confirmed)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':profitability_id' => $profitabilityId,
            ':category' => $item['cost_category'],
            ':description' => $item['description'],
            ':amount' => $item['amount'],
            ':vendor' => $item['vendor'] ?? null,
            ':invoice' => $item['invoice_number'] ?? null,
            ':confirmed' => $item['is_confirmed'] ?? 0,
        ]);
        $this->recalculate($profitabilityId);
        return ['item_id' => $this->pdo->lastInsertId()];
    }

    public function finalizeProfitability($profitabilityId)
    {
        $this->recalculate($profitabilityId);
        $sql = "UPDATE event_profitability SET status = 'FINALIZED' WHERE profitability_id = :profitability_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':profitability_id' => $profitabilityId]);
        return ['success' => true];
    }

    public function getProfitabilityList($tenantId, $branchId, $eventType = null, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT * FROM event_profitability WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($eventType) {
            $sql .= " AND event_type = :event_type";
            $params[':event_type'] = $eventType;
        }
        if ($dateFrom) {
            $sql .= " AND event_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND event_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        $sql .= " ORDER BY event_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProfitabilityDetail($profitabilityId)
    {
        $sql = "SELECT * FROM event_profitability WHERE profitability_id = :profitability_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':profitability_id' => $profitabilityId]);
        $profitability = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM event_cost_items WHERE profitability_id = :profitability_id ORDER BY cost_category";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':profitability_id' => $profitabilityId]);
        $costItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['profitability' => $profitability, 'cost_items' => $costItems];
    }

    public function getProfitabilitySummary($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT event_type, COUNT(*) as event_count,
                    SUM(total_revenue) as total_revenue, SUM(total_cost) as total_cost,
                    SUM(net_profit) as total_profit, SUM(attendance) as total_attendance,
                    AVG(net_margin_pct) as avg_margin
                FROM event_profitability
                WHERE tenant_id = :tenant_id AND event_date BETWEEN :date_from AND :date_to AND status IN ('FINALIZED','REVIEWED')";
        $params = [':tenant_id' => $tenantId, ':date_from' => $dateFrom, ':date_to' => $dateTo];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $sql .= " GROUP BY event_type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recalculate($profitabilityId)
    {
        $sql = "SELECT * FROM event_profitability WHERE profitability_id = :profitability_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':profitability_id' => $profitabilityId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) return;

        $sql = "SELECT * FROM event_cost_items WHERE profitability_id = :profitability_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':profitability_id' => $profitabilityId]);
        $costItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categoryTotals = [
            'COGS' => (float)$p['cogs'],
            'LABOR' => (float)$p['labor_cost'],
            'ARTIST' => (float)$p['artist_cost'],
            'PRODUCTION' => (float)$p['production_cost'],
            'MARKETING' => (float)$p['marketing_cost'],
            'OVERHEAD' => (float)$p['overhead_cost'],
            'OTHER' => (float)$p['other_cost'],
        ];

        foreach ($costItems as $ci) {
            $cat = $ci['cost_category'];
            if (isset($categoryTotals[$cat])) {
                $categoryTotals[$cat] += (float)$ci['amount'];
            }
        }

        $totalRevenue = (float)$p['ticket_revenue'] + (float)$p['fnb_revenue'] + (float)$p['bar_revenue'] + (float)$p['merch_revenue'] + (float)$p['other_revenue'];
        $totalCost = array_sum($categoryTotals);
        $grossProfit = $totalRevenue - $categoryTotals['COGS'];
        $netProfit = $totalRevenue - $totalCost;
        $attendance = (int)$p['attendance'];

        $sql = "UPDATE event_profitability SET
                    total_revenue = :total_rev,
                    total_cost = :total_cost,
                    gross_profit = :gross,
                    gross_margin_pct = :gross_pct,
                    net_profit = :net,
                    net_margin_pct = :net_pct,
                    revenue_per_head = :rev_head,
                    cost_per_head = :cost_head,
                    profit_per_head = :profit_head
                WHERE profitability_id = :profitability_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':profitability_id' => $profitabilityId,
            ':total_rev' => $totalRevenue,
            ':total_cost' => $totalCost,
            ':gross' => $grossProfit,
            ':gross_pct' => $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0,
            ':net' => $netProfit,
            ':net_pct' => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0,
            ':rev_head' => $attendance > 0 ? round($totalRevenue / $attendance, 2) : 0,
            ':cost_head' => $attendance > 0 ? round($totalCost / $attendance, 2) : 0,
            ':profit_head' => $attendance > 0 ? round($netProfit / $attendance, 2) : 0,
        ]);
    }
}
