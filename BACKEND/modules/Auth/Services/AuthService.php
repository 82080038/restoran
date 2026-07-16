<?php

namespace App\Modules\Auth\Services;

use App\Core\Database;
use App\Core\Transaction;
use App\Core\Audit;
use App\Core\JWT;
use App\Core\Logger;
use App\Modules\Auth\Repositories\AuthRepository;

class AuthService
{
    private $repository;
    private $db;
    private $jwt;
    private $logger;
    
    public function __construct()
    {
        $this->repository = new AuthRepository();
        $this->db = Database::getInstance();
        $this->jwt = new JWT();
        $this->logger = Logger::getInstance();
    }
    
    /**
     * Authenticate user and generate JWT token
     */
    public function login($username, $password)
    {
        $user = $this->repository->findByUsername($username);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->logger->error("Login failed", [
                'username' => $username,
                'user_found' => !empty($user),
                'password_verify' => !empty($user) ? password_verify($password, $user['password']) : 'N/A'
            ]);
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }
        
        // Determine user level
        $level = $user['is_platform_owner'] ? 'PLATFORM_OWNER' : 
                 ($user['role_name'] === 'Administrator' ? 'TENANT_OWNER' : 'TENANT_MEMBER');
        
        // Create JWT payload
        $payload = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'tenant_id' => $user['tenant_id'],
            'branch_id' => $user['branch_id'],
            'role' => $user['role_name'],
            'level' => $level,
            'is_platform_owner' => $user['is_platform_owner'],
            'exp' => time() + (60 * 60 * 8) // 8 hours
        ];
        
        // Generate token
        $token = $this->jwt->encode($payload);
        
        // Log successful login
        Audit::log($user['tenant_id'], $user['user_id'], 'AUTH_LOGIN', "User logged in: {$username}");
        
        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'tenant_id' => $user['tenant_id'],
                'branch_id' => $user['branch_id'],
                'role' => $user['role_name'],
                'level' => $level,
                'is_platform_owner' => (bool) $user['is_platform_owner']
            ]
        ];
    }
    
    /**
     * Validate JWT token
     */
    public function validateToken($token)
    {
        try {
            $payload = $this->jwt->decode($token);
            
            // Check if token is expired
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return [
                    'success' => false,
                    'message' => 'Token expired'
                ];
            }
            
            // Verify user still exists and is active
            $user = $this->repository->findById($payload['user_id'], $payload['tenant_id']);
            
            if (!$user || $user['status'] !== 'ACTIVE') {
                return [
                    'success' => false,
                    'message' => 'User not found or inactive'
                ];
            }
            
            return [
                'success' => true,
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            $this->logger->error("Token validation failed", [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Invalid token'
            ];
        }
    }
    
    /**
     * Create new user
     */
    public function createUser($data, $tenantId, $userId)
    {
        Transaction::begin();
        
        try {
            // Check if username already exists
            if ($this->repository->usernameExists($data['username'])) {
                return [
                    'success' => false,
                    'message' => 'Username already exists'
                ];
            }
            
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            
            // Add tenant and defaults
            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $data['branch_id'] ?? null;
            $data['phone'] = $data['phone'] ?? null;
            $data['is_platform_owner'] = $data['is_platform_owner'] ?? 0;
            $data['status'] = $data['status'] ?? 'ACTIVE';
            
            // Create user
            $userId = $this->repository->createUser($data);
            
            // Assign role if provided
            if (isset($data['role_id'])) {
                $this->repository->assignRole($userId, $data['role_id']);
            }
            
            Audit::log($tenantId, $userId, 'AUTH_USER_CREATE', "Created user with ID: {$userId}");
            
            Transaction::commit();
            
            return [
                'success' => true,
                'user_id' => $userId
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            $this->logger->error("User creation failed", [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update user
     */
    public function updateUser($userId, $data, $tenantId, $currentUserId)
    {
        Transaction::begin();
        
        try {
            // Remove password from data if present (handle separately)
            $password = null;
            if (isset($data['password'])) {
                $password = $data['password'];
                unset($data['password']);
            }
            
            // Update user
            $result = $this->repository->updateUser($userId, $data, $tenantId);
            
            // Update password if provided
            if ($password) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $this->repository->updatePassword($userId, $passwordHash, $tenantId);
            }
            
            Audit::log($tenantId, $currentUserId, 'AUTH_USER_UPDATE', "Updated user with ID: {$userId}");
            
            Transaction::commit();
            
            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            $this->logger->error("User update failed", [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Failed to update user'
            ];
        }
    }
    
    /**
     * Delete user (soft delete)
     */
    public function deleteUser($userId, $tenantId, $currentUserId)
    {
        Transaction::begin();
        
        try {
            $result = $this->repository->deleteUser($userId, $tenantId);
            
            Audit::log($tenantId, $currentUserId, 'AUTH_USER_DELETE', "Deleted user with ID: {$userId}");
            
            Transaction::commit();
            
            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            $this->logger->error("User deletion failed", [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Failed to delete user'
            ];
        }
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId)
    {
        return $this->repository->getUserPermissions($userId);
    }
    
    /**
     * Logout user (stub for test compatibility)
     */
    public function logout(): bool
    {
        return true;
    }

    /**
     * Get all users for tenant
     */
    public function getAllUsers($tenantId, $limit = 100, $offset = 0)
    {
        return $this->repository->getAllUsers($tenantId, $limit, $offset);
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId, $tenantId)
    {
        return $this->repository->findById($userId, $tenantId);
    }
}
