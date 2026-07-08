<?php

/**
 * Migration Runner
 * 
 * Executes database migrations from the migrations directory
 * Tracks which migrations have been executed and supports rollback
 * 
 * @version 1.0.0
 * @date 2026-07-08
 */

class MigrationRunner
{
    private $pdo;
    private $migrationsDir;
    private $migrationTable = 'migrations';

    public function __construct($pdo, $migrationsDir = __DIR__)
    {
        $this->pdo = $pdo;
        $this->migrationsDir = $migrationsDir;
        $this->ensureMigrationTable();
    }

    /**
     * Ensure the migrations tracking table exists
     */
    private function ensureMigrationTable()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Get all migration files
     * 
     * @return array Migration files sorted by name
     */
    private function getMigrationFiles()
    {
        $files = glob($this->migrationsDir . '/*.php');
        // Exclude MigrationRunner.php itself
        $files = array_filter($files, function($file) {
            return basename($file) !== 'MigrationRunner.php';
        });
        sort($files);
        return $files;
    }

    /**
     * Get executed migrations
     * 
     * @return array Array of executed migration names
     */
    private function getExecutedMigrations()
    {
        $stmt = $this->pdo->query("SELECT migration FROM {$this->migrationTable} ORDER BY migration");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Run pending migrations
     * 
     * @param bool $verbose Output verbose information
     * @return array Result with success status and message
     */
    public function migrate($verbose = true)
    {
        $migrationFiles = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        $pendingMigrations = [];
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            if (!in_array($migrationName, $executedMigrations)) {
                $pendingMigrations[] = $file;
            }
        }

        if (empty($pendingMigrations)) {
            if ($verbose) {
                echo "No pending migrations to run.\n";
            }
            return ['success' => true, 'message' => 'No pending migrations'];
        }

        if ($verbose) {
            echo "Running " . count($pendingMigrations) . " migration(s)...\n";
        }

        foreach ($pendingMigrations as $file) {
            $migrationName = basename($file);
            
            if ($verbose) {
                echo "  - Running {$migrationName}... ";
            }

            try {
                $migration = require $file;
                
                if (!is_array($migration) || !isset($migration['up']) || !is_callable($migration['up'])) {
                    throw new Exception("Invalid migration format in {$migrationName}");
                }

                // Execute up migration (without transaction wrapper)
                $migration['up']($this->pdo);
                
                // Record migration
                $stmt = $this->pdo->prepare("INSERT INTO {$this->migrationTable} (migration) VALUES (?)");
                $stmt->execute([$migrationName]);
                
                if ($verbose) {
                    echo "✓\n";
                }
            } catch (Exception $e) {
                if ($verbose) {
                    echo "✗\n";
                    echo "  Error: " . $e->getMessage() . "\n";
                }
                
                return [
                    'success' => false,
                    'message' => "Migration {$migrationName} failed: " . $e->getMessage()
                ];
            }
        }

        if ($verbose) {
            echo "Migrations completed successfully.\n";
        }

        return ['success' => true, 'message' => 'Migrations completed'];
    }

    /**
     * Rollback the last migration
     * 
     * @param int $steps Number of migrations to rollback
     * @param bool $verbose Output verbose information
     * @return array Result with success status and message
     */
    public function rollback($steps = 1, $verbose = true)
    {
        $executedMigrations = $this->getExecutedMigrations();
        
        if (empty($executedMigrations)) {
            if ($verbose) {
                echo "No migrations to rollback.\n";
            }
            return ['success' => true, 'message' => 'No migrations to rollback'];
        }

        $toRollback = array_slice($executedMigrations, -$steps);
        
        if ($verbose) {
            echo "Rolling back " . count($toRollback) . " migration(s)...\n";
        }

        // Rollback in reverse order
        foreach (array_reverse($toRollback) as $migrationName) {
            $file = $this->migrationsDir . '/' . $migrationName;
            
            if (!file_exists($file)) {
                if ($verbose) {
                    echo "  - Migration file {$migrationName} not found, skipping...\n";
                }
                continue;
            }
            
            if ($verbose) {
                echo "  - Rolling back {$migrationName}... ";
            }

            try {
                $migration = require $file;
                
                if (!is_array($migration) || !isset($migration['down']) || !is_callable($migration['down'])) {
                    throw new Exception("No down migration defined in {$migrationName}");
                }

                // Start transaction
                $this->pdo->beginTransaction();
                
                // Execute down migration
                $migration['down']($this->pdo);
                
                // Remove migration record
                $stmt = $this->pdo->prepare("DELETE FROM {$this->migrationTable} WHERE migration = ?");
                $stmt->execute([$migrationName]);
                
                // Commit transaction
                $this->pdo->commit();
                
                if ($verbose) {
                    echo "✓\n";
                }
            } catch (Exception $e) {
                // Rollback on error
                $this->pdo->rollBack();
                
                if ($verbose) {
                    echo "✗\n";
                    echo "  Error: " . $e->getMessage() . "\n";
                }
                
                return [
                    'success' => false,
                    'message' => "Rollback of {$migrationName} failed: " . $e->getMessage()
                ];
            }
        }

        if ($verbose) {
            echo "Rollback completed successfully.\n";
        }

        return ['success' => true, 'message' => 'Rollback completed'];
    }

    /**
     * Get migration status
     * 
     * @return array Migration status information
     */
    public function status()
    {
        $migrationFiles = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        $status = [];
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            $status[] = [
                'name' => $migrationName,
                'executed' => in_array($migrationName, $executedMigrations)
            ];
        }

        return [
            'total' => count($migrationFiles),
            'executed' => count($executedMigrations),
            'pending' => count($migrationFiles) - count($executedMigrations),
            'migrations' => $status
        ];
    }

    /**
     * Reset all migrations (rollback all then migrate)
     * 
     * @param bool $verbose Output verbose information
     * @return array Result with success status and message
     */
    public function reset($verbose = true)
    {
        $executedMigrations = $this->getExecutedMigrations();
        $steps = count($executedMigrations);
        
        if ($verbose) {
            echo "Resetting database...\n";
        }

        // Rollback all
        $result = $this->rollback($steps, $verbose);
        if (!$result['success']) {
            return $result;
        }

        // Migrate all
        return $this->migrate($verbose);
    }
}
