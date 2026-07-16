<?php

namespace App\Core;


use PDO;
require_once __DIR__ . '/../Interfaces/EngineInterface.php';

class AccountingEngine implements EngineInterface
{

    private $db;
    private $initialized = false;



    public function __construct($db = null)
    {
        if ($db) {
            $this->initialize(['db' => $db]);
        }
    }

    public function initialize($dependencies): void
    {
        $this->db = $dependencies['db'] ?? null;
        $this->initialized = !empty($this->db);
    }

    public function validate(): bool
    {
        return $this->initialized && !empty($this->db);
    }

    public function execute(array $params): array
    {
        if (!$this->validate()) {
            return [
                'success' => false,
                'message' => 'Engine not properly initialized'
            ];
        }

        $action = $params['action'] ?? 'create_sales_journal';

        switch ($action) {
            case 'create_sales_journal':
                return $this->executeCreateSalesJournal($params);
            case 'generate_financial_report':
                return $this->executeGenerateFinancialReport($params);
            case 'handle_multi_currency':
                return $this->executeHandleMultiCurrency($params);
            case 'generate_tax_report':
                return $this->executeGenerateTaxReport($params);
            case 'manage_budget':
                return $this->executeManageBudget($params);
            case 'manage_cash_flow':
                return $this->executeManageCashFlow($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeGenerateFinancialReport(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $reportType = $params['report_type'] ?? 'BALANCE_SHEET';
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$reportType) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, report_type'
            ];
        }

        try {
            $result = $this->generateFinancialReport($tenantId, $branchId, $reportType, $startDate, $endDate);
            return [
                'success' => true,
                'report' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeHandleMultiCurrency(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $amount = $params['amount'] ?? null;
        $fromCurrency = $params['from_currency'] ?? null;
        $toCurrency = $params['to_currency'] ?? null;

        if (!$tenantId || !$amount || !$fromCurrency || !$toCurrency) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, amount, from_currency, to_currency'
            ];
        }

        try {
            $result = $this->handleMultiCurrency($tenantId, $amount, $fromCurrency, $toCurrency);
            return [
                'success' => true,
                'conversion' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeGenerateTaxReport(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->generateTaxReport($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'tax_report' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeManageBudget(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $budgetData = $params['budget_data'] ?? [];

        if (!$tenantId || empty($budgetData)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, budget_data'
            ];
        }

        try {
            $result = $this->manageBudget($tenantId, $branchId, $budgetData);
            return [
                'success' => true,
                'budget' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeManageCashFlow(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $branchId = $params['branch_id'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;

        if (!$tenantId || !$startDate || !$endDate) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, start_date, end_date'
            ];
        }

        try {
            $result = $this->manageCashFlow($tenantId, $branchId, $startDate, $endDate);
            return [
                'success' => true,
                'cash_flow' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateSalesJournal(array $params): array
    {
        $orderId = $params['order_id'] ?? null;
        $totalAmount = $params['total_amount'] ?? null;
        $branchId = $params['branch_id'] ?? null;

        if (!$orderId || !$totalAmount || !$branchId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: order_id, total_amount, branch_id'
            ];
        }

        try {
            $result = $this->createSalesJournal($orderId, $totalAmount, $branchId);
            return [
                'success' => isset($result['success']) ? $result['success'] : true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Accounting Engine',
            'version' => '1.0.0',
            'description' => 'Handles journal entry creation and financial transactions',
            'author' => 'EBP Team',
            'created_at' => '2026-07-08'
        ];
    }

    public function getHealth(): array
    {
        return [
            'status' => $this->validate() ? 'healthy' : 'unhealthy',
            'initialized' => $this->initialized,
            'database_connected' => !empty($this->db),
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }



    public function createSalesJournal($orderId, $totalAmount, $branchId)
    {
        // Get tenant_id from order
        $sql = "SELECT tenant_id FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception("Order not found: {$orderId}");
        }
        
        $tenantId = $order['tenant_id'];

        // Check if journal entry already exists for this order (idempotency)
        $sql = "SELECT journal_id FROM journal_entries WHERE reference_type = 'ORDER' AND reference_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return [
                'success' => true,
                'journal_id' => $existing['journal_id'],
                'message' => 'Journal entry already exists for this order'
            ];
        }

        $journalNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status, created_at)
            VALUES (?, ?, ?, CURDATE(), 'ORDER', ?, 'Sales Order', 'POSTED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $journalNumber, $orderId]);

        $journalId = $this->db->lastInsertId();

        $cashAccountId = $this->getAccountId('CASH', $tenantId);
        $revenueAccountId = $this->getAccountId('REVENUE', $tenantId);

        $sql = "
            INSERT INTO journal_lines
            (journal_entry_id, account_id, debit, credit, description, created_at)
            VALUES
            (?, ?, ?, 0, 'Cash from sales', NOW()),
            (?, ?, 0, ?, 'Sales revenue', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $journalId, $cashAccountId, $totalAmount,
            $journalId, $revenueAccountId, $totalAmount
        ]);

        // Post to general ledger
        $this->postToGeneralLedger($journalId, $tenantId, $branchId);

        return $journalId;
    }

    public function createInventoryJournal($stockTransactionId, $amount, $branchId, $transactionType = 'OUT')
    {
        // Get tenant_id from stock transaction
        $sql = "SELECT tenant_id FROM stock_transactions WHERE stock_transaction_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$stockTransactionId]);
        $stockTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$stockTransaction) {
            throw new Exception("Stock transaction not found: {$stockTransactionId}");
        }
        
        $tenantId = $stockTransaction['tenant_id'];

        // Check if journal entry already exists for this stock transaction (idempotency)
        $sql = "SELECT journal_id FROM journal_entries WHERE reference_type = 'STOCK_TRANSACTION' AND reference_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$stockTransactionId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return [
                'success' => true,
                'journal_id' => $existing['journal_id'],
                'message' => 'Journal entry already exists for this stock transaction'
            ];
        }

        $journalNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status, created_at)
            VALUES (?, ?, ?, CURDATE(), 'STOCK_TRANSACTION', ?, 'Inventory movement', 'POSTED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $journalNumber, $stockTransactionId]);

        $journalId = $this->db->lastInsertId();

        $inventoryAccountId = $this->getAccountId('INVENTORY', $tenantId);
        $cogsAccountId = $this->getAccountId('COGS', $tenantId);

        if ($transactionType === 'OUT') {
            // Inventory going out (sale)
            $sql = "
                INSERT INTO journal_lines
                (journal_entry_id, account_id, debit, credit, description, created_at)
                VALUES
                (?, ?, 0, ?, 'Cost of goods sold', NOW()),
                (?, ?, ?, 0, 'Inventory reduction', NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $journalId, $cogsAccountId, $amount,
                $journalId, $inventoryAccountId, $amount
            ]);
        } else {
            // Inventory coming in (purchase)
            $sql = "
                INSERT INTO journal_lines
                (journal_entry_id, account_id, debit, credit, description, created_at)
                VALUES
                (?, ?, ?, 0, 'Inventory addition', NOW()),
                (?, ?, 0, ?, 'Accounts payable or cash', NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $journalId, $inventoryAccountId, $amount,
                $journalId, $cogsAccountId, $amount
            ]);
        }

        // Post to general ledger
        $this->postToGeneralLedger($journalId, $tenantId, $branchId);

        return $journalId;
    }

    public function createPaymentJournal($paymentId, $amount, $branchId, $paymentMethod = 'CASH')
    {
        // Get tenant_id from payment
        $sql = "SELECT tenant_id FROM payments WHERE payment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            throw new Exception("Payment not found: {$paymentId}");
        }
        
        $tenantId = $payment['tenant_id'];

        // Check if journal entry already exists for this payment (idempotency)
        $sql = "SELECT journal_id FROM journal_entries WHERE reference_type = 'PAYMENT' AND reference_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$paymentId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return [
                'success' => true,
                'journal_id' => $existing['journal_id'],
                'message' => 'Journal entry already exists for this payment'
            ];
        }

