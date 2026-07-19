<?php

declare(strict_types=1);

/**
 * Migration 043: Create CoA for Karaoke Bar, Beach Club, and Live Music Venue tenants
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getInstance()->connect();

// Find tenants by business type
$businessTypes = ['KARAOKE_BAR', 'BEACH_CLUB', 'LIVE_MUSIC_VENUE'];
$placeholders = implode(',', array_fill(0, count($businessTypes), '?'));
$stmt = $pdo->prepare("SELECT tenant_id, tenant_name, business_type FROM tenants WHERE business_type IN ($placeholders) AND status = 'ACTIVE'");
$stmt->execute($businessTypes);
$tenants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

if (empty($tenants)) {
    echo "No KARAOKE_BAR, BEACH_CLUB, or LIVE_MUSIC_VENUE tenants found.\n";
    echo "Migration 043 complete. No accounts created.\n";
    exit(0);
}

// Shared base accounts (same structure as discotheque, with entertainment-specific revenue accounts)
$baseAccounts = [
    // ASSETS
    ['1000', 'Cash on Hand', 'ASSET'],
    ['1010', 'Cash Register', 'ASSET'],
    ['1020', 'Bank Account', 'ASSET'],
    ['1100', 'Inventory - Beverages', 'ASSET'],
    ['1110', 'Inventory - Bar Supplies', 'ASSET'],
    ['1200', 'Equipment', 'ASSET'],
    ['1210', 'Furniture & Fixtures', 'ASSET'],
    ['1300', 'Accounts Receivable', 'ASSET'],
    ['1400', 'Prepaid Expenses', 'ASSET'],
    // LIABILITIES
    ['2000', 'Accounts Payable', 'LIABILITY'],
    ['2010', 'Accrued Entertainment Fees', 'LIABILITY'],
    ['2020', 'Accrued Staff Wages', 'LIABILITY'],
    ['2100', 'Customer Deposits', 'LIABILITY'],
    ['2200', 'Tax Payable', 'LIABILITY'],
    ['2300', 'Service Charge Payable', 'LIABILITY'],
    // EQUITY
    ['3000', "Owner's Equity", 'EQUITY'],
    ['3100', 'Retained Earnings', 'EQUITY'],
    ['3200', 'Current Year Profit/Loss', 'EQUITY'],
    // REVENUE
    ['4000', 'Entrance Fee Revenue', 'REVENUE'],
    ['4100', 'Bottle Service Revenue', 'REVENUE'],
    ['4200', 'Room Reservation Revenue', 'REVENUE'],
    ['4210', 'Karaoke Room Revenue', 'REVENUE'],
    ['4300', 'Cabana/Daybed Reservation Revenue', 'REVENUE'],
    ['4310', 'Beach Club Entry Revenue', 'REVENUE'],
    ['4400', 'F&B Revenue', 'REVENUE'],
    ['4500', 'Concert Ticket Revenue', 'REVENUE'],
    ['4510', 'VIP Box Revenue', 'REVENUE'],
    ['4600', 'Merchandise Revenue', 'REVENUE'],
    ['4700', 'Other Revenue', 'REVENUE'],
    // EXPENSE
    ['5000', 'COGS - Beverages', 'EXPENSE'],
    ['5010', 'COGS - Food', 'EXPENSE'],
    ['5020', 'COGS - Bar Supplies', 'EXPENSE'],
    ['5100', 'Entertainment Fees', 'EXPENSE'],
    ['5110', 'Artist/Performer Fees', 'EXPENSE'],
    ['5200', 'Security Staff Wages', 'EXPENSE'],
    ['5210', 'Bar Staff Wages', 'EXPENSE'],
    ['5220', 'Waitstaff Wages', 'EXPENSE'],
    ['5230', 'Management Salaries', 'EXPENSE'],
    ['5300', 'Marketing & Promotion', 'EXPENSE'],
    ['5400', 'Rent', 'EXPENSE'],
    ['5410', 'Utilities', 'EXPENSE'],
    ['5420', 'Equipment Maintenance', 'EXPENSE'],
    ['5500', 'Insurance', 'EXPENSE'],
    ['5600', 'Licenses & Permits', 'EXPENSE'],
    ['5700', 'Cleaning & Sanitation', 'EXPENSE'],
    ['5800', 'Depreciation - Equipment', 'EXPENSE'],
    ['5900', 'Miscellaneous Expenses', 'EXPENSE'],
];

$totalCreated = 0;
foreach ($tenants as $tenant) {
    $tenantId = $tenant['tenant_id'];
    echo "Creating CoA for tenant: {$tenant['tenant_name']} (ID: {$tenantId}, Type: {$tenant['business_type']})\n";

    $insertSql = "INSERT IGNORE INTO chart_of_accounts (tenant_id, account_code, account_name, account_type, is_active) VALUES (?, ?, ?, ?, 1)";

    foreach ($baseAccounts as $acc) {
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

echo "\nMigration 043 complete. Accounts created: {$totalCreated}\n";
