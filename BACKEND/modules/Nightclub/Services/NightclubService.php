<?php

namespace App\Modules\Nightclub\Services;

use App\Core\Database;
use PDO;

class NightclubService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== EVENTS ====================

    public function getEvents($tenantId, $branchId = null, $status = null)
    {
        $sql = "SELECT e.* FROM nightclub_events e WHERE e.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($branchId) {
            $sql .= " AND (e.branch_id IS NULL OR e.branch_id = :branch_id)";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND e.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY e.event_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEvent($eventId, $tenantId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM nightclub_events WHERE event_id = :event_id AND tenant_id = :tenant_id");
        $stmt->execute([':event_id' => $eventId, ':tenant_id' => $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEvent($data)
    {
        $sql = "INSERT INTO nightclub_events (tenant_id, branch_id, event_name, description, event_date, start_time, end_time, theme, dj_name, dj_genre, poster_url, capacity, status, is_active, created_by)
                VALUES (:tenant_id, :branch_id, :event_name, :description, :event_date, :start_time, :end_time, :theme, :dj_name, :dj_genre, :poster_url, :capacity, :status, 1, :created_by)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':event_name' => $data['event_name'],
            ':description' => $data['description'] ?? null,
            ':event_date' => $data['event_date'],
            ':start_time' => $data['start_time'] ?? '22:00:00',
            ':end_time' => $data['end_time'] ?? '04:00:00',
            ':theme' => $data['theme'] ?? null,
            ':dj_name' => $data['dj_name'] ?? null,
            ':dj_genre' => $data['dj_genre'] ?? null,
            ':poster_url' => $data['poster_url'] ?? null,
            ':capacity' => $data['capacity'] ?? null,
            ':status' => $data['status'] ?? 'SCHEDULED',
            ':created_by' => $data['created_by'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateEvent($eventId, $tenantId, $data)
    {
        $allowed = ['event_name', 'description', 'event_date', 'start_time', 'end_time', 'theme', 'dj_name', 'dj_genre', 'poster_url', 'capacity', 'status', 'is_active'];
        $fields = [];
        $params = [':event_id' => $eventId, ':tenant_id' => $tenantId];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE nightclub_events SET " . implode(', ', $fields) . " WHERE event_id = :event_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteEvent($eventId, $tenantId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM nightclub_events WHERE event_id = :event_id AND tenant_id = :tenant_id");
        return $stmt->execute([':event_id' => $eventId, ':tenant_id' => $tenantId]);
    }

    // ==================== ENTRANCE FEES ====================

    public function getEntranceFees($tenantId, $eventId = null)
    {
        $sql = "SELECT f.*, e.event_name FROM nightclub_entrance_fees f
                LEFT JOIN nightclub_events e ON f.event_id = e.event_id
                WHERE f.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($eventId) {
            $sql .= " AND (f.event_id IS NULL OR f.event_id = :event_id)";
            $params[':event_id'] = $eventId;
        }

        $sql .= " ORDER BY f.is_active DESC, f.fee_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEntranceFee($data)
    {
        $sql = "INSERT INTO nightclub_entrance_fees (tenant_id, branch_id, event_id, fee_name, fee_type, price, applicable_days, start_time, end_time, min_age, gender_restriction, includes_drink, description, is_active)
                VALUES (:tenant_id, :branch_id, :event_id, :fee_name, :fee_type, :price, :applicable_days, :start_time, :end_time, :min_age, :gender_restriction, :includes_drink, :description, 1)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':event_id' => $data['event_id'] ?? null,
            ':fee_name' => $data['fee_name'],
            ':fee_type' => $data['fee_type'] ?? 'COVER_CHARGE',
            ':price' => $data['price'],
            ':applicable_days' => $data['applicable_days'] ?? '5,6',
            ':start_time' => $data['start_time'] ?? null,
            ':end_time' => $data['end_time'] ?? null,
            ':min_age' => $data['min_age'] ?? null,
            ':gender_restriction' => $data['gender_restriction'] ?? null,
            ':includes_drink' => $data['includes_drink'] ?? 0,
            ':description' => $data['description'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateEntranceFee($feeId, $tenantId, $data)
    {
        $allowed = ['fee_name', 'fee_type', 'price', 'applicable_days', 'start_time', 'end_time', 'min_age', 'gender_restriction', 'includes_drink', 'description', 'is_active'];
        $fields = [];
        $params = [':fee_id' => $feeId, ':tenant_id' => $tenantId];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE nightclub_entrance_fees SET " . implode(', ', $fields) . " WHERE fee_id = :fee_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteEntranceFee($feeId, $tenantId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM nightclub_entrance_fees WHERE fee_id = :fee_id AND tenant_id = :tenant_id");
        return $stmt->execute([':fee_id' => $feeId, ':tenant_id' => $tenantId]);
    }

    // ==================== ENTRANCE TICKETS ====================

    public function getEntranceTickets($tenantId, $eventId = null, $checkInStatus = null)
    {
        $sql = "SELECT t.*, e.event_name, f.fee_name FROM nightclub_entrance_tickets t
                LEFT JOIN nightclub_events e ON t.event_id = e.event_id
                LEFT JOIN nightclub_entrance_fees f ON t.fee_id = f.fee_id
                WHERE t.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($eventId) {
            $sql .= " AND t.event_id = :event_id";
            $params[':event_id'] = $eventId;
        }
        if ($checkInStatus !== null) {
            $sql .= " AND t.check_in_status = :check_in";
            $params[':check_in'] = $checkInStatus;
        }

        $sql .= " ORDER BY t.sold_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEntranceTicket($data)
    {
        $ticketCode = 'NCT-' . strtoupper(substr(uniqid(), -8));

        $sql = "INSERT INTO nightclub_entrance_tickets (tenant_id, branch_id, event_id, fee_id, customer_name, phone, email, id_type, id_number, age_verified, gender, quantity, unit_price, total_amount, payment_status, payment_method, ticket_code, sold_by)
                VALUES (:tenant_id, :branch_id, :event_id, :fee_id, :customer_name, :phone, :email, :id_type, :id_number, :age_verified, :gender, :quantity, :unit_price, :total_amount, :payment_status, :payment_method, :ticket_code, :sold_by)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':event_id' => $data['event_id'] ?? null,
            ':fee_id' => $data['fee_id'] ?? null,
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':id_type' => $data['id_type'] ?? null,
            ':id_number' => $data['id_number'] ?? null,
            ':age_verified' => $data['age_verified'] ?? 0,
            ':gender' => $data['gender'] ?? null,
            ':quantity' => $data['quantity'] ?? 1,
            ':unit_price' => $data['unit_price'],
            ':total_amount' => $data['total_amount'] ?? ($data['unit_price'] * ($data['quantity'] ?? 1)),
            ':payment_status' => $data['payment_status'] ?? 'PENDING',
            ':payment_method' => $data['payment_method'] ?? null,
            ':ticket_code' => $ticketCode,
            ':sold_by' => $data['sold_by'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $ticketId = $this->pdo->lastInsertId();

        // Auto-post to accounting if payment is PAID
        if (($data['payment_status'] ?? 'PENDING') === 'PAID' && $data['total_amount'] > 0) {
            $this->postTicketRevenueToAccounting($ticketId, $data);
        }

        return ['ticket_id' => $ticketId, 'ticket_code' => $ticketCode];
    }

    public function checkInTicket($ticketId, $tenantId)
    {
        $sql = "UPDATE nightclub_entrance_tickets SET check_in_status = 1, check_in_at = NOW() WHERE ticket_id = :ticket_id AND tenant_id = :tenant_id AND check_in_status = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId, ':tenant_id' => $tenantId]);
        return $stmt->rowCount() > 0;
    }

    // ==================== GUEST LIST ====================

    public function getGuestList($tenantId, $eventId = null, $checkInStatus = null)
    {
        $sql = "SELECT g.*, e.event_name FROM nightclub_guest_lists g
                LEFT JOIN nightclub_events e ON g.event_id = e.event_id
                WHERE g.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($eventId) {
            $sql .= " AND g.event_id = :event_id";
            $params[':event_id'] = $eventId;
        }
        if ($checkInStatus !== null) {
            $sql .= " AND g.check_in_status = :check_in";
            $params[':check_in'] = $checkInStatus;
        }

        $sql .= " ORDER BY g.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addGuestListEntry($data)
    {
        $sql = "INSERT INTO nightclub_guest_lists (tenant_id, branch_id, event_id, guest_name, phone, email, party_size, entry_type, discount_percentage, added_by, notes)
                VALUES (:tenant_id, :branch_id, :event_id, :guest_name, :phone, :email, :party_size, :entry_type, :discount_percentage, :added_by, :notes)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':event_id' => $data['event_id'] ?? null,
            ':guest_name' => $data['guest_name'],
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':party_size' => $data['party_size'] ?? 1,
            ':entry_type' => $data['entry_type'] ?? 'FREE_ENTRY',
            ':discount_percentage' => $data['discount_percentage'] ?? 0,
            ':added_by' => $data['added_by'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function updateGuestListEntry($guestListId, $tenantId, $data)
    {
        $allowed = ['guest_name', 'phone', 'email', 'party_size', 'entry_type', 'discount_percentage', 'notes'];
        $fields = [];
        $params = [':guest_list_id' => $guestListId, ':tenant_id' => $tenantId];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE nightclub_guest_lists SET " . implode(', ', $fields) . " WHERE guest_list_id = :guest_list_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function checkInGuest($guestListId, $tenantId)
    {
        $sql = "UPDATE nightclub_guest_lists SET check_in_status = 1, check_in_at = NOW() WHERE guest_list_id = :guest_list_id AND tenant_id = :tenant_id AND check_in_status = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':guest_list_id' => $guestListId, ':tenant_id' => $tenantId]);
        return $stmt->rowCount() > 0;
    }

    public function deleteGuestListEntry($guestListId, $tenantId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM nightclub_guest_lists WHERE guest_list_id = :guest_list_id AND tenant_id = :tenant_id");
        return $stmt->execute([':guest_list_id' => $guestListId, ':tenant_id' => $tenantId]);
    }

    // ==================== BOTTLE SERVICE ====================

    public function getBottleServiceReservations($tenantId, $eventId = null, $status = null)
    {
        $sql = "SELECT b.*, e.event_name, t.table_number, z.zone_name FROM nightclub_bottle_service b
                LEFT JOIN nightclub_events e ON b.event_id = e.event_id
                LEFT JOIN tables t ON b.table_id = t.table_id
                LEFT JOIN zones z ON b.zone_id = z.zone_id
                WHERE b.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($eventId) {
            $sql .= " AND b.event_id = :event_id";
            $params[':event_id'] = $eventId;
        }
        if ($status) {
            $sql .= " AND b.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY b.reservation_date DESC, b.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBottleServiceReservation($data)
    {
        $sql = "INSERT INTO nightclub_bottle_service (tenant_id, branch_id, event_id, table_id, zone_id, customer_name, phone, party_size, package_name, bottle_type, bottle_quantity, unit_price, minimum_spend, total_amount, reservation_date, reservation_time, status, payment_status, payment_method, special_requests)
                VALUES (:tenant_id, :branch_id, :event_id, :table_id, :zone_id, :customer_name, :phone, :party_size, :package_name, :bottle_type, :bottle_quantity, :unit_price, :minimum_spend, :total_amount, :reservation_date, :reservation_time, :status, :payment_status, :payment_method, :special_requests)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':event_id' => $data['event_id'] ?? null,
            ':table_id' => $data['table_id'] ?? null,
            ':zone_id' => $data['zone_id'] ?? null,
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null,
            ':party_size' => $data['party_size'] ?? 1,
            ':package_name' => $data['package_name'],
            ':bottle_type' => $data['bottle_type'] ?? null,
            ':bottle_quantity' => $data['bottle_quantity'] ?? 1,
            ':unit_price' => $data['unit_price'],
            ':minimum_spend' => $data['minimum_spend'] ?? 0,
            ':total_amount' => $data['total_amount'] ?? ($data['unit_price'] * ($data['bottle_quantity'] ?? 1)),
            ':reservation_date' => $data['reservation_date'],
            ':reservation_time' => $data['reservation_time'] ?? null,
            ':status' => $data['status'] ?? 'PENDING',
            ':payment_status' => $data['payment_status'] ?? 'PENDING',
            ':payment_method' => $data['payment_method'] ?? null,
            ':special_requests' => $data['special_requests'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $reservationId = $this->pdo->lastInsertId();

        // Auto-post to accounting if payment is PAID
        if (($data['payment_status'] ?? 'PENDING') === 'PAID' && ($data['total_amount'] ?? 0) > 0) {
            $this->postBottleServiceRevenueToAccounting($reservationId, $data);
        }

        return $reservationId;
    }

    public function updateBottleServiceReservation($reservationId, $tenantId, $data)
    {
        $allowed = ['customer_name', 'phone', 'party_size', 'package_name', 'bottle_type', 'bottle_quantity', 'unit_price', 'minimum_spend', 'total_amount', 'reservation_date', 'reservation_time', 'status', 'payment_status', 'payment_method', 'special_requests', 'table_id', 'zone_id'];
        $fields = [];
        $params = [':bottle_service_id' => $reservationId, ':tenant_id' => $tenantId];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE nightclub_bottle_service SET " . implode(', ', $fields) . " WHERE bottle_service_id = :bottle_service_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteBottleServiceReservation($reservationId, $tenantId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM nightclub_bottle_service WHERE bottle_service_id = :bottle_service_id AND tenant_id = :tenant_id");
        return $stmt->execute([':bottle_service_id' => $reservationId, ':tenant_id' => $tenantId]);
    }

    // ==================== TABLE RESERVATIONS ====================

    public function getTableReservations($tenantId, $eventId = null, $status = null)
    {
        $sql = "SELECT r.*, e.event_name, t.table_number, z.zone_name FROM nightclub_table_reservations r
                LEFT JOIN nightclub_events e ON r.event_id = e.event_id
                LEFT JOIN tables t ON r.table_id = t.table_id
                LEFT JOIN zones z ON r.zone_id = z.zone_id
                WHERE r.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($eventId) {
            $sql .= " AND r.event_id = :event_id";
            $params[':event_id'] = $eventId;
        }
        if ($status) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY r.reservation_date DESC, r.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTableReservation($data)
    {
        $sql = "INSERT INTO nightclub_table_reservations (tenant_id, branch_id, event_id, table_id, zone_id, customer_name, phone, email, party_size, reservation_date, arrival_time, minimum_spend, table_type, status, assigned_by, notes)
                VALUES (:tenant_id, :branch_id, :event_id, :table_id, :zone_id, :customer_name, :phone, :email, :party_size, :reservation_date, :arrival_time, :minimum_spend, :table_type, :status, :assigned_by, :notes)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':event_id' => $data['event_id'] ?? null,
            ':table_id' => $data['table_id'] ?? null,
            ':zone_id' => $data['zone_id'] ?? null,
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':party_size' => $data['party_size'],
            ':reservation_date' => $data['reservation_date'],
            ':arrival_time' => $data['arrival_time'] ?? null,
            ':minimum_spend' => $data['minimum_spend'] ?? 0,
            ':table_type' => $data['table_type'] ?? null,
            ':status' => $data['status'] ?? 'PENDING',
            ':assigned_by' => $data['assigned_by'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $reservationId = $this->pdo->lastInsertId();

        // Auto-post deposit to accounting if confirmed with minimum spend
        if (($data['status'] ?? 'PENDING') === 'CONFIRMED' && ($data['minimum_spend'] ?? 0) > 0) {
            $this->postTableReservationDepositToAccounting($reservationId, $data);
        }

        return $reservationId;
    }

    public function updateTableReservation($reservationId, $tenantId, $data)
    {
        $allowed = ['customer_name', 'phone', 'email', 'party_size', 'reservation_date', 'arrival_time', 'minimum_spend', 'table_type', 'status', 'table_id', 'zone_id', 'notes'];
        $fields = [];
        $params = [':reservation_id' => $reservationId, ':tenant_id' => $tenantId];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE nightclub_table_reservations SET " . implode(', ', $fields) . " WHERE reservation_id = :reservation_id AND tenant_id = :tenant_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteTableReservation($reservationId, $tenantId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM nightclub_table_reservations WHERE reservation_id = :reservation_id AND tenant_id = :tenant_id");
        return $stmt->execute([':reservation_id' => $reservationId, ':tenant_id' => $tenantId]);
    }

    // ==================== DASHBOARD STATS ====================

    public function getDashboardStats($tenantId, $eventId = null)
    {
        $stats = [];

        // Event count
        $sql = "SELECT COUNT(*) as total_events, SUM(CASE WHEN status = 'SCHEDULED' THEN 1 ELSE 0 END) as upcoming FROM nightclub_events WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($eventId) { $sql .= " AND event_id = :event_id"; $params[':event_id'] = $eventId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stats['events'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Entrance tickets
        $sql = "SELECT COUNT(*) as total_tickets, SUM(CASE WHEN check_in_status = 1 THEN 1 ELSE 0 END) as checked_in, SUM(total_amount) as total_revenue FROM nightclub_entrance_tickets WHERE tenant_id = :tenant_id";
        if ($eventId) { $sql .= " AND event_id = :event_id"; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stats['tickets'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Guest list
        $sql = "SELECT COUNT(*) as total_guests, SUM(CASE WHEN check_in_status = 1 THEN 1 ELSE 0 END) as checked_in FROM nightclub_guest_lists WHERE tenant_id = :tenant_id";
        if ($eventId) { $sql .= " AND event_id = :event_id"; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stats['guest_list'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Bottle service
        $sql = "SELECT COUNT(*) as total_reservations, SUM(total_amount) as total_revenue FROM nightclub_bottle_service WHERE tenant_id = :tenant_id";
        if ($eventId) { $sql .= " AND event_id = :event_id"; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stats['bottle_service'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Table reservations
        $sql = "SELECT COUNT(*) as total_reservations, SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) as confirmed FROM nightclub_table_reservations WHERE tenant_id = :tenant_id";
        if ($eventId) { $sql .= " AND event_id = :event_id"; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stats['table_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $stats;
    }

    // ==================== ACCOUNTING INTEGRATION ====================

    /**
     * Get account ID by tenant and account_code
     */
    private function getAccountId($tenantId, $accountCode)
    {
        $stmt = $this->pdo->prepare("SELECT account_id FROM chart_of_accounts WHERE tenant_id = :tenant_id AND account_code = :account_code AND is_active = 1 AND deleted_at IS NULL");
        $stmt->execute([':tenant_id' => $tenantId, ':account_code' => $accountCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['account_id'] : null;
    }

    /**
     * Post entrance ticket revenue to accounting (journal entry)
     * Debit: Cash/Bank (1000/1020), Credit: Entrance Fee Revenue (4000)
     */
    public function postTicketRevenueToAccounting($ticketId, $data)
    {
        $tenantId = $data['tenant_id'];
        $branchId = $data['branch_id'] ?? null;
        $totalAmount = $data['total_amount'] ?? ($data['unit_price'] * ($data['quantity'] ?? 1));

        if ($totalAmount <= 0) return false;

        // Determine revenue account based on fee type or default to 4000
        $revenueAccountCode = '4000'; // Entrance Fee Revenue
        if (isset($data['fee_type']) && $data['fee_type'] === 'EARLY_BIRD') {
            $revenueAccountCode = '4010'; // Early Bird Ticket Revenue
        }

        // Determine cash account based on payment method
        $cashAccountCode = '1000'; // Cash on Hand
        if (($data['payment_method'] ?? 'CASH') === 'CARD' || ($data['payment_method'] ?? 'CASH') === 'TRANSFER') {
            $cashAccountCode = '1020'; // Bank Account
        }

        $cashAccountId = $this->getAccountId($tenantId, $cashAccountCode);
        $revenueAccountId = $this->getAccountId($tenantId, $revenueAccountCode);

        if (!$cashAccountId || !$revenueAccountId) {
            // CoA not set up for this tenant - skip silently
            return false;
        }

        $journalNumber = 'JE-NCT-' . date('Ymd') . '-' . str_pad((string)$ticketId, 6, '0', STR_PAD_LEFT);

        // Create journal entry
        $sql = "INSERT INTO journal_entries (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status)
                VALUES (:tenant_id, :branch_id, :journal_number, :journal_date, :reference_type, :reference_id, :description, 'POSTED')";
        $params = [
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':journal_number' => $journalNumber,
            ':journal_date' => date('Y-m-d'),
            ':reference_type' => 'NIGHTCLUB_TICKET',
            ':reference_id' => $ticketId,
            ':description' => 'Entrance ticket sale - ' . ($data['customer_name'] ?? 'Walk-in') . ' - Ticket #' . $ticketId,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $journalEntryId = $this->pdo->lastInsertId();

        // Debit: Cash/Bank
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (:journal_entry_id, :account_id, :debit, 0, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':journal_entry_id' => $journalEntryId,
            ':account_id' => $cashAccountId,
            ':debit' => $totalAmount,
            ':description' => 'Cash received for entrance ticket',
        ]);

        // Credit: Entrance Fee Revenue
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (:journal_entry_id, :account_id, 0, :credit, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':journal_entry_id' => $journalEntryId,
            ':account_id' => $revenueAccountId,
            ':credit' => $totalAmount,
            ':description' => 'Entrance fee revenue - ticket sale',
        ]);

        return $journalEntryId;
    }

    /**
     * Post bottle service revenue to accounting (journal entry)
     * Debit: Cash/Bank (1000/1020), Credit: Bottle Service Revenue (4100)
     */
    public function postBottleServiceRevenueToAccounting($reservationId, $data)
    {
        $tenantId = $data['tenant_id'];
        $branchId = $data['branch_id'] ?? null;
        $totalAmount = $data['total_amount'] ?? ($data['unit_price'] * ($data['bottle_quantity'] ?? 1));

        if ($totalAmount <= 0) return false;

        $revenueAccountCode = '4100'; // Bottle Service Revenue
        $cashAccountCode = '1000'; // Cash on Hand
        if (($data['payment_method'] ?? 'CASH') === 'CARD' || ($data['payment_method'] ?? 'CASH') === 'TRANSFER') {
            $cashAccountCode = '1020'; // Bank Account
        }

        $cashAccountId = $this->getAccountId($tenantId, $cashAccountCode);
        $revenueAccountId = $this->getAccountId($tenantId, $revenueAccountCode);

        if (!$cashAccountId || !$revenueAccountId) {
            return false;
        }

        $journalNumber = 'JE-NBS-' . date('Ymd') . '-' . str_pad((string)$reservationId, 6, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO journal_entries (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status)
                VALUES (:tenant_id, :branch_id, :journal_number, :journal_date, :reference_type, :reference_id, :description, 'POSTED')";
        $params = [
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':journal_number' => $journalNumber,
            ':journal_date' => $data['reservation_date'] ?? date('Y-m-d'),
            ':reference_type' => 'NIGHTCLUB_BOTTLE',
            ':reference_id' => $reservationId,
            ':description' => 'Bottle service - ' . ($data['customer_name'] ?? 'Customer') . ' - ' . ($data['package_name'] ?? 'Package'),
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $journalEntryId = $this->pdo->lastInsertId();

        // Debit: Cash/Bank
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (:journal_entry_id, :account_id, :debit, 0, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':journal_entry_id' => $journalEntryId,
            ':account_id' => $cashAccountId,
            ':debit' => $totalAmount,
            ':description' => 'Cash received for bottle service',
        ]);

        // Credit: Bottle Service Revenue
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (:journal_entry_id, :account_id, 0, :credit, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':journal_entry_id' => $journalEntryId,
            ':account_id' => $revenueAccountId,
            ':credit' => $totalAmount,
            ':description' => 'Bottle service revenue',
        ]);

        return $journalEntryId;
    }

    /**
     * Post table reservation deposit to accounting (journal entry)
     * Debit: Cash/Bank (1000/1020), Credit: Customer Deposits (2100)
     */
    public function postTableReservationDepositToAccounting($reservationId, $data)
    {
        $tenantId = $data['tenant_id'];
        $branchId = $data['branch_id'] ?? null;
        $depositAmount = $data['minimum_spend'] ?? 0;

        if ($depositAmount <= 0) return false;

        $liabilityAccountCode = '2100'; // Customer Deposits (Bottle Service)
        $cashAccountCode = '1000'; // Cash on Hand
        if (($data['payment_method'] ?? 'CASH') === 'CARD' || ($data['payment_method'] ?? 'CASH') === 'TRANSFER') {
            $cashAccountCode = '1020'; // Bank Account
        }

        $cashAccountId = $this->getAccountId($tenantId, $cashAccountCode);
        $liabilityAccountId = $this->getAccountId($tenantId, $liabilityAccountCode);

        if (!$cashAccountId || !$liabilityAccountId) {
            return false;
        }

        $journalNumber = 'JE-NTR-' . date('Ymd') . '-' . str_pad((string)$reservationId, 6, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO journal_entries (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status)
                VALUES (:tenant_id, :branch_id, :journal_number, :journal_date, :reference_type, :reference_id, :description, 'POSTED')";
        $params = [
            ':tenant_id' => $tenantId,
            ':branch_id' => $branchId,
            ':journal_number' => $journalNumber,
            ':journal_date' => $data['reservation_date'] ?? date('Y-m-d'),
            ':reference_type' => 'NIGHTCLUB_TABLE_RES',
            ':reference_id' => $reservationId,
            ':description' => 'Table reservation deposit - ' . ($data['customer_name'] ?? 'Customer') . ' - ' . ($data['table_type'] ?? 'Table'),
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $journalEntryId = $this->pdo->lastInsertId();

        // Debit: Cash/Bank
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (:journal_entry_id, :account_id, :debit, 0, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':journal_entry_id' => $journalEntryId,
            ':account_id' => $cashAccountId,
            ':debit' => $depositAmount,
            ':description' => 'Cash received for table reservation deposit',
        ]);

        // Credit: Customer Deposits (liability)
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (:journal_entry_id, :account_id, 0, :credit, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':journal_entry_id' => $journalEntryId,
            ':account_id' => $liabilityAccountId,
            ':credit' => $depositAmount,
            ':description' => 'Customer deposit for table reservation',
        ]);

        return $journalEntryId;
    }

    // ==================== REVENUE REPORTS ====================

    /**
     * Get revenue breakdown by stream (entrance, bottle service, table reservations)
     */
    public function getRevenueReport($tenantId, $startDate = null, $endDate = null)
    {
        $report = [];
        $start = $startDate ?? date('Y-m-01');
        $end = $endDate ?? date('Y-m-t');

        // Entrance ticket revenue
        $sql = "SELECT
                    COALESCE(SUM(total_amount), 0) as total_revenue,
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN payment_status = 'PAID' THEN total_amount ELSE 0 END) as paid_revenue,
                    SUM(CASE WHEN payment_status = 'PENDING' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN check_in_status = 1 THEN 1 ELSE 0 END) as checked_in
                 FROM nightclub_entrance_tickets
                 WHERE tenant_id = :tenant_id AND DATE(sold_at) BETWEEN :start AND :end";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':start' => $start, ':end' => $end]);
        $report['entrance_tickets'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Bottle service revenue
        $sql = "SELECT
                    COALESCE(SUM(total_amount), 0) as total_revenue,
                    COUNT(*) as total_reservations,
                    SUM(CASE WHEN payment_status = 'PAID' THEN total_amount ELSE 0 END) as paid_revenue,
                    SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending
                 FROM nightclub_bottle_service
                 WHERE tenant_id = :tenant_id AND reservation_date BETWEEN :start AND :end";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':start' => $start, ':end' => $end]);
        $report['bottle_service'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Table reservation stats
        $sql = "SELECT
                    COUNT(*) as total_reservations,
                    SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                    COALESCE(SUM(minimum_spend), 0) as total_minimum_spend
                 FROM nightclub_table_reservations
                 WHERE tenant_id = :tenant_id AND reservation_date BETWEEN :start AND :end";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':start' => $start, ':end' => $end]);
        $report['table_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Guest list stats
        $sql = "SELECT
                    COUNT(*) as total_guests,
                    SUM(CASE WHEN check_in_status = 1 THEN 1 ELSE 0 END) as checked_in,
                    SUM(party_size) as total_party_size
                 FROM nightclub_guest_lists
                 WHERE tenant_id = :tenant_id AND DATE(created_at) BETWEEN :start AND :end";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':start' => $start, ':end' => $end]);
        $report['guest_list'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Per-event breakdown
        $sql = "SELECT
                    e.event_id,
                    e.event_name,
                    e.event_date,
                    COALESCE(t.ticket_revenue, 0) as ticket_revenue,
                    COALESCE(t.ticket_count, 0) as ticket_count,
                    COALESCE(b.bottle_revenue, 0) as bottle_revenue,
                    COALESCE(b.bottle_count, 0) as bottle_count,
                    COALESCE(g.guest_count, 0) as guest_count,
                    COALESCE(r.reservation_count, 0) as reservation_count
                 FROM nightclub_events e
                 LEFT JOIN (SELECT event_id, SUM(total_amount) as ticket_revenue, COUNT(*) as ticket_count FROM nightclub_entrance_tickets WHERE tenant_id = ? GROUP BY event_id) t ON e.event_id = t.event_id
                 LEFT JOIN (SELECT event_id, SUM(total_amount) as bottle_revenue, COUNT(*) as bottle_count FROM nightclub_bottle_service WHERE tenant_id = ? GROUP BY event_id) b ON e.event_id = b.event_id
                 LEFT JOIN (SELECT event_id, COUNT(*) as guest_count FROM nightclub_guest_lists WHERE tenant_id = ? GROUP BY event_id) g ON e.event_id = g.event_id
                 LEFT JOIN (SELECT event_id, COUNT(*) as reservation_count FROM nightclub_table_reservations WHERE tenant_id = ? GROUP BY event_id) r ON e.event_id = r.event_id
                 WHERE e.tenant_id = ? AND e.event_date BETWEEN ? AND ?
                 ORDER BY e.event_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tenantId, $tenantId, $tenantId, $tenantId, $tenantId, $start, $end]);
        $report['per_event'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total summary
        $report['summary'] = [
            'total_revenue' => ($report['entrance_tickets']['paid_revenue'] ?? 0) + ($report['bottle_service']['paid_revenue'] ?? 0),
            'total_tickets_sold' => $report['entrance_tickets']['total_tickets'] ?? 0,
            'total_bottle_reservations' => $report['bottle_service']['total_reservations'] ?? 0,
            'total_guests' => $report['guest_list']['total_guests'] ?? 0,
            'total_table_reservations' => $report['table_reservations']['total_reservations'] ?? 0,
            'period_start' => $start,
            'period_end' => $end,
        ];

        return $report;
    }
}
