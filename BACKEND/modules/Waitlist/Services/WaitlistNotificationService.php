<?php

namespace App\Modules\Waitlist\Services;

use App\Core\Database;
use PDO;

class WaitlistNotificationService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function getNotifications($entryId)
    {
        $sql = "SELECT * FROM waitlist_notifications WHERE entry_id = :entry_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':entry_id' => $entryId]);
        return $stmt->fetchAll();
    }

    public function createNotification($entryId, $type, $message, $sentVia = 'SMS')
    {
        $sql = "INSERT INTO waitlist_notifications (entry_id, notification_type, message, sent_via, status) 
                VALUES (:entry_id, :notification_type, :message, :sent_via, 'PENDING')";
        
        $params = [
            ':entry_id' => $entryId,
            ':notification_type' => $type,
            ':message' => $message,
            ':sent_via' => $sentVia
        ];
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateNotificationStatus($notificationId, $status, $errorMessage = null)
    {
        $sql = "UPDATE waitlist_notifications SET status = :status";
        $params = [':status' => $status, ':notification_id' => $notificationId];
        
        if ($status === 'SENT') {
            $sql .= ", sent_at = NOW()";
        } elseif ($status === 'DELIVERED') {
            $sql .= ", delivered_at = NOW()";
        } elseif ($status === 'READ') {
            $sql .= ", read_at = NOW()";
        }
        
        if ($errorMessage) {
            $sql .= ", error_message = :error_message";
            $params[':error_message'] = $errorMessage;
        }
        
        $sql .= " WHERE notification_id = :notification_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function sendPositionUpdate($entryId, $currentPosition, $estimatedWaitTime)
    {
        $message = "Your queue position is #$currentPosition. Estimated wait time: $estimatedWaitTime minutes.";
        return $this->createNotification($entryId, 'POSITION_UPDATE', $message, 'SMS');
    }

    public function sendReadyToSeat($entryId)
    {
        $message = "Your table is ready! Please proceed to the host stand.";
        return $this->createNotification($entryId, 'READY_TO_SEAT', $message, 'SMS');
    }
}
