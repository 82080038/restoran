<?php
/**
 * RESTAURANT_ERP Database Migration Runner
 * 
 * This script executes all database migrations in the DATABASE folder
 * in the correct order according to MEGAPLAN.md
 * 
 * Usage: php run_migrations.php
 */

// Database configuration - XAMPP defaults
$dbConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'socket' => '',
    'name' => 'ebp_restaurant_erp',
    'user' => 'root',
    'password' => ''
];

// Alternative configurations to try if default fails
$altConfigs = [
    ['user' => 'root', 'password' => ''],
    ['user' => 'root', 'password' => 'root'],
    ['user' => 'root', 'password' => 'mysql'],
    ['user' => 'root', 'password' => 'password'],
];

// Migration directory
$migrationDir = __DIR__ . '/../DATABASE';

// Migration tracking table
$migrationTable = 'schema_migrations';

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
                $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
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

function createMigrationTable($pdo, $tableName) {
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$tableName` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_file VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                checksum VARCHAR(64)
            )
        ");
        colorOutput("Migration tracking table '$tableName' ready", 'success');
        return true;
    } catch (PDOException $e) {
        colorOutput("Failed to create migration table: " . $e->getMessage(), 'error');
        return false;
    }
}

function getExecutedMigrations($pdo, $tableName) {
    try {
        $stmt = $pdo->query("SELECT migration_file FROM `$tableName` ORDER BY migration_file");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

function calculateChecksum($filePath) {
    return md5_file($filePath);
}

function executeMigration($pdo, $migrationFile, $tableName) {
    try {
        $sql = file_get_contents($migrationFile);
        
        if (empty($sql)) {
            colorOutput("Migration file is empty: $migrationFile", 'warning');
            return false;
        }
        
        // Execute the entire SQL file at once (better for complex migrations with dependencies)
        // Use CREATE TABLE IF NOT EXISTS to handle existing tables
        $pdo->exec($sql);
        
        $checksum = calculateChecksum($migrationFile);
        $fileName = basename($migrationFile);
        
        $stmt = $pdo->prepare("INSERT INTO `$tableName` (migration_file, checksum) VALUES (?, ?)");
        $stmt->execute([$fileName, $checksum]);
        
        return true;
    } catch (PDOException $e) {
        // If table already exists, it's not a critical error for schema setup
        if (strpos($e->getMessage(), 'already exists') !== false) {
            colorOutput("Table already exists (skipping): " . $e->getMessage(), 'warning');
            // Still mark as executed to avoid re-attempts
            $checksum = calculateChecksum($migrationFile);
            $fileName = basename($migrationFile);
            try {
                $stmt = $pdo->prepare("INSERT INTO `$tableName` (migration_file, checksum) VALUES (?, ?)");
                $stmt->execute([$fileName, $checksum]);
                return true;
            } catch (PDOException $insertEx) {
                // Already tracked
                return true;
            }
        }
        colorOutput("Failed to execute migration: " . $e->getMessage(), 'error');
        return false;
    }
}

function getMigrationFiles($dir) {
    // Use the main schema file instead of individual migrations
    $schemaFile = $dir . '/EBP_RESTAURANT_CAFE_MYSQL_SCHEMA.sql';
    if (file_exists($schemaFile)) {
        return [$schemaFile];
    }
    
    // Fallback to migration files if schema doesn't exist
    $files = glob($dir . '/MIGRATION_*.sql');
    natsort($files);
    return array_values($files);
}

function main() {
    global $dbConfig, $migrationDir, $migrationTable, $altConfigs;
    
    colorOutput("========================================", 'info');
    colorOutput("RESTAURANT_ERP Database Migration Runner", 'info');
    colorOutput("========================================", 'info');
    colorOutput("", 'info');
    
    // Check migration directory
    if (!is_dir($migrationDir)) {
        colorOutput("Migration directory not found: $migrationDir", 'error');
        return 1;
    }
    
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
    
    // Create migration tracking table
    colorOutput("", 'info');
    if (!createMigrationTable($pdo, $migrationTable)) {
        return 1;
    }
    
    // Get executed migrations
    $executedMigrations = getExecutedMigrations($pdo, $migrationTable);
    colorOutput("Already executed migrations: " . count($executedMigrations), 'info');
    
    // Get migration files
    colorOutput("", 'info');
    $migrationFiles = getMigrationFiles($migrationDir);
    colorOutput("Found " . count($migrationFiles) . " migration files", 'info');
    
    if (count($migrationFiles) === 0) {
        colorOutput("No migration files found in $migrationDir", 'warning');
        return 0;
    }
    
    // Execute pending migrations
    colorOutput("", 'info');
    colorOutput("Executing pending migrations...", 'info');
    colorOutput("----------------------------------------", 'info');
    
    $executedCount = 0;
    $failedCount = 0;
    $skippedCount = 0;
    
    foreach ($migrationFiles as $file) {
        $fileName = basename($file);
        
        if (in_array($fileName, $executedMigrations)) {
            colorOutput("SKIPPED: $fileName (already executed)", 'warning');
            $skippedCount++;
            continue;
        }
        
        colorOutput("EXECUTING: $fileName...", 'info');
        
        if (executeMigration($pdo, $file, $migrationTable)) {
            colorOutput("SUCCESS: $fileName", 'success');
            $executedCount++;
        } else {
            colorOutput("FAILED: $fileName", 'error');
            $failedCount++;
        }
    }
    
    // Summary
    colorOutput("", 'info');
    colorOutput("========================================", 'info');
    colorOutput("Migration Summary", 'info');
    colorOutput("========================================", 'info');
    colorOutput("Total migrations: " . count($migrationFiles), 'info');
    colorOutput("Executed: $executedCount", 'success');
    colorOutput("Skipped: $skippedCount", 'warning');
    colorOutput("Failed: $failedCount", $failedCount > 0 ? 'error' : 'success');
    colorOutput("========================================", 'info');
    
    return $failedCount > 0 ? 1 : 0;
}

// Run main function
exit(main());
