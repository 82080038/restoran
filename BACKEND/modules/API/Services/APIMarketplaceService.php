<?php

namespace App\Modules\API\Services;

use App\Core\Database;
use App\Core\Audit;

class APIMarketplaceService
{
    private $db;
    private $audit;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->audit = Audit::getInstance();
    }

    /**
     * Generate API key
     */
    public function generateAPIKey($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $apiKey = $this->generateSecureKey();
            $keyName = $data->key_name ?? 'API Key';
            $permissions = json_encode($data->permissions ?? []);
            $expiryDate = $data->expiry_date ?? null;

            $keyData = [
                'tenant_id' => $tenantId,
                'key_name' => $keyName,
                'api_key_hash' => hash('sha256', $apiKey),
                'api_key_prefix' => substr($apiKey, 0, 8),
                'permissions' => $permissions,
                'rate_limit_per_minute' => $data->rate_limit_per_minute ?? 60,
                'rate_limit_per_hour' => $data->rate_limit_per_hour ?? 1000,
                'ip_whitelist' => json_encode($data->ip_whitelist ?? []),
                'expiry_date' => $expiryDate,
                'status' => 'ACTIVE',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO api_keys (tenant_id, key_name, api_key_hash, api_key_prefix, permissions, rate_limit_per_minute, rate_limit_per_hour, ip_whitelist, expiry_date, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $keyData['tenant_id'],
                $keyData['key_name'],
                $keyData['api_key_hash'],
                $keyData['api_key_prefix'],
                $keyData['permissions'],
                $keyData['rate_limit_per_minute'],
                $keyData['rate_limit_per_hour'],
                $keyData['ip_whitelist'],
                $keyData['expiry_date'],
                $keyData['status'],
                $keyData['created_by']
            ]);

            $keyId = $this->db->lastInsertId();

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'api_key', $keyId, 'CREATE', json_encode(['key_name' => $keyName]));

            return [
                'success' => true,
                'message' => 'API key generated',
                'key_id' => $keyId,
                'api_key' => $apiKey // Only return the full key on creation
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to generate API key: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate secure key
     */
    private function generateSecureKey()
    {
        return 'ebp_' . bin2hex(random_bytes(32));
    }

    /**
     * Get API keys
     */
    public function getAPIKeys($tenantId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT ak.*, 
                    (SELECT COUNT(*) FROM api_usage_logs aul WHERE aul.api_key_id = ak.id AND DATE(aul.created_at) = CURDATE()) as today_usage,
                    u.username as created_by_name
                FROM api_keys ak
                LEFT JOIN users u ON ak.created_by = u.id
                {$where}
                ORDER BY ak.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Revoke API key
     */
    public function revokeAPIKey($keyId, $tenantId, $userId)
    {
        try {
            $sql = "UPDATE api_keys SET status = 'REVOKED', revoked_by = ?, revoked_at = NOW() WHERE id = ? AND tenant_id = ?";
            $this->db->prepare($sql)->execute([$userId, $keyId, $tenantId]);

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'api_key', $keyId, 'REVOKE', json_encode(['key_id' => $keyId]));

            return [
                'success' => true,
                'message' => 'API key revoked'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to revoke API key: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create webhook
     */
    public function createWebhook($tenantId, $userId, $data)
    {
        try {
            $this->db->beginTransaction();

            $webhookData = [
                'tenant_id' => $tenantId,
                'webhook_name' => $data->webhook_name,
                'webhook_url' => $data->webhook_url,
                'event_type' => $data->event_type,
                'headers' => json_encode($data->headers ?? []),
                'secret' => $this->generateSecureKey(),
                'retry_on_failure' => $data->retry_on_failure ?? true,
                'max_retries' => $data->max_retries ?? 3,
                'status' => 'ACTIVE',
                'created_by' => $userId
            ];

            $sql = "INSERT INTO webhooks (tenant_id, webhook_name, webhook_url, event_type, headers, secret, retry_on_failure, max_retries, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $webhookData['tenant_id'],
                $webhookData['webhook_name'],
                $webhookData['webhook_url'],
                $webhookData['event_type'],
                $webhookData['headers'],
                $webhookData['secret'],
                $webhookData['retry_on_failure'],
                $webhookData['max_retries'],
                $webhookData['status'],
                $webhookData['created_by']
            ]);

            $webhookId = $this->db->lastInsertId();

            $this->db->commit();

            // Log audit
            $this->audit->log($tenantId, null, $userId, 'webhook', $webhookId, 'CREATE', json_encode($webhookData));

            return [
                'success' => true,
                'message' => 'Webhook created',
                'webhook_id' => $webhookId,
                'webhook_secret' => $webhookData['secret']
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create webhook: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get webhooks
     */
    public function getWebhooks($tenantId, $status)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $sql = "SELECT w.*, 
                    (SELECT COUNT(*) FROM webhook_logs wl WHERE wl.webhook_id = w.id AND DATE(wl.created_at) = CURDATE()) as today_deliveries,
                    u.username as created_by_name
                FROM webhooks w
                LEFT JOIN users u ON w.created_by = u.id
                {$where}
                ORDER BY w.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Log API usage
     */
    public function logAPIUsage($tenantId, $keyId, $endpoint, $method, $statusCode, $responseTime)
    {
        try {
            $sql = "INSERT INTO api_usage_logs (tenant_id, api_key_id, endpoint, http_method, status_code, response_time_ms, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->prepare($sql)->execute([
                $tenantId,
                $keyId,
                $endpoint,
                $method,
                $statusCode,
                $responseTime
            ]);

            return ['success' => true];

        } catch (Exception $e) {
            return ['success' => false];
        }
    }

    /**
     * Get API analytics
     */
    public function getAPIAnalytics($tenantId, $dateFrom, $dateTo)
    {
        $params = [$tenantId];
        $where = "WHERE tenant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }

        // Total requests
        $totalRequestsSql = "SELECT COUNT(*) as count FROM api_usage_logs {$where}";
        $totalRequests = $this->db->query($totalRequestsSql, $params)->fetch();

        // Success rate
        $successSql = "SELECT COUNT(*) as count FROM api_usage_logs {$where} AND status_code BETWEEN 200 AND 299";
        $success = $this->db->query($successSql, $params)->fetch();

        // Average response time
        $avgResponseSql = "SELECT AVG(response_time_ms) as avg FROM api_usage_logs {$where}";
        $avgResponse = $this->db->query($avgResponseSql, $params)->fetch();

        // Unique API keys
        $uniqueKeysSql = "SELECT COUNT(DISTINCT api_key_id) as count FROM api_usage_logs {$where}";
        $uniqueKeys = $this->db->query($uniqueKeysSql, $params)->fetch();

        // Top endpoints
        $topEndpointsSql = "SELECT endpoint, COUNT(*) as request_count FROM api_usage_logs {$where} GROUP BY endpoint ORDER BY request_count DESC LIMIT 10";
        $topEndpoints = $this->db->query($topEndpointsSql, $params)->fetchAll();

        $successRate = $totalRequests['count'] > 0 ? ($success['count'] / $totalRequests['count']) * 100 : 0;

        return [
            'total_requests' => $totalRequests['count'] ?? 0,
            'successful_requests' => $success['count'] ?? 0,
            'success_rate' => round($successRate, 2),
            'average_response_time' => round($avgResponse['avg'] ?? 0, 2),
            'unique_api_keys' => $uniqueKeys['count'] ?? 0,
            'top_endpoints' => $topEndpoints
        ];
    }

    /**
     * Get API summary
     */
    public function getSummary($tenantId)
    {
        // Active API keys
        $activeKeysSql = "SELECT COUNT(*) as count FROM api_keys WHERE tenant_id = ? AND status = 'ACTIVE'";
        $activeKeys = $this->db->query($activeKeysSql, [$tenantId])->fetch();

        // Active webhooks
        $activeWebhooksSql = "SELECT COUNT(*) as count FROM webhooks WHERE tenant_id = ? AND status = 'ACTIVE'";
        $activeWebhooks = $this->db->query($activeWebhooksSql, [$tenantId])->fetch();

        // Today's API calls
        $todayCallsSql = "SELECT COUNT(*) as count FROM api_usage_logs WHERE tenant_id = ? AND DATE(created_at) = CURDATE()";
        $todayCalls = $this->db->query($todayCallsSql, [$tenantId])->fetch();

        // Failed webhooks today
        $failedWebhooksSql = "SELECT COUNT(*) as count FROM webhook_logs WHERE tenant_id = ? AND status = 'FAILED' AND DATE(created_at) = CURDATE()";
        $failedWebhooks = $this->db->query($failedWebhooksSql, [$tenantId])->fetch();

        return [
            'active_api_keys' => $activeKeys['count'] ?? 0,
            'active_webhooks' => $activeWebhooks['count'] ?? 0,
            'today_api_calls' => $todayCalls['count'] ?? 0,
            'failed_webhooks_today' => $failedWebhooks['count'] ?? 0
        ];
    }
}
