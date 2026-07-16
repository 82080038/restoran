<?php

namespace App\Modules\Operations\Services;

use App\Core\Database;
use PDO;

class AYCEService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getAYCESessions($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT s.*, o.order_number, t.table_number 
                FROM ayce_sessions s 
                LEFT JOIN orders o ON s.order_id = o.order_id 
                LEFT JOIN tables t ON s.table_id = t.table_id 
                WHERE s.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        
        if ($branchId) {
            $sql .= " AND s.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        
        if ($status) {
            $sql .= " AND s.session_status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY s.session_start DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAYCESession($sessionId, $tenantId)
    {
        $sql = "SELECT s.*, o.order_number, t.table_number 
                FROM ayce_sessions s 
                LEFT JOIN orders o ON s.order_id = o.order_id 
                LEFT JOIN tables t ON s.table_id = t.table_id 
                WHERE s.session_id = :session_id AND s.tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':session_id' => $sessionId, ':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }

    public function createAYCESession($data)
    {
        $sql = "INSERT INTO ayce_sessions (tenant_id, branch_id, order_id, table_id, session_type, duration_minutes, max_reorders, session_status, notes) 
                VALUES (:tenant_id, :branch_id, :order_id, :table_id, :session_type, :duration_minutes, :max_reorders, :session_status, :notes)";
        
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':order_id' => $data['order_id'],
            ':table_id' => $data['table_id'],
            ':session_type' => $data['session_type'] ?? 'TIME_LIMITED',
            ':duration_minutes' => $data['duration_minutes'] ?? 120,
            ':max_reorders' => $data['max_reorders'] ?? null,
            ':session_status' => $data['session_status'] ?? 'ACTIVE',
            ':notes' => $data['notes'] ?? null
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function createAYCEReorder($sessionId, $orderId, $items, $totalAmount)
    {
        // Get current reorder count
        $sql = "SELECT current_reorder_count FROM ayce_sessions WHERE session_id = :session_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':session_id' => $sessionId]);
        $session = $stmt->fetch();
        
        if (!$session) return false;
        
        // Check max reorders if set
        if ($session['max_reorders'] && $session['current_reorder_count'] >= $session['max_reorders']) {
            return false;
        }
        
        $reorderNumber = $session['current_reorder_count'] + 1;
        
        $sql = "INSERT INTO ayce_reorders (session_id, order_id, reorder_number, reorder_time, item_count, total_amount, status) 
                VALUES (:session_id, :order_id, :reorder_number, NOW(), :item_count, :total_amount, 'PENDING')";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':session_id' => $sessionId,
            ':order_id' => $orderId,
            ':reorder_number' => $reorderNumber,
            ':item_count' => count($items),
            ':total_amount' => $totalAmount
        ]);
        
        $reorderId = $this->pdo->lastInsertId();
        
        // Update session reorder count
        $sql = "UPDATE ayce_sessions SET current_reorder_count = current_reorder_count + 1 
                WHERE session_id = :session_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':session_id' => $sessionId]);
        
        return $reorderId;
    }

    public function sendReorderToKitchen($reorderId, $kdsTicketId)
    {
        $sql = "UPDATE ayce_reorders SET status = 'SENT_TO_KITCHEN', kds_ticket_id = :kds_ticket_id 
                WHERE reorder_id = :reorder_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':reorder_id' => $reorderId,
            ':kds_ticket_id' => $kdsTicketId
        ]);
    }

    public function completeReorder($reorderId)
    {
        $sql = "UPDATE ayce_reorders SET status = 'COMPLETED' WHERE reorder_id = :reorder_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':reorder_id' => $reorderId]);
    }

    public function getSessionReorders($sessionId)
    {
        $sql = "SELECT * FROM ayce_reorders WHERE session_id = :session_id ORDER BY reorder_number ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':session_id' => $sessionId]);
        return $stmt->fetchAll();
    }

    public function endSession($sessionId, $tenantId)
    {
        $sql = "UPDATE ayce_sessions SET session_status = 'COMPLETED', session_end = NOW() 
                WHERE session_id = :session_id AND tenant_id = :tenant_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':session_id' => $sessionId, ':tenant_id' => $tenantId]);
    }

    public function checkSessionTimeouts()
    {
        // Check for timed-out sessions
        $sql = "UPDATE ayce_sessions 
                SET session_status = 'COMPLETED', session_end = NOW() 
                WHERE session_type = 'TIME_LIMITED' 
                AND session_status = 'ACTIVE' 
                AND TIMESTAMPDIFF(MINUTE, session_start, NOW()) >= duration_minutes";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}
