<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

/**
 * Simple Feedback Controller (compatible with current router pattern)
 * Manages customer reviews, feedback, and ratings
 */
class SimpleFeedbackController extends \App\Core\BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get reviews with filters
     * GET /api/v1/feedback/reviews
     */
    public function getReviews($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $status = $request['query']['status'] ?? null;
            $rating = $request['query']['rating'] ?? null;
            $source = $request['query']['source'] ?? null;
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT r.*, c.name AS customer_name, c.email AS customer_email,
                           rr.response_text, rr.responded_at, rr.responded_by
                    FROM reviews r
                    LEFT JOIN customers c ON r.customer_id = c.customer_id
                    LEFT JOIN review_responses rr ON r.review_id = rr.review_id
                    WHERE r.tenant_id = ?";
            $params = [$tenantId];

            if ($status) {
                $sql .= " AND r.status = ?";
                $params[] = $status;
            }
            if ($rating) {
                $sql .= " AND r.rating = ?";
                $params[] = (int)$rating;
            }
            if ($source) {
                $sql .= " AND r.source = ?";
                $params[] = $source;
            }

            // Count
            $countSql = "SELECT COUNT(*) as total FROM reviews r WHERE r.tenant_id = ?";
            $countParams = [$tenantId];
            if ($status) { $countSql .= " AND r.status = ?"; $countParams[] = $status; }
            if ($rating) { $countSql .= " AND r.rating = ?"; $countParams[] = (int)$rating; }
            if ($source) { $countSql .= " AND r.source = ?"; $countParams[] = $source; }
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $sql .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::paginated($reviews, $total, $page, $limit, 'Reviews retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Get a single review
     * GET /api/v1/feedback/reviews/{id}
     */
    public function getReview($request)
    {
        try {
            $pdo = $this->db->connect();
            $reviewId = $request['id'] ?? 0;
            $tenantId = $request['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("
                SELECT r.*, c.name AS customer_name, c.email AS customer_email
                FROM reviews r
                LEFT JOIN customers c ON r.customer_id = c.customer_id
                WHERE r.review_id = ? AND r.tenant_id = ?
            ");
            $stmt->execute([$reviewId, $tenantId]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$review) {
                return Response::notFound('Review not found');
            }

            // Get response if exists
            $stmt = $pdo->prepare("SELECT * FROM review_responses WHERE review_id = ?");
            $stmt->execute([$reviewId]);
            $review['response'] = $stmt->fetch(\PDO::FETCH_ASSOC);

            return Response::success($review, 'Review retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Create a review (public - for customers)
     * POST /api/v1/feedback/reviews
     */
    public function createReview($request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];

            $tenantId = $body['tenant_id'] ?? 1;
            $customerId = $body['customer_id'] ?? null;
            $orderId = $body['order_id'] ?? null;
            $rating = (int)($body['rating'] ?? 0);
            $title = $body['title'] ?? '';
            $comment = $body['comment'] ?? '';
            $source = $body['source'] ?? 'direct';
            $categories = $body['categories'] ?? [];

            if ($rating < 1 || $rating > 5) {
                return Response::error('Rating must be between 1 and 5', 400);
            }
            if (empty($comment)) {
                return Response::error('Comment is required', 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO reviews (tenant_id, customer_id, order_id, rating, title, comment, source, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$tenantId, $customerId, $orderId, $rating, $title, $comment, $source]);
            $reviewId = $pdo->lastInsertId();

            // Insert category ratings if provided
            if (!empty($categories)) {
                $catStmt = $pdo->prepare("
                    INSERT INTO review_category_ratings (review_id, category_id, rating)
                    VALUES (?, ?, ?)
                ");
                foreach ($categories as $cat) {
                    if (isset($cat['category_id']) && isset($cat['rating'])) {
                        $catStmt->execute([$reviewId, $cat['category_id'], (int)$cat['rating']]);
                    }
                }
            }

            return Response::success([
                'review_id' => (int)$reviewId,
                'rating' => $rating
            ], 'Review submitted successfully');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Update review status (approve/reject/feature)
     * PATCH /api/v1/feedback/reviews/{id}/status
     */
    public function updateReviewStatus($request)
    {
        try {
            $pdo = $this->db->connect();
            $reviewId = $request['id'] ?? 0;
            $tenantId = $request['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];
            $status = $body['status'] ?? '';

            $validStatuses = ['pending', 'approved', 'rejected', 'featured', 'archived'];
            if (!in_array($status, $validStatuses)) {
                return Response::error('Invalid status. Valid: ' . implode(', ', $validStatuses), 400);
            }

            $stmt = $pdo->prepare("UPDATE reviews SET status = ?, updated_at = NOW() WHERE review_id = ? AND tenant_id = ?");
            $stmt->execute([$status, $reviewId, $tenantId]);

            if ($stmt->rowCount() === 0) {
                return Response::notFound('Review not found');
            }

            return Response::success(['review_id' => (int)$reviewId, 'status' => $status], 'Review status updated');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Respond to a review
     * POST /api/v1/feedback/reviews/{id}/respond
     */
    public function respondToReview($request)
    {
        try {
            $pdo = $this->db->connect();
            $reviewId = $request['id'] ?? 0;
            $tenantId = $request['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];
            $responseText = $body['response_text'] ?? '';

            if (empty($responseText)) {
                return Response::error('response_text is required', 400);
            }

            // Verify review belongs to tenant
            $stmt = $pdo->prepare("SELECT review_id FROM reviews WHERE review_id = ? AND tenant_id = ?");
            $stmt->execute([$reviewId, $tenantId]);
            if (!$stmt->fetch()) {
                return Response::notFound('Review not found');
            }

            // Upsert response
            $stmt = $pdo->prepare("
                INSERT INTO review_responses (review_id, response_text, responded_by, responded_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE response_text = VALUES(response_text), responded_by = VALUES(responded_by), responded_at = NOW()
            ");
            $stmt->execute([$reviewId, $responseText, $request['user_id']]);

            return Response::success([
                'review_id' => (int)$reviewId,
                'response_text' => $responseText
            ], 'Review response saved');
        } catch (\Exception $e) {
            return Response::error('Failed to respond to review: ' . $e->getMessage());
        }
    }

    /**
     * Get general feedback (suggestions, complaints)
     * GET /api/v1/feedback
     */
    public function getFeedback($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $type = $request['query']['type'] ?? null;
            $status = $request['query']['status'] ?? null;
            $priority = $request['query']['priority'] ?? null;
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT f.*, c.name AS customer_name
                    FROM feedback f
                    LEFT JOIN customers c ON f.customer_id = c.customer_id
                    WHERE f.tenant_id = ?";
            $params = [$tenantId];

            if ($type) { $sql .= " AND f.feedback_type = ?"; $params[] = $type; }
            if ($status) { $sql .= " AND f.status = ?"; $params[] = $status; }
            if ($priority) { $sql .= " AND f.priority = ?"; $params[] = $priority; }

            $countSql = "SELECT COUNT(*) as total FROM feedback f WHERE f.tenant_id = ?";
            $countParams = [$tenantId];
            if ($type) { $countSql .= " AND f.feedback_type = ?"; $countParams[] = $type; }
            if ($status) { $countSql .= " AND f.status = ?"; $countParams[] = $status; }
            if ($priority) { $countSql .= " AND f.priority = ?"; $countParams[] = $priority; }
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $sql .= " ORDER BY f.created_at DESC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $feedback = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::paginated($feedback, $total, $page, $limit, 'Feedback retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Create feedback (public - for customers)
     * POST /api/v1/feedback
     */
    public function createFeedback($request)
    {
        try {
            $pdo = $this->db->connect();
            $body = $request['body'] ?? [];

            $tenantId = $body['tenant_id'] ?? 1;
            $branchId = $body['branch_id'] ?? 1;
            $customerId = $body['customer_id'] ?? null;
            $type = $body['feedback_type'] ?? $body['type'] ?? 'GENERAL';
            $comment = $body['comment'] ?? $body['message'] ?? '';
            $rating = $body['rating'] ?? null;

            if (empty($comment)) {
                return Response::error('comment or message is required', 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO feedback (tenant_id, branch_id, customer_id, feedback_type, rating, comment, status)
                VALUES (?, ?, ?, ?, ?, ?, 'NEW')
            ");
            $stmt->execute([$tenantId, $branchId, $customerId, $type, $rating, $comment]);
            $feedbackId = $pdo->lastInsertId();

            return Response::success(['feedback_id' => (int)$feedbackId], 'Feedback submitted successfully');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Update feedback status
     * PATCH /api/v1/feedback/{id}/status
     */
    public function updateFeedbackStatus($request)
    {
        try {
            $pdo = $this->db->connect();
            $feedbackId = $request['id'] ?? 0;
            $tenantId = $request['tenant_id'] ?? 1;
            $body = $request['body'] ?? [];
            $status = $body['status'] ?? '';

            $validStatuses = ['new', 'in_progress', 'resolved', 'closed', 'archived', 'open', 'pending'];
            $status = strtolower($status);
            if (!in_array($status, $validStatuses)) {
                return Response::error('Invalid status. Valid: ' . implode(', ', $validStatuses), 400);
            }

            $stmt = $pdo->prepare("UPDATE feedback SET status = ?, updated_at = NOW() WHERE feedback_id = ? AND tenant_id = ?");
            $stmt->execute([$status, $feedbackId, $tenantId]);

            if ($stmt->rowCount() === 0) {
                return Response::notFound('Feedback not found');
            }

            return Response::success(['feedback_id' => (int)$feedbackId, 'status' => $status], 'Feedback status updated');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Get feedback statistics
     * GET /api/v1/feedback/statistics
     */
    public function getStatistics($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;

            // Review stats
            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reviews,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_reviews
                FROM reviews WHERE tenant_id = ?
            ");
            $stmt->execute([$tenantId]);
            $reviewStats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Feedback stats
            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) as total_feedback,
                    SUM(CASE WHEN feedback_type = 'complaint' THEN 1 ELSE 0 END) as complaints,
                    SUM(CASE WHEN feedback_type = 'suggestion' THEN 1 ELSE 0 END) as suggestions,
                    SUM(CASE WHEN feedback_type = 'compliment' THEN 1 ELSE 0 END) as compliments,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_feedback,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_feedback
                FROM feedback WHERE tenant_id = ?
            ");
            $stmt->execute([$tenantId]);
            $feedbackStats = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Rating distribution
            $distribution = [
                '5' => (int)$reviewStats['five_star'],
                '4' => (int)$reviewStats['four_star'],
                '3' => (int)$reviewStats['three_star'],
                '2' => (int)$reviewStats['two_star'],
                '1' => (int)$reviewStats['one_star'],
            ];

            return Response::success([
                'reviews' => [
                    'total' => (int)$reviewStats['total_reviews'],
                    'average_rating' => round((float)$reviewStats['average_rating'], 2),
                    'distribution' => $distribution,
                    'pending' => (int)$reviewStats['pending_reviews'],
                    'approved' => (int)$reviewStats['approved_reviews']
                ],
                'feedback' => [
                    'total' => (int)$feedbackStats['total_feedback'],
                    'complaints' => (int)$feedbackStats['complaints'],
                    'suggestions' => (int)$feedbackStats['suggestions'],
                    'compliments' => (int)$feedbackStats['compliments'],
                    'new' => (int)$feedbackStats['new_feedback'],
                    'resolved' => (int)$feedbackStats['resolved_feedback']
                ]
            ], 'Feedback statistics retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Get review categories
     * GET /api/v1/feedback/review-categories
     */
    public function getReviewCategories($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;

            $stmt = $pdo->prepare("SELECT * FROM review_categories WHERE tenant_id = ? OR tenant_id IS NULL ORDER BY category_name");
            $stmt->execute([$tenantId]);
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success($categories, 'Review categories retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }
}
