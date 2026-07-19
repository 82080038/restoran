<?php

namespace App\Modules\SportsBarAdvanced\Services;

use App\Core\Database;
use PDO;

class SportsBarAdvancedService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    public function openTab($data)
    {
        $tabNumber = 'TAB-' . date('Ymd') . '-' . substr(uniqid(), -4);
        $sql = "INSERT INTO bar_tab_preauths (tenant_id, branch_id, tab_number, customer_name, customer_phone, card_last_four, card_brand, preauth_amount, opened_by, external_ref, notes)
                VALUES (:tenant_id, :branch_id, :tab_number, :name, :phone, :last_four, :brand, :preauth, :opened_by, :ref, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':tab_number' => $tabNumber,
            ':name' => $data['customer_name'],
            ':phone' => $data['customer_phone'] ?? null,
            ':last_four' => $data['card_last_four'] ?? null,
            ':brand' => $data['card_brand'] ?? null,
            ':preauth' => $data['preauth_amount'] ?? 0,
            ':opened_by' => $data['opened_by'] ?? null,
            ':ref' => $data['external_ref'] ?? null,
            ':notes' => $data['notes'] ?? null,
        ]);

        $tabId = $this->pdo->lastInsertId();
        $this->updateRemainingHold($tabId);
        return ['tab_id' => $tabId, 'tab_number' => $tabNumber];
    }

    public function addToTab($tabId, $items, $amount)
    {
        $sql = "SELECT items_json, consumed_amount FROM bar_tab_preauths WHERE tab_id = :tab_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId]);
        $tab = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tab) return ['success' => false, 'message' => 'Tab not found'];

        $existingItems = json_decode($tab['items_json'] ?? '[]', true);
        $existingItems = array_merge($existingItems, $items);

        $newConsumed = $tab['consumed_amount'] + $amount;
        $sql = "UPDATE bar_tab_preauths SET items_json = :items, consumed_amount = :consumed WHERE tab_id = :tab_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId, ':items' => json_encode($existingItems), ':consumed' => $newConsumed]);

        $this->updateRemainingHold($tabId);
        return ['success' => true, 'consumed_amount' => $newConsumed];
    }

    public function closeTab($tabId, $tipAmount = 0)
    {
        $sql = "SELECT * FROM bar_tab_preauths WHERE tab_id = :tab_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId]);
        $tab = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tab) return ['success' => false, 'message' => 'Tab not found'];

        $finalAmount = $tab['consumed_amount'] + $tipAmount;
        $sql = "UPDATE bar_tab_preauths SET status = 'CLOSED', tip_amount = :tip, final_amount = :final, closed_at = NOW() WHERE tab_id = :tab_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId, ':tip' => $tipAmount, ':final' => $finalAmount]);

        return ['success' => true, 'final_amount' => $finalAmount, 'tip' => $tipAmount];
    }

    public function captureTab($tabId)
    {
        $sql = "UPDATE bar_tab_preauths SET status = 'CAPTURED' WHERE tab_id = :tab_id AND status = 'CLOSED'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId]);
        return ['success' => true];
    }

    public function voidTab($tabId, $reason)
    {
        $sql = "UPDATE bar_tab_preauths SET status = 'VOIDED', notes = CONCAT(IFNULL(notes,''), :reason) WHERE tab_id = :tab_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId, ':reason' => "\n[Voided] " . $reason]);
        return ['success' => true];
    }

    public function getTabs($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM bar_tab_preauths WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) { $sql .= " AND branch_id = :branch_id"; $params[':branch_id'] = $branchId; }
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY opened_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTab($tabId)
    {
        $sql = "SELECT * FROM bar_tab_preauths WHERE tab_id = :tab_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateRemainingHold($tabId)
    {
        $sql = "UPDATE bar_tab_preauths SET remaining_hold = preauth_amount - consumed_amount WHERE tab_id = :tab_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tab_id' => $tabId]);
    }
}
