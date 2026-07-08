<?php

/**
 * Integration Connector
 * 
 * Base class for external system integrations
 * Provides common functionality for API connections, webhooks, and data normalization
 * 
 * @package EBP\Core\Integration
 * @version 1.0.0
 */

class IntegrationConnector
{
    protected $config;
    protected $httpClient;
    protected $logger;

    public function __construct($config, $httpClient = null, $logger = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?: $this->createDefaultHttpClient();
        $this->logger = $logger ?: $this->createDefaultLogger();
    }

    /**
     * Create default HTTP client
     */
    protected function createDefaultHttpClient()
    {
        return new class {
            public function request($method, $url, $options = [])
            {
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                if (isset($options['headers'])) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
                }

                if (isset($options['body'])) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
                }

                if (isset($options['auth'])) {
                    curl_setopt($ch, CURLOPT_USERPWD, $options['auth']['username'] . ':' . $options['auth']['password']);
                }

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($error) {
                    throw new Exception("HTTP request failed: " . $error);
                }

                return [
                    'status_code' => $httpCode,
                    'body' => $response,
                    'headers' => []
                ];
            }
        };
    }

    /**
     * Create default logger
     */
    protected function createDefaultLogger()
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
     * Make HTTP request with retry logic
     * 
     * @param string $method HTTP method
     * @param string $url URL
     * @param array $options Request options
     * @param int $maxRetries Maximum retry attempts
     * @return array Response data
     */
    protected function requestWithRetry($method, $url, $options = [], $maxRetries = 3)
    {
        $lastError = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $this->logger->log('info', "HTTP Request: {$method} {$url} (Attempt {$attempt}/{$maxRetries})");
                
                $response = $this->httpClient->request($method, $url, $options);
                
                $this->logger->log('info', "HTTP Response: Status {$response['status_code']}");
                
                return $response;
            } catch (Exception $e) {
                $lastError = $e;
                $this->logger->log('warning', "HTTP Request failed: " . $e->getMessage());
                
                if ($attempt < $maxRetries) {
                    $waitTime = pow(2, $attempt) * 1000; // Exponential backoff
                    usleep($waitTime * 1000);
                }
            }
        }
        
        throw new Exception("HTTP request failed after {$maxRetries} attempts: " . $lastError->getMessage());
    }

    /**
     * Normalize data from external system to internal format
     * 
     * @param array $externalData Data from external system
     * @param string $sourceType Type of external system
     * @return array Normalized data
     */
    public function normalizeData($externalData, $sourceType)
    {
        $normalizer = $this->getNormalizer($sourceType);
        
        if ($normalizer) {
            return $normalizer->normalize($externalData);
        }
        
        return $externalData;
    }

    /**
     * Get normalizer for specific source type
     * 
     * @param string $sourceType Source type
     * @return object|null Normalizer instance
     */
    protected function getNormalizer($sourceType)
    {
        $normalizerClass = "DataNormalizer_" . ucfirst($sourceType);
        
        if (class_exists($normalizerClass)) {
            return new $normalizerClass();
        }
        
        return null;
    }

    /**
     * Validate webhook signature
     * 
     * @param string $payload Webhook payload
     * @param string $signature Webhook signature
     * @param string $secret Webhook secret
     * @return bool Valid or not
     */
    public function validateWebhookSignature($payload, $signature, $secret)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse webhook payload
     * 
     * @param string $payload Raw webhook payload
     * @return array Parsed data
     */
    public function parseWebhookPayload($payload)
    {
        $data = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON payload: " . json_last_error_msg());
        }
        
        return $data;
    }

    /**
     * Transform internal data to external system format
     * 
     * @param array $internalData Internal data
     * @param string $targetType Target system type
     * @return array Transformed data
     */
    public function transformData($internalData, $targetType)
    {
        $transformer = $this->getTransformer($targetType);
        
        if ($transformer) {
            return $transformer->transform($internalData);
        }
        
        return $internalData;
    }

    /**
     * Get transformer for specific target type
     * 
     * @param string $targetType Target type
     * @return object|null Transformer instance
     */
    protected function getTransformer($targetType)
    {
        $transformerClass = "DataTransformer_" . ucfirst($targetType);
        
        if (class_exists($transformerClass)) {
            return new $transformerClass();
        }
        
        return null;
    }

    /**
     * Get integration health status
     * 
     * @param string $integrationId Integration ID
     * @return array Health status
     */
    public function getHealthStatus($integrationId)
    {
        $healthCheck = [
            'integration_id' => $integrationId,
            'status' => 'unknown',
            'last_check' => date('Y-m-d H:i:s'),
            'response_time' => null,
            'error_count' => 0,
            'success_count' => 0
        ];

        try {
            $startTime = microtime(true);
            
            // Attempt health check endpoint
            $healthUrl = $this->config['health_url'] ?? null;
            if ($healthUrl) {
                $response = $this->httpClient->request('GET', $healthUrl);
                $healthCheck['status'] = $response['status_code'] === 200 ? 'healthy' : 'unhealthy';
                $healthCheck['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
            } else {
                $healthCheck['status'] = 'no_health_endpoint';
            }
        } catch (Exception $e) {
            $healthCheck['status'] = 'error';
            $healthCheck['error'] = $e->getMessage();
        }

        return $healthCheck;
    }

    /**
     * Get integration metrics
     * 
     * @param string $integrationId Integration ID
     * @param int $days Number of days to analyze
     * @return array Integration metrics
     */
    public function getMetrics($integrationId, $days = 7)
    {
        $metrics = [
            'integration_id' => $integrationId,
            'period_days' => $days,
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'average_response_time' => 0,
            'error_rate' => 0,
            'uptime_percentage' => 0
        ];

        // This would typically query a metrics database
        // For now, return placeholder structure
        return $metrics;
    }

    /**
     * Log integration event
     * 
     * @param string $integrationId Integration ID
     * @param string $eventType Event type
     * @param array $eventData Event data
     */
    public function logEvent($integrationId, $eventType, $eventData = [])
    {
        $logEntry = [
            'integration_id' => $integrationId,
            'event_type' => $eventType,
            'event_data' => $eventData,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->logger->log('info', "Integration Event: {$eventType}", $logEntry);
    }

    /**
     * Get integration logs
     * 
     * @param string $integrationId Integration ID
     * @param array $filters Filter options
     * @return array Integration logs
     */
    public function getLogs($integrationId, $filters = [])
    {
        // This would typically query a logs database
        // For now, return placeholder structure
        return [
            'integration_id' => $integrationId,
            'filters' => $filters,
            'logs' => []
        ];
    }
}
