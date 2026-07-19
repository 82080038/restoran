<?php

namespace App\Modules\MiscFeatures\Services;

use App\Core\Database;
use PDO;

class MiscFeaturesService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== COAT CHECK ====================

    public function checkInCoat($data)
    {
        $checkNumber = 'CC-' . date('Ymd') . '-' . substr(uniqid(), -4);
        $sql = "INSERT INTO coat_check_items (tenant_id, branch_id, event_id, check_number, customer_name, item_type, item_description, item_count, fee_charged, fee_paid, handled_by)
                VALUES (:tenant_id, :branch_id, :event_id, :check_number, :name, :item_type, :desc, :count, :fee, :paid, :handled_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'],
            ':event_id' => $data['event_id'] ?? null, ':check_number' => $checkNumber,
            ':name' => $data['customer_name'] ?? null, ':item_type' => $data['item_type'] ?? 'COAT',
            ':desc' => $data['item_description'] ?? null, ':count' => $data['item_count'] ?? 1,
            ':fee' => $data['fee_charged'] ?? 0, ':paid' => $data['fee_paid'] ?? 0,
            ':handled_by' => $data['handled_by'] ?? null,
        ]);
        return ['coat_check_id' => $this->pdo->lastInsertId(), 'check_number' => $checkNumber];
    }

    public function checkOutCoat($coatCheckId, $handledBy)
    {
        $sql = "UPDATE coat_check_items SET status = 'CHECKED_OUT', checked_out_at = NOW(), handled_by = :handled_by WHERE coat_check_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $coatCheckId, ':handled_by' => $handledBy]);
        return ['success' => true];
    }

    public function getCoatCheckItems($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM coat_check_items WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY checked_in_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCoatCheckStats($tenantId, $branchId, $eventId = null)
    {
        $sql = "SELECT status, COUNT(*) as count, COALESCE(SUM(fee_charged), 0) as total_fees, COALESCE(SUM(CASE WHEN fee_paid = 1 THEN fee_charged ELSE 0 END), 0) as fees_collected FROM coat_check_items WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($eventId) { $sql .= " AND event_id = :event_id"; $params[':event_id'] = $eventId; }
        $sql .= " GROUP BY status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== KARAOKE SCORE ====================

    public function recordScore($data)
    {
        $sql = "INSERT INTO karaoke_scores (tenant_id, branch_id, room_id, song_id, singer_name, score, pitch_accuracy, rhythm_accuracy, volume_level, duration_seconds, applause_rating)
                VALUES (:tenant_id, :branch_id, :room_id, :song_id, :singer, :score, :pitch, :rhythm, :volume, :duration, :applause)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'],
            ':room_id' => $data['room_id'], ':song_id' => $data['song_id'] ?? null,
            ':singer' => $data['singer_name'] ?? null, ':score' => $data['score'] ?? 0,
            ':pitch' => $data['pitch_accuracy'] ?? null, ':rhythm' => $data['rhythm_accuracy'] ?? null,
            ':volume' => $data['volume_level'] ?? null, ':duration' => $data['duration_seconds'] ?? null,
            ':applause' => $data['applause_rating'] ?? null,
        ]);
        return ['score_id' => $this->pdo->lastInsertId()];
    }

    public function getHighScores($tenantId, $branchId, $limit = 20)
    {
        $sql = "SELECT ks.*, sc.title, sc.artist FROM karaoke_scores ks
                LEFT JOIN karaoke_song_catalog sc ON ks.song_id = sc.song_id
                WHERE ks.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND ks.branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " ORDER BY ks.score DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== EQUIPMENT TRACKING ====================

    public function addEquipment($data)
    {
        $sql = "INSERT INTO equipment_assets (tenant_id, branch_id, equipment_name, equipment_type, brand, model, serial_number, purchase_date, purchase_cost, condition_status, assigned_to, assigned_location, is_cross_hire, cross_hire_from, cross_hire_return_date, status, notes)
                VALUES (:tenant_id, :branch_id, :name, :type, :brand, :model, :serial, :purchase_date, :cost, :condition, :assigned_to, :location, :cross_hire, :cross_from, :cross_return, :status, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':name' => $data['equipment_name'], ':type' => $data['equipment_type'] ?? 'MISC',
            ':brand' => $data['brand'] ?? null, ':model' => $data['model'] ?? null,
            ':serial' => $data['serial_number'] ?? null,
            ':purchase_date' => $data['purchase_date'] ?? null,
            ':cost' => $data['purchase_cost'] ?? null,
            ':condition' => $data['condition_status'] ?? 'GOOD',
            ':assigned_to' => $data['assigned_to'] ?? null,
            ':location' => $data['assigned_location'] ?? null,
            ':cross_hire' => $data['is_cross_hire'] ?? 0,
            ':cross_from' => $data['cross_hire_from'] ?? null,
            ':cross_return' => $data['cross_hire_return_date'] ?? null,
            ':status' => $data['status'] ?? 'IN_STORAGE',
            ':notes' => $data['notes'] ?? null,
        ]);
        return ['equipment_id' => $this->pdo->lastInsertId()];
    }

    public function getEquipment($tenantId, $branchId, $status = null, $type = null)
    {
        $sql = "SELECT * FROM equipment_assets WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        if ($type) { $sql .= " AND equipment_type = :type"; $params[':type'] = $type; }
        $sql .= " ORDER BY equipment_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignEquipment($equipmentId, $eventId = null, $roomId = null, $assignedBy = null)
    {
        $sql = "INSERT INTO equipment_assignments (equipment_id, event_id, room_id, assigned_by) VALUES (:eid, :event, :room, :by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':eid' => $equipmentId, ':event' => $eventId, ':room' => $roomId, ':by' => $assignedBy]);

        $sql = "UPDATE equipment_assets SET status = 'IN_USE' WHERE equipment_id = :eid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':eid' => $equipmentId]);
        return ['assignment_id' => $this->pdo->lastInsertId()];
    }

    public function returnEquipment($assignmentId, $conditionAtReturn = null)
    {
        $sql = "UPDATE equipment_assignments SET returned_at = NOW(), condition_at_return = :condition WHERE assignment_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $assignmentId, ':condition' => $conditionAtReturn]);

        $sql = "UPDATE equipment_assets SET status = 'IN_STORAGE' WHERE equipment_id = (SELECT equipment_id FROM equipment_assignments WHERE assignment_id = :id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $assignmentId]);
        return ['success' => true];
    }

    // ==================== RADIUS CLAUSE CHECK ====================

    public function checkRadiusClause($tenantId, $dealId, $artistName, $radiusKm, $days, $eventDate)
    {
        $sql = "SELECT * FROM artist_deals WHERE tenant_id = :tenant_id AND artist_name = :artist AND deal_id != :deal_id AND concert_id IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':artist' => $artistName, ':deal_id' => $dealId]);
        $otherDeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = 'CLEAR';
        $conflictingVenue = null;
        $conflictingDistance = null;
        $conflictingDate = null;

        foreach ($otherDeals as $deal) {
            $daysDiff = abs(strtotime($eventDate) - strtotime($deal['created_at'])) / 86400;
            if ($daysDiff <= $days) {
                $result = 'VIOLATION';
                $conflictingVenue = $deal['branch_id'] ?? 'Unknown';
                $conflictingDate = $deal['created_at'];
                break;
            }
        }

        $sql = "INSERT INTO radius_clause_checks (tenant_id, deal_id, artist_name, clause_radius_km, clause_days, event_date, conflicting_venue, conflicting_venue_distance_km, conflicting_event_date, check_result)
                VALUES (:tenant_id, :deal_id, :artist, :radius, :days, :event_date, :venue, :distance, :conflict_date, :result)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId, ':deal_id' => $dealId, ':artist' => $artistName,
            ':radius' => $radiusKm, ':days' => $days, ':event_date' => $eventDate,
            ':venue' => $conflictingVenue, ':distance' => $conflictingDistance,
            ':conflict_date' => $conflictingDate, ':result' => $result,
        ]);

        return ['check_result' => $result, 'conflicting_venue' => $conflictingVenue, 'conflicting_date' => $conflictingDate];
    }

    // ==================== SOCIAL GROUP BOOKING ====================

    public function createGroupBooking($data)
    {
        $inviteLink = 'invite-' . substr(uniqid(), -8);
        $sql = "INSERT INTO social_group_bookings (tenant_id, branch_id, organizer_name, organizer_phone, organizer_email, event_date, event_name, total_party_size, total_amount, deposit_collected, split_type, invite_link)
                VALUES (:tenant_id, :branch_id, :name, :phone, :email, :date, :event_name, :party, :total, :deposit, :split, :invite)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':name' => $data['organizer_name'], ':phone' => $data['organizer_phone'] ?? null,
            ':email' => $data['organizer_email'] ?? null,
            ':date' => $data['event_date'], ':event_name' => $data['event_name'] ?? null,
            ':party' => $data['total_party_size'] ?? 1, ':total' => $data['total_amount'] ?? 0,
            ':deposit' => $data['deposit_collected'] ?? 0,
            ':split' => $data['split_type'] ?? 'EVEN', ':invite' => $inviteLink,
        ]);
        return ['group_booking_id' => $this->pdo->lastInsertId(), 'invite_link' => $inviteLink];
    }

    public function addGroupMember($groupBookingId, $data)
    {
        $sql = "INSERT INTO social_group_booking_members (group_booking_id, member_name, member_phone, member_email, share_amount)
                VALUES (:gid, :name, :phone, :email, :share)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':gid' => $groupBookingId, ':name' => $data['member_name'],
            ':phone' => $data['member_phone'] ?? null, ':email' => $data['member_email'] ?? null,
            ':share' => $data['share_amount'] ?? 0,
        ]);
        return ['member_id' => $this->pdo->lastInsertId()];
    }

    public function payShare($memberId)
    {
        $sql = "UPDATE social_group_booking_members SET share_paid = 1, paid_at = NOW() WHERE member_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $memberId]);
        return ['success' => true];
    }

    public function getGroupBooking($groupBookingId)
    {
        $sql = "SELECT * FROM social_group_bookings WHERE group_booking_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $groupBookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM social_group_booking_members WHERE group_booking_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $groupBookingId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['booking' => $booking, 'members' => $members];
    }

    // ==================== WINE PAIRING ====================

    public function addWine($data)
    {
        $sql = "INSERT INTO wine_list (tenant_id, wine_name, vintage, varietal, region, country, wine_type, bottle_price, glass_price, cost_per_bottle, inventory_bottles, pairings, tasting_notes, alcohol_pct, rating, is_available)
                VALUES (:tenant_id, :name, :vintage, :varietal, :region, :country, :type, :bottle_price, :glass_price, :cost, :inventory, :pairings, :notes, :alcohol, :rating, 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':name' => $data['wine_name'],
            ':vintage' => $data['vintage'] ?? null, ':varietal' => $data['varietal'] ?? null,
            ':region' => $data['region'] ?? null, ':country' => $data['country'] ?? null,
            ':type' => $data['wine_type'] ?? 'RED', ':bottle_price' => $data['bottle_price'] ?? 0,
            ':glass_price' => $data['glass_price'] ?? null, ':cost' => $data['cost_per_bottle'] ?? null,
            ':inventory' => $data['inventory_bottles'] ?? 0,
            ':pairings' => $data['pairings'] ?? null, ':notes' => $data['tasting_notes'] ?? null,
            ':alcohol' => $data['alcohol_pct'] ?? null, ':rating' => $data['rating'] ?? null,
        ]);
        return ['wine_id' => $this->pdo->lastInsertId()];
    }

    public function getWines($tenantId, $type = null, $availableOnly = true)
    {
        $sql = "SELECT * FROM wine_list WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($type) { $sql .= " AND wine_type = :type"; $params[':type'] = $type; }
        if ($availableOnly) { $sql .= " AND is_available = 1"; }
        $sql .= " ORDER BY wine_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPairingSuggestion($data)
    {
        $sql = "INSERT INTO wine_pairing_suggestions (tenant_id, wine_id, product_id, pairing_strength, pairing_reason)
                VALUES (:tenant_id, :wine_id, :product_id, :strength, :reason)
                ON DUPLICATE KEY UPDATE pairing_strength = :strength2, pairing_reason = :reason2";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':wine_id' => $data['wine_id'],
            ':product_id' => $data['product_id'],
            ':strength' => $data['pairing_strength'] ?? 'GOOD',
            ':reason' => $data['pairing_reason'] ?? null,
            ':strength2' => $data['pairing_strength'] ?? 'GOOD',
            ':reason2' => $data['pairing_reason'] ?? null,
        ]);
        return ['pairing_id' => $this->pdo->lastInsertId()];
    }

    public function getPairingsForProduct($tenantId, $productId)
    {
        $sql = "SELECT wp.*, w.wine_name, w.vintage, w.varietal, w.wine_type, w.glass_price, w.bottle_price
                FROM wine_pairing_suggestions wp
                JOIN wine_list w ON wp.wine_id = w.wine_id
                WHERE wp.tenant_id = :tenant_id AND wp.product_id = :product_id
                ORDER BY FIELD(wp.pairing_strength, 'CLASSIC', 'EXCELLENT', 'GOOD', 'EXPERIMENTAL')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':product_id' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== WAITER BUTTON ====================

    public function recordPress($tenantId, $branchId, $roomId)
    {
        $sql = "INSERT INTO waiter_button_presses (tenant_id, branch_id, room_id) VALUES (:tenant_id, :branch_id, :room_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':branch_id' => $branchId, ':room_id' => $roomId]);
        return ['press_id' => $this->pdo->lastInsertId()];
    }

    public function respondToPress($pressId, $respondedBy, $responseType = 'ACKNOWLEDGED')
    {
        $sql = "SELECT pressed_at FROM waiter_button_presses WHERE press_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $pressId]);
        $press = $stmt->fetch(PDO::FETCH_ASSOC);
        $responseSeconds = $press ? time() - strtotime($press['pressed_at']) : 0;

        $sql = "UPDATE waiter_button_presses SET responded_at = NOW(), response_seconds = :seconds, responded_by = :by, response_type = :type WHERE press_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $pressId, ':seconds' => $responseSeconds, ':by' => $respondedBy, ':type' => $responseType]);
        return ['success' => true, 'response_seconds' => $responseSeconds];
    }

    public function getWaiterButtonStats($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT room_id, COUNT(*) as total_presses,
                    AVG(response_seconds) as avg_response_time,
                    MAX(response_seconds) as max_response_time,
                    SUM(CASE WHEN response_type = 'ACKNOWLEDGED' THEN 1 ELSE 0 END) as acknowledged,
                    SUM(CASE WHEN response_type = 'SERVED' THEN 1 ELSE 0 END) as served
                FROM waiter_button_presses
                WHERE tenant_id = :tenant_id AND pressed_at >= :date_from AND pressed_at <= :date_to";
        $params = [':tenant_id' => $tenantId, ':date_from' => $dateFrom, ':date_to' => $dateTo];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " GROUP BY room_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== ENTERTAINER ROTATION ====================

    public function addRotationSlot($data)
    {
        $sql = "INSERT INTO entertainer_rotations (tenant_id, branch_id, event_id, entertainer_name, entertainer_type, set_number, set_start_time, set_end_time, set_duration_minutes, notes)
                VALUES (:tenant_id, :branch_id, :event_id, :name, :type, :set_num, :start, :end, :duration, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'],
            ':event_id' => $data['event_id'] ?? null, ':name' => $data['entertainer_name'],
            ':type' => $data['entertainer_type'] ?? 'DJ', ':set_num' => $data['set_number'] ?? 1,
            ':start' => $data['set_start_time'] ?? null, ':end' => $data['set_end_time'] ?? null,
            ':duration' => $data['set_duration_minutes'] ?? null, ':notes' => $data['notes'] ?? null,
        ]);
        return ['rotation_id' => $this->pdo->lastInsertId()];
    }

    public function getRotationSchedule($tenantId, $eventId)
    {
        $sql = "SELECT * FROM entertainer_rotations WHERE tenant_id = :tenant_id AND event_id = :event_id ORDER BY set_number, set_start_time";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRotationStatus($rotationId, $status)
    {
        $sql = "UPDATE entertainer_rotations SET status = :status WHERE rotation_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $rotationId, ':status' => $status]);
        return ['success' => true];
    }
}
