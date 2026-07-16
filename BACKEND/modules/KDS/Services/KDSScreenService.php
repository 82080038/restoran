<?php

namespace App\Modules\KDS\Services;

use App\Core\Database;
use PDO;

class KDSScreenService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getScreens($tenantId, $branchId, $stationId = null)
    {
        $sql = "SELECT s.*, st.station_name 
                FROM kds_screens s 
                LEFT JOIN kitchen_stations st ON s.station_id = st.station_id 
                WHERE s.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND s.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($stationId) {
            $sql .= " AND s.station_id = :station_id";
            $params[':station_id'] = $stationId;
        }
        
        $sql .= " ORDER BY s.display_order ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getScreen($screenId, $tenantId)
    {
        $sql = "SELECT s.*, st.station_name 
                FROM kds_screens s 
                LEFT JOIN kitchen_stations st ON s.station_id = st.station_id 
                WHERE s.screen_id = :screen_id AND s.tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':screen_id' => $screenId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    public function createScreen($data)
    {
        $sql = "INSERT INTO kds_screens (tenant_id, branch_id, station_id, screen_name, screen_type, display_order, max_tickets_display, auto_refresh_seconds, show_completed_tickets, color_scheme, is_active) 
                VALUES (:tenant_id, :branch_id, :station_id, :screen_name, :screen_type, :display_order, :max_tickets_display, :auto_refresh_seconds, :show_completed_tickets, :color_scheme, :is_active)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':station_id' => $data['station_id'],
            ':screen_name' => $data['screen_name'],
            ':screen_type' => $data['screen_type'] ?? 'PREP_STATION',
            ':display_order' => $data['display_order'] ?? 0,
            ':max_tickets_display' => $data['max_tickets_display'] ?? 20,
            ':auto_refresh_seconds' => $data['auto_refresh_seconds'] ?? 10,
            ':show_completed_tickets' => $data['show_completed_tickets'] ?? 0,
            ':color_scheme' => $data['color_scheme'] ?? 'DEFAULT',
            ':is_active' => $data['is_active'] ?? 1
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateScreen($screenId, $tenantId, $data)
    {
        $sql = "UPDATE kds_screens SET screen_name = :screen_name, screen_type = :screen_type, 
                display_order = :display_order, max_tickets_display = :max_tickets_display, 
                auto_refresh_seconds = :auto_refresh_seconds, show_completed_tickets = :show_completed_tickets, 
                color_scheme = :color_scheme, is_active = :is_active 
                WHERE screen_id = :screen_id AND tenant_id = :tenant_id";
        
        $params = [
            ':screen_name' => $data['screen_name'],
            ':screen_type' => $data['screen_type'],
            ':display_order' => $data['display_order'],
            ':max_tickets_display' => $data['max_tickets_display'],
            ':auto_refresh_seconds' => $data['auto_refresh_seconds'],
            ':show_completed_tickets' => $data['show_completed_tickets'],
            ':color_scheme' => $data['color_scheme'],
            ':is_active' => $data['is_active'],
            ':screen_id' => $screenId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteScreen($screenId, $tenantId)
    {
        $sql = "DELETE FROM kds_screens WHERE screen_id = :screen_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':screen_id' => $screenId, ':tenant_id' => $tenantId]);
    }
}
