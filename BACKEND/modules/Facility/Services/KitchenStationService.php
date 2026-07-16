<?php

namespace App\Modules\Facility\Services;

use App\Core\Database;
use PDO;

class KitchenStationService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    /**
     * Get all kitchen stations for a tenant and branch
     */
    public function getKitchenStations($tenantId, $branchId, $floorId = null)
    {
        $sql = "SELECT ks.*, f.floor_name, f.floor_level 
                FROM kitchen_stations ks 
                LEFT JOIN floors f ON ks.floor_id = f.floor_id 
                WHERE ks.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND ks.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($floorId) {
            $sql .= " AND ks.floor_id = :floor_id";
            $params[':floor_id'] = $floorId;
        }
        
        $sql .= " ORDER BY ks.is_central DESC, f.floor_level ASC, ks.display_order ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get single kitchen station by ID
     */
    public function getKitchenStation($stationId, $tenantId)
    {
        $sql = "SELECT ks.*, f.floor_name, f.floor_level 
                FROM kitchen_stations ks 
                LEFT JOIN floors f ON ks.floor_id = f.floor_id 
                WHERE ks.station_id = :station_id AND ks.tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':station_id' => $stationId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Create new kitchen station
     */
    public function createKitchenStation($data)
    {
        $sql = "INSERT INTO kitchen_stations (tenant_id, branch_id, floor_id, station_name, station_type, kitchen_code, kitchen_category, description, capacity, is_central, display_order, is_active) 
                VALUES (:tenant_id, :branch_id, :floor_id, :station_name, :station_type, :kitchen_code, :kitchen_category, :description, :capacity, :is_central, :display_order, :is_active)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':floor_id' => $data['floor_id'] ?? null,
            ':station_name' => $data['station_name'],
            ':station_type' => $data['station_type'] ?? 'PREPARATION',
            ':kitchen_code' => $data['kitchen_code'] ?? null,
            ':kitchen_category' => $data['kitchen_category'] ?? 'HOT_KITCHEN',
            ':description' => $data['description'] ?? null,
            ':capacity' => $data['capacity'] ?? 0,
            ':is_central' => $data['is_central'] ?? 0,
            ':display_order' => $data['display_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update kitchen station
     */
    public function updateKitchenStation($stationId, $tenantId, $data)
    {
        $sql = "UPDATE kitchen_stations SET station_name = :station_name, station_type = :station_type, 
                kitchen_code = :kitchen_code, kitchen_category = :kitchen_category, description = :description, 
                capacity = :capacity, is_central = :is_central, display_order = :display_order, is_active = :is_active 
                WHERE station_id = :station_id AND tenant_id = :tenant_id";
        
        $params = [
            ':station_name' => $data['station_name'],
            ':station_type' => $data['station_type'],
            ':kitchen_code' => $data['kitchen_code'],
            ':kitchen_category' => $data['kitchen_category'],
            ':description' => $data['description'],
            ':capacity' => $data['capacity'],
            ':is_central' => $data['is_central'],
            ':display_order' => $data['display_order'],
            ':is_active' => $data['is_active'],
            ':station_id' => $stationId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete kitchen station
     */
    public function deleteKitchenStation($stationId, $tenantId)
    {
        $sql = "DELETE FROM kitchen_stations WHERE station_id = :station_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':station_id' => $stationId, ':tenant_id' => $tenantId]);
    }

    /**
     * Get central kitchens for a tenant
     */
    public function getCentralKitchens($tenantId, $branchId = null)
    {
        $sql = "SELECT * FROM kitchen_stations WHERE tenant_id = :tenant_id AND is_central = 1";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY display_order ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
