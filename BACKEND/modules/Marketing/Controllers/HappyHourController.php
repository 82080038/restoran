<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

/**
 * Happy Hour / Promotional Pricing Controller
 *
 * Manages time-based promotional pricing:
 * - Happy hour discounts (e.g., 50% off drinks 3-6 PM)
 * - Day-of-week specific promotions
 * - Category/product-specific discounts
 * - Minimum order requirements
 * - Maximum discount caps
 */
class HappyHourController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get all promotions for a tenant
     * GET /api/v1/promotions
     */
    public function getPromotions($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $activeOnly = ($request['query']['active'] ?? 'false') === 'true';

            $sql = "SELECT * FROM happy_hour_promotions WHERE tenant_id = ?";
            $params = [$tenantId];

            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }

            $sql .= " ORDER BY priority DESC, created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $promotions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Check which are currently active based on time
            $now = new \DateTime();
            $currentTime = $now->format('H:i:s');
            $currentDay = (int)$now->format('N'); // 1=Monday, 7=Sunday

            foreach ($promotions as &$promo) {
                $applicableDays = explode(',', $promo['applicable_days']);
                $isToday = in_array((string)$currentDay, $applicableDays);
                $isTimeActive = ($currentTime >= $promo['start_time'] && $currentTime <= $promo['end_time']);
                $isDateValid = true;
                if ($promo['start_date'] && $now->format('Y-m-d') < $promo['start_date']) $isDateValid = false;
                if ($promo['end_date'] && $now->format('Y-m-d') > $promo['end_date']) $isDateValid = false;

                $promo['is_currently_active'] = $promo['is_active'] && $isToday && $isTimeActive && $isDateValid;
            }

            return Response::success($promotions, 'Promotions retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve promotions: ' . $e->getMessage());
        }
    }

    /**
     * Get a single promotion
     * GET /api/v1/promotions/{id}
     */
    public function getPromotion($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $promotionId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("SELECT * FROM happy_hour_promotions WHERE promotion_id = ? AND tenant_id = ?");
            $stmt->execute([$promotionId, $tenantId]);
            $promotion = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$promotion) {
                return Response::notFound('Promotion not found');
            }

            return Response::success($promotion, 'Promotion retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve promotion: ' . $e->getMessage());
        }
    }

    /**
     * Create a new promotion
     * POST /api/v1/promotions
     */
    public function createPromotion($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $name = $body['promotion_name'] ?? '';
            $startTime = $body['start_time'] ?? '';
            $endTime = $body['end_time'] ?? '';
            $discountType = $body['discount_type'] ?? 'percentage';
            $discountValue = (float)($body['discount_value'] ?? 0);

            if (empty($name) || empty($startTime) || empty($endTime) || $discountValue <= 0) {
                return Response::error('promotion_name, start_time, end_time, and discount_value are required', 400);
            }

            if (!in_array($discountType, ['percentage', 'fixed'])) {
                return Response::error('discount_type must be "percentage" or "fixed"', 400);
            }

            if ($discountType === 'percentage' && $discountValue > 100) {
                return Response::error('Percentage discount cannot exceed 100', 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO happy_hour_promotions
                    (tenant_id, branch_id, promotion_name, description, start_time, end_time,
                     applicable_days, discount_type, discount_value, min_order_amount,
                     max_discount_amount, applicable_categories, applicable_products,
                     is_active, priority, start_date, end_date, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $tenantId,
                $body['branch_id'] ?? null,
                $name,
                $body['description'] ?? null,
                $startTime,
                $endTime,
                $body['applicable_days'] ?? '1,2,3,4,5,6,7',
                $discountType,
                $discountValue,
                (float)($body['min_order_amount'] ?? 0),
                $body['max_discount_amount'] ?? null,
                $body['applicable_categories'] ?? null,
                $body['applicable_products'] ?? null,
                $body['is_active'] ?? 1,
                (int)($body['priority'] ?? 0),
                $body['start_date'] ?? null,
                $body['end_date'] ?? null,
                $payload['user_id'] ?? null
            ]);

            $promotionId = $pdo->lastInsertId();

            return Response::success([
                'promotion_id' => (int)$promotionId,
                'promotion_name' => $name
            ], 'Promotion created successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to create promotion: ' . $e->getMessage());
        }
    }

    /**
     * Update a promotion
     * PUT /api/v1/promotions/{id}
     */
    public function updatePromotion($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $promotionId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            // Verify ownership
            $stmt = $pdo->prepare("SELECT promotion_id FROM happy_hour_promotions WHERE promotion_id = ? AND tenant_id = ?");
            $stmt->execute([$promotionId, $tenantId]);
            if (!$stmt->fetch()) {
                return Response::notFound('Promotion not found');
            }

            $updateFields = [];
            $params = [];

            $allowedFields = ['promotion_name', 'description', 'start_time', 'end_time',
                'applicable_days', 'discount_type', 'discount_value', 'min_order_amount',
                'max_discount_amount', 'applicable_categories', 'applicable_products',
                'is_active', 'priority', 'start_date', 'end_date'];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $body)) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $body[$field];
                }
            }

            if (empty($updateFields)) {
                return Response::error('No fields to update', 400);
            }

            $updateFields[] = "updated_at = NOW()";
            $params[] = $promotionId;

            $sql = "UPDATE happy_hour_promotions SET " . implode(', ', $updateFields) . " WHERE promotion_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return Response::success(['promotion_id' => (int)$promotionId], 'Promotion updated successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to update promotion: ' . $e->getMessage());
        }
    }

    /**
     * Delete a promotion
     * DELETE /api/v1/promotions/{id}
     */
    public function deletePromotion($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $promotionId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("DELETE FROM happy_hour_promotions WHERE promotion_id = ? AND tenant_id = ?");
            $stmt->execute([$promotionId, $tenantId]);

            if ($stmt->rowCount() === 0) {
                return Response::notFound('Promotion not found');
            }

            return Response::success([], 'Promotion deleted successfully');
        } catch (\Exception $e) {
            return Response::error('Failed to delete promotion: ' . $e->getMessage());
        }
    }

    /**
     * Calculate applicable discount for an order
     * POST /api/v1/promotions/calculate
     */
    public function calculateDiscount($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $tenantId = $payload['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];

            $orderAmount = (float)($body['order_amount'] ?? 0);
            $items = $body['items'] ?? [];
            $branchId = $body['branch_id'] ?? null;

            // Find currently active promotions
            $now = new \DateTime();
            $currentTime = $now->format('H:i:s');
            $currentDate = $now->format('Y-m-d');
            $currentDay = (int)$now->format('N');

            $sql = "SELECT * FROM happy_hour_promotions
                    WHERE tenant_id = ? AND is_active = 1
                    AND start_time <= ? AND end_time >= ?
                    AND FIND_IN_SET(?, applicable_days)
                    AND (start_date IS NULL OR start_date <= ?)
                    AND (end_date IS NULL OR end_date >= ?)";

            $params = [$tenantId, $currentTime, $currentTime, $currentDay, $currentDate, $currentDate];

            if ($branchId) {
                $sql .= " AND (branch_id IS NULL OR branch_id = ?)";
                $params[] = $branchId;
            }

            $sql .= " ORDER BY priority DESC, discount_value DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $promotions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $bestDiscount = 0;
            $appliedPromotion = null;

            foreach ($promotions as $promo) {
                // Check minimum order
                if ($orderAmount < (float)$promo['min_order_amount']) continue;

                // Calculate discount
                if ($promo['discount_type'] === 'percentage') {
                    $discount = $orderAmount * ((float)$promo['discount_value'] / 100);
                } else {
                    $discount = (float)$promo['discount_value'];
                }

                // Cap at max discount
                if ($promo['max_discount_amount']) {
                    $discount = min($discount, (float)$promo['max_discount_amount']);
                }

                if ($discount > $bestDiscount) {
                    $bestDiscount = $discount;
                    $appliedPromotion = $promo;
                }
            }

            $finalAmount = $orderAmount - $bestDiscount;

            return Response::success([
                'order_amount' => $orderAmount,
                'discount_amount' => round($bestDiscount, 2),
                'final_amount' => round($finalAmount, 2),
                'applied_promotion' => $appliedPromotion ? [
                    'promotion_id' => (int)$appliedPromotion['promotion_id'],
                    'promotion_name' => $appliedPromotion['promotion_name'],
                    'discount_type' => $appliedPromotion['discount_type'],
                    'discount_value' => (float)$appliedPromotion['discount_value']
                ] : null,
                'available_promotions' => count($promotions)
            ], 'Discount calculated');
        } catch (\Exception $e) {
            return Response::error('Failed to calculate discount: ' . $e->getMessage());
        }
    }

    /**
     * Get promotion usage statistics
     * GET /api/v1/promotions/{id}/stats
     */
    public function getPromotionStats($request)
    {
        try {
            $payload = AuthMiddleware::handle($request);
            $pdo = $this->db->connect();
            $promotionId = $request['id'] ?? 0;
            $tenantId = $payload['tenant_id'] ?? 1;
            $dateFrom = $request['query']['date_from'] ?? date('Y-m-01');
            $dateTo = $request['query']['date_to'] ?? date('Y-m-d');

            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_usages,
                       SUM(discount_amount) as total_discount,
                       SUM(original_amount) as total_original,
                       SUM(final_amount) as total_final
                FROM promotion_usages
                WHERE promotion_id = ? AND tenant_id = ?
                AND DATE(used_at) BETWEEN ? AND ?
            ");
            $stmt->execute([$promotionId, $tenantId, $dateFrom, $dateTo]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Recent usages
            $stmt = $pdo->prepare("
                SELECT pu.*, o.order_number
                FROM promotion_usages pu
                LEFT JOIN orders o ON pu.order_id = o.order_id
                WHERE pu.promotion_id = ? AND pu.tenant_id = ?
                ORDER BY pu.used_at DESC LIMIT 20
            ");
            $stmt->execute([$promotionId, $tenantId]);
            $recentUsages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'stats' => $stats,
                'recent_usages' => $recentUsages
            ], 'Promotion stats retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve stats: ' . $e->getMessage());
        }
    }
}
