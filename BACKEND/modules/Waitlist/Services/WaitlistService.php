<?php

namespace App\Modules\Waitlist\Services;

use App\Core\Database;
use PDO;

class WaitlistService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getWaitlistEntries($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT w.*, t.table_number, z.zone_name 
                FROM waitlist_entries w 
                LEFT JOIN tables t ON w.table_id = t.table_id 
                LEFT JOIN zones z ON w.zone_id = z.zone_id 
                WHERE w.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND w.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($status) {
            $sql .= " AND w.arrival_status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY w.queue_position ASC, w.joined_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getWaitlistEntry($entryId, $tenantId)
    {
        $sql = "SELECT w.*, t.table_number, z.zone_name 
                FROM waitlist_entries w 
                LEFT JOIN tables t ON w.table_id = t.table_id 
                LEFT JOIN zones z ON w.zone_id = z.zone_id 
                WHERE w.entry_id = :entry_id AND w.tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':entry_id' => $entryId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    public function createWaitlistEntry($data)
    {
        // Get next queue position
        $sql = "SELECT COALESCE(MAX(queue_position), 0) + 1 as next_position 
                FROM waitlist_entries 
                WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND arrival_status IN ('ARRIVING_LATER', 'HERE_NOW')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id']]);
        $result = $stmt->fetch();
        $queuePosition = $result['next_position'];
        
        // Calculate estimated wait time based on party size and current queue
        $estimatedWaitTime = $this->calculateEstimatedWaitTime($data['tenant_id'], $data['branch_id'], $data['party_size']);
        
        $sql = "INSERT INTO waitlist_entries (tenant_id, branch_id, customer_name, phone, party_size, guest_count, arrival_status, queue_position, estimated_wait_time, joined_at, special_requests, source, is_vip) 
                VALUES (:tenant_id, :branch_id, :customer_name, :phone, :party_size, :guest_count, :arrival_status, :queue_position, :estimated_wait_time, NOW(), :special_requests, :source, :is_vip)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'],
            ':party_size' => $data['party_size'],
            ':guest_count' => $data['guest_count'] ?? $data['party_size'],
            ':arrival_status' => $data['arrival_status'] ?? 'ARRIVING_LATER',
            ':queue_position' => $queuePosition,
            ':estimated_wait_time' => $estimatedWaitTime,
            ':special_requests' => $data['special_requests'] ?? null,
            ':source' => $data['source'] ?? 'WALK_IN',
            ':is_vip' => $data['is_vip'] ?? 0
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateWaitlistEntry($entryId, $tenantId, $data)
    {
        $sql = "UPDATE waitlist_entries SET customer_name = :customer_name, phone = :phone, 
                party_size = :party_size, guest_count = :guest_count, arrival_status = :arrival_status, 
                special_requests = :special_requests, staff_notes = :staff_notes 
                WHERE entry_id = :entry_id AND tenant_id = :tenant_id";
        
        $params = [
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'],
            ':party_size' => $data['party_size'],
            ':guest_count' => $data['guest_count'],
            ':arrival_status' => $data['arrival_status'],
            ':special_requests' => $data['special_requests'],
            ':staff_notes' => $data['staff_notes'],
            ':entry_id' => $entryId,
            ':tenant_id' => $tenantId
        ];
        
        // Update timestamps based on status
        if ($data['arrival_status'] === 'HERE_NOW') {
            $sql .= ", estimated_seating_time = NOW()";
        } elseif ($data['arrival_status'] === 'SEATED') {
            $sql .= ", seated_at = NOW(), actual_wait_time = TIMESTAMPDIFF(MINUTE, joined_at, NOW())";
        } elseif ($data['arrival_status'] === 'CANCELLED') {
            $sql .= ", cancelled_at = NOW()";
        } elseif ($data['arrival_status'] === 'NO_SHOW') {
            $sql .= ", no_show_at = NOW()";
        }
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function seatGuest($entryId, $tenantId, $tableId, $zoneId = null)
    {
        $sql = "UPDATE waitlist_entries SET arrival_status = 'SEATED', 
                seated_at = NOW(), actual_wait_time = TIMESTAMPDIFF(MINUTE, joined_at, NOW()),
                table_id = :table_id, zone_id = :zone_id 
                WHERE entry_id = :entry_id AND tenant_id = :tenant_id";
        
        $params = [
            ':table_id' => $tableId,
            ':zone_id' => $zoneId,
            ':entry_id' => $entryId,
            ':tenant_id' => $tenantId
        ];
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateQueuePositions($tenantId, $branchId)
    {
        // Reorder queue positions for active entries
        $sql = "SET @row_number = 0;
                UPDATE waitlist_entries 
                SET queue_position = (@row_number := @row_number + 1)
                WHERE tenant_id = :tenant_id AND branch_id = :branch_id 
                AND arrival_status IN ('ARRIVING_LATER', 'HERE_NOW')
                ORDER BY joined_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId]);
    }

    public function deleteWaitlistEntry($entryId, $tenantId)
    {
        $sql = "DELETE FROM waitlist_entries WHERE entry_id = :entry_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':entry_id' => $entryId, ':tenant_id' => $tenantId]);
    }

    private function calculateEstimatedWaitTime($tenantId, $branchId, $partySize)
    {
        // Calculate based on current queue and average seating time
        $sql = "SELECT COUNT(*) as queue_count, AVG(actual_wait_time) as avg_wait_time 
                FROM waitlist_entries 
                WHERE tenant_id = :tenant_id AND branch_id = :branch_id 
                AND arrival_status IN ('ARRIVING_LATER', 'HERE_NOW')
                AND actual_wait_time IS NOT NULL";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId]);
        $result = $stmt->fetch();
        
        $queueCount = $result['queue_count'] ?? 0;
        $avgWaitTime = $result['avg_wait_time'] ?? 15; // Default 15 minutes
        
        // Add party size multiplier
        $partyMultiplier = $partySize > 4 ? 1.5 : 1.0;
        
        return (int)($queueCount * $avgWaitTime * $partyMultiplier);
    }
}
