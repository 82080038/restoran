<?php

use PDO;

require_once __DIR__ . '/../Interfaces/EngineInterface.php';

/**
 * LoyaltyEngine - Customer Loyalty and Rewards Engine
 * 
 * This engine handles points calculation, tier management,
 * reward redemption, and loyalty program analytics
 * 
 * @package EBP\Core\Engines
 * @version 1.0.0
 */

class LoyaltyEngine implements EngineInterface
{
    private $db;
    private $initialized = false;

    public function __construct($db = null)
    {
        if ($db) {
            $this->initialize(['db' => $db]);
        }
    }

    public function initialize($dependencies): void
    {
        $this->db = $dependencies['db'] ?? null;
        $this->initialized = !empty($this->db);
    }

    public function validate(): bool
    {
        return $this->initialized && !empty($this->db);
    }

    public function execute(array $params): array
    {
        if (!$this->validate()) {
            return [
                'success' => false,
                'message' => 'Engine not properly initialized'
            ];
        }

        $action = $params['action'] ?? 'calculate_points';

        switch ($action) {
            case 'calculate_points':
                return $this->executeCalculatePoints($params);
            case 'redeem_reward':
                return $this->executeRedeemReward($params);
            case 'check_tier':
                return $this->executeCheckTier($params);
            case 'create_promotion':
                return $this->executeCreatePromotion($params);
            case 'create_referral':
                return $this->executeCreateReferral($params);
            case 'process_referral':
                return $this->executeProcessReferral($params);
            case 'add_achievement':
                return $this->executeAddAchievement($params);
            case 'check_achievements':
                return $this->executeCheckAchievements($params);
            default:
                return [
                    'success' => false,
                    'message' => 'Unknown action'
                ];
        }
    }

