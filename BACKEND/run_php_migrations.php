<?php
/**
 * PHP Migration Runner
 * 
 * This script executes all PHP migration files using the MigrationRunner class
 * 
 * Usage: php run_php_migrations.php
 */

// Load bootstrap to get database configuration
require_once __DIR__ . '/bootstrap.php';

// Load MigrationRunner
require_once __DIR__ . '/migrations/MigrationRunner.php';

// Get database configuration from environment
$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'socket' => getenv('DB_SOCKET') ?: '',
    'name' => getenv('DB_NAME') ?: 'ebp_restaurant_db',
    'user' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: ''
];

// Alternative configurations to try if default fails
$altConfigs = [
    ['user' => 'root', 'password' => ''],
    ['user' => 'root', 'password' => 'root'],
    ['user' => 'root', 'password' => 'mysql'],
];

// Colors for output
$colors = [
    'success' => "\033[32m",
    'error' => "\033[31m",
    'warning' => "\033[33m",
    'info' => "\033[36m",
    'reset' => "\033[0m"
];

function colorOutput($message, $color = 'info') {
    global $colors;
    echo $colors[$color] . $message . $colors['reset'] . PHP_EOL;
}

function connectToDatabase($config, $altConfigs = []) {
    $configsToTry = array_merge([$config], $altConfigs);
    
    foreach ($configsToTry as $cfg) {
        try {
            if (!empty($config['socket'])) {
                $dsn = "mysql:unix_socket={$config['socket']};charset=utf8mb4";
            } else {
                $dsn = "mysql:host={$config['host']};charset=utf8mb4";
            }
            
            $pdo = new PDO($dsn, $cfg['user'], $cfg['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            colorOutput("Connected to MySQL with user: {$cfg['user']}", 'success');
            return $pdo;
        } catch (PDOException $e) {
            // Try next configuration
            continue;
        }
    }
    
    colorOutput("Failed to connect to MySQL with all configurations", 'error');
    return null;
}

function createDatabase($pdo, $dbName) {
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        colorOutput("Database '$dbName' created or already exists", 'success');
        return true;
    } catch (PDOException $e) {
        colorOutput("Failed to create database: " . $e->getMessage(), 'error');
        return false;
    }
}

function selectDatabase($pdo, $dbName) {
    try {
        $pdo->exec("USE `$dbName`");
        return true;
    } catch (PDOException $e) {
        colorOutput("Failed to select database: " . $e->getMessage(), 'error');
        return false;
    }
}

function main() {
    global $dbConfig, $altConfigs;
    
    colorOutput("========================================", 'info');
    colorOutput("PHP Migration Runner", 'info');
    colorOutput("========================================", 'info');
    colorOutput("", 'info');
    
    // Connect to MySQL
    colorOutput("Connecting to MySQL...", 'info');
    $pdo = connectToDatabase($dbConfig, $altConfigs);
    if (!$pdo) {
        return 1;
    }
    
    // Create database
    colorOutput("", 'info');
    colorOutput("Creating database if not exists...", 'info');
    if (!createDatabase($pdo, $dbConfig['name'])) {
        return 1;
    }
    
    // Select database
    if (!selectDatabase($pdo, $dbConfig['name'])) {
        return 1;
    }
    colorOutput("Using database: {$dbConfig['name']}", 'success');
    
    // Initialize MigrationRunner
    colorOutput("", 'info');
    colorOutput("Initializing MigrationRunner...", 'info');
    $migrationsDir = __DIR__ . '/migrations';
    $runner = new MigrationRunner($pdo, $migrationsDir);
    
    // Get migration status
    colorOutput("", 'info');
    colorOutput("Checking migration status...", 'info');
    $status = $runner->status(false);
    colorOutput("Total migrations: {$status['total']}", 'info');
    colorOutput("Executed: {$status['executed']}", 'info');
    colorOutput("Pending: {$status['pending']}", 'info');
    
    // Run migrations
    colorOutput("", 'info');
    colorOutput("Running migrations...", 'info');
    colorOutput("----------------------------------------", 'info');
    $result = $runner->migrate(true);
    
    // Summary
    colorOutput("", 'info');
    colorOutput("========================================", 'info');
    if ($result['success']) {
        colorOutput("Migration completed successfully!", 'success');
    } else {
        colorOutput("Migration failed: " . $result['message'], 'error');
    }
    colorOutput("========================================", 'info');
    
    return $result['success'] ? 0 : 1;
}

// Run main function
exit(main());