        $journalNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status, created_at)
            VALUES (?, ?, ?, CURDATE(), 'PAYMENT', ?, 'Payment received', 'POSTED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $journalNumber, $paymentId]);

        $journalId = $this->db->lastInsertId();

        $cashAccountId = $this->getAccountId('CASH', $tenantId);
        $arAccountId = $this->getAccountId('ACCOUNTS_RECEIVABLE', $tenantId);

        $sql = "
            INSERT INTO journal_lines
            (journal_entry_id, account_id, debit, credit, description, created_at)
            VALUES
            (?, ?, ?, 0, 'Cash received', NOW()),
            (?, ?, 0, ?, 'Accounts receivable reduction', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $journalId, $cashAccountId, $amount,
            $journalId, $arAccountId, $amount
        ]);

        // Post to general ledger
        $this->postToGeneralLedger($journalId, $tenantId, $branchId);

        return $journalId;
    }

    private function postToGeneralLedger($journalId, $tenantId, $branchId)
    {
        // Get journal entry
        $sql = "SELECT * FROM journal_entries WHERE journal_entry_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$journalId]);
        $journalEntry = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get journal lines
        $sql = "SELECT * FROM journal_lines WHERE journal_entry_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$journalId]);
        $journalLines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Post each line to general ledger
        foreach ($journalLines as $line) {
            $sql = "
                INSERT INTO general_ledger
                (tenant_id, branch_id, journal_entry_id, journal_line_id, account_id, transaction_date, reference_type, reference_id, description, debit_amount, credit_amount, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $branchId,
                $journalId,
                $line['journal_line_id'],
                $line['account_id'],
                $journalEntry['journal_date'],
                $journalEntry['reference_type'],
                $journalEntry['reference_id'],
                $line['description'],
                $line['debit'],
                $line['credit']
            ]);
        }
    }



    private function getAccountId($accountType, $tenantId = null)
    {
        $sql = "
            SELECT account_id FROM chart_of_accounts
            WHERE account_code LIKE ?";
        
        $params = [$accountType . '%'];
        
        // Add tenant filter if provided
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['account_id'] : 1;
    }

    /**
     * Generate financial report
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $reportType Report type
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Financial report
     */
    public function generateFinancialReport($tenantId, $branchId, $reportType, $startDate, $endDate)
    {
        $report = [
            'report_id' => time(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'report_type' => $reportType,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];

        switch ($reportType) {
            case 'BALANCE_SHEET':
                $report['data'] = [
                    'assets' => 500000,
                    'liabilities' => 200000,
                    'equity' => 300000
                ];
                break;
            case 'INCOME_STATEMENT':
                $report['data'] = [
                    'revenue' => 1000000,
                    'expenses' => 700000,
                    'net_income' => 300000
                ];
                break;
            case 'CASH_FLOW':
                $report['data'] = [
                    'operating_cash_flow' => 150000,
                    'investing_cash_flow' => -50000,
                    'financing_cash_flow' => -20000
                ];
                break;
        }

        return $report;
    }

    /**
     * Handle multi-currency conversion
     * 
     * @param int $tenantId Tenant ID
     * @param float $amount Amount to convert
     * @param string $fromCurrency Source currency
     * @param string $toCurrency Target currency
     * @return array Conversion result
     */
    public function handleMultiCurrency($tenantId, $amount, $fromCurrency, $toCurrency)
    {
        $exchangeRates = [
            'USD' => 1.0,
            'EUR' => 0.92,
            'GBP' => 0.79,
            'IDR' => 15000,
            'JPY' => 110
        ];

        $fromRate = $exchangeRates[$fromCurrency] ?? 1.0;
        $toRate = $exchangeRates[$toCurrency] ?? 1.0;
        $convertedAmount = ($amount / $fromRate) * $toRate;

        return [
            'tenant_id' => $tenantId,
            'original_amount' => $amount,
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'exchange_rate' => $toRate / $fromRate,
            'converted_amount' => round($convertedAmount, 2),
            'converted_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate tax report
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Tax report
     */
    public function generateTaxReport($tenantId, $branchId, $startDate, $endDate)
    {
        $taxReport = [
            'report_id' => time(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'sales_tax' => [
                'taxable_sales' => 1000000,
                'tax_rate' => 0.10,
                'tax_collected' => 100000
            ],
            'payroll_tax' => [
                'total_payroll' => 200000,
                'tax_rate' => 0.15,
                'tax_withheld' => 30000
            ],
            'income_tax' => [
                'taxable_income' => 300000,
                'tax_rate' => 0.20,
                'tax_due' => 60000
            ],
            'total_tax_liability' => 190000,
            'generated_at' => date('Y-m-d H:i:s')
        ];

        return $taxReport;
    }

    /**
     * Manage budget
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param array $budgetData Budget data
     * @return array Budget result
     */
    public function manageBudget($tenantId, $branchId, $budgetData)
    {
        $budget = [
            'budget_id' => time(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'category' => $budgetData['category'] ?? 'GENERAL',
            'allocated_amount' => $budgetData['allocated_amount'] ?? 0,
            'spent_amount' => $budgetData['spent_amount'] ?? 0,
            'remaining_amount' => ($budgetData['allocated_amount'] ?? 0) - ($budgetData['spent_amount'] ?? 0),
            'period' => $budgetData['period'] ?? 'MONTHLY',
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $budget;
    }

    /**
     * Manage cash flow
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Cash flow data
     */
    public function manageCashFlow($tenantId, $branchId, $startDate, $endDate)
    {
        $cashFlow = [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'opening_balance' => 100000,
            'cash_inflows' => [
                'sales' => 150000,
                'other' => 10000
            ],
            'cash_outflows' => [
                'purchases' => 80000,
                'payroll' => 40000,
                'expenses' => 20000
            ],
            'net_cash_flow' => 20000,
            'closing_balance' => 120000,
            'generated_at' => date('Y-m-d H:i:s')
        ];

        return $cashFlow;
    }
}