    private function executeCalculatePoints(array $params): array
    {
        $customerId = $params['customer_id'] ?? null;
        $orderId = $params['order_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;

        if (!$customerId || !$orderId || !$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: customer_id, order_id, tenant_id'
            ];
        }

        try {
            $result = $this->calculatePoints($customerId, $orderId, $tenantId);
            return [
                'success' => true,
                'points' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeRedeemReward(array $params): array
    {
        $customerId = $params['customer_id'] ?? null;
        $rewardId = $params['reward_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;

        if (!$customerId || !$rewardId || !$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: customer_id, reward_id, tenant_id'
            ];
        }

        try {
            $result = $this->redeemReward($customerId, $rewardId, $tenantId);
            return [
                'success' => true,
                'redemption' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCheckTier(array $params): array
    {
        $customerId = $params['customer_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;

        if (!$customerId || !$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: customer_id, tenant_id'
            ];
        }

        try {
            $result = $this->checkTier($customerId, $tenantId);
            return [
                'success' => true,
                'tier' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreateReferral(array $params): array
    {
        $referrerId = $params['referrer_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;
        $referralCode = $params['referral_code'] ?? null;

        if (!$referrerId || !$tenantId || !$referralCode) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: referrer_id, tenant_id, referral_code'
            ];
        }

        try {
            $result = $this->createReferral($referrerId, $tenantId, $referralCode);
            return [
                'success' => true,
                'referral' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeProcessReferral(array $params): array
    {
        $referralCode = $params['referral_code'] ?? null;
        $newCustomerId = $params['new_customer_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;

        if (!$referralCode || !$newCustomerId || !$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: referral_code, new_customer_id, tenant_id'
            ];
        }

        try {
            $result = $this->processReferral($referralCode, $newCustomerId, $tenantId);
            return [
                'success' => true,
                'result' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeAddAchievement(array $params): array
    {
        $customerId = $params['customer_id'] ?? null;
        $achievementType = $params['achievement_type'] ?? null;
        $achievementData = $params['achievement_data'] ?? [];

        if (!$customerId || !$achievementType) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: customer_id, achievement_type'
            ];
        }

        try {
            $result = $this->addAchievement($customerId, $achievementType, $achievementData);
            return [
                'success' => true,
                'achievement' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCheckAchievements(array $params): array
    {
        $customerId = $params['customer_id'] ?? null;
        $tenantId = $params['tenant_id'] ?? null;

        if (!$customerId || !$tenantId) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: customer_id, tenant_id'
            ];
        }

        try {
            $result = $this->checkAchievements($customerId, $tenantId);
            return [
                'success' => true,
                'achievements' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function executeCreatePromotion(array $params): array
    {
        $tenantId = $params['tenant_id'] ?? null;
        $promotionData = $params['promotion_data'] ?? [];

        if (!$tenantId || empty($promotionData)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: tenant_id, promotion_data'
            ];
        }

        try {
            $result = $this->createPromotion($tenantId, $promotionData);
            return [
                'success' => true,
                'promotion' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getMetadata(): array
    {
        return [
            'name' => 'Loyalty Engine',
            'version' => '1.0.0',
            'description' => 'Handles customer loyalty and rewards program',
            'author' => 'EBP Team',
            'created_at' => '2026-07-08'
        ];
    }

    public function getHealth(): array
    {
        return [
            'status' => $this->validate() ? 'healthy' : 'unhealthy',
            'initialized' => $this->initialized,
            'database_connected' => !empty($this->db),
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calculate points for an order
     */
    public function calculatePoints($customerId, $orderId, $tenantId)
    {
        // Get order details
        $order = $this->getOrder($orderId, $tenantId);
        
        if (!$order) {
            throw new Exception("Order not found");
        }

        // Get customer tier for multiplier
        $customerTier = $this->getCustomerTier($customerId, $tenantId);
        $pointsMultiplier = $this->getPointsMultiplier($customerTier);

        // Calculate base points (1 point per 1000 IDR)
        $basePoints = floor($order['total_amount'] / 1000);

        // Apply tier multiplier
        $totalPoints = $basePoints * $pointsMultiplier;

        // Check for active promotions
        $bonusPoints = $this->calculateBonusPoints($customerId, $tenantId, $orderId);
        $totalPoints += $bonusPoints;

        // Award points to customer
        $this->awardPoints($customerId, $tenantId, $totalPoints, 'EARNED', $orderId);

        return [
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'base_points' => $basePoints,
            'tier_multiplier' => $pointsMultiplier,
            'bonus_points' => $bonusPoints,
            'total_points' => $totalPoints,
            'customer_tier' => $customerTier
        ];
    }

    /**
     * Get order details
     */
    private function getOrder($orderId, $tenantId)
    {
        $sql = "
            SELECT order_id, total_amount, status, created_at
            FROM orders
            WHERE order_id = ? AND tenant_id = ? AND status = 'COMPLETED'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer tier
     */
    private function getCustomerTier($customerId, $tenantId)
    {
        $sql = "
            SELECT tier_level
            FROM loyalty_members
            WHERE customer_id = ? AND tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['tier_level'] : 'BRONZE';
    }

    /**
     * Get points multiplier based on tier
     */
    private function getPointsMultiplier($tier)
    {
        $multipliers = [
            'BRONZE' => 1.0,
            'SILVER' => 1.25,
            'GOLD' => 1.5,
            'PLATINUM' => 2.0
        ];
        
        return $multipliers[$tier] ?? 1.0;
    }

    /**
     * Calculate bonus points from promotions
     */
    private function calculateBonusPoints($customerId, $tenantId, $orderId)
    {
        $bonusPoints = 0;

        // Get active promotions
        $sql = "
            SELECT promotion_id, bonus_points, conditions
            FROM loyalty_promotions
            WHERE tenant_id = ? 
              AND status = 'ACTIVE'
              AND start_date <= CURDATE()
              AND end_date >= CURDATE()
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($promotions as $promotion) {
            if ($this->checkPromotionEligibility($customerId, $promotion, $orderId)) {
                $bonusPoints += $promotion['bonus_points'];
            }
        }

        return $bonusPoints;
    }

    /**
     * Check promotion eligibility
     */
    private function checkPromotionEligibility($customerId, $promotion, $orderId)
    {
        $conditions = json_decode($promotion['conditions'], true);
        
        // Check if customer has already used this promotion
        $sql = "
            SELECT COUNT(*) as usage_count
            FROM loyalty_redemptions
            WHERE customer_id = ? 
              AND promotion_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $promotion['promotion_id']]);
        $usage = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usage['usage_count'] >= ($conditions['max_uses_per_customer'] ?? 1)) {
            return false;
        }

        return true;
    }

    /**
     * Award points to customer
     */
    private function awardPoints($customerId, $tenantId, $points, $transactionType = 'EARNED', $referenceId = null)
    {
        // Update customer balance
        $sql = "
            INSERT INTO loyalty_members (customer_id, tenant_id, points_balance, tier_level, joined_at)
            VALUES (?, ?, ?, 'BRONZE', NOW())
            ON DUPLICATE KEY UPDATE 
                points_balance = points_balance + ?,
                last_activity_at = NOW()
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId, $points, $points]);

        // Log points transaction
        if ($tenantId) {
            $sql = "
                INSERT INTO loyalty_transactions
                (customer_id, tenant_id, points_earned, transaction_type, reference_id, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId, $tenantId, $points, $transactionType, $referenceId]);
        }

        // Check for tier upgrade
        if ($tenantId) {
            $this->checkTierUpgrade($customerId, $tenantId);
        }
    }

    /**
     * Check for tier upgrade
     */
    private function checkTierUpgrade($customerId, $tenantId)
    {
        // Get customer's total points earned
        $sql = "
            SELECT SUM(points_earned) as total_points
            FROM loyalty_transactions
            WHERE customer_id = ? AND tenant_id = ? AND transaction_type = 'EARNED'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalPoints = $result['total_points'] ?? 0;

        // Determine new tier
        $newTier = $this->determineTier($totalPoints);

        // Get current tier
        $currentTier = $this->getCustomerTier($customerId, $tenantId);

        // Update if tier changed
        if ($newTier !== $currentTier) {
            $sql = "
                UPDATE loyalty_members
                SET tier_level = ?, tier_upgraded_at = NOW()
                WHERE customer_id = ? AND tenant_id = ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newTier, $customerId, $tenantId]);
        }
    }

    /**
     * Determine tier based on points
     */
    private function determineTier($totalPoints)
    {
        if ($totalPoints >= 10000) return 'PLATINUM';
        if ($totalPoints >= 5000) return 'GOLD';
        if ($totalPoints >= 2000) return 'SILVER';
        return 'BRONZE';
    }

    /**
     * Redeem reward
     */
    public function redeemReward($customerId, $rewardId, $tenantId)
    {
        // Get customer points balance
        $customer = $this->getCustomer($customerId, $tenantId);
        
        if (!$customer) {
            throw new Exception("Customer not found");
        }

        // Get reward details
        $reward = $this->getReward($rewardId, $tenantId);
        
        if (!$reward) {
            throw new Exception("Reward not found");
        }

        // Check if customer has enough points
        if ($customer['points_balance'] < $reward['points_required']) {
            throw new Exception("Insufficient points balance");
        }

        // Check if reward is available
        if ($reward['quantity_available'] <= 0) {
            throw new Exception("Reward not available");
        }

        // Deduct points
        $this->deductPoints($customerId, $tenantId, $reward['points_required']);

        // Create redemption record
        $redemptionCode = 'RED-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $sql = "
            INSERT INTO loyalty_redemptions
            (customer_id, tenant_id, reward_id, redemption_code, points_used, status, redeemed_at)
            VALUES (?, ?, ?, ?, ?, 'REDEEMED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId, $rewardId, $redemptionCode, $reward['points_required']]);

        // Update reward quantity
        $sql = "
            UPDATE loyalty_rewards
            SET quantity_available = quantity_available - 1,
                total_redeemed = total_redeemed + 1
            WHERE reward_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rewardId]);

        return [
            'success' => true,
            'redemption_code' => $redemptionCode,
            'reward_name' => $reward['name'],
            'points_used' => $reward['points_required'],
            'remaining_balance' => $customer['points_balance'] - $reward['points_required']
        ];
    }

    /**
     * Get customer details
     */
    private function getCustomer($customerId, $tenantId)
    {
        $sql = "
            SELECT customer_id, points_balance, tier_level
            FROM loyalty_members
            WHERE customer_id = ? AND tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get reward details
     */
    private function getReward($rewardId, $tenantId)
    {
        $sql = "
            SELECT reward_id, name, description, points_required, quantity_available, total_redeemed
            FROM loyalty_rewards
            WHERE reward_id = ? AND tenant_id = ? AND status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rewardId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Deduct points from customer
     */
    private function deductPoints($customerId, $tenantId, $points)
    {
        $sql = "
            UPDATE loyalty_members
            SET points_balance = points_balance - ?,
                last_activity_at = NOW()
            WHERE customer_id = ? AND tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$points, $customerId, $tenantId]);

        // Log points transaction
        $sql = "
            INSERT INTO loyalty_transactions
            (customer_id, tenant_id, points_used, transaction_type, created_at)
            VALUES (?, ?, ?, 'REDEEMED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId, $points]);
    }

    /**
     * Check customer tier and benefits
     */
    public function checkTier($customerId, $tenantId)
    {
        $customer = $this->getCustomer($customerId, $tenantId);
        
        if (!$customer) {
            throw new Exception("Customer not found");
        }

        // Get tier benefits
        $benefits = $this->getTierBenefits($customer['tier_level']);

        // Get points to next tier
        $pointsToNextTier = $this->getPointsToNextTier($customer['tier_level'], $customerId, $tenantId);

        return [
            'customer_id' => $customerId,
            'current_tier' => $customer['tier_level'],
            'points_balance' => $customer['points_balance'],
            'benefits' => $benefits,
            'points_to_next_tier' => $pointsToNextTier,
            'next_tier' => $this->getNextTier($customer['tier_level'])
        ];
    }

    /**
     * Get tier benefits
     */
    private function getTierBenefits($tier)
    {
        $benefits = [
            'BRONZE' => [
                'points_multiplier' => 1.0,
                'free_delivery_threshold' => 50000,
                'birthday_bonus' => 100
            ],
            'SILVER' => [
                'points_multiplier' => 1.25,
                'free_delivery_threshold' => 40000,
                'birthday_bonus' => 250
            ],
            'GOLD' => [
                'points_multiplier' => 1.5,
                'free_delivery_threshold' => 30000,
                'birthday_bonus' => 500
            ],
            'PLATINUM' => [
                'points_multiplier' => 2.0,
                'free_delivery_threshold' => 0,
                'birthday_bonus' => 1000
            ]
        ];
        
        return $benefits[$tier] ?? $benefits['BRONZE'];
    }

    /**
     * Get points to next tier
     */
    private function getPointsToNextTier($currentTier, $customerId, $tenantId)
    {
        $tierThresholds = [
            'BRONZE' => 2000,
            'SILVER' => 5000,
            'GOLD' => 10000,
            'PLATINUM' => null
        ];

        $nextThreshold = $tierThresholds[$currentTier];
        
        if ($nextThreshold === null) {
            return 0; // Already at highest tier
        }

        // Get customer's total points
        $sql = "
            SELECT SUM(points_earned) as total_points
            FROM loyalty_transactions
            WHERE customer_id = ? AND tenant_id = ? AND transaction_type = 'EARNED'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalPoints = $result['total_points'] ?? 0;
        
        return max(0, $nextThreshold - $totalPoints);
    }

    /**
     * Get next tier
     */
    private function getNextTier($currentTier)
    {
        $tiers = ['BRONZE', 'SILVER', 'GOLD', 'PLATINUM'];
        $currentIndex = array_search($currentTier, $tiers);
        
        if ($currentIndex < count($tiers) - 1) {
            return $tiers[$currentIndex + 1];
        }
        
        return null; // Already at highest tier
    }

    /**
     * Create promotion
     */
    public function createPromotion($tenantId, $promotionData)
    {
        $sql = "
            INSERT INTO loyalty_promotions
            (tenant_id, name, description, bonus_points, conditions, start_date, end_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVE', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $promotionData['name'],
            $promotionData['description'] ?? '',
            $promotionData['bonus_points'],
            json_encode($promotionData['conditions'] ?? []),
            $promotionData['start_date'],
            $promotionData['end_date']
        ]);

        return [
            'success' => true,
            'promotion_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Get loyalty dashboard data
     */
    public function getDashboardData($tenantId, $branchId)
    {
        // Get total members
        $totalMembers = $this->getTotalMembers($tenantId);
        
        // Get tier distribution
        $tierDistribution = $this->getTierDistribution($tenantId);
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($tenantId);
        
        // Get top customers
        $topCustomers = $this->getTopCustomers($tenantId);

        return [
            'total_members' => $totalMembers,
            'tier_distribution' => $tierDistribution,
            'recent_activity' => $recentActivity,
            'top_customers' => $topCustomers
        ];
    }

    /**
     * Get total members
     */
    private function getTotalMembers($tenantId)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM loyalty_members
            WHERE tenant_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Get tier distribution
     */
    private function getTierDistribution($tenantId)
    {
        $sql = "
            SELECT tier_level, COUNT(*) as count
            FROM loyalty_members
            WHERE tenant_id = ?
            GROUP BY tier_level
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity($tenantId)
    {
        $sql = "
            SELECT 
                lt.transaction_id,
                lt.customer_id,
                c.name as customer_name,
                lt.points_earned,
                lt.points_used,
                lt.transaction_type,
                lt.created_at
            FROM loyalty_transactions lt
            LEFT JOIN customers c ON lt.customer_id = c.customer_id
            WHERE lt.tenant_id = ?
            ORDER BY lt.created_at DESC
            LIMIT 20
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top customers
     */
    private function getTopCustomers($tenantId)
    {
        $sql = "
            SELECT 
                lm.customer_id,
                c.name as customer_name,
                lm.points_balance,
                lm.tier_level,
                lm.joined_at
            FROM loyalty_members lm
            LEFT JOIN customers c ON lm.customer_id = c.customer_id
            WHERE lm.tenant_id = ?
            ORDER BY lm.points_balance DESC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create referral for a customer
     * 
     * @param int $referrerId Referrer customer ID
     * @param int $tenantId Tenant ID
     * @param string $referralCode Unique referral code
     * @return array Referral details
     */
    public function createReferral($referrerId, $tenantId, $referralCode)
    {
        // Check if referral code already exists
        $sql = "SELECT referral_id FROM referrals WHERE referral_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$referralCode]);
        
        if ($stmt->fetch()) {
            throw new Exception('Referral code already exists');
        }

        // Create referral
        $sql = "
            INSERT INTO referrals
            (referrer_id, tenant_id, referral_code, status, created_at)
            VALUES (?, ?, ?, 'ACTIVE', NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$referrerId, $tenantId, $referralCode]);
        $referralId = $this->db->lastInsertId();

        return [
            'referral_id' => $referralId,
            'referral_code' => $referralCode,
            'referrer_id' => $referrerId,
            'status' => 'ACTIVE'
        ];
    }

    /**
     * Process referral when new customer uses referral code
     * 
     * @param string $referralCode Referral code
     * @param int $newCustomerId New customer ID
     * @param int $tenantId Tenant ID
     * @return array Processing result
     */
    public function processReferral($referralCode, $newCustomerId, $tenantId)
    {
        // Get referral details
        $sql = "
            SELECT referral_id, referrer_id, status
            FROM referrals
            WHERE referral_code = ? AND tenant_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$referralCode, $tenantId]);
        $referral = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$referral) {
            throw new Exception('Invalid referral code');
        }

        if ($referral['status'] !== 'ACTIVE') {
            throw new Exception('Referral code is not active');
        }

        // Award points to referrer
        $referrerPoints = 500; // 500 points for successful referral
        $this->awardPoints($referral['referrer_id'], $tenantId, $referrerPoints, 'REFERRAL', $referral['referral_id']);

        // Award points to new customer
        $newCustomerPoints = 200; // 200 points for using referral
        $this->awardPoints($newCustomerId, $tenantId, $newCustomerPoints, 'REFERRAL_USED', $referral['referral_id']);

        // Update referral status
        $sql = "
            UPDATE referrals
            SET status = 'COMPLETED', referred_customer_id = ?, completed_at = NOW()
            WHERE referral_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$newCustomerId, $referral['referral_id']]);

        return [
            'referral_id' => $referral['referral_id'],
            'referrer_id' => $referral['referrer_id'],
            'new_customer_id' => $newCustomerId,
            'referrer_points_awarded' => $referrerPoints,
            'new_customer_points_awarded' => $newCustomerPoints
        ];
    }

    /**
     * Add achievement to customer
     * 
     * @param int $customerId Customer ID
     * @param string $achievementType Achievement type
     * @param array $achievementData Additional achievement data
     * @return array Achievement details
     */
    public function addAchievement($customerId, $achievementType, $achievementData = [])
    {
        // Check if achievement already exists
        $sql = "
            SELECT achievement_id FROM customer_achievements
            WHERE customer_id = ? AND achievement_type = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $achievementType]);
        
        if ($stmt->fetch()) {
            throw new Exception('Achievement already earned');
        }

        // Get points for this achievement
        $achievementPoints = $this->getAchievementPoints($achievementType);

        // Create achievement
        $sql = "
            INSERT INTO customer_achievements
            (customer_id, achievement_type, achievement_data, points_awarded, earned_at)
            VALUES (?, ?, ?, ?, NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $achievementType, json_encode($achievementData), $achievementPoints]);

        // Award points
        $this->awardPoints($customerId, null, $achievementPoints, 'ACHIEVEMENT', null);

        return [
            'achievement_type' => $achievementType,
            'points_awarded' => $achievementPoints,
            'earned_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check customer achievements
     * 
     * @param int $customerId Customer ID
     * @param int $tenantId Tenant ID
     * @return array Customer achievements
     */
    public function checkAchievements($customerId, $tenantId)
    {
        // Get earned achievements
        $sql = "
            SELECT 
                achievement_id,
                achievement_type,
                achievement_data,
                points_awarded,
                earned_at
            FROM customer_achievements
            WHERE customer_id = ?
            ORDER BY earned_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $earnedAchievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get available achievements
        $availableAchievements = $this->getAvailableAchievements($tenantId);

        // Check for new achievements based on customer behavior
        $newAchievements = $this->checkForNewAchievements($customerId, $tenantId);

        return [
            'earned' => $earnedAchievements,
            'available' => $availableAchievements,
            'new' => $newAchievements
        ];
    }


    /**
     * Get achievement points
     */
    private function getAchievementPoints($achievementType)
    {
        $pointsMap = [
            'FIRST_ORDER' => 100,
            '10_ORDERS' => 500,
            '50_ORDERS' => 2000,
            '100_ORDERS' => 5000,
            'SOCIAL_SHARE' => 50,
            'REVIEW_SUBMITTED' => 100,
            'BIRTHDAY_VISIT' => 200,
            'WEEKLY_VISITOR' => 300,
            'MONTHLY_VISITOR' => 1000
        ];

        return $pointsMap[$achievementType] ?? 0;
    }

    /**
     * Get available achievements
     */
    private function getAvailableAchievements($tenantId)
    {
        return [
            ['type' => 'FIRST_ORDER', 'name' => 'First Order', 'points' => 100],
            ['type' => '10_ORDERS', 'name' => '10 Orders', 'points' => 500],
            ['type' => '50_ORDERS', 'name' => '50 Orders', 'points' => 2000],
            ['type' => '100_ORDERS', 'name' => '100 Orders', 'points' => 5000],
            ['type' => 'SOCIAL_SHARE', 'name' => 'Social Share', 'points' => 50],
            ['type' => 'REVIEW_SUBMITTED', 'name' => 'Review Submitted', 'points' => 100],
            ['type' => 'BIRTHDAY_VISIT', 'name' => 'Birthday Visit', 'points' => 200],
            ['type' => 'WEEKLY_VISITOR', 'name' => 'Weekly Visitor', 'points' => 300],
            ['type' => 'MONTHLY_VISITOR', 'name' => 'Monthly Visitor', 'points' => 1000]
        ];
    }

    /**
     * Check for new achievements based on customer behavior
     */
    private function checkForNewAchievements($customerId, $tenantId)
    {
        $newAchievements = [];

        // Check order count achievements
        $sql = "SELECT COUNT(*) as order_count FROM orders WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $orderCount = $stmt->fetchColumn();

        if ($orderCount == 1) {
            $newAchievements[] = ['type' => 'FIRST_ORDER', 'points' => 100];
        } elseif ($orderCount == 10) {
            $newAchievements[] = ['type' => '10_ORDERS', 'points' => 500];
        } elseif ($orderCount == 50) {
            $newAchievements[] = ['type' => '50_ORDERS', 'points' => 2000];
        } elseif ($orderCount == 100) {
            $newAchievements[] = ['type' => '100_ORDERS', 'points' => 5000];
        }

        return $newAchievements;
    }
}
