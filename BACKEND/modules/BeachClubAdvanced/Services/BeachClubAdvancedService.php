<?php

namespace App\Modules\BeachClubAdvanced\Services;

use App\Core\Database;
use PDO;

class BeachClubAdvancedService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== SEAT MAP ====================

    public function getSeatMap($tenantId, $branchId)
    {
        $sql = "SELECT * FROM beach_seat_map WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " ORDER BY zone_name, position_y, position_x";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addSeat($data)
    {
        $sql = "INSERT INTO beach_seat_map (tenant_id, branch_id, zone_name, seat_label, seat_type, capacity, position_x, position_y, width, height, base_price, premium_multiplier, minimum_spend, is_bookable)
                VALUES (:tenant_id, :branch_id, :zone, :label, :type, :capacity, :x, :y, :w, :h, :price, :multiplier, :min_spend, :bookable)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':zone' => $data['zone_name'],
            ':label' => $data['seat_label'],
            ':type' => $data['seat_type'],
            ':capacity' => $data['capacity'] ?? 2,
            ':x' => $data['position_x'] ?? 0,
            ':y' => $data['position_y'] ?? 0,
            ':w' => $data['width'] ?? 80,
            ':h' => $data['height'] ?? 80,
            ':price' => $data['base_price'] ?? 0,
            ':multiplier' => $data['premium_multiplier'] ?? 1.00,
            ':min_spend' => $data['minimum_spend'] ?? 0,
            ':bookable' => $data['is_bookable'] ?? 1,
        ]);
        return ['seat_id' => $this->pdo->lastInsertId()];
    }

    public function updateSeatPosition($seatId, $x, $y)
    {
        $sql = "UPDATE beach_seat_map SET position_x = :x, position_y = :y WHERE seat_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $seatId, ':x' => $x, ':y' => $y]);
        return ['success' => true];
    }

    public function getSeatAvailability($tenantId, $branchId, $date)
    {
        $sql = "SELECT s.*, CASE WHEN b.booking_id IS NOT NULL THEN 'BOOKED' ELSE 'AVAILABLE' END as availability
                FROM beach_seat_map s
                LEFT JOIN beach_reservations b ON s.seat_id = b.seat_id AND b.reservation_date = :date AND b.status IN ('CONFIRMED','CHECKED_IN')
                WHERE s.tenant_id = :tenant_id AND s.is_bookable = 1";
        $params = [':tenant_id' => $tenantId, ':date' => $date];
        if ($branchId) { $sql .= " AND s.branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " ORDER BY s.zone_name, s.position_y, s.position_x";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== WEATHER / RAIN CHECK ====================

    public function createRainCheck($data)
    {
        $sql = "INSERT INTO weather_rain_checks (tenant_id, branch_id, booking_id, customer_name, customer_phone, customer_email, original_date, weather_condition, refund_amount, issued_by, expiry_date, notes)
                VALUES (:tenant_id, :branch_id, :booking_id, :name, :phone, :email, :original_date, :weather, :refund, :issued_by, :expiry, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':booking_id' => $data['booking_id'] ?? null,
            ':name' => $data['customer_name'],
            ':phone' => $data['customer_phone'] ?? null,
            ':email' => $data['customer_email'] ?? null,
            ':original_date' => $data['original_date'],
            ':weather' => $data['weather_condition'] ?? null,
            ':refund' => $data['refund_amount'] ?? 0,
            ':issued_by' => $data['issued_by'] ?? null,
            ':expiry' => $data['expiry_date'] ?? date('Y-m-d', strtotime('+30 days')),
            ':notes' => $data['notes'] ?? null,
        ]);
        return ['rain_check_id' => $this->pdo->lastInsertId()];
    }

    public function rescheduleRainCheck($rainCheckId, $newDate, $rescheduledTo)
    {
        $sql = "UPDATE weather_rain_checks SET status = 'RESCHEDULED', rescheduled_date = :new_date, rescheduled_to = :rescheduled_to WHERE rain_check_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $rainCheckId, ':new_date' => $newDate, ':rescheduled_to' => $rescheduledTo]);
        return ['success' => true];
    }

    public function refundRainCheck($rainCheckId)
    {
        $sql = "UPDATE weather_rain_checks SET status = 'REFUNDED' WHERE rain_check_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $rainCheckId]);
        return ['success' => true];
    }

    public function getRainChecks($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM weather_rain_checks WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY original_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWeatherPolicies($tenantId, $branchId)
    {
        $sql = "SELECT * FROM weather_policies WHERE tenant_id = :tenant_id AND is_active = 1";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createWeatherPolicy($data)
    {
        $sql = "INSERT INTO weather_policies (tenant_id, branch_id, policy_name, rain_threshold_mm, auto_issue_rain_check, reschedule_window_days, refund_policy, partial_refund_pct, notification_template, is_active)
                VALUES (:tenant_id, :branch_id, :name, :threshold, :auto, :window, :refund_policy, :partial_pct, :template, 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':name' => $data['policy_name'],
            ':threshold' => $data['rain_threshold_mm'] ?? 5.00,
            ':auto' => $data['auto_issue_rain_check'] ?? 0,
            ':window' => $data['reschedule_window_days'] ?? 30,
            ':refund_policy' => $data['refund_policy'] ?? 'CREDIT',
            ':partial_pct' => $data['partial_refund_pct'] ?? 0,
            ':template' => $data['notification_template'] ?? null,
        ]);
        return ['policy_id' => $this->pdo->lastInsertId()];
    }
}
