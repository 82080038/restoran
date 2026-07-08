<?php

/**
 * Database Migration Script
 * 
 * Run this script to execute all pending database migrations
 * Usage: php migrate.php
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/migrations/MigrationRunner.php';

try {
    // Get database connection from environment variables
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbSocket = getenv('DB_SOCKET') ?: '/opt/lampp/var/mysql/mysql.sock';
    $dbName = getenv('DB_NAME') ?: 'ebp_restaurant_db';
    $dbUser = getenv('DB_USER') ?: 'ebp_app';
    $dbPassword = getenv('DB_PASSWORD') ?: 'ebp_secure_password_2026';

    // Create PDO connection with socket
    $dsn = "mysql:host={$dbHost};unix_socket={$dbSocket};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connecting to database: {$dbName}\n";
    echo "Host: {$dbHost}\n";
    echo "User: {$dbUser}\n\n";

    // Create migration runner
    $runner = new MigrationRunner($pdo, __DIR__ . '/migrations');

    // Show current status
    echo "=== Migration Status ===\n";
    $status = $runner->status();
    echo "Total migrations: {$status['total']}\n";
    echo "Executed: {$status['executed']}\n";
    echo "Pending: {$status['pending']}\n\n";

    if ($status['pending'] > 0) {
        echo "=== Running Migrations ===\n";
        $result = $runner->migrate();
        
        if ($result['success']) {
            echo "\n✅ Migrations completed successfully!\n";
        } else {
            echo "\n❌ Migration failed: {$result['message']}\n";
            exit(1);
        }
    } else {
        echo "No pending migrations to run.\n";
    }

    // Show final status
    echo "\n=== Final Status ===\n";
    $finalStatus = $runner->status();
    echo "Total migrations: {$finalStatus['total']}\n";
    echo "Executed: {$finalStatus['executed']}\n";
    echo "Pending: {$finalStatus['pending']}\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
