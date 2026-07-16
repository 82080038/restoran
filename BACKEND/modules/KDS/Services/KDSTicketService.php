<?php

namespace App\Modules\KDS\Services;

use App\Core\Database;
use PDO;

class KDSTicketService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getTickets($tenantId, $branchId, $stationId = null, $status = null)
    {
        $sql = "SELECT t.*, sc.screen_name, st.station_name 
                FROM kds_tickets t 
                LEFT JOIN kds_screens sc ON t.screen_id = sc.screen_id 
                LEFT JOIN kitchen_stations st ON t.station_id = st.station_id 
                WHERE t.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND t.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($stationId) {
            $sql .= " AND t.station_id = :station_id";
            $params[':station_id'] = $stationId;
        }
        
        if ($status) {
            $sql .= " AND t.ticket_status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY t.urgency_level DESC, t.created_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTicket($ticketId, $tenantId)
    {
        $sql = "SELECT t.*, sc.screen_name, st.station_name 
                FROM kds_tickets t 
                LEFT JOIN kds_screens sc ON t.screen_id = sc.screen_id 
                LEFT JOIN kitchen_stations st ON t.station_id = st.station_id 
                WHERE t.ticket_id = :ticket_id AND t.tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    public function createTicket($data)
    {
        $sql = "INSERT INTO kds_tickets (tenant_id, branch_id, order_id, station_id, screen_id, ticket_number, table_id, customer_name, dining_option, ticket_status, urgency_level, estimated_prep_time, course_number, is_rerouted, also_at_stations, notes) 
                VALUES (:tenant_id, :branch_id, :order_id, :station_id, :screen_id, :ticket_number, :table_id, :customer_name, :dining_option, :ticket_status, :urgency_level, :estimated_prep_time, :course_number, :is_rerouted, :also_at_stations, :notes)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':order_id' => $data['order_id'],
            ':station_id' => $data['station_id'],
            ':screen_id' => $data['screen_id'],
            ':ticket_number' => $data['ticket_number'],
            ':table_id' => $data['table_id'] ?? null,
            ':customer_name' => $data['customer_name'] ?? null,
            ':dining_option' => $data['dining_option'] ?? 'DINE_IN',
            ':ticket_status' => $data['ticket_status'] ?? 'NEW',
            ':urgency_level' => $data['urgency_level'] ?? 'NORMAL',
            ':estimated_prep_time' => $data['estimated_prep_time'] ?? null,
            ':course_number' => $data['course_number'] ?? 1,
            ':is_rerouted' => $data['is_rerouted'] ?? 0,
            ':also_at_stations' => $data['also_at_stations'] ?? null,
            ':notes' => $data['notes'] ?? null
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateTicketStatus($ticketId, $tenantId, $status, $urgencyLevel = null)
    {
        $sql = "UPDATE kds_tickets SET ticket_status = :ticket_status";
        $params = [
            ':ticket_status' => $status,
            ':ticket_id' => $ticketId,
            ':tenant_id' => $tenantId
        ];
        
        if ($urgencyLevel) {
            $sql .= ", urgency_level = :urgency_level";
            $params[':urgency_level'] = $urgencyLevel;
        }
        
        // Update timestamps based on status
        if ($status === 'IN_PROGRESS') {
            $sql .= ", prep_started_at = NOW()";
        } elseif ($status === 'READY') {
            $sql .= ", ready_at = NOW()";
        } elseif ($status === 'FULFILLED') {
            $sql .= ", fulfilled_at = NOW()";
        }
        
        $sql .= " WHERE ticket_id = :ticket_id AND tenant_id = :tenant_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateUrgencyLevels()
    {
        // Update urgency based on time elapsed
        $sql = "UPDATE kds_tickets 
                SET urgency_level = CASE 
                    WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 30 THEN 'OVERDUE'
                    WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 20 THEN 'URGENT'
                    WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 10 THEN 'HIGH'
                    ELSE 'NORMAL'
                END
                WHERE ticket_status IN ('NEW', 'IN_PROGRESS')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }

    public function deleteTicket($ticketId, $tenantId)
    {
        $sql = "DELETE FROM kds_tickets WHERE ticket_id = :ticket_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':ticket_id' => $ticketId, ':tenant_id' => $tenantId]);
    }
}
