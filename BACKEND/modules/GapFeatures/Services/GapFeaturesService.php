<?php

namespace App\Modules\GapFeatures\Services;

use App\Core\Database;
use PDO;

class GapFeaturesService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== SCAN ID / VERIFIKASI USIA ====================

    public function scanId($data)
    {
        $dob = $data['date_of_birth'] ?? null;
        $age = null;
        $over21 = 0;
        $over18 = 0;
        $result = 'APPROVED';

        if ($dob) {
            $age = (int)date('Y') - (int)substr($dob, 0, 4);
            $over18 = $age >= 18 ? 1 : 0;
            $over21 = $age >= 21 ? 1 : 0;
            if (!$over18) {
                $result = 'REJECTED';
            }
        }

        $sql = "INSERT INTO id_scans (tenant_id, branch_id, event_id, guest_name, id_type, id_number, date_of_birth, age_calculated, is_over_21, is_over_18, scan_result, rejection_reason, scanned_by, photo_path)
                VALUES (:tenant_id, :branch_id, :event_id, :name, :id_type, :id_number, :dob, :age, :over21, :over18, :result, :reason, :scanned_by, :photo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':event_id' => $data['event_id'] ?? null, ':name' => $data['guest_name'] ?? null,
            ':id_type' => $data['id_type'] ?? 'KTP', ':id_number' => $data['id_number'] ?? null,
            ':dob' => $dob, ':age' => $age, ':over21' => $over21, ':over18' => $over18,
            ':result' => $result, ':reason' => $result === 'REJECTED' ? 'Under 18' : null,
            ':scanned_by' => $data['scanned_by'] ?? null, ':photo' => $data['photo_path'] ?? null,
        ]);
        return ['scan_id' => $this->pdo->lastInsertId(), 'scan_result' => $result, 'age' => $age, 'is_over_18' => $over18, 'is_over_21' => $over21];
    }

    public function getIdScans($tenantId, $branchId, $eventId = null, $date = null)
    {
        $sql = "SELECT * FROM id_scans WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($eventId) { $sql .= " AND event_id = :event_id"; $params[':event_id'] = $eventId; }
        if ($date) { $sql .= " AND DATE(scanned_at) = :date"; $params[':date'] = $date; }
        $sql .= " ORDER BY scanned_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIdScanStats($tenantId, $branchId, $date)
    {
        $sql = "SELECT scan_result, COUNT(*) as count, SUM(CASE WHEN is_over_21 = 1 THEN 1 ELSE 0 END) as over_21_count FROM id_scans WHERE tenant_id = :tenant_id AND DATE(scanned_at) = :date";
        $params = [':tenant_id' => $tenantId, ':date' => $date];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " GROUP BY scan_result";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== COGS MINUMAN KONSOLIDASI ====================

    public function generateCogsReport($tenantId, $branchId, $date)
    {
        $sql = "INSERT INTO beverage_cogs (tenant_id, branch_id, report_date, beverage_category, product_id, product_name, unit_type, opening_qty, received_qty, sold_qty, closing_qty, unit_cost, total_cost, revenue, pour_cost_pct)
                SELECT :tenant_id, :branch_id, :date,
                    CASE
                        WHEN p.pricing_type = 'WEIGHT_BASED' THEN 'DRAFT_BEER'
                        WHEN p.product_name LIKE '%spirit%' OR p.product_name LIKE '%vodka%' OR p.product_name LIKE '%whisky%' THEN 'SPIRITS'
                        WHEN p.product_name LIKE '%wine%' THEN 'WINE'
                        ELSE 'PACKAGED'
                    END,
                    p.product_id, p.product_name,
                    CASE p.pricing_type WHEN 'WEIGHT_BASED' THEN 'KEG' WHEN 'UNIT_BASED' THEN 'BOTTLE' ELSE 'UNIT' END,
                    COALESCE(si.opening_qty, 0), COALESCE(si.received_qty, 0),
                    COALESCE(si.sold_qty, 0), COALESCE(si.closing_qty, 0),
                    COALESCE(p.cost, 0), COALESCE(p.cost * COALESCE(si.sold_qty, 0), 0),
                    COALESCE(si.revenue, 0),
                    CASE WHEN COALESCE(si.revenue, 0) > 0 THEN ROUND((COALESCE(p.cost * si.sold_qty, 0) / si.revenue) * 100, 2) ELSE 0 END
                FROM products p
                LEFT JOIN (
                    SELECT product_id,
                        SUM(CASE WHEN movement_type = 'OPENING' THEN quantity ELSE 0 END) as opening_qty,
                        SUM(CASE WHEN movement_type = 'RECEIVED' THEN quantity ELSE 0 END) as received_qty,
                        SUM(CASE WHEN movement_type = 'SOLD' THEN quantity ELSE 0 END) as sold_qty,
                        SUM(CASE WHEN movement_type = 'CLOSING' THEN quantity ELSE 0 END) as closing_qty,
                        SUM(CASE WHEN movement_type = 'SOLD' THEN total_amount ELSE 0 END) as revenue
                    FROM stock_movements WHERE DATE(movement_date) = :date2
                    GROUP BY product_id
                ) si ON p.product_id = si.product_id
                WHERE p.tenant_id = :tenant_id2 AND p.is_available = 1
                ON DUPLICATE KEY UPDATE
                    opening_qty = VALUES(opening_qty), sold_qty = VALUES(sold_qty),
                    closing_qty = VALUES(closing_qty), total_cost = VALUES(total_cost),
                    revenue = VALUES(revenue), pour_cost_pct = VALUES(pour_cost_pct)";

        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':tenant_id' => $tenantId, ':branch_id' => $branchId, ':date' => $date,
            ':date2' => $date, ':tenant_id2' => $tenantId,
        ];
        $stmt->execute($params);

        $sql = "SELECT beverage_category, COUNT(*) as product_count,
                    SUM(total_cost) as total_cost, SUM(revenue) as total_revenue,
                    ROUND(SUM(total_cost) / NULLIF(SUM(revenue), 0) * 100, 2) as avg_pour_cost_pct,
                    SUM(opening_qty) as total_opening, SUM(sold_qty) as total_sold,
                    SUM(closing_qty) as total_closing
                FROM beverage_cogs WHERE tenant_id = :tenant_id AND report_date = :date";
        $params = [':tenant_id' => $tenantId, ':date' => $date];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " GROUP BY beverage_category ORDER BY total_cost DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCogsReport($tenantId, $branchId, $date)
    {
        $sql = "SELECT * FROM beverage_cogs WHERE tenant_id = :tenant_id AND report_date = :date";
        $params = [':tenant_id' => $tenantId, ':date' => $date];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " ORDER BY beverage_category, product_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== KONTRAK E-SIGNATURE ====================

    public function createContract($data)
    {
        $documentHash = hash('sha256', $data['document_content'] ?? '');
        $sql = "INSERT INTO e_signatures (tenant_id, branch_id, contract_id, document_type, document_title, document_content, document_hash, signer_name, signer_email, signer_role, expires_at)
                VALUES (:tenant_id, :branch_id, :contract_id, :doc_type, :title, :content, :hash, :name, :email, :role, :expires)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':contract_id' => $data['contract_id'] ?? null,
            ':doc_type' => $data['document_type'] ?? 'CATERING_CONTRACT',
            ':title' => $data['document_title'], ':content' => $data['document_content'] ?? '',
            ':hash' => $documentHash, ':name' => $data['signer_name'],
            ':email' => $data['signer_email'] ?? null, ':role' => $data['signer_role'] ?? 'CLIENT',
            ':expires' => $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);
        return ['signature_id' => $this->pdo->lastInsertId(), 'document_hash' => $documentHash];
    }

    public function signContract($signatureId, $signatureData, $signerIp)
    {
        $sql = "UPDATE e_signatures SET signature_data = :sig_data, signature_ip = :ip, signed_at = NOW(), status = 'SIGNED' WHERE signature_id = :id AND status = 'PENDING'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $signatureId, ':sig_data' => $signatureData, ':ip' => $signerIp]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function getContracts($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT signature_id, document_title, signer_name, signer_email, status, created_at, signed_at, expires_at FROM e_signatures WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== LANGGANAN MAKAN KORPORAT ====================

    public function createSubscription($data)
    {
        $monthlyTotal = ($data['price_per_head'] ?? 0) * ($data['head_count'] ?? 10) * 22;
        $sql = "INSERT INTO corporate_meal_subscriptions (tenant_id, branch_id, company_name, contact_person, contact_phone, contact_email, billing_address, meal_plan, head_count, delivery_address, delivery_time, frequency, days_of_week, price_per_head, monthly_total, start_date, end_date, auto_renew, status)
                VALUES (:tenant_id, :branch_id, :company, :contact, :phone, :email, :billing, :plan, :heads, :addr, :time, :freq, :days, :price, :monthly, :start, :end, :renew, 'ACTIVE')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':company' => $data['company_name'], ':contact' => $data['contact_person'] ?? null,
            ':phone' => $data['contact_phone'] ?? null, ':email' => $data['contact_email'] ?? null,
            ':billing' => $data['billing_address'] ?? null, ':plan' => $data['meal_plan'] ?? 'DAILY_LUNCH',
            ':heads' => $data['head_count'] ?? 10, ':addr' => $data['delivery_address'] ?? null,
            ':time' => $data['delivery_time'] ?? '12:00:00', ':freq' => $data['frequency'] ?? 'WEEKLY',
            ':days' => $data['days_of_week'] ?? 'MON,TUE,WED,THU,FRI',
            ':price' => $data['price_per_head'] ?? 0, ':monthly' => $monthlyTotal,
            ':start' => $data['start_date'] ?? date('Y-m-d'),
            ':end' => $data['end_date'] ?? date('Y-m-d', strtotime('+1 year')),
            ':renew' => $data['auto_renew'] ?? 1,
        ]);
        return ['subscription_id' => $this->pdo->lastInsertId(), 'monthly_total' => $monthlyTotal];
    }

    public function getSubscriptions($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM corporate_meal_subscriptions WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recordDelivery($data)
    {
        $sql = "INSERT INTO corporate_meal_deliveries (subscription_id, tenant_id, delivery_date, head_count_served, total_amount, status, notes)
                VALUES (:sub_id, :tenant_id, :date, :heads, :amount, 'DELIVERED', :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sub_id' => $data['subscription_id'], ':tenant_id' => $data['tenant_id'],
            ':date' => $data['delivery_date'], ':heads' => $data['head_count_served'] ?? null,
            ':amount' => $data['total_amount'] ?? 0, ':notes' => $data['notes'] ?? null,
        ]);
        return ['delivery_id' => $this->pdo->lastInsertId()];
    }

    public function getDeliveryHistory($subscriptionId)
    {
        $sql = "SELECT * FROM corporate_meal_deliveries WHERE subscription_id = :id ORDER BY delivery_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $subscriptionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== DRIVE-THRU ====================

    public function startDriveThruSession($data)
    {
        $sql = "INSERT INTO drive_thru_sessions (tenant_id, branch_id, lane_number, vehicle_description, status)
                VALUES (:tenant_id, :branch_id, :lane, :vehicle, 'DETECTED')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':lane' => $data['lane_number'] ?? 1, ':vehicle' => $data['vehicle_description'] ?? null,
        ]);
        return ['session_id' => $this->pdo->lastInsertId()];
    }

    public function updateDriveThruStatus($sessionId, $status, $orderId = null, $orderTotal = null)
    {
        $timeField = '';
        switch ($status) {
            case 'GREETED': $timeField = ', greeted_at = NOW()'; break;
            case 'ORDERED': $timeField = ', order_taken_at = NOW()'; break;
            case 'PAID': $timeField = ', payment_at = NOW()'; break;
            case 'COMPLETED': $timeField = ', pickup_at = NOW(), total_wait_seconds = TIMESTAMPDIFF(SECOND, detected_at, NOW())'; break;
        }
        $sql = "UPDATE drive_thru_sessions SET status = :status$timeField";
        $params = [':id' => $sessionId, ':status' => $status];
        if ($orderId) { $sql .= ", order_id = :order_id"; $params[':order_id'] = $orderId; }
        if ($orderTotal !== null) { $sql .= ", order_total = :total"; $params[':total'] = $orderTotal; }
        $sql .= " WHERE session_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ['success' => true];
    }

    public function getDriveThruStats($tenantId, $branchId, $date)
    {
        $sql = "SELECT
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed,
                    AVG(CASE WHEN total_wait_seconds > 0 THEN total_wait_seconds ELSE NULL END) as avg_wait_seconds,
                    SUM(order_total) as total_revenue
                FROM drive_thru_sessions WHERE tenant_id = :tenant_id AND DATE(detected_at) = :date";
        $params = [':tenant_id' => $tenantId, ':date' => $date];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==================== TASTING MENU ====================

    public function createTastingMenu($data)
    {
        $sql = "INSERT INTO tasting_menus (tenant_id, branch_id, menu_name, description, price_per_cover, course_count, is_active)
                VALUES (:tenant_id, :branch_id, :name, :desc, :price, :courses, 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':name' => $data['menu_name'], ':desc' => $data['description'] ?? null,
            ':price' => $data['price_per_cover'] ?? 0, ':courses' => $data['course_count'] ?? 5,
        ]);
        return ['tasting_menu_id' => $this->pdo->lastInsertId()];
    }

    public function addTastingCourse($data)
    {
        $sql = "INSERT INTO tasting_menu_courses (tasting_menu_id, course_number, course_name, course_description, product_id, pairing_beverage, prep_time_minutes, is_optional)
                VALUES (:menu_id, :num, :name, :desc, :product_id, :pairing, :prep, :optional)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':menu_id' => $data['tasting_menu_id'], ':num' => $data['course_number'],
            ':name' => $data['course_name'], ':desc' => $data['course_description'] ?? null,
            ':product_id' => $data['product_id'] ?? null, ':pairing' => $data['pairing_beverage'] ?? null,
            ':prep' => $data['prep_time_minutes'] ?? 10, ':optional' => $data['is_optional'] ?? 0,
        ]);
        return ['course_id' => $this->pdo->lastInsertId()];
    }

    public function getTastingMenus($tenantId, $branchId)
    {
        $sql = "SELECT * FROM tasting_menus WHERE tenant_id = :tenant_id AND is_active = 1";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTastingMenuDetail($menuId)
    {
        $sql = "SELECT * FROM tasting_menus WHERE tasting_menu_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $menuId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM tasting_menu_courses WHERE tasting_menu_id = :id ORDER BY course_number";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $menuId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['menu' => $menu, 'courses' => $courses];
    }

    public function createTastingReservation($data)
    {
        $sql = "INSERT INTO tasting_menu_reservations (tenant_id, branch_id, tasting_menu_id, customer_name, phone, party_size, reservation_date, reservation_time, table_id, total_amount, status, special_requests)
                VALUES (:tenant_id, :branch_id, :menu_id, :name, :phone, :party, :date, :time, :table_id, :total, 'PENDING', :requests)";
        $stmt = $this->pdo->prepare($sql);
        $total = ($data['price_per_cover'] ?? 0) * ($data['party_size'] ?? 2);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':menu_id' => $data['tasting_menu_id'], ':name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null, ':party' => $data['party_size'] ?? 2,
            ':date' => $data['reservation_date'], ':time' => $data['reservation_time'] ?? '19:00:00',
            ':table_id' => $data['table_id'] ?? null, ':total' => $total,
            ':requests' => $data['special_requests'] ?? null,
        ]);
        return ['reservation_id' => $this->pdo->lastInsertId(), 'total_amount' => $total];
    }

    // ==================== DEPOSIT RESERVASI ====================

    public function createReservationDeposit($data)
    {
        $depositAmount = $data['deposit_amount'] ?? 0;
        if (($data['deposit_type'] ?? 'PER_PERSON') === 'PER_PERSON') {
            $depositAmount = ($data['deposit_per_person'] ?? 0) * ($data['party_size'] ?? 1);
        }
        $sql = "INSERT INTO reservation_deposits (tenant_id, branch_id, reservation_id, customer_name, phone, party_size, reservation_date, reservation_time, deposit_amount, deposit_type, payment_method, payment_ref, no_show_cutoff, status)
                VALUES (:tenant_id, :branch_id, :res_id, :name, :phone, :party, :date, :time, :amount, :dtype, :method, :ref, :cutoff, 'PAID')";
        $stmt = $this->pdo->prepare($sql);
        $cutoff = ($data['reservation_date'] ?? date('Y-m-d')) . ' ' . ($data['reservation_time'] ?? '19:00:00');
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':branch_id' => $data['branch_id'] ?? null,
            ':res_id' => $data['reservation_id'] ?? null, ':name' => $data['customer_name'],
            ':phone' => $data['phone'] ?? null, ':party' => $data['party_size'] ?? 1,
            ':date' => $data['reservation_date'], ':time' => $data['reservation_time'] ?? '19:00:00',
            ':amount' => $depositAmount, ':dtype' => $data['deposit_type'] ?? 'PER_PERSON',
            ':method' => $data['payment_method'] ?? 'CASH', ':ref' => $data['payment_ref'] ?? null,
            ':cutoff' => $cutoff,
        ]);
        $depositId = $this->pdo->lastInsertId();

        $sql = "UPDATE reservation_deposits SET paid_at = NOW() WHERE deposit_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $depositId]);

        return ['deposit_id' => $depositId, 'deposit_amount' => $depositAmount];
    }

    public function getReservationDeposits($tenantId, $branchId, $status = null, $date = null)
    {
        $sql = "SELECT * FROM reservation_deposits WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        if ($date) { $sql .= " AND reservation_date = :date"; $params[':date'] = $date; }
        $sql .= " ORDER BY reservation_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function forfeitDeposit($depositId)
    {
        $sql = "UPDATE reservation_deposits SET status = 'FORFEITED', forfeited_at = NOW() WHERE deposit_id = :id AND status = 'PAID'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $depositId]);
        return ['success' => $stmt->rowCount() > 0];
    }

    public function refundDeposit($depositId)
    {
        $sql = "UPDATE reservation_deposits SET status = 'REFUNDED', refunded_at = NOW() WHERE deposit_id = :id AND status = 'PAID'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $depositId]);
        return ['success' => $stmt->rowCount() > 0];
    }
}
