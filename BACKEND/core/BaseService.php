<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

abstract class BaseService
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    protected function transaction(callable $operation): mixed
    {
        $started = !$this->db->inTransaction();

        if ($started) {
            $this->db->beginTransaction();
        }

        try {
            $result = $operation();

            if ($started) {
                $this->db->commit();
            }

            return $result;
        } catch (Throwable $exception) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }
}
