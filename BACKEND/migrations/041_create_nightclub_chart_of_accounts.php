<?php

declare(strict_types=1);

/**
 * Migration 041: Create Chart of Accounts for Discotheque/Nightclub tenants
 *
 * Inserts a complete CoA template for nightclub operations:
 * - ASSET: Cash, Bank, Inventory (Beverages), VIP Booth Equipment
 * - LIABILITY: Accounts Payable, Accrued DJ Fees, Customer Deposits
 * - EQUITY: Owner's Equity, Retained Earnings
 * - REVENUE: Entrance Fee Revenue, Bottle Service Revenue, Bar Revenue,
 *            Table Reservation Revenue, Guest List Revenue
 * - EXPENSE: COGS - Beverages, DJ Fees, Security Staff, Marketing,
 *            Rent, Utilities, Insurance
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

// Find all DISCOTHEQUE tenants
$stmt = $pdo->query("SELECT tenant_id, tenant_name FROM tenants WHERE business_type = 'DISCOTHEQUE' AND status = 'ACTIVE'");
$tenants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

if (empty($tenants)) {
    echo "No DISCOTHEQUE tenants found. Skipping CoA creation.\n";
    echo "Migration 041 complete. No accounts created.\n";
    exit(0);
}

$accounts = [
    // ASSETS
    ['1000', 'Cash on Hand', 'ASSET', null],
    ['1010', 'Cash Register', 'ASSET', null],
    ['1020', 'Bank Account', 'ASSET', null],
    ['1100', 'Inventory - Beverages', 'ASSET', null],
    ['1110', 'Inventory - Bar Supplies', 'ASSET', null],
    ['1200', 'Sound & Lighting Equipment', 'ASSET', null],
    ['1210', 'Furniture & Fixtures', 'ASSET', null],
    ['1300', 'Accounts Receivable', 'ASSET', null],
    ['1400', 'Prepaid Expenses', 'ASSET', null],

    // LIABILITIES
    ['2000', 'Accounts Payable', 'LIABILITY', null],
    ['2010', 'Accrued DJ Fees', 'LIABILITY', null],
    ['2020', 'Accrued Staff Wages', 'LIABILITY', null],
    ['2100', 'Customer Deposits (Bottle Service)', 'LIABILITY', null],
    ['2200', 'Tax Payable', 'LIABILITY', null],
    ['2300', 'Service Charge Payable', 'LIABILITY', null],

    // EQUITY
    ['3000', "Owner's Equity", 'EQUITY', null],
    ['3100', 'Retained Earnings', 'EQUITY', null],
    ['3200', 'Current Year Profit/Loss', 'EQUITY', null],

    // REVENUE - Nightclub specific
    ['4000', 'Entrance Fee Revenue', 'REVENUE', null],
    ['4010', 'Early Bird Ticket Revenue', 'REVENUE', null],
    ['4100', 'Bottle Service Revenue', 'REVENUE', null],
    ['4110', 'VIP Booth Revenue', 'REVENUE', null],
    ['4200', 'Bar Revenue (Drinks)', 'REVENUE', null],
    ['4210', 'Food Revenue', 'REVENUE', null],
    ['4300', 'Table Reservation Revenue', 'REVENUE', null],
    ['4400', 'Guest List Revenue (Discounted)', 'REVENUE', null],
    ['4500', 'Event Ticket Revenue', 'REVENUE', null],
    ['4600', 'Coat Check Revenue', 'REVENUE', null],
    ['4700', 'Other Revenue', 'REVENUE', null],

    // EXPENSE - Nightclub specific
    ['5000', 'COGS - Beverages', 'EXPENSE', null],
    ['5010', 'COGS - Food', 'EXPENSE', null],
    ['5020', 'COGS - Bar Supplies', 'EXPENSE', null],
    ['5100', 'DJ Fees & Entertainment', 'EXPENSE', null],
    ['5110', 'Live Performance Fees', 'EXPENSE', null],
    ['5200', 'Security Staff Wages', 'EXPENSE', null],
    ['5210', 'Bar Staff Wages', 'EXPENSE', null],
    ['5220', 'Waitstaff Wages', 'EXPENSE', null],
    ['5230', 'Management Salaries', 'EXPENSE', null],
    ['5300', 'Marketing & Promotion', 'EXPENSE', null],
    ['5310', 'Social Media Advertising', 'EXPENSE', null],
    ['5400', 'Rent', 'EXPENSE', null],
    ['5410', 'Utilities (Electricity, Water)', 'EXPENSE', null],
    ['5420', 'Sound & Lighting Maintenance', 'EXPENSE', null],
    ['5500', 'Insurance', 'EXPENSE', null],
    ['5600', 'Licenses & Permits', 'EXPENSE', null],
    ['5700', 'Cleaning & Sanitation', 'EXPENSE', null],
    ['5800', 'Depreciation - Equipment', 'EXPENSE', null],
    ['5900', 'Miscellaneous Expenses', 'EXPENSE', null],
];

$totalCreated = 0;
foreach ($tenants as $tenant) {
    $tenantId = $tenant['tenant_id'];
    echo "Creating CoA for tenant: {$tenant['tenant_name']} (ID: {$tenantId})\n";

    // First insert parent accounts (parent_id = NULL)
    $insertSql = "INSERT IGNORE INTO chart_of_accounts (tenant_id, account_code, account_name, account_type, parent_id, is_active)
                  VALUES (?, ?, ?, ?, NULL, 1)";

    foreach ($accounts as $acc) {
        try {
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([$tenantId, $acc[0], $acc[1], $acc[2]]);
            if ($stmt->rowCount() > 0) {
                echo "  + Created account: {$acc[0]} - {$acc[1]}\n";
                $totalCreated++;
            }
        } catch (\PDOException $e) {
            echo "  x Failed: {$acc[0]}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nMigration 041 complete. Accounts created: {$totalCreated}\n";
