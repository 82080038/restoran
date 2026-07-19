<?php

namespace App\Modules\NightclubAdvanced\Services;

use App\Core\Database;
use PDO;

class NightclubAdvancedService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== TABLE DEPOSITS ====================

    public function createTableDeposit($data)
    {
        $sql = "INSERT INTO table_deposits (tenant_id, branch_id, reservation_id, table_id, customer_name, customer_phone, event_date, deposit_amount, payment_method, payment_ref, minimum_spend, no_show_cutoff, notes)
                VALUES (:tenant_id, :branch_id, :reservation_id, :table_id, :customer_name, :customer_phone, :event_date, :deposit_amount, :payment_method, :payment_ref, :minimum_spend, :no_show_cutoff, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':reservation_id' => $data['reservation_id'] ?? null,
            ':table_id' => $data['table_id'] ?? null,
            ':customer_name' => $data['customer_name'],
            ':customer_phone' => $data['customer_phone'] ?? null,
            ':event_date' => $data['event_date'],
            ':deposit_amount' => $data['deposit_amount'],
            ':payment_method' => $data['payment_method'] ?? null,
            ':payment_ref' => $data['payment_ref'] ?? null,
            ':minimum_spend' => $data['minimum_spend'] ?? 0,
            ':no_show_cutoff' => $data['no_show_cutoff'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ]);
        return ['deposit_id' => $this->pdo->lastInsertId()];
    }

    public function markDepositPaid($depositId, $paymentMethod, $paymentRef)
    {
        $sql = "UPDATE table_deposits SET deposit_status = 'PAID', payment_method = :pm, payment_ref = :ref WHERE deposit_id = :deposit_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':deposit_id' => $depositId, ':pm' => $paymentMethod, ':ref' => $paymentRef]);
        return ['success' => true];
    }

    public function forfeitDeposit($depositId, $reason)
    {
        $sql = "UPDATE table_deposits SET deposit_status = 'FORFEITED', notes = CONCAT(IFNULL(notes,''), :reason) WHERE deposit_id = :deposit_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':deposit_id' => $depositId, ':reason' => "\n[Forfeited] " . $reason]);
        return ['success' => true];
    }

    public function refundDeposit($depositId, $reason)
    {
        $sql = "UPDATE table_deposits SET deposit_status = 'REFUNDED', notes = CONCAT(IFNULL(notes,''), :reason) WHERE deposit_id = :deposit_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':deposit_id' => $depositId, ':reason' => "\n[Refunded] " . $reason]);
        return ['success' => true];
    }

    public function getTableDeposits($tenantId, $branchId, $status = null, $eventDate = null)
    {
        $sql = "SELECT * FROM table_deposits WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND deposit_status = :status"; $params[':status'] = $status; }
        if ($eventDate) { $sql .= " AND event_date = :event_date"; $params[':event_date'] = $eventDate; }
        $sql .= " ORDER BY event_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== BOTTLE SERVICE INVENTORY ====================

    public function getBottleInventory($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM bottle_service_inventory WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY bottle_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addBottleInventory($data)
    {
        $sql = "INSERT INTO bottle_service_inventory (tenant_id, branch_id, product_id, bottle_name, bottle_size, quantity_on_hand, unit_cost, selling_price, storage_location)
                VALUES (:tenant_id, :branch_id, :product_id, :bottle_name, :bottle_size, :qty, :unit_cost, :selling_price, :location)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':product_id' => $data['product_id'],
            ':bottle_name' => $data['bottle_name'],
            ':bottle_size' => $data['bottle_size'] ?? '750ml',
            ':qty' => $data['quantity_on_hand'] ?? 0,
            ':unit_cost' => $data['unit_cost'] ?? 0,
            ':selling_price' => $data['selling_price'] ?? 0,
            ':location' => $data['storage_location'] ?? null,
        ]);
        return ['bottle_inv_id' => $this->pdo->lastInsertId()];
    }

    public function assignBottle($data)
    {
        $sql = "INSERT INTO bottle_service_assignments (tenant_id, branch_id, event_id, table_id, bottle_inv_id, quantity, assigned_by)
                VALUES (:tenant_id, :branch_id, :event_id, :table_id, :bottle_inv_id, :qty, :assigned_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':event_id' => $data['event_id'] ?? null,
            ':table_id' => $data['table_id'] ?? null,
            ':bottle_inv_id' => $data['bottle_inv_id'],
            ':qty' => $data['quantity'] ?? 1,
            ':assigned_by' => $data['assigned_by'] ?? null,
        ]);

        $sql = "UPDATE bottle_service_inventory SET quantity_on_hand = quantity_on_hand - :qty, quantity_reserved = quantity_reserved + :qty WHERE bottle_inv_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $data['bottle_inv_id'], ':qty' => $data['quantity'] ?? 1]);

        return ['assignment_id' => $this->pdo->lastInsertId()];
    }

    public function serveBottle($assignmentId)
    {
        $sql = "UPDATE bottle_service_assignments SET status = 'SERVED' WHERE assignment_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $assignmentId]);

        $sql = "SELECT bottle_inv_id, quantity FROM bottle_service_assignments WHERE assignment_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $assignmentId]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($assignment) {
            $sql = "UPDATE bottle_service_inventory SET quantity_reserved = quantity_reserved - :qty, quantity_sold = quantity_sold + :qty WHERE bottle_inv_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $assignment['bottle_inv_id'], ':qty' => $assignment['quantity']]);
        }

        return ['success' => true];
    }

    // ==================== PROMOTERS ====================

    public function createPromoter($data)
    {
        $sql = "INSERT INTO promoters (tenant_id, branch_id, promoter_name, promoter_code, phone, email, commission_type, commission_rate, guest_list_limit, notes)
                VALUES (:tenant_id, :branch_id, :name, :code, :phone, :email, :commission_type, :rate, :limit, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':name' => $data['promoter_name'],
            ':code' => $data['promoter_code'] ?? 'PROMO-' . substr(uniqid(), -4),
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? null,
            ':commission_type' => $data['commission_type'] ?? 'PER_HEAD',
            ':rate' => $data['commission_rate'] ?? 0,
            ':limit' => $data['guest_list_limit'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ]);
        return ['promoter_id' => $this->pdo->lastInsertId()];
    }

    public function getPromoters($tenantId, $branchId, $activeOnly = false)
    {
        $sql = "SELECT * FROM promoters WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($activeOnly) { $sql .= " AND is_active = 1"; }
        $sql .= " ORDER BY promoter_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addGuestToPromoterList($data)
    {
        $commission = $this->calculatePromoterCommission($data['promoter_id'], $data['party_size'] ?? 1);

        $sql = "INSERT INTO promoter_guest_lists (promoter_id, tenant_id, branch_id, event_id, guest_name, guest_phone, party_size, entry_type, discount_amount, commission_amount)
                VALUES (:promoter_id, :tenant_id, :branch_id, :event_id, :guest_name, :guest_phone, :party_size, :entry_type, :discount, :commission)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':promoter_id' => $data['promoter_id'],
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':event_id' => $data['event_id'],
            ':guest_name' => $data['guest_name'],
            ':guest_phone' => $data['guest_phone'] ?? null,
            ':party_size' => $data['party_size'] ?? 1,
            ':entry_type' => $data['entry_type'] ?? 'FREE',
            ':discount' => $data['discount_amount'] ?? 0,
            ':commission' => $commission,
        ]);
        return ['guest_id' => $this->pdo->lastInsertId(), 'commission' => $commission];
    }

    public function checkInPromoterGuest($guestId)
    {
        $sql = "UPDATE promoter_guest_lists SET check_in_status = 'CHECKED_IN', checked_in_at = NOW() WHERE guest_id = :guest_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':guest_id' => $guestId]);

        $sql = "SELECT promoter_id, party_size, commission_amount FROM promoter_guest_lists WHERE guest_id = :guest_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':guest_id' => $guestId]);
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guest) {
            $sql = "UPDATE promoters SET total_guests_brought = total_guests_brought + :party_size, total_commission_earned = total_commission_earned + :commission WHERE promoter_id = :promoter_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':promoter_id' => $guest['promoter_id'],
                ':party_size' => $guest['party_size'],
                ':commission' => $guest['commission_amount'],
            ]);
        }

        return ['success' => true];
    }

    public function getPromoterGuestList($promoterId, $eventId)
    {
        $sql = "SELECT * FROM promoter_guest_lists WHERE promoter_id = :promoter_id AND event_id = :event_id ORDER BY created_at";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':promoter_id' => $promoterId, ':event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPromoterStats($promoterId)
    {
        $sql = "SELECT p.*, COUNT(gl.guest_id) as total_guests, SUM(gl.commission_amount) as total_commission,
                    SUM(CASE WHEN gl.check_in_status = 'CHECKED_IN' THEN 1 ELSE 0 END) as checked_in
                FROM promoters p
                LEFT JOIN promoter_guest_lists gl ON p.promoter_id = gl.promoter_id
                WHERE p.promoter_id = :promoter_id
                GROUP BY p.promoter_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':promoter_id' => $promoterId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function calculatePromoterCommission($promoterId, $partySize)
    {
        $sql = "SELECT commission_type, commission_rate FROM promoters WHERE promoter_id = :promoter_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':promoter_id' => $promoterId]);
        $promoter = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$promoter) return 0;

        switch ($promoter['commission_type']) {
            case 'PER_HEAD':
                return $partySize * $promoter['commission_rate'];
            case 'FLAT':
                return $promoter['commission_rate'];
            case 'PERCENTAGE':
                return 0;
            default:
                return 0;
        }
    }
}
