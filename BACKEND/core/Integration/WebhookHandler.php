<?php

namespace App\Core;


use PDO;
/**
 * Webhook Handler
 * 
 * Handles incoming webhooks from external systems
 * Supports signature validation, payload parsing, and event routing
 * 
 * @package EBP\App\Core\Integration
 * @version 1.0.0
 */

class WebhookHandler
{
    private $db;
    private $connector;
    private $logger;

    public function __construct($db, $connector = null, $logger = null)
    {
        $this->db = $db;
        $this->connector = $connector ?: new IntegrationConnector([]);
        $this->logger = $logger ?: $this->createDefaultLogger();
    }

    /**
     * Create default logger
     */
    private function createDefaultLogger()
    {
        return new class {
            public function log($level, $message, $context = [])
            {
                $timestamp = date('Y-m-d H:i:s');
                $logMessage = "[{$timestamp}] [{$level}] {$message}";
                if (!empty($context)) {
                    $logMessage .= " " . json_encode($context);
                }
                error_log($logMessage);
            }
        };
    }

    /**
     * Handle incoming webhook
     * 
     * @param string $sourceType Source system type
     * @param string $payload Raw webhook payload
     * @param string $signature Webhook signature
     * @param string $secret Webhook secret
     * @return array Handling result
     */
    public function handleWebhook($sourceType, $payload, $signature = null, $secret = null)
    {
        try {
            // Validate signature if provided
            if ($signature && $secret) {
                if (!$this->connector->validateWebhookSignature($payload, $signature, $secret)) {
                    $this->logger->log('error', 'Invalid webhook signature', ['source' => $sourceType]);
                    return [
                        'success' => false,
                        'message' => 'Invalid signature'
                    ];
                }
            }

            // Parse payload
            $data = $this->connector->parseWebhookPayload($payload);

            // Normalize data
            $normalizedData = $this->connector->normalizeData($data, $sourceType);

            // Route to appropriate handler
            $result = $this->routeWebhook($sourceType, $normalizedData);

            // Log webhook receipt
            $this->logWebhook($sourceType, $data, $result);

            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
                'result' => $result
            ];

        } catch (Exception $e) {
            $this->logger->log('error', 'Webhook handling failed: ' . $e->getMessage(), ['source' => $sourceType]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Route webhook to appropriate handler
     * 
     * @param string $sourceType Source system type
     * @param array $data Normalized data
     * @return array Handler result
     */
    private function routeWebhook($sourceType, $data)
    {
        $eventType = $data['event_type'] ?? 'unknown';
        
        $handlerClass = "WebhookHandler_" . ucfirst($sourceType);
        
        if (class_exists($handlerClass)) {
            $handler = new $handlerClass($this->db);
            return $handler->handle($eventType, $data);
        }

        // Default handler
        return $this->defaultHandler($eventType, $data);
    }

    /**
     * Default webhook handler
     * 
     * @param string $eventType Event type
     * @param array $data Event data
     * @return array Handler result
     */
    private function defaultHandler($eventType, $data)
    {
        // Store webhook in database for later processing
        $this->storeWebhook($eventType, $data);
        
        return [
            'status' => 'queued',
            'message' => 'Webhook queued for processing'
        ];
    }

    /**
     * Store webhook in database
     * 
     * @param string $eventType Event type
     * @param array $data Event data
     */
    private function storeWebhook($eventType, $data)
    {
        $sql = "
            INSERT INTO webhook_queue
            (event_type, source_type, payload_json, status, created_at)
            VALUES (?, ?, ?, 'PENDING', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $eventType,
            $data['source_type'] ?? 'unknown',
            json_encode($data)
        ]);
    }

    /**
     * Log webhook
     * 
     * @param string $sourceType Source type
     * @param array $data Webhook data
     * @param array $result Processing result
     */
    private function logWebhook($sourceType, $data, $result)
    {
        $sql = "
            INSERT INTO webhook_logs
            (source_type, event_type, payload_json, result_json, processed_at)
            VALUES (?, ?, ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $sourceType,
            $data['event_type'] ?? 'unknown',
            json_encode($data),
            json_encode($result)
        ]);
    }

    /**
     * Process queued webhooks
     * 
     * @param int $limit Maximum number of webhooks to process
     * @return array Processing results
     */
    public function processQueuedWebhooks($limit = 100)
    {
        $sql = "
            SELECT webhook_id, event_type, source_type, payload_json
            FROM webhook_queue
            WHERE status = 'PENDING'
            ORDER BY created_at ASC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $webhooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($webhooks as $webhook) {
            $data = json_decode($webhook['payload_json'], true);
            
            try {
                $result = $this->routeWebhook($webhook['source_type'], $data);
                
                // Update webhook status
                $this->updateWebhookStatus($webhook['webhook_id'], 'PROCESSED', $result);
                
                $results[] = [
                    'webhook_id' => $webhook['webhook_id'],
                    'success' => true,
                    'result' => $result
                ];
            } catch (Exception $e) {
                $this->updateWebhookStatus($webhook['webhook_id'], 'FAILED', ['error' => $e->getMessage()]);
                
                $results[] = [
                    'webhook_id' => $webhook['webhook_id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'total_processed' => count($results),
            'successful' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => !$r['success'])),
            'results' => $results
        ];
    }

    /**
     * Update webhook status
     * 
     * @param int $webhookId Webhook ID
     * @param string $status New status
     * @param array $result Processing result
     */
    private function updateWebhookStatus($webhookId, $status, $result)
    {
        $sql = "
            UPDATE webhook_queue
            SET status = ?, result_json = ?, processed_at = NOW()
            WHERE webhook_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $status,
            json_encode($result),
            $webhookId
        ]);
    }

    /**
     * Get webhook statistics
     * 
     * @param string $sourceType Optional source type filter
     * @param string $startDate Optional start date
     * @param string $endDate Optional end date
     * @return array Statistics
     */
    public function getWebhookStatistics($sourceType = null, $startDate = null, $endDate = null)
    {
        $whereClause = "WHERE 1=1";
        $params = [];

        if ($sourceType) {
            $whereClause .= " AND source_type = ?";
            $params[] = $sourceType;
        }

        if ($startDate) {
            $whereClause .= " AND created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $whereClause .= " AND created_at <= ?";
            $params[] = $endDate;
        }

        $sql = "
            SELECT 
                COUNT(*) as total_webhooks,
                SUM(CASE WHEN status = 'PROCESSED' THEN 1 ELSE 0 END) as processed,
                SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending
            FROM webhook_logs
            {$whereClause}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
