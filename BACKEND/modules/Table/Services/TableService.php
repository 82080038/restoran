<?php

if (!class_exists('TableRepository')) {
    require_once __DIR__ . '/../Repositories/TableRepository.php';
}



class TableService
{
    private $tableRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->tableRepository = new TableRepository();
        $this->transaction = new Transaction();
        $this->audit = \App\Core\Audit::getInstance();
    }

    public function getAllTables(int $tenantId, ?int $branchId = null, ?int $limit = null): array
    {
        $tables = $this->tableRepository->findAll($tenantId, $branchId, $limit);
        return array_map(function($t) { return $t->toArray(); }, $tables);
    }

    public function getAvailableTables(int $tenantId, ?int $branchId = null): array
    {
        $tables = $this->tableRepository->findAvailable($tenantId, $branchId);
        return array_map(function($t) { return $t->toArray(); }, $tables);
    }

    public function getTable(int $tenantId, int $tableId): ?array
    {
        $table = $this->tableRepository->findById($tenantId, $tableId);
        return $table ? $table->toArray() : null;
    }

    public function createTable(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            $table = new \App\Modules\Table\Models\Table($data);
            
            // Check if table number already exists for this branch
            $existing = $this->tableRepository->findByNumber(
                $tenantId, 
                $table->branch_id, 
                $table->table_number
            );
            
            if ($existing) {
                $this->transaction->rollback();
                return false;
            }
            
            $result = $this->tableRepository->create($table);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'TABLE',
                    'CREATE_TABLE',
                    $table->table_id,
                    'restaurant_tables',
                    null,
                    $table->toArray()
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateTable(int $tenantId, int $tableId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldTable = $this->tableRepository->findById($tenantId, $tableId);
            
            $data['tenant_id'] = $tenantId;
            $data['table_id'] = $tableId;
            $table = new \App\Modules\Table\Models\Table($data);
            
            $result = $this->tableRepository->update($table);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'TABLE',
                    'UPDATE_TABLE',
                    $tableId,
                    'restaurant_tables',
                    $oldTable ? $oldTable->toArray() : null,
                    $table->toArray()
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateTableStatus(int $tenantId, int $tableId, string $status): bool
    {
        $this->transaction->begin();
        
        try {
            $oldTable = $this->tableRepository->findById($tenantId, $tableId);
            
            $result = $this->tableRepository->updateStatus($tenantId, $tableId, $status);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'TABLE',
                    'UPDATE_TABLE_STATUS',
                    $tableId,
                    'restaurant_tables',
                    ['old_status' => $oldTable ? $oldTable->status : null],
                    ['new_status' => $status]
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function deleteTable(int $tenantId, int $tableId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldTable = $this->tableRepository->findById($tenantId, $tableId);
            
            $result = $this->tableRepository->delete($tenantId, $tableId);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'TABLE',
                    'DELETE_TABLE',
                    $tableId,
                    'restaurant_tables',
                    $oldTable ? $oldTable->toArray() : null,
                    null
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }
}
