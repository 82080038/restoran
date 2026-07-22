<?php

namespace App\Modules\POSBankReconciliation\Services;

use App\Core\Database;
use PDO;

class POSBankReconciliationService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getDeposits($tenantId, $branchId, $dateFrom = null, $dateTo = null, $status = null)
    {
        $sql = "SELECT * FROM pos_bank_deposits WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($dateFrom) {
            $sql .= " AND deposit_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND deposit_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY deposit_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDeposit($data)
    {
        $sql = "INSERT INTO pos_bank_deposits (tenant_id, branch_id, deposit_date, pos_sales_total, cash_sales_total, non_cash_sales_total, bank_deposit_amount, cash_drawer_counted, cash_drawer_expected, cash_variance, total_variance, notes)
                VALUES (:tenant_id, :branch_id, :deposit_date, :pos_sales_total, :cash_sales_total, :non_cash_sales_total, :bank_deposit_amount, :cash_drawer_counted, :cash_drawer_expected, :cash_variance, :total_variance, :notes)";
        $cashVariance = ($data['cash_drawer_counted'] ?? 0) - ($data['cash_drawer_expected'] ?? 0);
        $totalVariance = ($data['bank_deposit_amount'] ?? 0) - ($data['pos_sales_total'] ?? 0);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':deposit_date' => $data['deposit_date'],
            ':pos_sales_total' => $data['pos_sales_total'] ?? 0,
            ':cash_sales_total' => $data['cash_sales_total'] ?? 0,
            ':non_cash_sales_total' => $data['non_cash_sales_total'] ?? 0,
            ':bank_deposit_amount' => $data['bank_deposit_amount'] ?? 0,
            ':cash_drawer_counted' => $data['cash_drawer_counted'] ?? 0,
            ':cash_drawer_expected' => $data['cash_drawer_expected'] ?? 0,
            ':cash_variance' => $cashVariance,
            ':total_variance' => $totalVariance,
            ':notes' => $data['notes'] ?? null,
        ]);
        $depositId = $this->pdo->lastInsertId();

        $status = 'PENDING';
        if (abs($totalVariance) < 0.01 && abs($cashVariance) < 0.01) {
            $status = 'MATCHED';
        } elseif (abs($totalVariance) > 1.00 || abs($cashVariance) > 1.00) {
            $status = 'VARIANCE';
        }
        $this->updateDepositStatus($depositId, $status);

        return ['deposit_id' => $depositId, 'status' => $status, 'cash_variance' => $cashVariance, 'total_variance' => $totalVariance];
    }

    public function matchDeposit(int $depositId, int $tenantId, int $matchedBy): array
    {
        $statement = $this->pdo->prepare('SELECT cash_variance, total_variance, status FROM pos_bank_deposits WHERE deposit_id = :deposit_id AND tenant_id = :tenant_id');
        $statement->execute([':deposit_id' => $depositId, ':tenant_id' => $tenantId]);
        $deposit = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$deposit) {
            return ['success' => false, 'message' => 'Deposit not found'];
        }

        if (abs((float) $deposit['cash_variance']) >= 0.01 || abs((float) $deposit['total_variance']) >= 0.01) {
            return ['success' => false, 'message' => 'A deposit with a variance cannot be matched'];
        }

        $statement = $this->pdo->prepare("UPDATE pos_bank_deposits SET status = 'MATCHED', matched_by = :matched_by, matched_at = NOW() WHERE deposit_id = :deposit_id AND tenant_id = :tenant_id AND status IN ('PENDING', 'MATCHED')");
        $statement->execute([':deposit_id' => $depositId, ':tenant_id' => $tenantId, ':matched_by' => $matchedBy]);

        return ['success' => true];
    }

    public function resolveDeposit(int $depositId, int $tenantId, string $notes, int $resolvedBy): array
    {
        if (trim($notes) === '') {
            return ['success' => false, 'message' => 'Resolution notes are required'];
        }

        $statement = $this->pdo->prepare("UPDATE pos_bank_deposits SET status = 'RESOLVED', matched_by = :matched_by, matched_at = NOW(), notes = CONCAT(IFNULL(notes, ''), :notes) WHERE deposit_id = :deposit_id AND tenant_id = :tenant_id AND status IN ('PENDING', 'VARIANCE')");
        $statement->execute([
            ':deposit_id' => $depositId,
            ':tenant_id' => $tenantId,
            ':matched_by' => $resolvedBy,
            ':notes' => "\n[Resolved] " . trim($notes),
        ]);

        return $statement->rowCount() === 1
            ? ['success' => true]
            : ['success' => false, 'message' => 'Deposit not found or cannot be resolved'];
    }

    public function getVarianceReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT deposit_date, pos_sales_total, bank_deposit_amount, cash_drawer_counted, cash_drawer_expected, cash_variance, total_variance, status
                FROM pos_bank_deposits WHERE tenant_id = :tenant_id AND deposit_date BETWEEN :date_from AND :date_to";
        $params = [':tenant_id' => $tenantId, ':date_from' => $dateFrom, ':date_to' => $dateTo];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $sql .= " ORDER BY deposit_date";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalSales = array_sum(array_column($deposits, 'pos_sales_total'));
        $totalBank = array_sum(array_column($deposits, 'bank_deposit_amount'));
        $totalCashVariance = array_sum(array_column($deposits, 'cash_variance'));
        $totalVariance = array_sum(array_column($deposits, 'total_variance'));

        return [
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'daily_breakdown' => $deposits,
            'summary' => [
                'total_pos_sales' => $totalSales,
                'total_bank_deposits' => $totalBank,
                'total_cash_variance' => $totalCashVariance,
                'total_variance' => $totalVariance,
                'variance_pct' => $totalSales > 0 ? round(($totalVariance / $totalSales) * 100, 2) : 0,
            ],
        ];
    }

    public function addMerchantFee($data)
    {
        $netAmount = ($data['gross_amount'] ?? 0) - ($data['fee_amount'] ?? 0);
        $sql = "INSERT INTO merchant_fees (tenant_id, branch_id, order_id, transaction_date, payment_method, processor_name, gross_amount, fee_amount, fee_percentage, net_amount, external_transaction_id, metadata)
                VALUES (:tenant_id, :branch_id, :order_id, :transaction_date, :payment_method, :processor_name, :gross_amount, :fee_amount, :fee_percentage, :net_amount, :external_transaction_id, :metadata)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':order_id' => $data['order_id'] ?? null,
            ':transaction_date' => $data['transaction_date'],
            ':payment_method' => $data['payment_method'],
            ':processor_name' => $data['processor_name'],
            ':gross_amount' => $data['gross_amount'],
            ':fee_amount' => $data['fee_amount'] ?? 0,
            ':fee_percentage' => $data['fee_percentage'] ?? 0,
            ':net_amount' => $netAmount,
            ':external_transaction_id' => $data['external_transaction_id'] ?? null,
            ':metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ]);
        return ['fee_id' => $this->pdo->lastInsertId(), 'net_amount' => $netAmount];
    }

    public function getMerchantFees($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT * FROM merchant_fees WHERE tenant_id = :tenant_id AND transaction_date BETWEEN :date_from AND :date_to";
        $params = [':tenant_id' => $tenantId, ':date_from' => $dateFrom, ':date_to' => $dateTo];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $sql .= " ORDER BY transaction_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byProcessor = [];
        foreach ($fees as $f) {
            $p = $f['processor_name'];
            if (!isset($byProcessor[$p])) {
                $byProcessor[$p] = ['gross' => 0, 'fees' => 0, 'net' => 0, 'count' => 0];
            }
            $byProcessor[$p]['gross'] += $f['gross_amount'];
            $byProcessor[$p]['fees'] += $f['fee_amount'];
            $byProcessor[$p]['net'] += $f['net_amount'];
            $byProcessor[$p]['count']++;
        }

        return [
            'transactions' => $fees,
            'by_processor' => $byProcessor,
            'total_gross' => array_sum(array_column($fees, 'gross_amount')),
            'total_fees' => array_sum(array_column($fees, 'fee_amount')),
            'total_net' => array_sum(array_column($fees, 'net_amount')),
        ];
    }

    public function createEODCloseout($data)
    {
        $statement = $this->pdo->prepare("SELECT closeout_id FROM eod_closeouts WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND closeout_date = :closeout_date AND status = 'OPEN'");
        $statement->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':closeout_date' => $data['closeout_date'],
        ]);
        $existingCloseout = $statement->fetch(PDO::FETCH_ASSOC);
        if ($existingCloseout) {
            return ['success' => false, 'message' => 'An open closeout already exists for this branch and date'];
        }

        $sql = "INSERT INTO eod_closeouts (tenant_id, branch_id, closeout_date, opened_by, opening_cash, status)
                VALUES (:tenant_id, :branch_id, :closeout_date, :opened_by, :opening_cash, 'OPEN')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':closeout_date' => $data['closeout_date'],
            ':opened_by' => $data['opened_by'],
            ':opening_cash' => $data['opening_cash'] ?? 0,
        ]);
        return ['success' => true, 'closeout_id' => $this->pdo->lastInsertId()];
    }

    public function closeEODCloseout(int $closeoutId, int $tenantId, array $data): array
    {
        $expectedCash = ($data['opening_cash'] ?? 0) + ($data['cash_in'] ?? 0) - ($data['cash_out'] ?? 0);
        $cashVariance = ($data['counted_cash'] ?? 0) - $expectedCash;
        $status = abs($cashVariance) < 1.00 ? 'RECONCILED' : 'DISCREPANCY';

        $sql = "UPDATE eod_closeouts SET
                    closed_by = :closed_by,
                    closed_at = NOW(),
                    cash_in = :cash_in,
                    cash_out = :cash_out,
                    expected_cash = :expected_cash,
                    counted_cash = :counted_cash,
                    cash_variance = :cash_variance,
                    total_sales = :total_sales,
                    total_refunds = :total_refunds,
                    total_discounts = :total_discounts,
                    payment_breakdown = :payment_breakdown,
                    status = :status,
                    notes = :notes
                WHERE closeout_id = :closeout_id AND tenant_id = :tenant_id AND status = 'OPEN'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':closeout_id' => $closeoutId,
            ':tenant_id' => $tenantId,
            ':closed_by' => $data['closed_by'],
            ':cash_in' => $data['cash_in'] ?? 0,
            ':cash_out' => $data['cash_out'] ?? 0,
            ':expected_cash' => $expectedCash,
            ':counted_cash' => $data['counted_cash'] ?? 0,
            ':cash_variance' => $cashVariance,
            ':total_sales' => $data['total_sales'] ?? 0,
            ':total_refunds' => $data['total_refunds'] ?? 0,
            ':total_discounts' => $data['total_discounts'] ?? 0,
            ':payment_breakdown' => isset($data['payment_breakdown']) ? json_encode($data['payment_breakdown']) : null,
            ':status' => $status,
            ':notes' => $data['notes'] ?? null,
        ]);
        return $stmt->rowCount() === 1
            ? ['success' => true, 'closeout_id' => $closeoutId, 'status' => $status, 'cash_variance' => $cashVariance]
            : ['success' => false, 'message' => 'Open closeout not found'];
    }

    public function getEODCloseouts($tenantId, $branchId, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT * FROM eod_closeouts WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($dateFrom) {
            $sql .= " AND closeout_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND closeout_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        $sql .= " ORDER BY closeout_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updateDepositStatus($depositId, $status)
    {
        $sql = "UPDATE pos_bank_deposits SET status = :status WHERE deposit_id = :deposit_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':deposit_id' => $depositId, ':status' => $status]);
    }
}
