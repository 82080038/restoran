<?php

namespace App\Modules\Entertainment\Services;

use App\Core\Database;
use PDO;

/**
 * EntertainmentService - Handles Karaoke Bar, Beach Club, and Live Music Venue operations
 * 
 * These business types share similar flow with Nightclub:
 * - Time-based reservations (rooms/cabanas/seats)
 * - Events with entrance fees
 * - F&B service integration
 * - Accounting auto-post to journal
 */
class EntertainmentService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== KARAOKE BAR ====================

    public function getKaraokeRooms($tenantId, $roomType = null)
    {
        $sql = "SELECT * FROM karaoke_rooms WHERE tenant_id = :tenant_id AND is_active = 1";
        $params = [':tenant_id' => $tenantId];
        if ($roomType) {
            $sql .= " AND room_type = :room_type";
            $params[':room_type'] = $roomType;
        }
        $sql .= " ORDER BY sort_order IS NULL, room_code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createKaraokeRoom($data)
    {
        $sql = "INSERT INTO karaoke_rooms (tenant_id, branch_id, room_code, room_name, room_type, capacity, hourly_rate, minimum_spend, has_private_bathroom, has_waiter_button)
                VALUES (:tenant_id, :branch_id, :room_code, :room_name, :room_type, :capacity, :hourly_rate, :minimum_spend, :has_private_bathroom, :has_waiter_button)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':room_code' => $data['room_code'],
            ':room_name' => $data['room_name'],
            ':room_type' => $data['room_type'] ?? 'STANDARD',
            ':capacity' => $data['capacity'] ?? 4,
            ':hourly_rate' => $data['hourly_rate'] ?? 0,
            ':minimum_spend' => $data['minimum_spend'] ?? 0,
            ':has_private_bathroom' => $data['has_private_bathroom'] ?? 0,
            ':has_waiter_button' => $data['has_waiter_button'] ?? 1,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function getKaraokeReservations($tenantId, $date = null, $status = null)
    {
        $sql = "SELECT r.*, rm.room_name, rm.room_code, rm.room_type FROM karaoke_reservations r
                LEFT JOIN karaoke_rooms rm ON r.room_id = rm.room_id
                WHERE r.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($date) {
            $sql .= " AND r.reservation_date = :date";
            $params[':date'] = $date;
        }
        if ($status) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY r.reservation_date DESC, r.start_time DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createKaraokeReservation($data)
    {
        $sql = "INSERT INTO karaoke_reservations (tenant_id, branch_id, room_id, customer_name, phone, email, party_size, reservation_date, start_time, end_time, hourly_rate, room_charge, minimum_spend, total_amount, payment_status, payment_method, status, special_requests)
                VALUES (:tenant_id, :branch_id, :room_id, :customer_name, :phone, :email, :party_size, :reservation_date, :start_time, :end_time, :hourly_rate, :room_charge, :minimum_spend, :total_amount, :payment_status, :payment_method, :status, :special_requests)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':room_id' => $data['room_id'],
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':party_size' => $data['party_size'] ?? 1,
            ':reservation_date' => $data['reservation_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'] ?? null,
            ':hourly_rate' => $data['hourly_rate'],
            ':room_charge' => $data['room_charge'] ?? 0,
            ':minimum_spend' => $data['minimum_spend'] ?? 0,
            ':total_amount' => $data['total_amount'] ?? 0,
            ':payment_status' => $data['payment_status'] ?? 'PENDING',
            ':payment_method' => $data['payment_method'] ?? null,
            ':status' => $data['status'] ?? 'PENDING',
            ':special_requests' => $data['special_requests'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $reservationId = $this->pdo->lastInsertId();

        // Auto-post to accounting if PAID
        if (($data['payment_status'] ?? 'PENDING') === 'PAID' && ($data['total_amount'] ?? 0) > 0) {
            $this->postToAccounting($reservationId, $data, 'KARAOKE_RESERVATION', '4200', 'Karaoke room reservation');
        }

        return $reservationId;
    }

    public function checkInKaraoke($reservationId, $tenantId)
    {
        $sql = "UPDATE karaoke_reservations SET status = 'CHECKED_IN', checked_in_at = NOW() WHERE reservation_id = :id AND tenant_id = :tenant_id AND status IN ('PENDING','CONFIRMED')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $reservationId, ':tenant_id' => $tenantId]);
        return $stmt->rowCount() > 0;
    }

    public function checkOutKaraoke($reservationId, $tenantId, $actualEndTime, $totalBill)
    {
        $sql = "UPDATE karaoke_reservations SET status = 'COMPLETED', actual_end_time = :end_time, total_amount = :total, checked_out_at = NOW() WHERE reservation_id = :id AND tenant_id = :tenant_id AND status = 'CHECKED_IN'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $reservationId, ':tenant_id' => $tenantId, ':end_time' => $actualEndTime, ':total' => $totalBill]);
        return $stmt->rowCount() > 0;
    }

    // ==================== BEACH CLUB ====================

    public function getBeachCabanas($tenantId, $cabanaType = null)
    {
        $sql = "SELECT * FROM beach_club_cabanas WHERE tenant_id = :tenant_id AND is_active = 1";
        $params = [':tenant_id' => $tenantId];
        if ($cabanaType) {
            $sql .= " AND cabana_type = :cabana_type";
            $params[':cabana_type'] = $cabanaType;
        }
        $sql .= " ORDER BY cabana_code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBeachCabana($data)
    {
        $sql = "INSERT INTO beach_club_cabanas (tenant_id, branch_id, cabana_code, cabana_name, cabana_type, capacity, daily_rate, minimum_spend, location, has_butler, has_private_pool)
                VALUES (:tenant_id, :branch_id, :cabana_code, :cabana_name, :cabana_type, :capacity, :daily_rate, :minimum_spend, :location, :has_butler, :has_private_pool)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':cabana_code' => $data['cabana_code'],
            ':cabana_name' => $data['cabana_name'],
            ':cabana_type' => $data['cabana_type'] ?? 'SUNBED',
            ':capacity' => $data['capacity'] ?? 2,
            ':daily_rate' => $data['daily_rate'] ?? 0,
            ':minimum_spend' => $data['minimum_spend'] ?? 0,
            ':location' => $data['location'] ?? null,
            ':has_butler' => $data['has_butler'] ?? 0,
            ':has_private_pool' => $data['has_private_pool'] ?? 0,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function getBeachReservations($tenantId, $date = null, $status = null)
    {
        $sql = "SELECT r.*, c.cabana_name, c.cabana_code, c.cabana_type FROM beach_club_reservations r
                LEFT JOIN beach_club_cabanas c ON r.cabana_id = c.cabana_id
                WHERE r.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($date) {
            $sql .= " AND r.reservation_date = :date";
            $params[':date'] = $date;
        }
        if ($status) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY r.reservation_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBeachReservation($data)
    {
        $sql = "INSERT INTO beach_club_reservations (tenant_id, branch_id, cabana_id, customer_name, phone, email, party_size, reservation_date, arrival_time, daily_rate, minimum_spend, total_amount, payment_status, payment_method, status, includes_pool_access, includes_towel, special_requests)
                VALUES (:tenant_id, :branch_id, :cabana_id, :customer_name, :phone, :email, :party_size, :reservation_date, :arrival_time, :daily_rate, :minimum_spend, :total_amount, :payment_status, :payment_method, :status, :includes_pool_access, :includes_towel, :special_requests)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':cabana_id' => $data['cabana_id'],
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':party_size' => $data['party_size'] ?? 1,
            ':reservation_date' => $data['reservation_date'],
            ':arrival_time' => $data['arrival_time'] ?? null,
            ':daily_rate' => $data['daily_rate'],
            ':minimum_spend' => $data['minimum_spend'] ?? 0,
            ':total_amount' => $data['total_amount'] ?? 0,
            ':payment_status' => $data['payment_status'] ?? 'PENDING',
            ':payment_method' => $data['payment_method'] ?? null,
            ':status' => $data['status'] ?? 'PENDING',
            ':includes_pool_access' => $data['includes_pool_access'] ?? 1,
            ':includes_towel' => $data['includes_towel'] ?? 1,
            ':special_requests' => $data['special_requests'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $reservationId = $this->pdo->lastInsertId();

        if (($data['payment_status'] ?? 'PENDING') === 'PAID' && ($data['total_amount'] ?? 0) > 0) {
            $this->postToAccounting($reservationId, $data, 'BEACH_CLUB_RESERVATION', '4300', 'Beach club cabana reservation');
        }

        return $reservationId;
    }

    public function getBeachEvents($tenantId, $status = null)
    {
        $sql = "SELECT * FROM beach_club_events WHERE tenant_id = :tenant_id AND is_active = 1";
        $params = [':tenant_id' => $tenantId];
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY event_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBeachEvent($data)
    {
        $sql = "INSERT INTO beach_club_events (tenant_id, branch_id, event_name, description, event_date, start_time, end_time, theme, dj_name, music_genre, entrance_fee, capacity, status)
                VALUES (:tenant_id, :branch_id, :event_name, :description, :event_date, :start_time, :end_time, :theme, :dj_name, :music_genre, :entrance_fee, :capacity, :status)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':event_name' => $data['event_name'],
            ':description' => $data['description'] ?? null,
            ':event_date' => $data['event_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'] ?? null,
            ':theme' => $data['theme'] ?? null,
            ':dj_name' => $data['dj_name'] ?? null,
            ':music_genre' => $data['music_genre'] ?? null,
            ':entrance_fee' => $data['entrance_fee'] ?? 0,
            ':capacity' => $data['capacity'] ?? 200,
            ':status' => $data['status'] ?? 'SCHEDULED',
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    // ==================== LIVE MUSIC VENUE ====================

    public function getConcerts($tenantId, $status = null)
    {
        $sql = "SELECT * FROM live_music_concerts WHERE tenant_id = :tenant_id AND is_active = 1";
        $params = [':tenant_id' => $tenantId];
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY concert_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createConcert($data)
    {
        $sql = "INSERT INTO live_music_concerts (tenant_id, branch_id, concert_name, artist_name, genre, concert_date, doors_open_time, show_time, end_time, venue_capacity, status, poster_url, description)
                VALUES (:tenant_id, :branch_id, :concert_name, :artist_name, :genre, :concert_date, :doors_open_time, :show_time, :end_time, :venue_capacity, :status, :poster_url, :description)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':concert_name' => $data['concert_name'],
            ':artist_name' => $data['artist_name'],
            ':genre' => $data['genre'] ?? null,
            ':concert_date' => $data['concert_date'],
            ':doors_open_time' => $data['doors_open_time'],
            ':show_time' => $data['show_time'],
            ':end_time' => $data['end_time'] ?? null,
            ':venue_capacity' => $data['venue_capacity'] ?? 500,
            ':status' => $data['status'] ?? 'SCHEDULED',
            ':poster_url' => $data['poster_url'] ?? null,
            ':description' => $data['description'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function getSeatingSections($tenantId)
    {
        $sql = "SELECT * FROM live_music_seating_sections WHERE tenant_id = :tenant_id AND is_active = 1 ORDER BY sort_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSeatingSection($data)
    {
        $sql = "INSERT INTO live_music_seating_sections (tenant_id, branch_id, section_name, section_type, capacity, price, is_numbered, sort_order)
                VALUES (:tenant_id, :branch_id, :section_name, :section_type, :capacity, :price, :is_numbered, :sort_order)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':section_name' => $data['section_name'],
            ':section_type' => $data['section_type'] ?? 'GA_STANDING',
            ':capacity' => $data['capacity'],
            ':price' => $data['price'],
            ':is_numbered' => $data['is_numbered'] ?? 0,
            ':sort_order' => $data['sort_order'] ?? 0,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function getConcertTickets($tenantId, $concertId = null)
    {
        $sql = "SELECT t.*, c.concert_name, c.artist_name, c.concert_date, s.section_name, s.section_type
                FROM live_music_tickets t
                LEFT JOIN live_music_concerts c ON t.concert_id = c.concert_id
                LEFT JOIN live_music_seating_sections s ON t.section_id = s.section_id
                WHERE t.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($concertId) {
            $sql .= " AND t.concert_id = :concert_id";
            $params[':concert_id'] = $concertId;
        }
        $sql .= " ORDER BY t.sold_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createConcertTicket($data)
    {
        $ticketCode = 'LMT-' . strtoupper(substr(uniqid(), -8));
        $sql = "INSERT INTO live_music_tickets (tenant_id, branch_id, concert_id, section_id, seat_number, customer_name, phone, email, ticket_type, unit_price, quantity, total_amount, ticket_code, payment_status, payment_method, sold_by)
                VALUES (:tenant_id, :branch_id, :concert_id, :section_id, :seat_number, :customer_name, :phone, :email, :ticket_type, :unit_price, :quantity, :total_amount, :ticket_code, :payment_status, :payment_method, :sold_by)";
        $params = [
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':concert_id' => $data['concert_id'],
            ':section_id' => $data['section_id'],
            ':seat_number' => $data['seat_number'] ?? null,
            ':customer_name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':ticket_type' => $data['ticket_type'] ?? 'GA',
            ':unit_price' => $data['unit_price'],
            ':quantity' => $data['quantity'] ?? 1,
            ':total_amount' => $data['total_amount'] ?? ($data['unit_price'] * ($data['quantity'] ?? 1)),
            ':ticket_code' => $ticketCode,
            ':payment_status' => $data['payment_status'] ?? 'PENDING',
            ':payment_method' => $data['payment_method'] ?? null,
            ':sold_by' => $data['sold_by'] ?? null,
        ];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $ticketId = $this->pdo->lastInsertId();

        if (($data['payment_status'] ?? 'PENDING') === 'PAID' && ($data['total_amount'] ?? 0) > 0) {
            $this->postToAccounting($ticketId, $data, 'LIVE_MUSIC_TICKET', '4500', 'Concert ticket sale');
        }

        return ['ticket_id' => $ticketId, 'ticket_code' => $ticketCode];
    }

    public function checkInConcertTicket($ticketId, $tenantId)
    {
        $sql = "UPDATE live_music_tickets SET check_in_status = 1, check_in_at = NOW() WHERE ticket_id = :id AND tenant_id = :tenant_id AND check_in_status = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $ticketId, ':tenant_id' => $tenantId]);
        return $stmt->rowCount() > 0;
    }

    // ==================== DASHBOARD STATS ====================

    public function getDashboardStats($tenantId, $businessType)
    {
        $stats = [];

        if ($businessType === 'KARAOKE_BAR') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_rooms, COUNT(CASE WHEN equipment_status='ACTIVE' THEN 1 END) as active_rooms FROM karaoke_rooms WHERE tenant_id = :tenant_id AND is_active=1");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['rooms'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_reservations, COALESCE(SUM(total_amount),0) as total_revenue, COUNT(CASE WHEN status='CHECKED_IN' THEN 1 END) as active_sessions FROM karaoke_reservations WHERE tenant_id = :tenant_id");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['reservations'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($businessType === 'BEACH_CLUB') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_cabanas, COUNT(CASE WHEN is_active=1 THEN 1 END) as active_cabanas FROM beach_club_cabanas WHERE tenant_id = :tenant_id");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['cabanas'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_reservations, COALESCE(SUM(total_amount),0) as total_revenue FROM beach_club_reservations WHERE tenant_id = :tenant_id");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['reservations'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_events FROM beach_club_events WHERE tenant_id = :tenant_id AND is_active=1");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['events'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($businessType === 'LIVE_MUSIC_VENUE') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_concerts, COUNT(CASE WHEN status='SCHEDULED' THEN 1 END) as upcoming FROM live_music_concerts WHERE tenant_id = :tenant_id AND is_active=1");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['concerts'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_tickets, COALESCE(SUM(total_amount),0) as total_revenue, COUNT(CASE WHEN payment_status='PAID' THEN 1 END) as paid_tickets FROM live_music_tickets WHERE tenant_id = :tenant_id");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['tickets'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_sections FROM live_music_seating_sections WHERE tenant_id = :tenant_id AND is_active=1");
            $stmt->execute([':tenant_id' => $tenantId]);
            $stats['sections'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $stats;
    }

    // ==================== REVENUE REPORTS ====================

    public function getRevenueReport($tenantId, $businessType, $startDate = null, $endDate = null)
    {
        $start = $startDate ?? date('Y-m-01');
        $end = $endDate ?? date('Y-m-t');
        $report = ['period_start' => $start, 'period_end' => $end];

        if ($businessType === 'KARAOKE_BAR') {
            $sql = "SELECT COALESCE(SUM(total_amount),0) as total_revenue, COUNT(*) as total_reservations,
                    SUM(CASE WHEN payment_status='PAID' THEN total_amount ELSE 0 END) as paid_revenue,
                    SUM(CASE WHEN status='COMPLETED' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status='CHECKED_IN' THEN 1 ELSE 0 END) as active
                    FROM karaoke_reservations WHERE tenant_id = ? AND reservation_date BETWEEN ? AND ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tenantId, $start, $end]);
            $report['karaoke'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($businessType === 'BEACH_CLUB') {
            $sql = "SELECT COALESCE(SUM(total_amount),0) as total_revenue, COUNT(*) as total_reservations,
                    SUM(CASE WHEN payment_status='PAID' THEN total_amount ELSE 0 END) as paid_revenue,
                    SUM(CASE WHEN status='COMPLETED' THEN 1 ELSE 0 END) as completed
                    FROM beach_club_reservations WHERE tenant_id = ? AND reservation_date BETWEEN ? AND ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tenantId, $start, $end]);
            $report['beach_club'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT COUNT(*) as total_events FROM beach_club_events WHERE tenant_id = ? AND event_date BETWEEN ? AND ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tenantId, $start, $end]);
            $report['events'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($businessType === 'LIVE_MUSIC_VENUE') {
            $sql = "SELECT COALESCE(SUM(total_amount),0) as total_revenue, COUNT(*) as total_tickets,
                    SUM(CASE WHEN payment_status='PAID' THEN total_amount ELSE 0 END) as paid_revenue,
                    SUM(CASE WHEN check_in_status=1 THEN 1 ELSE 0 END) as checked_in
                    FROM live_music_tickets WHERE tenant_id = ? AND DATE(sold_at) BETWEEN ? AND ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tenantId, $start, $end]);
            $report['tickets'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Per-concert breakdown
            $sql = "SELECT c.concert_id, c.concert_name, c.artist_name, c.concert_date,
                    COALESCE(t.ticket_count,0) as ticket_count, COALESCE(t.revenue,0) as revenue
                    FROM live_music_concerts c
                    LEFT JOIN (SELECT concert_id, COUNT(*) as ticket_count, SUM(total_amount) as revenue FROM live_music_tickets WHERE tenant_id = ? GROUP BY concert_id) t ON c.concert_id = t.concert_id
                    WHERE c.tenant_id = ? AND c.concert_date BETWEEN ? AND ?
                    ORDER BY c.concert_date DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tenantId, $tenantId, $start, $end]);
            $report['per_concert'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $report;
    }

    // ==================== ACCOUNTING INTEGRATION ====================

    private function getAccountId($tenantId, $accountCode)
    {
        $stmt = $this->pdo->prepare("SELECT account_id FROM chart_of_accounts WHERE tenant_id = :tenant_id AND account_code = :account_code AND is_active = 1 AND deleted_at IS NULL");
        $stmt->execute([':tenant_id' => $tenantId, ':account_code' => $accountCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['account_id'] : null;
    }

    /**
     * Generic accounting post for entertainment venues
     * Debit: Cash/Bank, Credit: Revenue account
     */
    private function postToAccounting($referenceId, $data, $referenceType, $revenueAccountCode, $description)
    {
        $tenantId = $data['tenant_id'];
        $branchId = $data['branch_id'] ?? null;
        $totalAmount = $data['total_amount'] ?? 0;

        if ($totalAmount <= 0) return false;

        $cashAccountCode = '1000';
        if (($data['payment_method'] ?? 'CASH') === 'CARD' || ($data['payment_method'] ?? 'CASH') === 'TRANSFER') {
            $cashAccountCode = '1020';
        }

        $cashAccountId = $this->getAccountId($tenantId, $cashAccountCode);
        $revenueAccountId = $this->getAccountId($tenantId, $revenueAccountCode);

        if (!$cashAccountId || !$revenueAccountId) return false;

        $journalNumber = 'JE-' . substr($referenceType, 0, 3) . '-' . date('Ymd') . '-' . str_pad((string)$referenceId, 6, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO journal_entries (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status)
                VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 'POSTED')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $journalNumber, $referenceType, $referenceId, $description . ' - #' . $referenceId]);
        $journalEntryId = $this->pdo->lastInsertId();

        // Debit: Cash
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (?, ?, ?, 0, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$journalEntryId, $cashAccountId, $totalAmount, 'Cash received']);

        // Credit: Revenue
        $sql = "INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, description) VALUES (?, ?, 0, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$journalEntryId, $revenueAccountId, $totalAmount, $description]);

        return $journalEntryId;
    }
}
