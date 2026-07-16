<?php

namespace App\Modules\Facility\Services;

use App\Core\Database;
use PDO;

class FloorService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    /**
     * Get all floors for a tenant and branch
     */
    public function getFloors($tenantId, $branchId)
    {
        $sql = "SELECT * FROM floors WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY floor_level ASC, sort_order ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get single floor by ID
     */
    public function getFloor($floorId, $tenantId)
    {
        $sql = "SELECT * FROM floors WHERE floor_id = :floor_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':floor_id' => $floorId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Create new floor
     */
    public function createFloor($data)
    {
        $sql = "INSERT INTO floors (tenant_id, branch_id, floor_code, floor_name, floor_level, floor_type, description, sort_order, status) 
                VALUES (:tenant_id, :branch_id, :floor_code, :floor_name, :floor_level, :floor_type, :description, :sort_order, :status)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':floor_code' => $data['floor_code'],
            ':floor_name' => $data['floor_name'],
            ':floor_level' => $data['floor_level'] ?? 1,
            ':floor_type' => $data['floor_type'] ?? 'DINING',
            ':description' => $data['description'] ?? null,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':status' => $data['status'] ?? 'ACTIVE'
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update floor
     */
    public function updateFloor($floorId, $tenantId, $data)
    {
        $sql = "UPDATE floors SET floor_code = :floor_code, floor_name = :floor_name, floor_level = :floor_level, 
                floor_type = :floor_type, description = :description, sort_order = :sort_order, status = :status 
                WHERE floor_id = :floor_id AND tenant_id = :tenant_id";
        
        $params = [
            ':floor_code' => $data['floor_code'],
            ':floor_name' => $data['floor_name'],
            ':floor_level' => $data['floor_level'],
            ':floor_type' => $data['floor_type'],
            ':description' => $data['description'],
            ':sort_order' => $data['sort_order'],
            ':status' => $data['status'],
            ':floor_id' => $floorId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete floor
     */
    public function deleteFloor($floorId, $tenantId)
    {
        $sql = "DELETE FROM floors WHERE floor_id = :floor_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':floor_id' => $floorId, ':tenant_id' => $tenantId]);
    }

    /**
     * Get zones for a floor
     */
    public function getFloorZones($floorId, $tenantId)
    {
        $sql = "SELECT * FROM zones WHERE floor_id = :floor_id AND tenant_id = :tenant_id ORDER BY sort_order ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':floor_id' => $floorId, ':tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }
}
