<?php

declare(strict_types=1);

namespace App\Core;


use PDO;
use PDOException;
/**
 * EBP Core - Database Connection Manager
 * 
 * This is a core component of the Enterprise Business Platform
 * Used for database connectivity across all EBP products
 * 
 * @package EBP\App\Core\Database
 * @version 1.0.0
 */

class Database
{
    private string $host;
    private string $socket;
    private string $port;
    private string $dbname;
    private string $username;
    private string $password;
    private string $charset;
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    public function __construct(array $config = [])
    {
        $this->host = $config['host'] ?? getenv('DB_HOST') ?: 'localhost';
        $this->socket = $config['socket'] ?? getenv('DB_SOCKET') ?: '';
        $this->port = $config['port'] ?? getenv('DB_PORT') ?: '3306';
        $this->dbname = $config['dbname'] ?? getenv('DB_NAME') ?: '';
        $this->username = $config['username'] ?? getenv('DB_USER') ?: '';
        $this->password = $config['password'] ?? getenv('DB_PASSWORD') ?: '';
        $this->charset = $config['charset'] ?? 'utf8mb4';
    }

    /**
     * Get singleton instance
     * 
     * @param array $config Database configuration
     * @return Database
     */
    public static function getInstance(array $config = []): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Connect to database
     * 
     * @return PDO PDO instance
     * @throws PDOException If connection fails
     */
    public function connect(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        // Determine connection strategy based on environment
        $useSocket = !empty($this->socket) && file_exists($this->socket);
        
        try {
            if ($useSocket) {
                // Use Unix socket connection
                $this->pdo = new PDO(
                    "mysql:host={$this->host};unix_socket={$this->socket};dbname={$this->dbname};charset={$this->charset}",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false
                    ]
                );
            } else {
                // Use TCP connection with port
                $this->pdo = new PDO(
                    "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false
                    ]
                );
            }
            return $this->pdo;
        } catch (PDOException $e) {
            // Fallback: try the other connection method
            try {
                if ($useSocket) {
                    // Fallback to TCP
                    $this->pdo = new PDO(
                        "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}",
                        $this->username,
                        $this->password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                            PDO::ATTR_PERSISTENT => false
                        ]
                    );
                } else {
                    // Fallback to socket (if specified)
                    if (!empty($this->socket)) {
                        $this->pdo = new PDO(
                            "mysql:host={$this->host};unix_socket={$this->socket};dbname={$this->dbname};charset={$this->charset}",
                            $this->username,
                            $this->password,
                            [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false,
                                PDO::ATTR_PERSISTENT => false
                            ]
                        );
                    } else {
                        throw $e;
                    }
                }
                return $this->pdo;
            } catch (PDOException $e2) {
                throw new PDOException("Database connection failed: " . $e2->getMessage());
            }
        }
    }

    /**
     * Test database connection
     * 
     * @return bool True if connection successful
     */
    public function testConnection(): bool
    {
        try {
            $pdo = $this->connect();
            return $pdo !== null;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get database information
     * 
     * @return array Database metadata
     */
    public function getDatabaseInfo(): array
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->query("SELECT VERSION() as version");
            $result = $stmt->fetch();
            return [
                'version' => $result['version'] ?? 'Unknown',
                'database' => $this->dbname
            ];
        } catch (PDOException $e) {
            return [
                'version' => 'Unknown',
                'database' => $this->dbname,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Proxy undefined method calls to PDO instance
     * Allows $db->query(), $db->prepare(), etc. without explicit connect() call
     * 
     * @param string $name Method name
     * @param array $arguments Method arguments
     * @return mixed PDO method result
     */
    public function __call($name, $arguments)
    {
        $pdo = $this->connect();
        
        // Handle query() with parameter array pattern: $db->query($sql, [$params])
        if ($name === 'query' && count($arguments) >= 2 && is_array($arguments[1])) {
            $stmt = $pdo->prepare($arguments[0]);
            $stmt->execute($arguments[1]);
            return $stmt;
        }
        
        return call_user_func_array([$pdo, $name], $arguments);
    }
}
