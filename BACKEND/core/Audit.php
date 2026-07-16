<?php

namespace App\Core;

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

class Audit
{
    private static ?self $instance = null;

    private $db;

    private function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public static function log($tenantId, $userId, $module, $action, $recordId = null, $tableName = null, $oldValue = null, $newValue = null)
    {
        $sql = "
            INSERT INTO audit_logs
            (tenant_id, user_id, module, action, record_id, table_name, old_values, new_values, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $db = self::getInstance()->db;
        $stmt = $db->prepare($sql);

        $stmt->execute([
            $tenantId,
            $userId,
            $module,
            $action,
            $recordId,
            $tableName,
            json_encode($oldValue),
            json_encode($newValue),
            $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }
}
