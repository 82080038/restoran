<?php

namespace App\Modules\Settlement\Services;

use App\Core\Database;
use PDO;

class SettlementService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== ARTIST DEALS ====================

    public function createDeal($data)
    {
        $sql = "INSERT INTO artist_deals (tenant_id, branch_id, concert_id, artist_name, deal_type, guarantee_amount, percentage_artist, percentage_venue, ticket_price_range_min, ticket_price_range_max, radius_clause_km, radius_clause_days, merch_split_artist, merch_split_venue, bar_revenue_included, notes)
                VALUES (:tenant_id, :branch_id, :concert_id, :artist_name, :deal_type, :guarantee, :pct_artist, :pct_venue, :price_min, :price_max, :radius_km, :radius_days, :merch_artist, :merch_venue, :bar_included, :notes)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':concert_id' => $data['concert_id'] ?? null,
            ':artist_name' => $data['artist_name'],
            ':deal_type' => $data['deal_type'],
            ':guarantee' => $data['guarantee_amount'] ?? 0,
            ':pct_artist' => $data['percentage_artist'] ?? 0,
            ':pct_venue' => $data['percentage_venue'] ?? 0,
            ':price_min' => $data['ticket_price_range_min'] ?? null,
            ':price_max' => $data['ticket_price_range_max'] ?? null,
            ':radius_km' => $data['radius_clause_km'] ?? null,
            ':radius_days' => $data['radius_clause_days'] ?? null,
            ':merch_artist' => $data['merch_split_artist'] ?? 100,
            ':merch_venue' => $data['merch_split_venue'] ?? 0,
            ':bar_included' => $data['bar_revenue_included'] ?? 0,
            ':notes' => $data['notes'] ?? null,
        ]);
        return ['deal_id' => $this->pdo->lastInsertId()];
    }

    public function getDeals($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM artist_deals WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND contract_status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function signDeal($dealId)
    {
        $sql = "UPDATE artist_deals SET contract_status = 'SIGNED', contract_signed_at = NOW() WHERE deal_id = :deal_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':deal_id' => $dealId]);
        return ['success' => true];
    }

    // ==================== SETTLEMENTS ====================

    public function createSettlement($data)
    {
        $sql = "INSERT INTO settlements (tenant_id, branch_id, concert_id, deal_id, settlement_type, settlement_date, estimated_ticket_revenue, actual_ticket_revenue, ticket_count_sold, ticket_count_comp, bar_revenue, merch_revenue, total_revenue, artist_guarantee, venue_production_cost, status)
                VALUES (:tenant_id, :branch_id, :concert_id, :deal_id, :settlement_type, :settlement_date, :estimated, :actual, :sold, :comp, :bar, :merch, :total_rev, :guarantee, :production, 'ESTIMATED')";
        $totalRev = ($data['actual_ticket_revenue'] ?? 0) + ($data['bar_revenue'] ?? 0) + ($data['merch_revenue'] ?? 0);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':concert_id' => $data['concert_id'] ?? null,
            ':deal_id' => $data['deal_id'] ?? null,
            ':settlement_type' => $data['settlement_type'],
            ':settlement_date' => $data['settlement_date'],
            ':estimated' => $data['estimated_ticket_revenue'] ?? 0,
            ':actual' => $data['actual_ticket_revenue'] ?? 0,
            ':sold' => $data['ticket_count_sold'] ?? 0,
            ':comp' => $data['ticket_count_comp'] ?? 0,
            ':bar' => $data['bar_revenue'] ?? 0,
            ':merch' => $data['merch_revenue'] ?? 0,
            ':total_rev' => $totalRev,
            ':guarantee' => $data['artist_guarantee'] ?? 0,
            ':production' => $data['venue_production_cost'] ?? 0,
        ]);
        $settlementId = $this->pdo->lastInsertId();

        $this->recalculateSettlement($settlementId);

        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->addSettlementItem($settlementId, $item);
            }
        }

        return ['settlement_id' => $settlementId];
    }

    public function addSettlementItem($settlementId, $item)
    {
        $sql = "INSERT INTO settlement_items (settlement_id, item_type, description, amount, is_revenue, metadata)
                VALUES (:settlement_id, :item_type, :description, :amount, :is_revenue, :metadata)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':settlement_id' => $settlementId,
            ':item_type' => $item['item_type'],
            ':description' => $item['description'] ?? '',
            ':amount' => $item['amount'],
            ':is_revenue' => $item['is_revenue'] ?? 0,
            ':metadata' => isset($item['metadata']) ? json_encode($item['metadata']) : null,
        ]);
        $this->recalculateSettlement($settlementId);
        return ['item_id' => $this->pdo->lastInsertId()];
    }

    public function finalizeSettlement($settlementId, $finalizedBy)
    {
        $this->recalculateSettlement($settlementId);
        $sql = "UPDATE settlements SET status = 'FINALIZED', finalized_by = :finalized_by, finalized_at = NOW() WHERE settlement_id = :settlement_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':settlement_id' => $settlementId, ':finalized_by' => $finalizedBy]);
        return ['success' => true];
    }

    public function markSettlementPaid($settlementId)
    {
        $sql = "UPDATE settlements SET status = 'PAID' WHERE settlement_id = :settlement_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':settlement_id' => $settlementId]);
        return ['success' => true];
    }

    public function getSettlements($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT s.*, ad.artist_name, ad.deal_type FROM settlements s
                LEFT JOIN artist_deals ad ON s.deal_id = ad.deal_id
                WHERE s.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];
        if ($branchId) {
            $sql .= " AND s.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        if ($status) {
            $sql .= " AND s.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY s.settlement_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSettlementDetail($settlementId)
    {
        $sql = "SELECT s.*, ad.artist_name, ad.deal_type, ad.percentage_artist, ad.percentage_venue FROM settlements s
                LEFT JOIN artist_deals ad ON s.deal_id = ad.deal_id
                WHERE s.settlement_id = :settlement_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':settlement_id' => $settlementId]);
        $settlement = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM settlement_items WHERE settlement_id = :settlement_id ORDER BY item_type";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':settlement_id' => $settlementId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['settlement' => $settlement, 'items' => $items];
    }

    // ==================== ADVANCING SHEETS ====================

    public function createAdvancingSheet($data)
    {
        $sql = "INSERT INTO advancing_sheets (tenant_id, branch_id, concert_id, deal_id, load_in_time, soundcheck_time, doors_time, set_times, stage_plot_path, input_list, hospitality_rider, tech_requirements, ground_transport, security_plan, contact_phone, contact_email)
                VALUES (:tenant_id, :branch_id, :concert_id, :deal_id, :load_in, :soundcheck, :doors, :set_times, :stage_plot, :input_list, :hospitality, :tech_req, :transport, :security, :phone, :email)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':concert_id' => $data['concert_id'],
            ':deal_id' => $data['deal_id'] ?? null,
            ':load_in' => $data['load_in_time'] ?? null,
            ':soundcheck' => $data['soundcheck_time'] ?? null,
            ':doors' => $data['doors_time'] ?? null,
            ':set_times' => isset($data['set_times']) ? json_encode($data['set_times']) : null,
            ':stage_plot' => $data['stage_plot_path'] ?? null,
            ':input_list' => isset($data['input_list']) ? json_encode($data['input_list']) : null,
            ':hospitality' => isset($data['hospitality_rider']) ? json_encode($data['hospitality_rider']) : null,
            ':tech_req' => isset($data['tech_requirements']) ? json_encode($data['tech_requirements']) : null,
            ':transport' => isset($data['ground_transport']) ? json_encode($data['ground_transport']) : null,
            ':security' => $data['security_plan'] ?? null,
            ':phone' => $data['contact_phone'] ?? null,
            ':email' => $data['contact_email'] ?? null,
        ]);
        return ['sheet_id' => $this->pdo->lastInsertId()];
    }

    public function getAdvancingSheet($concertId)
    {
        $sql = "SELECT * FROM advancing_sheets WHERE concert_id = :concert_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':concert_id' => $concertId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function confirmAdvancingSheet($sheetId)
    {
        $sql = "UPDATE advancing_sheets SET status = 'CONFIRMED' WHERE sheet_id = :sheet_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sheet_id' => $sheetId]);
        return ['success' => true];
    }

    // ==================== CALCULATIONS ====================

    private function recalculateSettlement($settlementId)
    {
        $detail = $this->getSettlementDetail($settlementId);
        $s = $detail['settlement'];
        if (!$s) return;

        $totalRevenue = ($s['actual_ticket_revenue'] ?? 0) + ($s['bar_revenue'] ?? 0) + ($s['merch_revenue'] ?? 0);
        $totalCosts = 0;
        $artistPayout = $s['artist_guarantee'] ?? 0;

        foreach ($detail['items'] as $item) {
            if ($item['is_revenue']) {
                $totalRevenue += $item['amount'];
            } else {
                $totalCosts += $item['amount'];
            }
        }

        $dealType = $s['deal_type'] ?? 'FLAT_GUARANTEE';
        switch ($dealType) {
            case 'PERCENTAGE':
            case 'DOOR_DEAL':
                $pctArtist = ($s['percentage_artist'] ?? 0) / 100;
                $artistPayout = ($s['actual_ticket_revenue'] ?? 0) * $pctArtist;
                break;
            case 'VERSUS':
                $pctArtist = ($s['percentage_artist'] ?? 0) / 100;
                $doorDeal = ($s['actual_ticket_revenue'] ?? 0) * $pctArtist;
                $artistPayout = max($s['artist_guarantee'] ?? 0, $doorDeal);
                break;
            case 'FLAT_GUARANTEE':
            case 'PLUS_DEAL':
            default:
                $artistPayout = $s['artist_guarantee'] ?? 0;
                break;
        }

        $merchArtistPayout = ($s['merch_revenue'] ?? 0) * (($s['percentage_artist'] ?? 0) / 100);
        $totalCosts += $artistPayout + ($s['venue_production_cost'] ?? 0);
        $venueProfit = $totalRevenue - $totalCosts;

        $sql = "UPDATE settlements SET total_revenue = :total_rev, artist_payout = :payout, venue_profit = :profit WHERE settlement_id = :settlement_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':settlement_id' => $settlementId,
            ':total_rev' => $totalRevenue,
            ':payout' => $artistPayout,
            ':profit' => $venueProfit,
        ]);
    }
}
