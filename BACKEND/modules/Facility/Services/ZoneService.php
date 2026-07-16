<?php

namespace App\Modules\Facility\Services;

use App\Core\Database;
use PDO;

class ZoneService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    /**
     * Get all zones for a tenant and branch
     */
    public function getZones($tenantId, $branchId, $floorId = null)
    {
        $sql = "SELECT z.*, f.floor_name, f.floor_level 
                FROM zones z 
                LEFT JOIN floors f ON z.floor_id = f.floor_id 
                WHERE z.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND z.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($floorId) {
            $sql .= " AND z.floor_id = :floor_id";
            $params[':floor_id'] = $floorId;
        }
        
        $sql .= " ORDER BY f.floor_level ASC, z.sort_order ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get single zone by ID
     */
    public function getZone($zoneId, $tenantId)
    {
        $sql = "SELECT z.*, f.floor_name, f.floor_level 
                FROM zones z 
                LEFT JOIN floors f ON z.floor_id = f.floor_id 
                WHERE z.zone_id = :zone_id AND z.tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':zone_id' => $zoneId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Create new zone
     */
    public function createZone($data)
    {
        $sql = "INSERT INTO zones (tenant_id, branch_id, floor_id, zone_code, zone_name, zone_type, service_type, description, capacity, sort_order, status) 
                VALUES (:tenant_id, :branch_id, :floor_id, :zone_code, :zone_name, :zone_type, :service_type, :description, :capacity, :sort_order, :status)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':floor_id' => $data['floor_id'],
            ':zone_code' => $data['zone_code'],
            ':zone_name' => $data['zone_name'],
            ':zone_type' => $data['zone_type'] ?? 'DINING',
            ':service_type' => $data['service_type'] ?? 'TABLE_SERVICE',
            ':description' => $data['description'] ?? null,
            ':capacity' => $data['capacity'] ?? 0,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':status' => $data['status'] ?? 'ACTIVE'
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update zone
     */
    public function updateZone($zoneId, $tenantId, $data)
    {
        $sql = "UPDATE zones SET zone_code = :zone_code, zone_name = :zone_name, zone_type = :zone_type, 
                service_type = :service_type, description = :description, capacity = :capacity, 
                sort_order = :sort_order, status = :status 
                WHERE zone_id = :zone_id AND tenant_id = :tenant_id";
        
        $params = [
            ':zone_code' => $data['zone_code'],
            ':zone_name' => $data['zone_name'],
            ':zone_type' => $data['zone_type'],
            ':service_type' => $data['service_type'],
            ':description' => $data['description'],
            ':capacity' => $data['capacity'],
            ':sort_order' => $data['sort_order'],
            ':status' => $data['status'],
            ':zone_id' => $zoneId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete zone
     */
    public function deleteZone($zoneId, $tenantId)
    {
        $sql = "DELETE FROM zones WHERE zone_id = :zone_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':zone_id' => $zoneId, ':tenant_id' => $tenantId]);
    }

    /**
     * Get tables for a zone
     */
    public function getZoneTables($zoneId, $tenantId)
    {
        $sql = "SELECT * FROM tables WHERE zone_id = :zone_id AND tenant_id = :tenant_id ORDER BY table_number ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':zone_id' => $zoneId, ':tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }
}
