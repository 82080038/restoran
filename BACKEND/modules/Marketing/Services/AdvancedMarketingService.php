<?php

namespace App\Modules\Marketing\Services;

use App\Core\Database;
use App\Core\Audit;

class AdvancedMarketingService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = Audit::getInstance();
    }

    /**
     * Create customer segment
     */
    public function createCustomerSegment($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $segmentData = [
                'tenant_id' => $tenantId,
                'segment_name' => $data->segment_name,
                'segment_description' => $data->segment_description ?? null,
                'segment_type' => $data->segment_type,
                'criteria' => json_encode($data->criteria ?? []),
                'status' => 'ACTIVE',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO customer_segments (tenant_id, segment_name, segment_description, segment_type, criteria, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $segmentData['tenant_id'],
                $segmentData['segment_name'],
                $segmentData['segment_description'],
                $segmentData['segment_type'],
                $segmentData['criteria'],
                $segmentData['status'],
                $segmentData['created_by']
            ]);

            $segmentId = $this->db->lastInsertId();

            // Run segmentation to populate segment members
            $this->populateSegmentMembers($segmentId, $tenantId, $data->criteria ?? []);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'customer_segment', $segmentId, 'CREATE', json_encode($segmentData));

            return [
                'success' => true,
                'message' => 'Customer segment created',
                'segment_id' => $segmentId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create segment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Populate segment members based on criteria
     */
    private function populateSegmentMembers($segmentId, $tenantId, $criteria)
    {
        $where = "WHERE c.tenant_id = ?";
        $params = [$tenantId];

        // Build criteria-based WHERE clause
        if (isset($criteria['min_total_spent'])) {
            $where .= " AND c.total_spent >= ?";
            $params[] = $criteria['min_total_spent'];
        }

        if (isset($criteria['max_total_spent'])) {
            $where .= " AND c.total_spent <= ?";
            $params[] = $criteria['max_total_spent'];
        }

        if (isset($criteria['min_order_count'])) {
            $where .= " AND c.order_count >= ?";
            $params[] = $criteria['min_order_count'];
        }

        if (isset($criteria['customer_type'])) {
            $where .= " AND c.customer_type = ?";
            $params[] = $criteria['customer_type'];
        }

        if (isset($criteria['last_visit_days'])) {
            $where .= " AND c.last_visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
            $params[] = $criteria['last_visit_days'];
        }

        if (isset($criteria['loyalty_tier'])) {
            $where .= " AND c.loyalty_tier = ?";
            $params[] = $criteria['loyalty_tier'];
        }

        // Get matching customers
        $sql = "SELECT c.id as customer_id FROM customers c {$where}";
        $customers = $this->db->query($sql, $params)->fetchAll();

        // Insert segment members
        foreach ($customers as $customer) {
            $insertSql = "INSERT INTO segment_members (segment_id, customer_id, added_at) VALUES (?, ?, NOW())";
            $this->db->prepare($insertSql)->execute([$segmentId, $customer['customer_id']]);
        }
    }

    /**
     * Get customer segments
     */
    public function getCustomerSegments($tenantId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT cs.*, 
                    (SELECT COUNT(*) FROM segment_members sm WHERE sm.segment_id = cs.id) as member_count,
                    u.username as created_by_name
                FROM customer_segments cs
                LEFT JOIN users u ON cs.created_by = u.id
                {$where}
                ORDER BY cs.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get segment members
     */
    public function getSegmentMembers($segmentId, $tenantId)
    {
        $sql = "SELECT sm.*, c.customer_name, c.email, c.phone, c.total_spent, c.order_count, c.last_visit_date
                FROM segment_members sm
                LEFT JOIN customers c ON sm.customer_id = c.id
                WHERE sm.segment_id = ? AND c.tenant_id = ?
                ORDER BY sm.added_at DESC";

        return $this->db->query($sql, [$segmentId, $tenantId])->fetchAll();
    }

    /**
     * Create email campaign
     */
    public function createEmailCampaign($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $campaignData = [
                'tenant_id' => $tenantId,
                'campaign_name' => $data->campaign_name,
                'subject' => $data->subject,
                'from_email' => $data->from_email,
                'from_name' => $data->from_name ?? null,
                'content_html' => $data->content_html,
                'content_text' => $data->content_text ?? null,
                'segment_id' => $data->segment_id ?? null,
                'scheduled_date' => $data->scheduled_date ?? null,
                'status' => $data->scheduled_date ? 'SCHEDULED' : 'DRAFT',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO email_campaigns (tenant_id, campaign_name, subject, from_email, from_name, content_html, content_text, segment_id, scheduled_date, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $campaignData['tenant_id'],
                $campaignData['campaign_name'],
                $campaignData['subject'],
                $campaignData['from_email'],
                $campaignData['from_name'],
                $campaignData['content_html'],
                $campaignData['content_text'],
                $campaignData['segment_id'],
                $campaignData['scheduled_date'],
                $campaignData['status'],
                $campaignData['created_by']
            ]);

            $campaignId = $this->db->lastInsertId();

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'email_campaign', $campaignId, 'CREATE', json_encode($campaignData));

            return [
                'success' => true,
                'message' => 'Email campaign created',
                'campaign_id' => $campaignId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create campaign: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email campaign
     */
    public function sendEmailCampaign($campaignId, $tenantId, $userId)
    {
        try {
            $this->db->beginTransaction();

            // Get campaign details
            $campaignSql = "SELECT * FROM email_campaigns WHERE id = ? AND tenant_id = ?";
            $campaign = $this->db->query($campaignSql, [$campaignId, $tenantId])->fetch();

            if (!$campaign) {
                throw new Exception('Campaign not found');
            }

            // Get recipients
            $recipients = [];
            if ($campaign['segment_id']) {
                $recipientSql = "SELECT c.id as customer_id, c.email, c.customer_name 
                                FROM segment_members sm
                                LEFT JOIN customers c ON sm.customer_id = c.id
                                WHERE sm.segment_id = ?";
                $recipients = $this->db->query($recipientSql, [$campaign['segment_id']])->fetchAll();
            }

            // Create email logs for each recipient
            foreach ($recipients as $recipient) {
                $logSql = "INSERT INTO email_logs (campaign_id, customer_id, email, to_name, status, sent_at, created_at)
                           VALUES (?, ?, ?, ?, 'SENT', NOW(), NOW())";
                $this->db->prepare($logSql)->execute([
                    $campaignId,
                    $recipient['customer_id'],
                    $recipient['email'],
                    $recipient['customer_name']
                ]);
            }

            // Update campaign status
            $updateSql = "UPDATE email_campaigns SET status = 'SENT', sent_at = NOW(), sent_by = ? WHERE id = ?";
            $this->db->prepare($updateSql)->execute([$userId, $campaignId]);

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'email_campaign', $campaignId, 'SEND', json_encode(['recipient_count' => count($recipients)]));

            return [
                'success' => true,
                'message' => 'Email campaign sent',
                'recipient_count' => count($recipients)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to send campaign: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get email campaigns
     */
    public function getEmailCampaigns($tenantId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT ec.*, 
                    (SELECT COUNT(*) FROM email_logs el WHERE el.campaign_id = ec.id) as email_count,
                    (SELECT COUNT(*) FROM email_logs el WHERE el.campaign_id = ec.id AND el.status = 'OPENED') as opened_count,
                    (SELECT COUNT(*) FROM email_logs el WHERE el.campaign_id = ec.id AND el.status = 'CLICKED') as clicked_count,
                    u.username as created_by_name
                FROM email_campaigns ec
                LEFT JOIN users u ON ec.created_by = u.id
                {$where}
                ORDER BY ec.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Track email engagement
     */
    public function trackEmailEngagement($tenantId, $data)
    {
        try {
            $logSql = "UPDATE email_logs 
                       SET status = ?, opened_at = CASE WHEN ? = 'OPENED' THEN NOW() ELSE opened_at END,
                           clicked_at = CASE WHEN ? = 'CLICKED' THEN NOW() ELSE clicked_at END
                       WHERE id = ? AND campaign_id IN (SELECT id FROM email_campaigns WHERE tenant_id = ?)";
            
            $this->db->prepare($logSql)->execute([
                $data->status,
                $data->status,
                $data->status,
                $data->log_id,
                $tenantId
            ]);

            return [
                'success' => true,
                'message' => 'Email engagement tracked'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to track engagement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get promotion tracking
     */
    public function getPromotionTracking($tenantId, $promotionId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE p.tenant_id = ?";
        
        if ($promotionId) {
            $where .= " AND p.id = ?";
            $params[] = $promotionId;
        }
        
        if ($dateFrom) {
            $where .= " AND o.order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND o.order_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT p.id, p.promotion_name, p.promotion_code, p.discount_type, p.discount_value,
                    COUNT(DISTINCT o.id) as usage_count,
                    SUM(CASE WHEN o.promotion_id = p.id THEN o.total_amount ELSE 0 END) as total_sales,
                    SUM(CASE WHEN o.promotion_id = p.id THEN o.discount_amount ELSE 0 END) as total_discount,
                    COUNT(DISTINCT CASE WHEN o.promotion_id = p.id THEN o.customer_id END) as unique_customers
                FROM promotions p
                LEFT JOIN orders o ON o.promotion_id = p.id
                {$where}
                GROUP BY p.id, p.promotion_name, p.promotion_code, p.discount_type, p.discount_value
                ORDER BY usage_count DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get marketing summary
     */
    public function getSummary($tenantId)
    {
        // Active segments
        $activeSegmentsSql = "SELECT COUNT(*) as count FROM customer_segments WHERE tenant_id = ? AND status = 'ACTIVE'";
        $activeSegments = $this->db->query($activeSegmentsSql, [$tenantId])->fetch();

        // Scheduled email campaigns
        $scheduledCampaignsSql = "SELECT COUNT(*) as count FROM email_campaigns WHERE tenant_id = ? AND status = 'SCHEDULED'";
        $scheduledCampaigns = $this->db->query($scheduledCampaignsSql, [$tenantId])->fetch();

        // Active promotions
        $activePromotionsSql = "SELECT COUNT(*) as count FROM promotions WHERE tenant_id = ? AND promotion_status = 'ACTIVE'";
        $activePromotions = $this->db->query($activePromotionsSql, [$tenantId])->fetch();

        // This month's email sends
        $emailSendsSql = "SELECT COUNT(*) as count FROM email_campaigns WHERE tenant_id = ? AND sent_at >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $monthlyEmailSends = $this->db->query($emailSendsSql, [$tenantId])->fetch();

        return [
            'active_customer_segments' => $activeSegments['count'] ?? 0,
            'scheduled_email_campaigns' => $scheduledCampaigns['count'] ?? 0,
            'active_promotions' => $activePromotions['count'] ?? 0,
            'monthly_email_sends' => $monthlyEmailSends['count'] ?? 0
        ];
    }
}
