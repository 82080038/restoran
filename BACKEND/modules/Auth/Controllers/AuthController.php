<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class AuthController
{
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

        $db = new Database();
        $pdo = $db->connect();

        $sql = "
            SELECT u.user_id, u.username, u.password, u.tenant_id, u.branch_id, u.is_platform_owner, r.role_name
            FROM users u
            INNER JOIN user_roles ur ON u.user_id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.role_id
            WHERE u.username = ? AND u.status = 'ACTIVE'
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($input['password'], $user['password'])) {
            $logger = Logger::getInstance();
            $logger->error("Login failed", [
                'username' => $input['username'] ?? 'not provided',
                'user_found' => !empty($user),
                'password_verify' => !empty($user) ? password_verify($input['password'], $user['password']) : 'N/A'
            ]);
            Response::error("Invalid credentials");
        }

        $jwt = new JWT();
        $level = $user['is_platform_owner'] ? 'PLATFORM_OWNER' : ($user['role_name'] === 'Administrator' ? 'TENANT_OWNER' : 'TENANT_MEMBER');

        $payload = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'tenant_id' => $user['tenant_id'],
            'branch_id' => $user['branch_id'],
            'role' => $user['role_name'],
            'level' => $level,
            'is_platform_owner' => $user['is_platform_owner'],
            'exp' => $this->getExpiration()
        ];

        $token = $jwt->encode($payload);

        // Update last login timestamp
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")->execute([$user['user_id']]);

        Response::success([
            'access_token' => $token,
            'user' => [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'tenant_id' => $user['tenant_id'],
                'branch_id' => $user['branch_id'],
                'role' => $user['role_name'],
                'level' => $level,
                'is_platform_owner' => (bool) $user['is_platform_owner']
            ]
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
}
