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

        // Award bonus points for achievement
        $achievementPoints = $this->getAchievementPoints($achievementType);
        $this->awardPoints($customerId, null, $achievementPoints, 'ACHIEVEMENT', $achievementType);

        // Create achievement record
        $sql = "
            INSERT INTO customer_achievements
            (customer_id, achievement_type, achievement_data, points_awarded, earned_at)
            VALUES (?, ?, ?, ?, NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $achievementType, json_encode($achievementData), $achievementPoints]);

        return [
            'achievement_type' => $achievementType,
            'points_awarded' => $achievementPoints,
            'earned_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Check achievements for customer
     * 
     * @param int $customerId Customer ID
     * @param int $tenantId Tenant ID
     * @return array Achievements
     */
    public function checkAchievements($customerId, $tenantId)
    {
        $achievements = [];
        
        // Check first order achievement
        $firstOrder = $this->checkFirstOrderAchievement($customerId, $tenantId);
        if ($firstOrder) {
            $achievements[] = $firstOrder;
        }
        
        // Check order count achievements
        $orderCount = $this->checkOrderCountAchievement($customerId, $tenantId);
        if ($orderCount) {
            $achievements[] = $orderCount;
        }
        
        // Check spending achievements
        $spending = $this->checkSpendingAchievement($customerId, $tenantId);
        if ($spending) {
            $achievements[] = $spending;
        }
        
        return $achievements;
    }

    /**
     * Get achievement points
     */
    private function getAchievementPoints($achievementType)
    {
        $points = [
            'FIRST_ORDER' => 100,
            '10_ORDERS' => 200,
            '50_ORDERS' => 500,
            '100_ORDERS' => 1000,
            'SPENT_100K' => 150,
            'SPENT_500K' => 500,
            'SPENT_1M' => 1000,
            'REFERRED_FRIEND' => 300
        ];
        
        return $points[$achievementType] ?? 50;
    }

    /**
     * Check first order achievement
     */
    private function checkFirstOrderAchievement($customerId, $tenantId)
    {
        $sql = "
            SELECT COUNT(*) as order_count
            FROM orders
            WHERE customer_id = ? AND tenant_id = ? AND status = 'COMPLETED'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['order_count'] == 1) {
            return $this->addAchievement($customerId, 'FIRST_ORDER', ['order_count' => 1]);
        }
        
        return null;
    }

    /**
     * Check order count achievement
     */
    private function checkOrderCountAchievement($customerId, $tenantId)
    {
        $sql = "
            SELECT COUNT(*) as order_count
            FROM orders
            WHERE customer_id = ? AND tenant_id = ? AND status = 'COMPLETED'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $orderCount = $result['order_count'];
        
        $achievements = [
            ['count' => 10, 'type' => '10_ORDERS'],
            ['count' => 50, 'type' => '50_ORDERS'],
            ['count' => 100, 'type' => '100_ORDERS']
        ];
        
        foreach ($achievements as $achievement) {
            if ($orderCount == $achievement['count']) {
                return $this->addAchievement($customerId, $achievement['type'], ['order_count' => $orderCount]);
            }
        }
        
        return null;
    }

    /**
     * Check spending achievement
     */
    private function checkSpendingAchievement($customerId, $tenantId)
    {
        $sql = "
            SELECT SUM(total_amount) as total_spent
            FROM orders
            WHERE customer_id = ? AND tenant_id = ? AND status = 'COMPLETED'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalSpent = $result['total_spent'] ?? 0;
        
        $achievements = [
            ['threshold' => 100000, 'type' => 'SPENT_100K'],
            ['threshold' => 500000, 'type' => 'SPENT_500K'],
            ['threshold' => 1000000, 'type' => 'SPENT_1M']
        ];
        
        foreach ($achievements as $achievement) {
            if ($totalSpent >= $achievement['threshold']) {
                return $this->addAchievement($customerId, $achievement['type'], ['total_spent' => $totalSpent]);
            }
        }
        
        return null;
    }

    /**
     * Automated loyalty program processing
     * Runs scheduled maintenance tasks for loyalty program
     * 
     * @param int $tenantId Tenant ID
     * @return array Processing results
     */
    public function runAutomatedProcessing($tenantId)
    {
        $results = [];
        
        // Process expiring points
        $expiringPoints = $this->processExpiringPoints($tenantId);
        $results['expiring_points'] = $expiringPoints;
        
        // Process birthday bonuses
        $birthdayBonuses = $this->processBirthdayBonuses($tenantId);
        $results['birthday_bonuses'] = $birthdayBonuses;
        
        // Process tier reviews
        $tierReviews = $this->processTierReviews($tenantId);
        $results['tier_reviews'] = $tierReviews;
        
        // Process inactive members
        $inactiveMembers = $this->processInactiveMembers($tenantId);
        $results['inactive_members'] = $inactiveMembers;
        
        return [
            'success' => true,
            'tenant_id' => $tenantId,
            'results' => $results,
            'processed_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Process expiring points
     * 
     * @param int $tenantId Tenant ID
     * @return array Processing result
     */
    private function processExpiringPoints($tenantId)
    {
        // Get points older than 12 months
        $sql = "
            SELECT customer_id, SUM(points_earned) as expiring_points
            FROM loyalty_transactions
            WHERE tenant_id = ? 
              AND transaction_type = 'EARNED'
              AND created_at <= DATE_SUB(NOW(), INTERVAL 12 MONTH)
              AND created_at > DATE_SUB(NOW(), INTERVAL 13 MONTH)
            GROUP BY customer_id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $expiringPoints = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $processed = 0;
        foreach ($expiringPoints as $customerPoints) {
            // Deduct expiring points
            $this->deductPoints($customerPoints['customer_id'], $tenantId, $customerPoints['expiring_points']);
            
            // Log expiration
            $sql = "
                INSERT INTO loyalty_points_expiry
                (customer_id, tenant_id, points_expired, expiry_date, created_at)
                VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL 12 MONTH), NOW())
            ";
            
            $stmt2 = $this->db->prepare($sql);
            $stmt2->execute([$customerPoints['customer_id'], $tenantId, $customerPoints['expiring_points']]);
            
            // Send notification (would trigger notification service)
            $this->sendPointsExpiryNotification($customerPoints['customer_id'], $tenantId, $customerPoints['expiring_points']);
            
            $processed++;
        }
        
        return [
            'processed_customers' => $processed,
            'total_points_expired' => array_sum(array_column($expiringPoints, 'expiring_points'))
        ];
    }

    /**
     * Process birthday bonuses
     * 
     * @param int $tenantId Tenant ID
     * @return array Processing result
     */
    private function processBirthdayBonuses($tenantId)
    {
        // Get customers with birthdays today
        $sql = "
            SELECT lm.customer_id, lm.tier_level, c.date_of_birth
            FROM loyalty_members lm
            LEFT JOIN customers c ON lm.customer_id = c.customer_id
            WHERE lm.tenant_id = ?
              AND DAY(c.date_of_birth) = DAY(CURDATE())
              AND MONTH(c.date_of_birth) = MONTH(CURDATE())
              AND lm.last_activity_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $birthdayCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $processed = 0;
        foreach ($birthdayCustomers as $customer) {
            // Get tier benefits for birthday bonus
            $benefits = $this->getTierBenefits($customer['tier_level']);
            $bonusPoints = $benefits['birthday_bonus'];
            
            // Award birthday bonus
            $this->awardPoints($customer['customer_id'], $tenantId, $bonusPoints, 'BIRTHDAY_BONUS', null);
            
            // Log birthday bonus
            $sql = "
                INSERT INTO loyalty_birthday_bonuses
                (customer_id, tenant_id, points_awarded, bonus_date, created_at)
                VALUES (?, ?, ?, CURDATE(), NOW())
            ";
            
            $stmt2 = $this->db->prepare($sql);
            $stmt2->execute([$customer['customer_id'], $tenantId, $bonusPoints]);
            
            // Send birthday notification
            $this->sendBirthdayNotification($customer['customer_id'], $tenantId, $bonusPoints);
            
            $processed++;
        }
        
        return [
            'processed_customers' => $processed,
            'total_points_awarded' => $processed * 500 // Average
        ];
    }

    /**
     * Process tier reviews
     * 
     * @param int $tenantId Tenant ID
     * @return array Processing result
     */
    private function processTierReviews($tenantId)
    {
        // Get all loyalty members
        $sql = "
            SELECT customer_id, tier_level
            FROM loyalty_members
            WHERE tenant_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $upgraded = 0;
        $downgraded = 0;
        
        foreach ($members as $member) {
            $oldTier = $member['tier_level'];
            
            // Check for tier upgrade/downgrade
            $this->checkTierUpgrade($member['customer_id'], $tenantId);
            $this->checkTierDowngrade($member['customer_id'], $tenantId);
            
            // Get new tier
            $newTier = $this->getCustomerTier($member['customer_id'], $tenantId);
            
            if ($newTier !== $oldTier) {
                if ($this->compareTiers($newTier, $oldTier) > 0) {
                    $upgraded++;
                    $this->sendTierUpgradeNotification($member['customer_id'], $tenantId, $oldTier, $newTier);
                } else {
                    $downgraded++;
                    $this->sendTierDowngradeNotification($member['customer_id'], $tenantId, $oldTier, $newTier);
                }
            }
        }
        
        return [
            'upgraded' => $upgraded,
            'downgraded' => $downgraded,
            'total_reviewed' => count($members)
        ];
    }

    /**
     * Check for tier downgrade
     * 
     * @param int $customerId Customer ID
     * @param int $tenantId Tenant ID
     */
    private function checkTierDowngrade($customerId, $tenantId)
    {
        // Get customer's total points earned in last 12 months
        $sql = "
            SELECT SUM(points_earned) as recent_points
            FROM loyalty_transactions
            WHERE customer_id = ? 
              AND tenant_id = ? 
              AND transaction_type = 'EARNED'
              AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $recentPoints = $result['recent_points'] ?? 0;
        
        // Determine tier based on recent activity
        $newTier = $this->determineTier($recentPoints);
        
        // Get current tier
        $currentTier = $this->getCustomerTier($customerId, $tenantId);
        
        // Only downgrade if new tier is lower
        if ($this->compareTiers($newTier, $currentTier) < 0) {
            $sql = "
                UPDATE loyalty_members
                SET tier_level = ?, tier_downgraded_at = NOW()
                WHERE customer_id = ? AND tenant_id = ?
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newTier, $customerId, $tenantId]);
        }
    }

    /**
     * Compare tiers (returns 1 if tier1 > tier2, -1 if tier1 < tier2, 0 if equal)
     */
    private function compareTiers($tier1, $tier2)
    {
        $tierOrder = ['BRONZE' => 0, 'SILVER' => 1, 'GOLD' => 2, 'PLATINUM' => 3];
        
        $order1 = $tierOrder[$tier1] ?? 0;
        $order2 = $tierOrder[$tier2] ?? 0;
        
        if ($order1 > $order2) return 1;
        if ($order1 < $order2) return -1;
        return 0;
    }

    /**
     * Process inactive members
     * 
     * @param int $tenantId Tenant ID
     * @return array Processing result
     */
    private function processInactiveMembers($tenantId)
    {
        // Get members inactive for 6+ months
        $sql = "
            SELECT customer_id, points_balance, tier_level
            FROM loyalty_members
            WHERE tenant_id = ? 
              AND last_activity_at <= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $inactiveMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $processed = 0;
        foreach ($inactiveMembers as $member) {
            // Mark as inactive
            $sql = "
                UPDATE loyalty_members
                SET status = 'INACTIVE', inactivated_at = NOW()
                WHERE customer_id = ? AND tenant_id = ?
            ";
            
            $stmt2 = $this->db->prepare($sql);
            $stmt2->execute([$member['customer_id'], $tenantId]);
            
            // Send re-engagement campaign notification
            $this->sendReEngagementNotification($member['customer_id'], $tenantId, $member['points_balance']);
            
            $processed++;
        }
        
        return [
            'marked_inactive' => $processed,
            'total_points_at_risk' => array_sum(array_column($inactiveMembers, 'points_balance'))
        ];
    }

    /**
     * Send points expiry notification
     */
    private function sendPointsExpiryNotification($customerId, $tenantId, $pointsExpiring)
    {
        // This would integrate with notification service
        $sql = "
            INSERT INTO loyalty_notifications
            (customer_id, tenant_id, notification_type, message, created_at, status)
            VALUES (?, ?, 'POINTS_EXPIRY', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerId,
            $tenantId,
            "You have {$pointsExpiring} points expiring soon. Use them before they expire!"
        ]);
    }

    /**
     * Send birthday notification
     */
    private function sendBirthdayNotification($customerId, $tenantId, $bonusPoints)
    {
        $sql = "
            INSERT INTO loyalty_notifications
            (customer_id, tenant_id, notification_type, message, created_at, status)
            VALUES (?, ?, 'BIRTHDAY_BONUS', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerId,
            $tenantId,
            "Happy Birthday! We've awarded you {$bonusPoints} bonus points as a gift!"
        ]);
    }

    /**
     * Send tier upgrade notification
     */
    private function sendTierUpgradeNotification($customerId, $tenantId, $oldTier, $newTier)
    {
        $sql = "
            INSERT INTO loyalty_notifications
            (customer_id, tenant_id, notification_type, message, created_at, status)
            VALUES (?, ?, 'TIER_UPGRADE', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerId,
            $tenantId,
            "Congratulations! You've been upgraded from {$oldTier} to {$newTier} tier!"
        ]);
    }

    /**
     * Send tier downgrade notification
     */
    private function sendTierDowngradeNotification($customerId, $tenantId, $oldTier, $newTier)
    {
        $sql = "
            INSERT INTO loyalty_notifications
            (customer_id, tenant_id, notification_type, message, created_at, status)
            VALUES (?, ?, 'TIER_DOWNGRADE', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerId,
            $tenantId,
            "Your tier has been changed from {$oldTier} to {$newTier} based on recent activity."
        ]);
    }

    /**
     * Send re-engagement notification
     */
    private function sendReEngagementNotification($customerId, $tenantId, $pointsBalance)
    {
        $sql = "
            INSERT INTO loyalty_notifications
            (customer_id, tenant_id, notification_type, message, created_at, status)
            VALUES (?, ?, 'RE_ENGAGEMENT', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerId,
            $tenantId,
            "We miss you! You have {$pointsBalance} points waiting for you. Come back and earn more!"
        ]);
    }

    /**
     * Schedule automated loyalty processing
     * 
     * @param int $tenantId Tenant ID
     * @param string $frequency Frequency (DAILY, WEEKLY, MONTHLY)
     * @return array Schedule result
     */
    public function scheduleAutomatedProcessing($tenantId, $frequency = 'DAILY')
    {
        $sql = "
            INSERT INTO loyalty_processing_schedule
            (tenant_id, frequency, last_run, next_run, status, created_at)
            VALUES (?, ?, NULL, NOW(), 'ACTIVE', NOW())
            ON DUPLICATE KEY UPDATE
                frequency = VALUES(frequency),
                next_run = NOW(),
                updated_at = NOW()
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $frequency]);
        
        return [
            'success' => true,
            'tenant_id' => $tenantId,
            'frequency' => $frequency,
            'scheduled_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get loyalty program analytics
     * 
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Analytics data
     */
    public function getAnalytics($tenantId, $startDate, $endDate)
    {
        // Points earned vs redeemed
        $pointsEarned = $this->getPointsEarned($tenantId, $startDate, $endDate);
        $pointsRedeemed = $this->getPointsRedeemed($tenantId, $startDate, $endDate);
        
        // Redemption rate
        $redemptionRate = $pointsEarned > 0 ? ($pointsRedeemed / $pointsEarned) * 100 : 0;
        
        // Active members
        $activeMembers = $this->getActiveMembers($tenantId);
        
        // Tier changes
        $tierChanges = $this->getTierChanges($tenantId, $startDate, $endDate);
        
        // Top performers
        $topPerformers = $this->getTopPerformers($tenantId, $startDate, $endDate);
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'points' => [
                'earned' => $pointsEarned,
                'redeemed' => $pointsRedeemed,
                'redemption_rate' => round($redemptionRate, 2)
            ],
            'members' => [
                'active' => $activeMembers
            ],
            'tier_changes' => $tierChanges,
            'top_performers' => $topPerformers,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get points earned in period
     */
    private function getPointsEarned($tenantId, $startDate, $endDate)
    {
        $sql = "
            SELECT COALESCE(SUM(points_earned), 0) as total
            FROM loyalty_transactions
            WHERE tenant_id = ? 
              AND transaction_type = 'EARNED'
              AND created_at BETWEEN ? AND ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $startDate, $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Get points redeemed in period
     */
    private function getPointsRedeemed($tenantId, $startDate, $endDate)
    {
        $sql = "
            SELECT COALESCE(SUM(points_used), 0) as total
            FROM loyalty_transactions
            WHERE tenant_id = ? 
              AND transaction_type = 'REDEEMED'
              AND created_at BETWEEN ? AND ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $startDate, $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Get active members
     */
    private function getActiveMembers($tenantId)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM loyalty_members
            WHERE tenant_id = ? 
              AND status = 'ACTIVE'
              AND last_activity_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Get tier changes in period
     */
    private function getTierChanges($tenantId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_changes,
                SUM(CASE WHEN tier_upgraded_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as upgrades,
                SUM(CASE WHEN tier_downgraded_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as downgrades
            FROM loyalty_members
            WHERE tenant_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $startDate, $endDate, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get top performers in period
     */
    private function getTopPerformers($tenantId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                lt.customer_id,
                c.name as customer_name,
                SUM(lt.points_earned) as points_earned
            FROM loyalty_transactions lt
            LEFT JOIN customers c ON lt.customer_id = c.customer_id
            WHERE lt.tenant_id = ? 
              AND lt.transaction_type = 'EARNED'
              AND lt.created_at BETWEEN ? AND ?
            GROUP BY lt.customer_id, c.name
            ORDER BY points_earned DESC
            LIMIT 10
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
