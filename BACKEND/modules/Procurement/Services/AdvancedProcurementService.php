<?php

namespace App\Modules\Procurement\Services;

use App\Core\Database;
use App\Core\Audit;

class AdvancedProcurementService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = new Audit();
    }

    /**
     * Generate purchase plan based on demand forecast
     */
    public function generatePurchasePlan($tenantId, $branchId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $planData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'plan_name' => $data->plan_name,
                'plan_type' => $data->plan_type ?? 'AUTO_GENERATED',
                'planning_period_start' => $data->planning_period_start,
                'planning_period_end' => $data->planning_period_end,
                'status' => 'DRAFT',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO purchase_plans (tenant_id, branch_id, plan_name, plan_type, planning_period_start, planning_period_end, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $planData['tenant_id'],
                $planData['branch_id'],
                $planData['plan_name'],
                $planData['plan_type'],
                $planData['planning_period_start'],
                $planData['planning_period_end'],
                $planData['status'],
                $planData['created_by']
            ]);

            $planId = $this->db->lastInsertId();

            // Generate purchase plan items based on stock forecast
            $this->generatePlanItems($planId, $tenantId, $branchId, $data->planning_period_start, $data->planning_period_end, $userId);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, $branchId, $userId, 'purchase_plan', $planId, 'CREATE', json_encode($planData));

            return [
                'success' => true,
                'message' => 'Purchase plan generated',
                'plan_id' => $planId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to generate purchase plan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate plan items based on stock forecast
     */
    private function generatePlanItems($planId, $tenantId, $branchId, $periodStart, $periodEnd, $userId)
    {
        // Get items that need replenishment
        $sql = "SELECT ii.id as inventory_item_id, ii.item_name, ii.current_stock, ii.minimum_stock, ii.reorder_point,
                        ii.unit_of_measure, ii.lead_time_days,
                        (ii.minimum_stock - ii.current_stock) as suggested_quantity
                FROM inventory_items ii
                WHERE ii.tenant_id = ? AND ii.branch_id = ?
                AND ii.current_stock < ii.minimum_stock
                AND ii.deleted_at IS NULL";

        $items = $this->db->query($sql, [$tenantId, $branchId])->fetchAll();

        foreach ($items as $item) {
            // Calculate demand forecast (simplified - using historical average)
            $forecastQuantity = $this->calculateDemandForecast($item['inventory_item_id'], $tenantId, $branchId, $periodStart, $periodEnd);
            
            // Calculate safety stock (20% of forecast)
            $safetyStock = $forecastQuantity * 0.2;
            
            // Calculate total required quantity
            $totalRequired = $item['suggested_quantity'] + $forecastQuantity + $safetyStock;
            
            // Get preferred supplier
            $supplierId = $this->getPreferredSupplier($item['inventory_item_id'], $tenantId);
            
            // Get estimated cost
            $estimatedCost = $this->getEstimatedCost($item['inventory_item_id'], $supplierId, $totalRequired);

            $itemSql = "INSERT INTO purchase_plan_items (purchase_plan_id, inventory_item_id, supplier_id, current_stock, minimum_stock, forecast_demand, safety_stock, suggested_quantity, estimated_cost, priority, created_by, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($itemSql);
            $stmt->execute([
                $planId,
                $item['inventory_item_id'],
                $supplierId,
                $item['current_stock'],
                $item['minimum_stock'],
                $forecastQuantity,
                $safetyStock,
                $totalRequired,
                $estimatedCost,
                $item['current_stock'] < $item['reorder_point'] ? 'HIGH' : 'MEDIUM',
                $userId
            ]);
        }
    }

    /**
     * Calculate demand forecast based on historical usage
     */
    private function calculateDemandForecast($inventoryItemId, $tenantId, $branchId, $periodStart, $periodEnd)
    {
        // Calculate days in planning period
        $startDate = new DateTime($periodStart);
        $endDate = new DateTime($periodEnd);
        $daysInPeriod = $startDate->diff($endDate)->days;

        // Get historical usage for last 30 days
        $historicalSql = "SELECT SUM(quantity) as total_usage, COUNT(DISTINCT DATE(transaction_date)) as days_with_usage
                         FROM stock_movements
                         WHERE inventory_item_id = ? AND tenant_id = ? AND branch_id = ?
                         AND movement_type = 'OUT'
                         AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

        $historical = $this->db->query($historicalSql, [$inventoryItemId, $tenantId, $branchId])->fetch();

        if (!$historical || $historical['total_usage'] == 0) {
            return 0;
        }

        // Calculate daily average usage
        $dailyAverage = $historical['total_usage'] / max(1, $historical['days_with_usage']);

        // Forecast for planning period
        return round($dailyAverage * $daysInPeriod, 2);
    }

    /**
     * Get preferred supplier for item
     */
    private function getPreferredSupplier($inventoryItemId, $tenantId)
    {
        $sql = "SELECT sp.supplier_id, sp.is_preferred
                FROM supplier_products sp
                WHERE sp.inventory_item_id = ? AND sp.tenant_id = ?
                AND sp.is_preferred = 1
                LIMIT 1";

        $result = $this->db->query($sql, [$inventoryItemId, $tenantId])->fetch();

        return $result['supplier_id'] ?? null;
    }

    /**
     * Get estimated cost from supplier
     */
    private function getEstimatedCost($inventoryItemId, $supplierId, $quantity)
    {
        if (!$supplierId) {
            return 0;
        }

        $sql = "SELECT unit_price FROM supplier_products
                WHERE inventory_item_id = ? AND supplier_id = ?
                LIMIT 1";

        $result = $this->db->query($sql, [$inventoryItemId, $supplierId])->fetch();

        if (!$result) {
            return 0;
        }

        return $result['unit_price'] * $quantity;
    }

    /**
     * Get purchase plans
     */
    public function getPurchasePlans($tenantId, $branchId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($branchId) {
            $where .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND planning_period_start >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND planning_period_end <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT pp.*, 
                    (SELECT COUNT(*) FROM purchase_plan_items ppi WHERE ppi.purchase_plan_id = pp.id) as item_count,
                    (SELECT SUM(estimated_cost) FROM purchase_plan_items ppi WHERE ppi.purchase_plan_id = pp.id) as total_estimated_cost,
                    u.username as created_by_name
                FROM purchase_plans pp
                LEFT JOIN users u ON pp.created_by = u.id
                {$where}
                ORDER BY pp.planning_period_start DESC, pp.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Three-way matching: PO vs DO vs Invoice
     */
    public function performThreeWayMatch($tenantId, $userId, $data)
    {
        try {
            $purchaseOrderId = $data->purchase_order_id;
            $goodsReceiptId = $data->goods_receipt_id;
            $invoiceId = $data->invoice_id;

            // Get PO details
            $poSql = "SELECT po.*, 
                        (SELECT SUM(quantity * unit_price) FROM purchase_order_items WHERE purchase_order_id = po.id) as po_total
                      FROM purchase_orders po
                      WHERE po.id = ? AND po.tenant_id = ?";
            $po = $this->db->query($poSql, [$purchaseOrderId, $tenantId])->fetch();

            if (!$po) {
                return ['success' => false, 'message' => 'Purchase order not found'];
            }

            // Get Goods Receipt details
            $grSql = "SELECT gr.*, 
                        (SELECT SUM(quantity_received * unit_price) FROM goods_receipt_items WHERE goods_receipt_id = gr.id) as gr_total
                      FROM goods_receipts gr
                      WHERE gr.id = ? AND gr.tenant_id = ?";
            $gr = $this->db->query($grSql, [$goodsReceiptId, $tenantId])->fetch();

            if (!$gr) {
                return ['success' => false, 'message' => 'Goods receipt not found'];
            }

            // Get Invoice details
            $invoiceSql = "SELECT inv.*, 
                            (SELECT SUM(quantity * unit_price) FROM invoice_items WHERE invoice_id = inv.id) as invoice_total
                          FROM supplier_invoices inv
                          WHERE inv.id = ? AND inv.tenant_id = ?";
            $invoice = $this->db->query($invoiceSql, [$invoiceId, $tenantId])->fetch();

            if (!$invoice) {
                return ['success' => false, 'message' => 'Invoice not found'];
            }

            // Perform matching
            $matchResult = $this->compareDocuments($po, $gr, $invoice);

            // Create match record
            $this->db->beginTransaction();

            $matchData = [
                'tenant_id' => $tenantId,
                'purchase_order_id' => $purchaseOrderId,
                'goods_receipt_id' => $goodsReceiptId,
                'invoice_id' => $invoiceId,
                'match_status' => $matchResult['status'],
                'variance_amount' => $matchResult['variance_amount'],
                'variance_percentage' => $matchResult['variance_percentage'],
                'match_details' => json_encode($matchResult['details']),
                'matched_by' => $userId
            ];

            $matchSql = "INSERT INTO three_way_matches (tenant_id, purchase_order_id, goods_receipt_id, invoice_id, match_status, variance_amount, variance_percentage, match_details, matched_by, matched_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($matchSql);
            $stmt->execute([
                $matchData['tenant_id'],
                $matchData['purchase_order_id'],
                $matchData['goods_receipt_id'],
                $matchData['invoice_id'],
                $matchData['match_status'],
                $matchData['variance_amount'],
                $matchData['variance_percentage'],
                $matchData['match_details'],
                $matchData['matched_by']
            ]);

            $matchId = $this->db->lastInsertId();

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'three_way_match', $matchId, 'CREATE', json_encode($matchData));

            return [
                'success' => true,
                'message' => 'Three-way match completed',
                'match_id' => $matchId,
                'match_result' => $matchResult
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to perform three-way match: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Compare PO, GR, and Invoice documents
     */
    private function compareDocuments($po, $gr, $invoice)
    {
        $details = [];
        $varianceAmount = 0;
        $matchStatus = 'MATCHED';

        // Compare PO vs GR
        $poVsGrVariance = abs($po['po_total'] - $gr['gr_total']);
        $poVsGrVariancePct = $po['po_total'] > 0 ? ($poVsGrVariance / $po['po_total']) * 100 : 0;

        $details['po_vs_gr'] = [
            'po_total' => $po['po_total'],
            'gr_total' => $gr['gr_total'],
            'variance_amount' => $poVsGrVariance,
            'variance_percentage' => round($poVsGrVariancePct, 2)
        ];

        if ($poVsGrVariancePct > 5) {
            $matchStatus = 'VARIANCE_DETECTED';
        }

        // Compare GR vs Invoice
        $grVsInvoiceVariance = abs($gr['gr_total'] - $invoice['invoice_total']);
        $grVsInvoiceVariancePct = $gr['gr_total'] > 0 ? ($grVsInvoiceVariance / $gr['gr_total']) * 100 : 0;

        $details['gr_vs_invoice'] = [
            'gr_total' => $gr['gr_total'],
            'invoice_total' => $invoice['invoice_total'],
            'variance_amount' => $grVsInvoiceVariance,
            'variance_percentage' => round($grVsInvoiceVariancePct, 2)
        ];

        if ($grVsInvoiceVariancePct > 5) {
            $matchStatus = 'VARIANCE_DETECTED';
        }

        // Compare PO vs Invoice
        $poVsInvoiceVariance = abs($po['po_total'] - $invoice['invoice_total']);
        $poVsInvoiceVariancePct = $po['po_total'] > 0 ? ($poVsInvoiceVariance / $po['po_total']) * 100 : 0;

        $details['po_vs_invoice'] = [
            'po_total' => $po['po_total'],
            'invoice_total' => $invoice['invoice_total'],
            'variance_amount' => $poVsInvoiceVariance,
            'variance_percentage' => round($poVsInvoiceVariancePct, 2)
        ];

        if ($poVsInvoiceVariancePct > 5) {
            $matchStatus = 'VARIANCE_DETECTED';
        }

        // Calculate total variance
        $varianceAmount = max($poVsGrVariance, $grVsInvoiceVariance, $poVsInvoiceVariance);
        $variancePercentage = max($poVsGrVariancePct, $grVsInvoiceVariancePct, $poVsInvoiceVariancePct);

        if ($variancePercentage > 10) {
            $matchStatus = 'REQUIRES_REVIEW';
        }

        return [
            'status' => $matchStatus,
            'variance_amount' => $varianceAmount,
            'variance_percentage' => round($variancePercentage, 2),
            'details' => $details
        ];
    }

    /**
     * Get three-way matches
     */
    public function getThreeWayMatches($tenantId, $status, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE twm.tenant_id = ?";
        
        if ($status) {
            $where .= " AND twm.match_status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND twm.matched_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND twm.matched_at <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT twm.*, 
                    po.po_number,
                    gr.gr_number,
                    inv.invoice_number,
                    u.username as matched_by_name
                FROM three_way_matches twm
                LEFT JOIN purchase_orders po ON twm.purchase_order_id = po.id
                LEFT JOIN goods_receipts gr ON twm.goods_receipt_id = gr.id
                LEFT JOIN supplier_invoices inv ON twm.invoice_id = inv.id
                LEFT JOIN users u ON twm.matched_by = u.id
                {$where}
                ORDER BY twm.matched_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Stock forecasting using moving average
     */
    public function forecastStock($tenantId, $branchId, $data)
    {
        $forecastDays = $data->forecast_days ?? 30;
        $inventoryItemId = $data->inventory_item_id ?? null;

        $params = [$tenantId, $branchId];
        $where = "WHERE sm.tenant_id = ? AND sm.branch_id = ?";
        
        if ($inventoryItemId) {
            $where .= " AND sm.inventory_item_id = ?";
            $params[] = $inventoryItemId;
        }

        // Get historical stock movements for last 90 days
        $sql = "SELECT sm.inventory_item_id, ii.item_name, ii.current_stock, ii.minimum_stock,
                        DATE(sm.transaction_date) as movement_date,
                        SUM(CASE WHEN sm.movement_type = 'IN' THEN sm.quantity ELSE 0 END) as daily_in,
                        SUM(CASE WHEN sm.movement_type = 'OUT' THEN sm.quantity ELSE 0 END) as daily_out
                FROM stock_movements sm
                LEFT JOIN inventory_items ii ON sm.inventory_item_id = ii.id
                {$where}
                AND sm.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                GROUP BY sm.inventory_item_id, ii.item_name, ii.current_stock, ii.minimum_stock, DATE(sm.transaction_date)
                ORDER BY sm.inventory_item_id, movement_date";

        $movements = $this->db->query($sql, $params)->fetchAll();

        $forecasts = [];

        // Group by inventory item
        $groupedMovements = [];
        foreach ($movements as $movement) {
            $itemId = $movement['inventory_item_id'];
            if (!isset($groupedMovements[$itemId])) {
                $groupedMovements[$itemId] = [
                    'item_name' => $movement['item_name'],
                    'current_stock' => $movement['current_stock'],
                    'minimum_stock' => $movement['minimum_stock'],
                    'movements' => []
                ];
            }
            $groupedMovements[$itemId]['movements'][] = $movement;
        }

        // Calculate forecast for each item
        foreach ($groupedMovements as $itemId => $itemData) {
            $avgDailyOut = 0;
            $avgDailyIn = 0;
            $movementCount = count($itemData['movements']);

            if ($movementCount > 0) {
                $totalOut = array_sum(array_column($itemData['movements'], 'daily_out'));
                $totalIn = array_sum(array_column($itemData['movements'], 'daily_in'));
                $avgDailyOut = $totalOut / $movementCount;
                $avgDailyIn = $totalIn / $movementCount;
            }

            // Forecast stock for each day
            $projectedStock = $itemData['current_stock'];
            $dailyForecasts = [];
            $stockoutDate = null;

            for ($day = 1; $day <= $forecastDays; $day++) {
                $projectedStock = $projectedStock - $avgDailyOut + $avgDailyIn;
                $forecastDate = date('Y-m-d', strtotime("+$day days"));
                
                $dailyForecasts[] = [
                    'date' => $forecastDate,
                    'projected_stock' => max(0, round($projectedStock, 2))
                ];

                if ($stockoutDate === null && $projectedStock < $itemData['minimum_stock']) {
                    $stockoutDate = $forecastDate;
                }
            }

            $forecasts[] = [
                'inventory_item_id' => $itemId,
                'item_name' => $itemData['item_name'],
                'current_stock' => $itemData['current_stock'],
                'minimum_stock' => $itemData['minimum_stock'],
                'average_daily_consumption' => round($avgDailyOut, 2),
                'average_daily_replenishment' => round($avgDailyIn, 2),
                'stockout_prediction_date' => $stockoutDate,
                'days_until_stockout' => $stockoutDate ? (new DateTime($stockoutDate))->diff(new DateTime())->days : null,
                'daily_forecast' => $dailyForecasts
            ];
        }

        return [
            'success' => true,
            'forecast_days' => $forecastDays,
            'data' => $forecasts
        ];
    }

    /**
     * Get procurement summary
     */
    public function getSummary($tenantId, $branchId)
    {
        // Active purchase plans
        $activePlansSql = "SELECT COUNT(*) as count FROM purchase_plans WHERE tenant_id = ? AND branch_id = ? AND status = 'DRAFT'";
        $activePlans = $this->db->query($activePlansSql, [$tenantId, $branchId])->fetch();

        // Pending three-way matches
        $pendingMatchesSql = "SELECT COUNT(*) as count FROM three_way_matches WHERE tenant_id = ? AND match_status = 'REQUIRES_REVIEW'";
        $pendingMatches = $this->db->query($pendingMatchesSql, [$tenantId])->fetch();

        // Items below minimum stock
        $lowStockSql = "SELECT COUNT(*) as count FROM inventory_items WHERE tenant_id = ? AND branch_id = ? AND current_stock < minimum_stock AND deleted_at IS NULL";
        $lowStock = $this->db->query($lowStockSql, [$tenantId, $branchId])->fetch();

        // Forecasted stockouts in next 7 days
        $stockoutSql = "SELECT COUNT(*) as count FROM inventory_items WHERE tenant_id = ? AND branch_id = ? AND current_stock < (minimum_stock * 1.5) AND deleted_at IS NULL";
        $imminentStockouts = $this->db->query($stockoutSql, [$tenantId, $branchId])->fetch();

        return [
            'active_purchase_plans' => $activePlans['count'] ?? 0,
            'pending_three_way_matches' => $pendingMatches['count'] ?? 0,
            'items_below_minimum_stock' => $lowStock['count'] ?? 0,
            'imminent_stockouts' => $imminentStockouts['count'] ?? 0
        ];
    }
}
