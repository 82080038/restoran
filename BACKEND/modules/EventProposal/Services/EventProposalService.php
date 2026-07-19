<?php

namespace App\Modules\EventProposal\Services;

use App\Core\Database;
use PDO;

class EventProposalService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== PROPOSALS ====================

    public function createProposal($data)
    {
        $proposalNumber = $data['proposal_number'] ?? 'PROP-' . date('Ymd') . '-' . substr(uniqid(), -4);
        $subtotal = ($data['per_head_price'] ?? 0) * ($data['guest_count'] ?? 0);
        $discount = $data['discount_amount'] ?? 0;
        $tax = $data['tax_amount'] ?? 0;
        $total = $subtotal - $discount + $tax;
        $deposit = $data['deposit_required'] ?? ($total * 0.3);

        $sql = "INSERT INTO event_proposals (tenant_id, branch_id, proposal_number, client_name, client_company, client_phone, client_email, event_name, event_type, event_date, event_end_date, event_venue, guest_count, service_style, menu_package, per_head_price, subtotal, discount_amount, tax_amount, total_amount, deposit_required, deposit_paid, balance_due, deposit_due_date, balance_due_date, valid_until, notes, internal_notes, created_by)
                VALUES (:tenant_id, :branch_id, :proposal_number, :client_name, :client_company, :client_phone, :client_email, :event_name, :event_type, :event_date, :event_end_date, :event_venue, :guest_count, :service_style, :menu_package, :per_head_price, :subtotal, :discount, :tax, :total, :deposit, 0, :balance, :deposit_due, :balance_due, :valid_until, :notes, :internal_notes, :created_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':proposal_number' => $proposalNumber,
            ':client_name' => $data['client_name'],
            ':client_company' => $data['client_company'] ?? null,
            ':client_phone' => $data['client_phone'] ?? null,
            ':client_email' => $data['client_email'] ?? null,
            ':event_name' => $data['event_name'] ?? null,
            ':event_type' => $data['event_type'] ?? null,
            ':event_date' => $data['event_date'] ?? null,
            ':event_end_date' => $data['event_end_date'] ?? null,
            ':event_venue' => $data['event_venue'] ?? null,
            ':guest_count' => $data['guest_count'] ?? 0,
            ':service_style' => $data['service_style'] ?? 'BUFFET',
            ':menu_package' => $data['menu_package'] ?? null,
            ':per_head_price' => $data['per_head_price'] ?? 0,
            ':subtotal' => $subtotal,
            ':discount' => $discount,
            ':tax' => $tax,
            ':total' => $total,
            ':deposit' => $deposit,
            ':balance' => $total - $deposit,
            ':deposit_due' => $data['deposit_due_date'] ?? null,
            ':balance_due' => $data['balance_due_date'] ?? null,
            ':valid_until' => $data['valid_until'] ?? date('Y-m-d', strtotime('+30 days')),
            ':notes' => $data['notes'] ?? null,
            ':internal_notes' => $data['internal_notes'] ?? null,
            ':created_by' => $data['created_by'] ?? null,
        ]);
        $proposalId = $this->pdo->lastInsertId();

        if (!empty($data['menu_items'])) {
            foreach ($data['menu_items'] as $item) {
                $this->addProposalMenuItem($proposalId, $item);
            }
        }
        if (!empty($data['addons'])) {
            foreach ($data['addons'] as $addon) {
                $this->addProposalAddon($proposalId, $addon);
            }
        }

        return ['proposal_id' => $proposalId, 'proposal_number' => $proposalNumber, 'total_amount' => $total];
    }

    public function addProposalMenuItem($proposalId, $item)
    {
        $totalPrice = ($item['quantity_per_head'] ?? 1) * ($item['unit_price'] ?? 0);
        $sql = "INSERT INTO proposal_menu_items (proposal_id, product_id, item_name, item_description, course_type, quantity_per_head, unit_price, total_price, dietary_tags, allergen_info, sort_order)
                VALUES (:proposal_id, :product_id, :item_name, :description, :course_type, :qty_per_head, :unit_price, :total_price, :dietary, :allergen, :sort_order)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':proposal_id' => $proposalId,
            ':product_id' => $item['product_id'] ?? null,
            ':item_name' => $item['item_name'],
            ':description' => $item['item_description'] ?? null,
            ':course_type' => $item['course_type'] ?? null,
            ':qty_per_head' => $item['quantity_per_head'] ?? 1,
            ':unit_price' => $item['unit_price'] ?? 0,
            ':total_price' => $totalPrice,
            ':dietary' => $item['dietary_tags'] ?? null,
            ':allergen' => $item['allergen_info'] ?? null,
            ':sort_order' => $item['sort_order'] ?? 0,
        ]);
        return ['item_id' => $this->pdo->lastInsertId()];
    }

    public function addProposalAddon($proposalId, $addon)
    {
        $totalPrice = ($addon['quantity'] ?? 1) * ($addon['unit_price'] ?? 0);
        $sql = "INSERT INTO proposal_addons (proposal_id, addon_type, description, quantity, unit_price, total_price)
                VALUES (:proposal_id, :addon_type, :description, :quantity, :unit_price, :total_price)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':proposal_id' => $proposalId,
            ':addon_type' => $addon['addon_type'],
            ':description' => $addon['description'],
            ':quantity' => $addon['quantity'] ?? 1,
            ':unit_price' => $addon['unit_price'] ?? 0,
            ':total_price' => $totalPrice,
        ]);
        return ['addon_id' => $this->pdo->lastInsertId()];
    }

    public function getProposals($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM event_proposals WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProposalDetail($proposalId)
    {
        $sql = "SELECT * FROM event_proposals WHERE proposal_id = :proposal_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':proposal_id' => $proposalId]);
        $proposal = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM proposal_menu_items WHERE proposal_id = :proposal_id ORDER BY sort_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':proposal_id' => $proposalId]);
        $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM proposal_addons WHERE proposal_id = :proposal_id ORDER BY addon_type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':proposal_id' => $proposalId]);
        $addons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['proposal' => $proposal, 'menu_items' => $menuItems, 'addons' => $addons];
    }

    public function updateProposalStatus($proposalId, $status)
    {
        $sql = "UPDATE event_proposals SET status = :status WHERE proposal_id = :proposal_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':proposal_id' => $proposalId, ':status' => $status]);
        return ['success' => true];
    }

    public function recordDepositPayment($proposalId, $amount)
    {
        $sql = "UPDATE event_proposals SET deposit_paid = deposit_paid + :amount, balance_due = balance_due - :amount WHERE proposal_id = :proposal_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':proposal_id' => $proposalId, ':amount' => $amount]);
        return ['success' => true];
    }

    // ==================== BEOs ====================

    public function convertToBEO($proposalId, $createdBy)
    {
        $detail = $this->getProposalDetail($proposalId);
        $proposal = $detail['proposal'];
        if (!$proposal) {
            return ['success' => false, 'message' => 'Proposal not found'];
        }

        $beoNumber = 'BEO-' . date('Ymd') . '-' . substr(uniqid(), -4);

        $sql = "INSERT INTO beos (tenant_id, branch_id, proposal_id, beo_number, event_name, event_date, client_name, guest_count, service_style, venue, contact_person, contact_phone, status, created_by)
                VALUES (:tenant_id, :branch_id, :proposal_id, :beo_number, :event_name, :event_date, :client_name, :guest_count, :service_style, :venue, :contact_person, :contact_phone, 'DRAFT', :created_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $proposal['tenant_id'],
            ':branch_id' => $proposal['branch_id'],
            ':proposal_id' => $proposalId,
            ':beo_number' => $beoNumber,
            ':event_name' => $proposal['event_name'],
            ':event_date' => $proposal['event_date'],
            ':client_name' => $proposal['client_name'],
            ':guest_count' => $proposal['guest_count'],
            ':service_style' => $proposal['service_style'],
            ':venue' => $proposal['event_venue'],
            ':contact_person' => $proposal['client_name'],
            ':contact_phone' => $proposal['client_phone'],
            ':created_by' => $createdBy,
        ]);
        $beoId = $this->pdo->lastInsertId();

        foreach ($detail['menu_items'] as $mi) {
            $sql = "INSERT INTO beo_items (beo_id, item_type, description, quantity, unit, sort_order) VALUES (:beo_id, 'MENU', :desc, :qty, :unit, :sort)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':beo_id' => $beoId,
                ':desc' => $mi['item_name'] . ' (' . $mi['course_type'] . ')',
                ':qty' => $mi['quantity_per_head'] * $proposal['guest_count'],
                ':unit' => 'portion',
                ':sort' => $mi['sort_order'],
            ]);
        }

        foreach ($detail['addons'] as $addon) {
            $sql = "INSERT INTO beo_items (beo_id, item_type, description, quantity, unit) VALUES (:beo_id, 'EQUIPMENT', :desc, :qty, :unit)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':beo_id' => $beoId,
                ':desc' => $addon['description'] . ' (' . $addon['addon_type'] . ')',
                ':qty' => $addon['quantity'],
                ':unit' => 'pcs',
            ]);
        }

        $this->updateProposalStatus($proposalId, 'CONVERTED');

        return ['beo_id' => $beoId, 'beo_number' => $beoNumber];
    }

    public function getBEOs($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM beos WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY event_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBEODetail($beoId)
    {
        $sql = "SELECT * FROM beos WHERE beo_id = :beo_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':beo_id' => $beoId]);
        $beo = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM beo_items WHERE beo_id = :beo_id ORDER BY item_type, sort_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':beo_id' => $beoId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['beo' => $beo, 'items' => $items];
    }

    public function addBEOItem($beoId, $item)
    {
        $sql = "INSERT INTO beo_items (beo_id, item_type, description, quantity, unit, assigned_to, notes, sort_order)
                VALUES (:beo_id, :item_type, :description, :quantity, :unit, :assigned_to, :notes, :sort_order)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':beo_id' => $beoId,
            ':item_type' => $item['item_type'],
            ':description' => $item['description'],
            ':quantity' => $item['quantity'] ?? 1,
            ':unit' => $item['unit'] ?? 'pcs',
            ':assigned_to' => $item['assigned_to'] ?? null,
            ':notes' => $item['notes'] ?? null,
            ':sort_order' => $item['sort_order'] ?? 0,
        ]);
        return ['item_id' => $this->pdo->lastInsertId()];
    }

    public function completeBEOItem($itemId)
    {
        $sql = "UPDATE beo_items SET completed = 1, completed_at = NOW() WHERE item_id = :item_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':item_id' => $itemId]);
        return ['success' => true];
    }

    public function updateBEOStatus($beoId, $status)
    {
        $sql = "UPDATE beos SET status = :status WHERE beo_id = :beo_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':beo_id' => $beoId, ':status' => $status]);
        return ['success' => true];
    }
}
