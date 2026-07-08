<?php

/**
 * Migration CLI Runner
 * 
 * Command-line interface for running database migrations
 * 
 * Usage:
 *   php run_migrations_new.php migrate    - Run pending migrations
 *   php run_migrations_new.php rollback   - Rollback last migration
 *   php run_migrations_new.php rollback N - Rollback N migrations
 *   php run_migrations_new.php status     - Show migration status
 *   php run_migrations_new.php reset      - Rollback all and migrate
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

// Load migration runner
require_once __DIR__ . '/migrations/MigrationRunner.php';

// Get database connection
$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'socket' => getenv('DB_SOCKET') ?: '/opt/lampp/var/mysql/mysql.sock',
    'dbname' => getenv('DB_NAME') ?: 'ebp_restaurant_erp',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: ''
];

try {
    // Connect using socket if available
    if (!empty($dbConfig['socket']) && file_exists($dbConfig['socket'])) {
        $dsn = "mysql:unix_socket={$dbConfig['socket']};dbname={$dbConfig['dbname']};charset=utf8mb4";
    } else {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4";
    }
    
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "Attempting to create database...\n";
    
    // Try to connect without database to create it
    try {
        $dsn = "mysql:host={$dbConfig['host']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database created successfully.\n";
        
        // Reconnect with database
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e2) {
        echo "Failed to create database: " . $e2->getMessage() . "\n";
        exit(1);
    }
}

// Create migration runner
$migrationsDir = __DIR__ . '/migrations';
$runner = new MigrationRunner($pdo, $migrationsDir);

// Get command
$command = $argv[1] ?? 'status';

// Execute command
switch ($command) {
    case 'migrate':
        $result = $runner->migrate();
        echo $result['message'] . "\n";
        exit($result['success'] ? 0 : 1);
        
    case 'rollback':
        $steps = isset($argv[2]) ? (int)$argv[2] : 1;
        $result = $runner->rollback($steps);
        echo $result['message'] . "\n";
        exit($result['success'] ? 0 : 1);
        
    case 'status':
        $status = $runner->status();
        echo "Migration Status:\n";
        echo "================\n";
        echo "Total: " . $status['total'] . "\n";
        echo "Executed: " . $status['executed'] . "\n";
        echo "Pending: " . $status['pending'] . "\n\n";
        
        foreach ($status['migrations'] as $migration) {
            $statusIcon = $migration['executed'] ? '✓' : '○';
            echo "  {$statusIcon} {$migration['name']}\n";
        }
        exit(0);
        
    case 'reset':
        $result = $runner->reset();
        echo $result['message'] . "\n";
        exit($result['success'] ? 0 : 1);
        
    default:
        echo "Unknown command: {$command}\n\n";
        echo "Usage:\n";
        echo "  php run_migrations_new.php migrate    - Run pending migrations\n";
        echo "  php run_migrations_new.php rollback   - Rollback last migration\n";
        echo "  php run_migrations_new.php rollback N - Rollback N migrations\n";
        echo "  php run_migrations_new.php status     - Show migration status\n";
        echo "  php run_migrations_new.php reset      - Rollback all and migrate\n";
        exit(1);
}
