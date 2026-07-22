<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class AuthController extends \App\Core\BaseController
{
    private \App\Modules\Auth\Services\AuthService $authService;

    public function __construct()
    {
        $this->authService = new \App\Modules\Auth\Services\AuthService();
    }

    private function getExpiration(): int
    {
        $envExp = getenv('JWT_EXPIRATION');
        return time() + (int)($envExp ?: 28800);
    }

    public function login($request = null)
    {
        // Get input from request parameter or php://input
        if ($request && isset($request['body'])) {
            $input = $request['body'];
        } else {
            $input = json_decode(
                file_get_contents("php://input"),
                true
            );
        }

        // Validate input
        if (empty($input['username']) || empty($input['password'])) {
            Response::error("Username and password are required", 400);
        }

        $result = $this->authService->login($input['username'], $input['password']);

        if (!$result['success']) {
            Response::error($result['message'], 401);
        }

        Response::success([
            'access_token' => $result['token'],
            'user' => $result['user']
        ], 'Login successful');
    }

    public function refresh($request = null)
    {
        try {
            $payload = AuthMiddleware::handle($request ?? []);
        } catch (\Throwable $e) {
            Response::error("Invalid or expired token", 401);
        }

        $jwt = new JWT();
        $newPayload = [
            'user_id' => $payload['user_id'],
            'username' => $payload['username'],
            'tenant_id' => $payload['tenant_id'],
            'branch_id' => $payload['branch_id'],
            'role' => $payload['role'],
            'level' => $payload['level'],
            'is_platform_owner' => $payload['is_platform_owner'],
            'exp' => $this->getExpiration()
        ];

        $token = $jwt->encode($newPayload);

        Response::success([
            'access_token' => $token,
            'user' => [
                'id' => $payload['user_id'],
                'username' => $payload['username'],
                'tenant_id' => $payload['tenant_id'],
                'branch_id' => $payload['branch_id'],
                'role' => $payload['role'],
                'level' => $payload['level'],
                'is_platform_owner' => (bool) $payload['is_platform_owner']
            ]
        ], 'Token refreshed successfully');
    }

    public function logout($request = null)
    {
        // Log the logout event for audit trail
        try {
            $payload = AuthMiddleware::handle($request ?? []);
            $logger = Logger::getInstance();
            $logger->info("User logged out", [
                'user_id' => $payload['user_id'] ?? null,
                'username' => $payload['username'] ?? null
            ]);
        } catch (\Throwable $e) {
            // Even if token is invalid, return success for logout
        }

        Response::success([], 'Logout successful');
    }

    public function changePassword($request = null)
    {
        try {
            $payload = AuthMiddleware::handle($request ?? []);
        } catch (\Throwable $e) {
            Response::error("Authentication required", 401);
        }

        if ($request && isset($request['body'])) {
            $input = $request['body'];
        } else {
            $input = json_decode(file_get_contents("php://input"), true);
        }

        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            Response::error("Current password and new password are required", 400);
        }

        if (strlen($newPassword) < 6) {
            Response::error("New password must be at least 6 characters", 400);
        }

        $db = new Database();
        $pdo = $db->connect();

        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ? AND status = 'ACTIVE'");
        $stmt->execute([$payload['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            Response::error("Current password is incorrect", 400);
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?")
            ->execute([$hashedPassword, $payload['user_id']]);

        $logger = Logger::getInstance();
        $logger->info("Password changed", [
            'user_id' => $payload['user_id'],
            'username' => $payload['username']
        ]);

        Response::success([], 'Password changed successfully');
    }

    /**
     * Request a password reset token
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword($request = null)
    {
        if ($request && isset($request['body'])) {
            $input = $request['body'];
        } else {
            $input = json_decode(file_get_contents("php://input"), true);
        }

        $email = $input['email'] ?? '';

        if (empty($email)) {
            Response::error("Email is required", 400);
        }

        $db = new Database();
        $pdo = $db->connect();

        // Find user by email
        $stmt = $pdo->prepare("SELECT user_id, username, email FROM users WHERE email = ? AND status = 'ACTIVE'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Always return success to prevent email enumeration
        if (!$user) {
            Response::success([], 'If the email exists, a reset link has been sent');
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30 minutes

        // Store token
        $stmt = $pdo->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user['user_id'], $token, $expiresAt]);

        // Build reset link
        $appUrl = getenv('APP_URL') ?: 'http://localhost/restoran/FRONTEND';
        $resetLink = $appUrl . '/reset-password.html?token=' . $token;

        // Send email
        if (class_exists('\\App\\Core\\EmailService')) {
            $emailService = new \App\Core\EmailService();
            $emailService->sendPasswordReset([
                'email' => $email,
                'username' => $user['username'],
                'reset_link' => $resetLink
            ]);
        }

        $logger = Logger::getInstance();
        $logger->info("Password reset requested", [
            'user_id' => $user['user_id'],
            'email' => $email
        ]);

        Response::success([], 'If the email exists, a reset link has been sent');
    }

    /**
     * Reset password using a valid token
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword($request = null)
    {
        if ($request && isset($request['body'])) {
            $input = $request['body'];
        } else {
            $input = json_decode(file_get_contents("php://input"), true);
        }

        $token = $input['token'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (empty($token) || empty($newPassword)) {
            Response::error("Token and new password are required", 400);
        }

        if (strlen($newPassword) < 6) {
            Response::error("New password must be at least 6 characters", 400);
        }

        $db = new Database();
        $pdo = $db->connect();

        // Validate token
        $stmt = $pdo->prepare("
            SELECT t.*, u.username, u.email
            FROM password_reset_tokens t
            INNER JOIN users u ON t.user_id = u.user_id
            WHERE t.token = ? AND t.used = 0 AND t.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resetData) {
            Response::error("Invalid or expired reset token", 400);
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password
        $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?")
            ->execute([$hashedPassword, $resetData['user_id']]);

        // Mark token as used
        $pdo->prepare("UPDATE password_reset_tokens SET used = 1, used_at = NOW() WHERE token = ?")
            ->execute([$token]);

        $logger = Logger::getInstance();
        $logger->info("Password reset completed", [
            'user_id' => $resetData['user_id'],
            'username' => $resetData['username']
        ]);

        Response::success([], 'Password reset successfully. Please login with your new password.');
    }
}
