<?php

require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';

/**
 * FreePaymentController
 *
 * Handles zero-fee payment methods:
 * 1. Upload bukti transfer for bank_transfer payments
 * 2. QRIS static QR code management
 * 3. Internal wallet / prepaid balance
 *
 * These methods avoid expensive payment gateway fees (Stripe, MidTrans, Xendit).
 */
class FreePaymentController
{
    private $db;
    private $uploadDir;

    public function __construct()
    {
        $this->db = new Database();
        $this->uploadDir = __DIR__ . '/../../../public/uploads/payments/';
    }

    // ===================================================================
    // MODULE 1: UPLOAD BUKTI TRANSFER
    // ===================================================================

    /**
     * Upload bukti transfer for a bank_transfer payment
     * POST /api/v1/free-payment/transfer-proof/upload
     */
    public function uploadTransferProof($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['body']['tenant_id'] ?? 1);
            $branchId = $request['branch_id'] ?? ($request['body']['branch_id'] ?? null);
            $userId = $request['user_id'] ?? null;

            $paymentId = $request['body']['payment_id'] ?? null;
            $orderId = $request['body']['order_id'] ?? null;

            if (!$paymentId || !$orderId) {
                return Response::error('payment_id and order_id are required', 400);
            }

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return Response::error('No file uploaded or upload error', 400);
            }

            $file = $_FILES['file'];

            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'];
            $maxSize = 5 * 1024 * 1024;

            if ($file['size'] > $maxSize) {
                return Response::error('File size exceeds 5MB limit', 400);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                return Response::error('Invalid file type. Only JPEG, PNG, WebP, and PDF allowed', 400);
            }

            // Verify payment exists and belongs to tenant
            $stmt = $pdo->prepare("
                SELECT * FROM payments
                WHERE payment_id = ? AND tenant_id = ? AND payment_method = 'bank_transfer'
            ");
            $stmt->execute([$paymentId, $tenantId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payment) {
                return Response::notFound('Bank transfer payment not found');
            }

            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'proof_' . $paymentId . '_' . time() . '.' . $ext;
            $proofDir = $this->uploadDir . 'transfer_proofs/';

            if (!is_dir($proofDir)) {
                mkdir($proofDir, 0755, true);
            }

            $filepath = $proofDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return Response::error('Failed to save uploaded file', 500);
            }

            $relativePath = 'uploads/payments/transfer_proofs/' . $filename;

            // Insert transfer proof record
            $stmt = $pdo->prepare("
                INSERT INTO transfer_proofs
                    (tenant_id, branch_id, payment_id, order_id, file_path, file_name,
                     file_size, file_type, bank_from, account_holder, transfer_amount,
                     transfer_date, reference_number, uploaded_by, uploaded_at, verification_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')
            ");

            $stmt->execute([
                $tenantId,
                $branchId,
                $paymentId,
                $orderId,
                $relativePath,
                $file['name'],
                $file['size'],
                $mimeType,
                $request['body']['bank_from'] ?? null,
                $request['body']['account_holder'] ?? null,
                $request['body']['transfer_amount'] ?? $payment['amount'],
                $request['body']['transfer_date'] ?? date('Y-m-d'),
                $request['body']['reference_number'] ?? null,
                $userId
            ]);

            $proofId = $pdo->lastInsertId();

            // Update payment status to awaiting_verification
            $stmt = $pdo->prepare("
                UPDATE payments SET payment_status = 'awaiting_verification', notes = 'Bukti transfer uploaded'
                WHERE payment_id = ?
            ");
            $stmt->execute([$paymentId]);

            return Response::success([
                'proof_id' => $proofId,
                'file_path' => $relativePath,
                'verification_status' => 'pending'
            ], 'Bukti transfer uploaded successfully, waiting for verification');
        } catch (\Exception $e) {
            return Response::error('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get transfer proofs (list or by payment)
     * GET /api/v1/free-payment/transfer-proof
     */
    public function getTransferProofs($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
            $paymentId = $request['query']['payment_id'] ?? null;
            $status = $request['query']['status'] ?? null;
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT tp.*, p.payment_number, p.amount, p.payment_status,
                           o.order_number
                    FROM transfer_proofs tp
                    LEFT JOIN payments p ON tp.payment_id = p.payment_id
                    LEFT JOIN orders o ON tp.order_id = o.order_id
                    WHERE tp.tenant_id = ?";
            $params = [$tenantId];

            if ($paymentId) {
                $sql .= " AND tp.payment_id = ?";
                $params[] = $paymentId;
            }
            if ($status) {
                $sql .= " AND tp.verification_status = ?";
                $params[] = $status;
            }

            $countSql = "SELECT COUNT(*) as total FROM transfer_proofs WHERE tenant_id = ?";
            $countParams = [$tenantId];
            if ($paymentId) {
                $countSql .= " AND payment_id = ?";
                $countParams[] = $paymentId;
            }
            if ($status) {
                $countSql .= " AND verification_status = ?";
                $countParams[] = $status;
            }

            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $sql .= " ORDER BY tp.uploaded_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $proofs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'data' => $proofs,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ], 'Transfer proofs retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve transfer proofs: ' . $e->getMessage());
        }
    }

    /**
     * Verify (approve/reject) a transfer proof
     * POST /api/v1/free-payment/transfer-proof/{id}/verify
     */
    public function verifyTransferProof($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $userId = $request['user_id'] ?? null;
            $proofId = $request['id'] ?? 0;
            $body = $request['body'] ?? [];
            $action = $body['action'] ?? '';

            if (!in_array($action, ['approve', 'reject'])) {
                return Response::error('action must be "approve" or "reject"', 400);
            }

            $stmt = $pdo->prepare("
                SELECT tp.*, p.payment_status, p.order_id, p.amount
                FROM transfer_proofs tp
                JOIN payments p ON tp.payment_id = p.payment_id
                WHERE tp.proof_id = ? AND tp.tenant_id = ?
            ");
            $stmt->execute([$proofId, $tenantId]);
            $proof = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$proof) {
                return Response::notFound('Transfer proof not found');
            }

            if ($proof['verification_status'] !== 'pending') {
                return Response::error('Transfer proof already verified', 400);
            }

            $pdo->beginTransaction();

            if ($action === 'approve') {
                // Update proof status
                $stmt = $pdo->prepare("
                    UPDATE transfer_proofs
                    SET verification_status = 'approved', verified_by = ?, verified_at = NOW()
                    WHERE proof_id = ?
                ");
                $stmt->execute([$userId, $proofId]);

                // Update payment to completed
                $stmt = $pdo->prepare("
                    UPDATE payments
                    SET payment_status = 'completed', processed_at = NOW(), completed_at = NOW(),
                        gateway_transaction_id = ?, notes = 'Verified bank transfer',
                        gateway_response = ?
                    WHERE payment_id = ?
                ");
                $stmt->execute([
                    'BANK-VERIFY-' . $proofId,
                    json_encode(['proof_id' => $proofId, 'verified_by' => $userId]),
                    $proof['payment_id']
                ]);

                // Update order payment status
                $this->updateOrderPaymentStatus($pdo, $proof['order_id']);

                $pdo->commit();

                return Response::success([
                    'proof_id' => $proofId,
                    'payment_id' => $proof['payment_id'],
                    'payment_status' => 'completed'
                ], 'Transfer proof approved, payment completed');
            } else {
                // Reject
                $rejectionReason = $body['rejection_reason'] ?? 'Transfer could not be verified';

                $stmt = $pdo->prepare("
                    UPDATE transfer_proofs
                    SET verification_status = 'rejected', verified_by = ?, verified_at = NOW(),
                        rejection_reason = ?
                    WHERE proof_id = ?
                ");
                $stmt->execute([$userId, $rejectionReason, $proofId]);

                // Revert payment to pending
                $stmt = $pdo->prepare("
                    UPDATE payments
                    SET payment_status = 'pending', notes = ?
                    WHERE payment_id = ?
                ");
                $stmt->execute(['Transfer proof rejected: ' . $rejectionReason, $proof['payment_id']]);

                $pdo->commit();

                return Response::success([
                    'proof_id' => $proofId,
                    'verification_status' => 'rejected'
                ], 'Transfer proof rejected');
            }
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Response::error('Verification failed: ' . $e->getMessage(), 500);
        }
    }

    // ===================================================================
    // MODULE 2: QRIS STATIS
    // ===================================================================

    /**
     * Get QRIS static config for tenant/branch
     * GET /api/v1/free-payment/qris
     */
    public function getQrisConfig($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
            $branchId = $request['query']['branch_id'] ?? null;

            $sql = "SELECT * FROM qris_static_configs WHERE tenant_id = ? AND is_active = TRUE";
            $params = [$tenantId];

            if ($branchId) {
                $sql .= " AND (branch_id = ? OR branch_id IS NULL)";
                $params[] = $branchId;
            }
            $sql .= " ORDER BY branch_id DESC LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $config = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$config) {
                return Response::success([
                    'configured' => false,
                    'message' => 'QRIS not configured. Please set up QRIS static QR first.'
                ], 'QRIS config not found');
            }

            return Response::success([
                'configured' => true,
                'qris_id' => $config['qris_id'],
                'merchant_name' => $config['merchant_name'],
                'qr_content' => $config['qr_content'],
                'qr_image_path' => $config['qr_image_path'],
                'acquirer_bank' => $config['acquirer_bank'],
                'mdr_rate' => (float)$config['mdr_rate'],
                'is_active' => (bool)$config['is_active']
            ], 'QRIS config retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve QRIS config: ' . $e->getMessage());
        }
    }

    /**
     * Create or update QRIS static config
     * POST /api/v1/free-payment/qris
     */
    public function saveQrisConfig($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['body']['tenant_id'] ?? 1);
            $branchId = $request['body']['branch_id'] ?? null;
            $userId = $request['user_id'] ?? null;
            $body = $request['body'] ?? [];

            $merchantName = $body['merchant_name'] ?? null;
            $qrContent = $body['qr_content'] ?? null;

            if (!$merchantName || !$qrContent) {
                return Response::error('merchant_name and qr_content are required', 400);
            }

            // Check if config already exists for this tenant/branch
            $stmt = $pdo->prepare("
                SELECT qris_id FROM qris_static_configs
                WHERE tenant_id = ? AND (branch_id = ? OR (branch_id IS NULL AND ? IS NULL))
            ");
            $stmt->execute([$tenantId, $branchId, $branchId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE qris_static_configs
                    SET merchant_name = ?, merchant_id = ?, nmid = ?, terminal_id = ?,
                        qr_content = ?, qr_image_path = ?, acquirer_bank = ?, acquirer_code = ?,
                        mdr_rate = ?, is_active = ?
                    WHERE qris_id = ?
                ");
                $stmt->execute([
                    $merchantName,
                    $body['merchant_id'] ?? null,
                    $body['nmid'] ?? null,
                    $body['terminal_id'] ?? null,
                    $qrContent,
                    $body['qr_image_path'] ?? null,
                    $body['acquirer_bank'] ?? null,
                    $body['acquirer_code'] ?? null,
                    $body['mdr_rate'] ?? 0.0070,
                    $body['is_active'] ?? true,
                    $existing['qris_id']
                ]);

                return Response::success([
                    'qris_id' => $existing['qris_id'],
                    'action' => 'updated'
                ], 'QRIS config updated');
            } else {
                // Create
                $stmt = $pdo->prepare("
                    INSERT INTO qris_static_configs
                        (tenant_id, branch_id, merchant_name, merchant_id, nmid, terminal_id,
                         qr_content, qr_image_path, acquirer_bank, acquirer_code, mdr_rate,
                         is_active, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $tenantId,
                    $branchId,
                    $merchantName,
                    $body['merchant_id'] ?? null,
                    $body['nmid'] ?? null,
                    $body['terminal_id'] ?? null,
                    $qrContent,
                    $body['qr_image_path'] ?? null,
                    $body['acquirer_bank'] ?? null,
                    $body['acquirer_code'] ?? null,
                    $body['mdr_rate'] ?? 0.0070,
                    $body['is_active'] ?? true,
                    $userId
                ]);

                $qrisId = $pdo->lastInsertId();

                return Response::success([
                    'qris_id' => $qrisId,
                    'action' => 'created'
                ], 'QRIS config created', );
            }
        } catch (\Exception $e) {
            return Response::error('Failed to save QRIS config: ' . $e->getMessage());
        }
    }

    /**
     * Generate QRIS payment string for a specific order amount
     * GET /api/v1/free-payment/qris/generate?amount=50000&order_id=123
     *
     * Generates a dynamic QRIS string with embedded amount from the static config.
     * The customer scans this QR and pays exactly the specified amount.
     */
    public function generateQrisPayment($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
            $branchId = $request['query']['branch_id'] ?? null;
            $amount = (float)($request['query']['amount'] ?? 0);
            $orderId = $request['query']['order_id'] ?? null;

            if ($amount <= 0 || !$orderId) {
                return Response::error('order_id and amount greater than zero are required', 400);
            }

            $stmt = $pdo->prepare("SELECT o.total_amount, COALESCE(SUM(CASE WHEN p.payment_status = 'completed' THEN p.amount ELSE 0 END), 0) AS paid_amount FROM orders o LEFT JOIN payments p ON p.order_id = o.order_id WHERE o.order_id = ? AND o.tenant_id = ? GROUP BY o.order_id, o.total_amount");
            $stmt->execute([$orderId, $tenantId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$order) {
                return Response::notFound('Order not found');
            }

            $outstandingAmount = (float) $order['total_amount'] - (float) $order['paid_amount'];
            if ($amount > $outstandingAmount + 0.01) {
                return Response::error('Payment amount exceeds the outstanding order balance', 400);
            }

            // Get QRIS config
            $sql = "SELECT * FROM qris_static_configs WHERE tenant_id = ? AND is_active = TRUE";
            $params = [$tenantId];
            if ($branchId) {
                $sql .= " AND (branch_id = ? OR branch_id IS NULL)";
                $params[] = $branchId;
            }
            $sql .= " ORDER BY branch_id DESC LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $config = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$config) {
                return Response::error('QRIS not configured. Please set up QRIS static QR first.', 404);
            }

            // Build QRIS dynamic payment string
            // QRIS format follows MPAN standard - embed amount into the static QR
            $qrContent = $config['qr_content'];

            // Insert amount into QRIS string (format: 02 02 02 + amount as CRC)
            // The amount is embedded using tag 54 in the QRIS MPAN format
            $amountStr = number_format($amount, 2, '.', '');
            $amountTag = '54' . str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT) . $amountStr;

            // Insert before CRC tag (63) if present
            if (strpos($qrContent, '6304') !== false) {
                $dynamicQr = substr($qrContent, 0, -8) . $amountTag . '6304' . substr($qrContent, -4);
            } else {
                $dynamicQr = $qrContent . $amountTag;
            }

            // Calculate MDR fee for transparency
            $mdrRate = (float)$config['mdr_rate'];
            $mdrFee = $amount * $mdrRate;
            $netAmount = $amount - $mdrFee;

            return Response::success([
                'qris_id' => $config['qris_id'],
                'merchant_name' => $config['merchant_name'],
                'qr_content' => $dynamicQr,
                'qr_image_path' => $config['qr_image_path'],
                'amount' => $amount,
                'order_id' => $orderId,
                'outstanding_amount' => round($outstandingAmount, 2),
                'mdr_rate' => $mdrRate,
                'mdr_fee' => round($mdrFee, 2),
                'net_amount' => round($netAmount, 2),
                'acquirer_bank' => $config['acquirer_bank']
            ], 'QRIS payment generated');
        } catch (\Exception $e) {
            return Response::error('Failed to generate QRIS payment: ' . $e->getMessage());
        }
    }

    /**
     * Manually confirm a QRIS payment (kasir confirms after checking bank statement)
     * POST /api/v1/free-payment/qris/confirm
     */
    public function confirmQrisPayment($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $userId = $request['user_id'] ?? null;
            $body = $request['body'] ?? [];

            $paymentId = $body['payment_id'] ?? null;
            $referenceNumber = $body['reference_number'] ?? null;

            if (!$paymentId) {
                return Response::error('payment_id is required', 400);
            }

            $stmt = $pdo->prepare("
                SELECT * FROM payments WHERE payment_id = ? AND tenant_id = ? AND payment_method = 'qris'
            ");
            $stmt->execute([$paymentId, $tenantId]);
            $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payment) {
                return Response::notFound('QRIS payment not found');
            }

            if ($payment['payment_status'] !== 'pending') {
                return Response::error('Payment already processed', 400);
            }

            $stmt = $pdo->prepare("
                UPDATE payments
                SET payment_status = 'completed', processed_at = NOW(), completed_at = NOW(),
                    gateway_transaction_id = ?, notes = 'QRIS payment confirmed manually',
                    gateway_response = ?
                WHERE payment_id = ?
            ");
            $stmt->execute([
                'QRIS-' . ($referenceNumber ?? time()),
                json_encode(['reference' => $referenceNumber, 'confirmed_by' => $userId]),
                $paymentId
            ]);

            $this->updateOrderPaymentStatus($pdo, $payment['order_id']);

            return Response::success([
                'payment_id' => $paymentId,
                'payment_status' => 'completed'
            ], 'QRIS payment confirmed');
        } catch (\Exception $e) {
            return Response::error('Failed to confirm QRIS payment: ' . $e->getMessage());
        }
    }

    // ===================================================================
    // MODULE 3: INTERNAL WALLET / PREPAID
    // ===================================================================

    /**
     * Get or create wallet for customer
     * GET /api/v1/free-payment/wallet
     */
    public function getWallet($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
            $customerId = $request['query']['customer_id'] ?? ($request['body']['customer_id'] ?? null);

            if (!$customerId) {
                return Response::error('customer_id is required', 400);
            }

            $wallet = $this->getOrCreateWallet($pdo, $tenantId, $customerId);

            // Get recent transactions
            $stmt = $pdo->prepare("
                SELECT * FROM wallet_transactions
                WHERE wallet_id = ? AND tenant_id = ?
                ORDER BY created_at DESC LIMIT 10
            ");
            $stmt->execute([$wallet['wallet_id'], $tenantId]);
            $recentTxns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'wallet_id' => $wallet['wallet_id'],
                'wallet_number' => $wallet['wallet_number'],
                'customer_id' => $customerId,
                'balance' => (float)$wallet['balance'],
                'held_balance' => (float)$wallet['held_balance'],
                'available_balance' => (float)$wallet['balance'] - (float)$wallet['held_balance'],
                'status' => $wallet['status'],
                'recent_transactions' => $recentTxns
            ], 'Wallet retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve wallet: ' . $e->getMessage());
        }
    }

    /**
     * Request wallet top-up (via manual bank transfer - zero fee)
     * POST /api/v1/free-payment/wallet/topup
     */
    public function requestTopup($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['body']['tenant_id'] ?? 1);
            $userId = $request['user_id'] ?? null;
            $body = $request['body'] ?? [];

            $customerId = $body['customer_id'] ?? null;
            $amount = (float)($body['amount'] ?? 0);

            if (!$customerId || $amount <= 0) {
                return Response::error('customer_id and amount (> 0) are required', 400);
            }

            $wallet = $this->getOrCreateWallet($pdo, $tenantId, $customerId);

            // Handle proof file if uploaded
            $proofPath = null;
            $proofName = null;
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'topup_' . $wallet['wallet_id'] . '_' . time() . '.' . $ext;
                $topupDir = $this->uploadDir . 'topup_proofs/';
                if (!is_dir($topupDir)) {
                    mkdir($topupDir, 0755, true);
                }
                move_uploaded_file($file['tmp_name'], $topupDir . $filename);
                $proofPath = 'uploads/payments/topup_proofs/' . $filename;
                $proofName = $file['name'];
            }

            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $stmt = $pdo->prepare("
                INSERT INTO wallet_topup_requests
                    (tenant_id, wallet_id, customer_id, amount, topup_method,
                     bank_from, account_holder, transfer_date, reference_number,
                     proof_file_path, proof_file_name, status, expires_at)
                VALUES (?, ?, ?, ?, 'bank_transfer', ?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $stmt->execute([
                $tenantId,
                $wallet['wallet_id'],
                $customerId,
                $amount,
                $body['bank_from'] ?? null,
                $body['account_holder'] ?? null,
                $body['transfer_date'] ?? date('Y-m-d'),
                $body['reference_number'] ?? null,
                $proofPath,
                $proofName,
                $expiresAt
            ]);

            $topupId = $pdo->lastInsertId();

            return Response::success([
                'topup_id' => $topupId,
                'wallet_id' => $wallet['wallet_id'],
                'amount' => $amount,
                'status' => 'pending',
                'expires_at' => $expiresAt,
                'message' => 'Top-up request created. Please transfer the amount to the bank account and upload proof of transfer.'
            ], 'Top-up request created');
        } catch (\Exception $e) {
            return Response::error('Failed to create top-up request: ' . $e->getMessage());
        }
    }

    /**
     * Verify (approve/reject) a wallet top-up request
     * POST /api/v1/free-payment/wallet/topup/{id}/verify
     */
    public function verifyTopup($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? 1;
            $userId = $request['user_id'] ?? null;
            $topupId = $request['id'] ?? 0;
            $body = $request['body'] ?? [];
            $action = $body['action'] ?? '';

            if (!in_array($action, ['approve', 'reject'])) {
                return Response::error('action must be "approve" or "reject"', 400);
            }

            $stmt = $pdo->prepare("
                SELECT * FROM wallet_topup_requests
                WHERE topup_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$topupId, $tenantId]);
            $topup = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$topup) {
                return Response::notFound('Top-up request not found');
            }

            if ($topup['status'] !== 'pending') {
                return Response::error('Top-up request already processed', 400);
            }

            $pdo->beginTransaction();

            if ($action === 'approve') {
                // Get current wallet balance
                $stmt = $pdo->prepare("SELECT * FROM wallets WHERE wallet_id = ? FOR UPDATE");
                $stmt->execute([$topup['wallet_id']]);
                $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);

                $balanceBefore = (float)$wallet['balance'];
                $balanceAfter = $balanceBefore + (float)$topup['amount'];

                // Update topup status
                $stmt = $pdo->prepare("
                    UPDATE wallet_topup_requests
                    SET status = 'approved', verified_by = ?, verified_at = NOW()
                    WHERE topup_id = ?
                ");
                $stmt->execute([$userId, $topupId]);

                // Credit wallet
                $stmt = $pdo->prepare("
                    UPDATE wallets
                    SET balance = ?, last_topup_at = NOW(), last_transaction_at = NOW()
                    WHERE wallet_id = ?
                ");
                $stmt->execute([$balanceAfter, $topup['wallet_id']]);

                // Record transaction
                $stmt = $pdo->prepare("
                    INSERT INTO wallet_transactions
                        (tenant_id, wallet_id, customer_id, transaction_type, direction,
                         amount, balance_before, balance_after, topup_request_id,
                         reference_number, description, status, created_by)
                    VALUES (?, ?, ?, 'topup', 'credit', ?, ?, ?, ?, ?, 'Wallet top-up via bank transfer', 'completed', ?)
                ");
                $stmt->execute([
                    $tenantId,
                    $topup['wallet_id'],
                    $topup['customer_id'],
                    $topup['amount'],
                    $balanceBefore,
                    $balanceAfter,
                    $topupId,
                    $topup['reference_number'],
                    $userId
                ]);

                $pdo->commit();

                return Response::success([
                    'topup_id' => $topupId,
                    'wallet_id' => $topup['wallet_id'],
                    'amount' => (float)$topup['amount'],
                    'new_balance' => $balanceAfter
                ], 'Top-up approved, wallet credited');
            } else {
                // Reject
                $rejectionReason = $body['rejection_reason'] ?? 'Top-up could not be verified';

                $stmt = $pdo->prepare("
                    UPDATE wallet_topup_requests
                    SET status = 'rejected', verified_by = ?, verified_at = NOW(), rejection_reason = ?
                    WHERE topup_id = ?
                ");
                $stmt->execute([$userId, $rejectionReason, $topupId]);

                $pdo->commit();

                return Response::success([
                    'topup_id' => $topupId,
                    'status' => 'rejected'
                ], 'Top-up request rejected');
            }
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Response::error('Verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Pay order using wallet balance (zero fee)
     * POST /api/v1/free-payment/wallet/pay
     */
    public function payWithWallet($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['body']['tenant_id'] ?? 1);
            $userId = $request['user_id'] ?? null;
            $body = $request['body'] ?? [];

            $customerId = $body['customer_id'] ?? null;
            $orderId = $body['order_id'] ?? null;
            $amount = (float)($body['amount'] ?? 0);

            if (!$customerId || !$orderId || $amount <= 0) {
                return Response::error('customer_id, order_id, and amount (> 0) are required', 400);
            }

            $wallet = $this->getOrCreateWallet($pdo, $tenantId, $customerId);

            $availableBalance = (float)$wallet['balance'] - (float)$wallet['held_balance'];

            if ($availableBalance < $amount) {
                return Response::error('Insufficient wallet balance. Available: ' . $availableBalance . ', Required: ' . $amount, 400);
            }

            $pdo->beginTransaction();

            // Lock wallet row
            $stmt = $pdo->prepare("SELECT * FROM wallets WHERE wallet_id = ? FOR UPDATE");
            $stmt->execute([$wallet['wallet_id']]);
            $lockedWallet = $stmt->fetch(\PDO::FETCH_ASSOC);

            $balanceBefore = (float)$lockedWallet['balance'];
            $balanceAfter = $balanceBefore - $amount;

            // Debit wallet
            $stmt = $pdo->prepare("
                UPDATE wallets
                SET balance = ?, last_transaction_at = NOW()
                WHERE wallet_id = ?
            ");
            $stmt->execute([$balanceAfter, $wallet['wallet_id']]);

            // Create payment record
            $paymentNumber = 'WALLET-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $stmt = $pdo->prepare("
                INSERT INTO payments
                    (tenant_id, branch_id, order_id, payment_number, payment_method,
                     payment_status, amount, currency, processed_by, processed_at, completed_at,
                     gateway_transaction_id, notes, created_at)
                VALUES (?, ?, ?, ?, 'wallet', 'completed', ?, 'IDR', ?, NOW(), NOW(), ?, 'Paid via internal wallet', NOW())
            ");
            $stmt->execute([
                $tenantId,
                $body['branch_id'] ?? null,
                $orderId,
                $paymentNumber,
                $amount,
                $userId,
                'WALLET-' . $wallet['wallet_id'] . '-' . time()
            ]);
            $paymentId = $pdo->lastInsertId();

            // Record wallet transaction
            $stmt = $pdo->prepare("
                INSERT INTO wallet_transactions
                    (tenant_id, wallet_id, customer_id, transaction_type, direction,
                     amount, balance_before, balance_after, order_id, payment_id,
                     description, status, created_by)
                VALUES (?, ?, ?, 'payment', 'debit', ?, ?, ?, ?, ?, 'Payment for order', 'completed', ?)
            ");
            $stmt->execute([
                $tenantId,
                $wallet['wallet_id'],
                $customerId,
                $amount,
                $balanceBefore,
                $balanceAfter,
                $orderId,
                $paymentId,
                $userId
            ]);

            // Update order payment status
            $this->updateOrderPaymentStatus($pdo, $orderId);

            $pdo->commit();

            return Response::success([
                'payment_id' => $paymentId,
                'payment_number' => $paymentNumber,
                'payment_status' => 'completed',
                'wallet_balance_before' => $balanceBefore,
                'wallet_balance_after' => $balanceAfter,
                'amount_paid' => $amount
            ], 'Payment successful via wallet');
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Response::error('Wallet payment failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get wallet transaction history
     * GET /api/v1/free-payment/wallet/transactions
     */
    public function getWalletTransactions($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
            $customerId = $request['query']['customer_id'] ?? null;
            $type = $request['query']['type'] ?? null;
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            if (!$customerId) {
                return Response::error('customer_id is required', 400);
            }

            $sql = "SELECT wt.*, o.order_number
                    FROM wallet_transactions wt
                    LEFT JOIN orders o ON wt.order_id = o.order_id
                    WHERE wt.tenant_id = ? AND wt.customer_id = ?";
            $params = [$tenantId, $customerId];

            if ($type) {
                $sql .= " AND wt.transaction_type = ?";
                $params[] = $type;
            }

            $countSql = "SELECT COUNT(*) as total FROM wallet_transactions WHERE tenant_id = ? AND customer_id = ?";
            $countParams = [$tenantId, $customerId];
            if ($type) {
                $countSql .= " AND transaction_type = ?";
                $countParams[] = $type;
            }

            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $sql .= " ORDER BY wt.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'data' => $transactions,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ], 'Wallet transactions retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve transactions: ' . $e->getMessage());
        }
    }

    /**
     * Get pending top-up requests (for kasir/admin verification)
     * GET /api/v1/free-payment/wallet/topups
     */
    public function getTopupRequests($request)
    {
        try {
            $pdo = $this->db->connect();
            $tenantId = $request['tenant_id'] ?? ($request['query']['tenant_id'] ?? 1);
            $status = $request['query']['status'] ?? 'pending';
            $page = (int)($request['query']['page'] ?? 1);
            $limit = (int)($request['query']['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            $sql = "SELECT wt.*, w.wallet_number, w.balance as current_balance
                    FROM wallet_topup_requests wt
                    JOIN wallets w ON wt.wallet_id = w.wallet_id
                    WHERE wt.tenant_id = ? AND wt.status = ?";
            $params = [$tenantId, $status];

            $countSql = "SELECT COUNT(*) as total FROM wallet_topup_requests WHERE tenant_id = ? AND status = ?";
            $countParams = [$tenantId, $status];

            $stmt = $pdo->prepare($countSql);
            $stmt->execute($countParams);
            $total = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            $sql .= " ORDER BY wt.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $topups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return Response::success([
                'data' => $topups,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => (int)ceil($total / $limit)
                ]
            ], 'Top-up requests retrieved');
        } catch (\Exception $e) {
            return Response::error('Failed to retrieve top-up requests: ' . $e->getMessage());
        }
    }

    // ===================================================================
    // PRIVATE HELPERS
    // ===================================================================

    /**
     * Get or create wallet for a customer
     */
    private function getOrCreateWallet($pdo, $tenantId, $customerId): array
    {
        $stmt = $pdo->prepare("
            SELECT * FROM wallets WHERE tenant_id = ? AND customer_id = ?
        ");
        $stmt->execute([$tenantId, $customerId]);
        $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($wallet) {
            return $wallet;
        }

        // Create new wallet
        $walletNumber = 'WAL-' . date('Ymd') . '-' . str_pad((string)$customerId, 6, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));

        $stmt = $pdo->prepare("
            INSERT INTO wallets (tenant_id, customer_id, wallet_number, balance, held_balance, currency, status)
            VALUES (?, ?, ?, 0, 0, 'IDR', 'active')
        ");
        $stmt->execute([$tenantId, $customerId, $walletNumber]);

        $walletId = $pdo->lastInsertId();

        return [
            'wallet_id' => $walletId,
            'wallet_number' => $walletNumber,
            'tenant_id' => $tenantId,
            'customer_id' => $customerId,
            'balance' => 0,
            'held_balance' => 0,
            'status' => 'active'
        ];
    }

    /**
     * Update order payment status based on completed payments
     */
    private function updateOrderPaymentStatus($pdo, $orderId): void
    {
        $stmt = $pdo->prepare("
            SELECT SUM(amount) as total_paid
            FROM payments WHERE order_id = ? AND payment_status = 'completed'
        ");
        $stmt->execute([$orderId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $totalPaid = (float)($result['total_paid'] ?? 0);

        $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        $orderTotal = (float)($order['total_amount'] ?? 0);

        $paymentStatus = 'unpaid';
        if ($totalPaid >= $orderTotal && $orderTotal > 0) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, paid_amount = ? WHERE order_id = ?");
        $stmt->execute([$paymentStatus, $totalPaid, $orderId]);
    }
}
