<?php

namespace App\Core;

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

class Transaction
{
    private static ?self $instance = null;

    private $db;

    public function __construct(?\PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->connect();
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public static function begin(): void
    {
        self::getInstance()->db->beginTransaction();
    }

    public static function commit(): void
    {
        self::getInstance()->db->commit();
    }

    public static function rollback(): void
    {
        self::getInstance()->db->rollBack();
    }
}