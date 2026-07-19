<?php

namespace App\Modules\VenueAdvanced\Services;

use App\Core\Database;
use PDO;

class VenueAdvancedService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== DYNAMIC PRICING ====================

    public function createPricingRule($data)
    {
        $sql = "INSERT INTO dynamic_pricing_rules (tenant_id, branch_id, rule_name, product_id, category_id, trigger_type, trigger_condition, price_modifier_type, price_modifier_value, min_price, max_price, priority, is_active, valid_from, valid_to)
                VALUES (:tenant_id, :branch_id, :name, :product_id, :category_id, :trigger_type, :condition, :mod_type, :mod_value, :min_price, :max_price, :priority, 1, :valid_from, :valid_to)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':name' => $data['rule_name'], ':product_id' => $data['product_id'] ?? null,
            ':category_id' => $data['category_id'] ?? null,
            ':trigger_type' => $data['trigger_type'],
            ':condition' => isset($data['trigger_condition']) ? json_encode($data['trigger_condition']) : null,
            ':mod_type' => $data['price_modifier_type'],
            ':mod_value' => $data['price_modifier_value'],
            ':min_price' => $data['min_price'] ?? null, ':max_price' => $data['max_price'] ?? null,
            ':priority' => $data['priority'] ?? 0,
            ':valid_from' => $data['valid_from'] ?? null, ':valid_to' => $data['valid_to'] ?? null,
        ]);
        return ['rule_id' => $this->pdo->lastInsertId()];
    }

    public function getPricingRules($tenantId, $branchId, $activeOnly = true)
    {
        $sql = "SELECT * FROM dynamic_pricing_rules WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($activeOnly) { $sql .= " AND is_active = 1"; }
        $sql .= " ORDER BY priority DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateDynamicPrice($tenantId, $branchId, $productId, $basePrice, $context = [])
    {
        $rules = $this->getPricingRules($tenantId, $branchId, true);
        $adjustedPrice = $basePrice;

        foreach ($rules as $rule) {
            if ($rule['product_id'] && $rule['product_id'] != $productId) continue;
            if (!$this->ruleMatchesContext($rule, $context)) continue;

            switch ($rule['price_modifier_type']) {
                case 'PERCENTAGE':
                    $adjustedPrice += $basePrice * ($rule['price_modifier_value'] / 100);
                    break;
                case 'FLAT':
                    $adjustedPrice += $rule['price_modifier_value'];
                    break;
                case 'MULTIPLIER':
                    $adjustedPrice *= $rule['price_modifier_value'];
                    break;
            }

            if ($rule['min_price'] !== null && $adjustedPrice < $rule['min_price']) {
                $adjustedPrice = $rule['min_price'];
            }
            if ($rule['max_price'] !== null && $adjustedPrice > $rule['max_price']) {
                $adjustedPrice = $rule['max_price'];
            }

            $this->logPriceHistory($tenantId, $productId, $basePrice, $adjustedPrice, $rule['rule_id'], $rule['rule_name']);
            break;
        }

        return round($adjustedPrice, 2);
    }

    private function ruleMatchesContext($rule, $context)
    {
        $condition = json_decode($rule['trigger_condition'] ?? '{}', true);
        switch ($rule['trigger_type']) {
            case 'TIME_OF_DAY':
                $hour = (int)date('H');
                if (isset($condition['start_hour']) && $hour < $condition['start_hour']) return false;
                if (isset($condition['end_hour']) && $hour >= $condition['end_hour']) return false;
                return true;
            case 'DAY_OF_WEEK':
                $dow = (int)date('N');
                if (isset($condition['days']) && !in_array($dow, $condition['days'])) return false;
                return true;
            case 'OCCUPANCY':
                $occ = $context['occupancy_pct'] ?? 0;
                if (isset($condition['min_occupancy']) && $occ < $condition['min_occupancy']) return false;
                return true;
            case 'SEASON':
                $month = (int)date('n');
                if (isset($condition['months']) && !in_array($month, $condition['months'])) return false;
                return true;
            default:
                return true;
        }
    }

    private function logPriceHistory($tenantId, $productId, $original, $adjusted, $ruleId, $reason)
    {
        $sql = "INSERT INTO dynamic_price_history (tenant_id, product_id, original_price, adjusted_price, rule_id, adjustment_reason)
                VALUES (:tenant_id, :product_id, :original, :adjusted, :rule_id, :reason)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId, ':product_id' => $productId,
            ':original' => $original, ':adjusted' => $adjusted,
            ':rule_id' => $ruleId, ':reason' => $reason,
        ]);
    }

    // ==================== MEMBERSHIP ====================

    public function createMembership($data)
    {
        $sql = "INSERT INTO memberships (tenant_id, branch_id, member_name, member_email, member_phone, tier, join_date, expiry_date, family_account, guest_passes_remaining)
                VALUES (:tenant_id, :branch_id, :name, :email, :phone, :tier, :join_date, :expiry, :family, :guest_passes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':name' => $data['member_name'], ':email' => $data['member_email'] ?? null,
            ':phone' => $data['member_phone'] ?? null, ':tier' => $data['tier'] ?? 'BRONZE',
            ':join_date' => $data['join_date'] ?? date('Y-m-d'),
            ':expiry' => $data['expiry_date'] ?? date('Y-m-d', strtotime('+1 year')),
            ':family' => $data['family_account'] ?? 0,
            ':guest_passes' => $data['guest_passes_remaining'] ?? 0,
        ]);
        return ['membership_id' => $this->pdo->lastInsertId()];
    }

    public function getMemberships($tenantId, $branchId, $tier = null, $status = null)
    {
        $sql = "SELECT * FROM memberships WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($tier) { $sql .= " AND tier = :tier"; $params[':tier'] = $tier; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY member_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function earnPoints($membershipId, $points, $orderId = null, $description = null)
    {
        $sql = "INSERT INTO membership_transactions (membership_id, transaction_type, points, order_id, description) VALUES (:mid, 'EARN', :points, :order_id, :desc)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':mid' => $membershipId, ':points' => $points, ':order_id' => $orderId, ':desc' => $description]);

        $sql = "UPDATE memberships SET points_balance = points_balance + :points, total_spent = total_spent + :spent WHERE membership_id = :mid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':mid' => $membershipId, ':points' => $points, ':spent' => $points * 100]);
        return ['success' => true];
    }

    public function redeemPoints($membershipId, $points, $description = null)
    {
        $sql = "SELECT points_balance FROM memberships WHERE membership_id = :mid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':mid' => $membershipId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$member || $member['points_balance'] < $points) {
            return ['success' => false, 'message' => 'Insufficient points'];
        }

        $sql = "INSERT INTO membership_transactions (membership_id, transaction_type, points, description) VALUES (:mid, 'REDEEM', :points, :desc)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':mid' => $membershipId, ':points' => $points, ':desc' => $description]);

        $sql = "UPDATE memberships SET points_balance = points_balance - :points WHERE membership_id = :mid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':mid' => $membershipId, ':points' => $points]);
        return ['success' => true];
    }

    // ==================== QR TICKET SCANNING ====================

    public function scanTicket($tenantId, $branchId, $eventId, $qrCode, $scannedBy, $deviceId = null)
    {
        $sql = "SELECT * FROM entrance_tickets WHERE qr_code = :qr_code AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':qr_code' => $qrCode, ':tenant_id' => $tenantId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = 'NOT_FOUND';
        $ticketId = null;

        if ($ticket) {
            $ticketId = $ticket['ticket_id'];
            if ($ticket['status'] === 'USED') {
                $result = 'DUPLICATE';
            } elseif (isset($ticket['expires_at']) && strtotime($ticket['expires_at']) < time()) {
                $result = 'EXPIRED';
            } else {
                $result = 'VALID';
                $sql = "UPDATE entrance_tickets SET status = 'USED', used_at = NOW() WHERE ticket_id = :tid";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':tid' => $ticket['ticket_id']]);
            }
        }

        $sql = "INSERT INTO qr_ticket_scans (tenant_id, branch_id, event_id, ticket_id, qr_code, scan_result, scanned_by, device_id)
                VALUES (:tenant_id, :branch_id, :event_id, :ticket_id, :qr_code, :result, :scanned_by, :device_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId, ':branch_id' => $branchId,
            ':event_id' => $eventId, ':ticket_id' => $ticketId,
            ':qr_code' => $qrCode, ':result' => $result,
            ':scanned_by' => $scannedBy, ':device_id' => $deviceId,
        ]);

        return ['scan_result' => $result, 'ticket_id' => $ticketId];
    }

    public function getScanStats($tenantId, $eventId)
    {
        $sql = "SELECT scan_result, COUNT(*) as count FROM qr_ticket_scans WHERE tenant_id = :tenant_id AND event_id = :event_id GROUP BY scan_result";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== OCCUPANCY TRACKING ====================

    public function recordEntry($tenantId, $branchId, $count = 1)
    {
        $this->ensureOccupancyRecord($tenantId, $branchId);
        $sql = "UPDATE occupancy_tracking SET current_occupancy = current_occupancy + :count, entry_count = entry_count + :count, last_updated = NOW() WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND tracking_date = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId, ':count' => $count]);

        $this->logOccupancyEvent($tenantId, $branchId, 'ENTRY', $count);
        $this->updateOccupancyStatus($tenantId, $branchId);

        return $this->getOccupancy($tenantId, $branchId);
    }

    public function recordExit($tenantId, $branchId, $count = 1)
    {
        $this->ensureOccupancyRecord($tenantId, $branchId);
        $sql = "UPDATE occupancy_tracking SET current_occupancy = GREATEST(current_occupancy - :count, 0), exit_count = exit_count + :count, last_updated = NOW() WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND tracking_date = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId, ':count' => $count]);

        $this->logOccupancyEvent($tenantId, $branchId, 'EXIT', $count);
        $this->updateOccupancyStatus($tenantId, $branchId);

        return $this->getOccupancy($tenantId, $branchId);
    }

    public function getOccupancy($tenantId, $branchId)
    {
        $sql = "SELECT * FROM occupancy_tracking WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND tracking_date = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId]);
        $occ = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$occ) return null;
        $occ['occupancy_pct'] = $occ['max_capacity'] > 0 ? round(($occ['current_occupancy'] / $occ['max_capacity']) * 100, 1) : 0;
        return $occ;
    }

    public function setMaxCapacity($tenantId, $branchId, $capacity)
    {
        $this->ensureOccupancyRecord($tenantId, $branchId);
        $sql = "UPDATE occupancy_tracking SET max_capacity = :capacity WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND tracking_date = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId, ':capacity' => $capacity]);
        $this->updateOccupancyStatus($tenantId, $branchId);
        return ['success' => true];
    }

    private function ensureOccupancyRecord($tenantId, $branchId)
    {
        $sql = "SELECT occupancy_id FROM occupancy_tracking WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND tracking_date = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $sql = "INSERT INTO occupancy_tracking (tenant_id, branch_id, tracking_date, max_capacity) VALUES (:tenant_id, :branch_id, CURDATE(), 100)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId]);
        }
    }

    private function logOccupancyEvent($tenantId, $branchId, $type, $count)
    {
        $sql = "SELECT occupancy_id FROM occupancy_tracking WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND tracking_date = CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId]);
        $occ = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($occ) {
            $sql = "INSERT INTO occupancy_events (occupancy_id, event_type, person_count) VALUES (:oid, :type, :count)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':oid' => $occ['occupancy_id'], ':type' => $type, ':count' => $count]);
        }
    }

    private function updateOccupancyStatus($tenantId, $branchId)
    {
        $occ = $this->getOccupancy($tenantId, $branchId);
        if (!$occ) return;
        $status = 'OPEN';
        if ($occ['current_occupancy'] >= $occ['max_capacity']) $status = 'AT_CAPACITY';
        elseif ($occ['occupancy_pct'] >= 90) $status = 'WAITLIST';
        $sql = "UPDATE occupancy_tracking SET status = :status WHERE occupancy_id = :oid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $occ['occupancy_id'], ':status' => $status]);
    }

    // ==================== KARAOKE ROOM CALENDAR ====================

    public function getRoomCalendar($tenantId, $branchId, $roomId, $dateFrom, $dateTo)
    {
        $sql = "SELECT * FROM karaoke_room_calendar WHERE tenant_id = :tenant_id AND room_id = :room_id AND start_time >= :date_from AND end_time <= :date_to ORDER BY start_time";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':room_id' => $roomId, ':date_from' => $dateFrom, ':date_to' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCalendarBlock($data)
    {
        $sql = "INSERT INTO karaoke_room_calendar (tenant_id, branch_id, room_id, reservation_id, start_time, end_time, status, customer_name, notes)
                VALUES (:tenant_id, :branch_id, :room_id, :reservation_id, :start, :end, :status, :customer, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':room_id' => $data['room_id'], ':reservation_id' => $data['reservation_id'] ?? null,
            ':start' => $data['start_time'], ':end' => $data['end_time'],
            ':status' => $data['status'] ?? 'BOOKED',
            ':customer' => $data['customer_name'] ?? null, ':notes' => $data['notes'] ?? null,
        ]);
        return ['calendar_id' => $this->pdo->lastInsertId()];
    }

    // ==================== KARAOKE OVERTIME ====================

    public function calculateOvertime($tenantId, $branchId, $roomId, $reservationId, $bookedEndTime, $actualEndTime, $ratePerHour)
    {
        $overtimeMinutes = max(0, (strtotime($actualEndTime) - strtotime($bookedEndTime)) / 60);
        $overtimeCharge = ($overtimeMinutes / 60) * $ratePerHour;

        $sql = "INSERT INTO karaoke_overtime_charges (tenant_id, branch_id, room_id, reservation_id, booked_end_time, actual_end_time, overtime_minutes, overtime_rate_per_hour, overtime_charge)
                VALUES (:tenant_id, :branch_id, :room_id, :reservation_id, :booked, :actual, :minutes, :rate, :charge)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId, ':branch_id' => $branchId,
            ':room_id' => $roomId, ':reservation_id' => $reservationId,
            ':booked' => $bookedEndTime, ':actual' => $actualEndTime,
            ':minutes' => $overtimeMinutes, ':rate' => $ratePerHour, ':charge' => $overtimeCharge,
        ]);
        return ['overtime_id' => $this->pdo->lastInsertId(), 'overtime_minutes' => $overtimeMinutes, 'overtime_charge' => $overtimeCharge];
    }

    public function waiveOvertime($overtimeId)
    {
        $sql = "UPDATE karaoke_overtime_charges SET status = 'WAIVED' WHERE overtime_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $overtimeId]);
        return ['success' => true];
    }

    // ==================== HOLDS CALENDAR ====================

    public function addHold($data)
    {
        $sql = "INSERT INTO event_holds_calendar (tenant_id, branch_id, event_date, artist_name, hold_type, priority_rank, promoter_name, hold_expires_at, notes)
                VALUES (:tenant_id, :branch_id, :event_date, :artist, :hold_type, :rank, :promoter, :expires, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':event_date' => $data['event_date'], ':artist' => $data['artist_name'] ?? null,
            ':hold_type' => $data['hold_type'] ?? 'FIRST_HOLD',
            ':rank' => $data['priority_rank'] ?? 1,
            ':promoter' => $data['promoter_name'] ?? null,
            ':expires' => $data['hold_expires_at'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ]);
        return ['hold_id' => $this->pdo->lastInsertId()];
    }

    public function releaseHold($holdId, $rolledToDate = null)
    {
        $sql = "UPDATE event_holds_calendar SET hold_type = 'RELEASED', released_at = NOW(), rolled_to_date = :rolled WHERE hold_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $holdId, ':rolled' => $rolledToDate]);
        return ['success' => true];
    }

    public function confirmHold($holdId)
    {
        $sql = "UPDATE event_holds_calendar SET hold_type = 'CONFIRMED' WHERE hold_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $holdId]);
        return ['success' => true];
    }

    public function getHolds($tenantId, $branchId, $date = null)
    {
        $sql = "SELECT * FROM event_holds_calendar WHERE tenant_id = :tenant_id AND hold_type != 'RELEASED'";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($date) { $sql .= " AND event_date = :date"; $params[':date'] = $date; }
        $sql .= " ORDER BY event_date, priority_rank";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== COMP / GUEST LIST ====================

    public function addCompGuest($data)
    {
        $sql = "INSERT INTO comp_guest_lists (tenant_id, branch_id, event_id, list_type, guest_name, guest_phone, party_size, comp_type, comp_value, added_by, notes)
                VALUES (:tenant_id, :branch_id, :event_id, :list_type, :name, :phone, :party, :comp_type, :comp_value, :added_by, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':event_id' => $data['event_id'], ':list_type' => $data['list_type'] ?? 'GUEST',
            ':name' => $data['guest_name'], ':phone' => $data['guest_phone'] ?? null,
            ':party' => $data['party_size'] ?? 1, ':comp_type' => $data['comp_type'] ?? 'FULL',
            ':comp_value' => $data['comp_value'] ?? 0,
            ':added_by' => $data['added_by'] ?? null, ':notes' => $data['notes'] ?? null,
        ]);
        return ['comp_id' => $this->pdo->lastInsertId()];
    }

    public function checkInCompGuest($compId)
    {
        $sql = "UPDATE comp_guest_lists SET check_in_status = 'CHECKED_IN', checked_in_at = NOW() WHERE comp_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $compId]);
        return ['success' => true];
    }

    public function getCompList($tenantId, $eventId, $listType = null)
    {
        $sql = "SELECT * FROM comp_guest_lists WHERE tenant_id = :tenant_id AND event_id = :event_id";
        $params = [':tenant_id' => $tenantId, ':event_id' => $eventId];
        if ($listType) { $sql .= " AND list_type = :list_type"; $params[':list_type'] = $listType; }
        $sql .= " ORDER BY list_type, created_at";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
