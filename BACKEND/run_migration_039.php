<?php
/**
 * Run migration 039 directly
 * Uses XAMPP default MySQL credentials
 */

$host = 'localhost';
$dbname = 'ebp_restaurant_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "Connected to database: {$dbname}\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    echo "Trying to create database first...\n";
    try {
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE {$dbname}");
        echo "Database created: {$dbname}\n";
    } catch (PDOException $e2) {
        echo "Failed to create database: " . $e2->getMessage() . "\n";
        exit(1);
    }
}

// Run migration
echo "Running migration 039_create_free_payment_tables.php...\n";
$migration = require __DIR__ . '/migrations/039_create_free_payment_tables.php';

if (is_array($migration) && isset($migration['up']) && is_callable($migration['up'])) {
    $migration['up']($pdo);
    echo "Migration completed successfully!\n";
    
    // Verify tables
    $tables = ['transfer_proofs', 'qris_static_configs', 'wallets', 'wallet_transactions', 'wallet_topup_requests'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        $exists = $stmt->fetch();
        echo "  - Table '{$table}': " . ($exists ? "OK" : "MISSING") . "\n";
    }
} else {
    echo "ERROR: Invalid migration format\n";
    exit(1);
}
