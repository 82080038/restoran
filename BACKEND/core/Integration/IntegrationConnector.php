<?php

namespace App\Core;

/**
 * Integration Connector
 * 
 * Base class for external system integrations
 * Provides common functionality for API connections, webhooks, and data normalization
 * 
 * @package EBP\App\Core\Integration
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

    /**
     * Connect to Square POS
     * 
     * @param array $config Square configuration
     * @return array Connection result
     */
    public function connectSquare($config)
    {
        $this->config = array_merge($this->config, $config);
        
        try {
            // Test connection to Square API
            $response = $this->requestWithRetry('GET', 'https://connect.squareup.com/v2/locations', [
                'headers' => [
                    'Authorization: Bearer ' . $config['access_token'],
                    'Content-Type: application/json'
                ]
            ]);
            
            $this->logEvent('square', 'CONNECTION_SUCCESS', [
                'locations' => json_decode($response['body'], true)
            ]);
            
            return [
                'success' => true,
                'provider' => 'Square',
                'connected' => true,
                'locations' => json_decode($response['body'], true)
            ];
        } catch (Exception $e) {
            $this->logEvent('square', 'CONNECTION_FAILED', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'provider' => 'Square',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Connect to Toast POS
     * 
     * @param array $config Toast configuration
     * @return array Connection result
     */
    public function connectToast($config)
    {
        $this->config = array_merge($this->config, $config);
        
        try {
            // Test connection to Toast API
            $response = $this->requestWithRetry('GET', $config['api_url'] . '/restaurants', [
                'headers' => [
                    'Authorization: Bearer ' . $config['api_key'],
                    'Content-Type: application/json'
                ]
            ]);
            
            $this->logEvent('toast', 'CONNECTION_SUCCESS', [
                'restaurants' => json_decode($response['body'], true)
            ]);
            
            return [
                'success' => true,
                'provider' => 'Toast',
                'connected' => true,
                'restaurants' => json_decode($response['body'], true)
            ];
        } catch (Exception $e) {
            $this->logEvent('toast', 'CONNECTION_FAILED', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'provider' => 'Toast',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Connect to Clover POS
     * 
     * @param array $config Clover configuration
     * @return array Connection result
     */
    public function connectClover($config)
    {
        $this->config = array_merge($this->config, $config);
        
        try {
            // Test connection to Clover API
            $response = $this->requestWithRetry('GET', $config['api_url'] . '/v3/merchants/' . $config['merchant_id'], [
                'headers' => [
                    'Authorization: Bearer ' . $config['api_token'],
                    'Content-Type: application/json'
                ]
            ]);
            
            $this->logEvent('clover', 'CONNECTION_SUCCESS', [
                'merchant' => json_decode($response['body'], true)
            ]);
            
            return [
                'success' => true,
                'provider' => 'Clover',
                'connected' => true,
                'merchant' => json_decode($response['body'], true)
            ];
        } catch (Exception $e) {
            $this->logEvent('clover', 'CONNECTION_FAILED', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'provider' => 'Clover',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Connect to Micros 3700
     * 
     * @param array $config Micros configuration
     * @return array Connection result
     */
    public function connectMicros($config)
    {
        $this->config = array_merge($this->config, $config);
        
        try {
            // Test connection to Micros API (typically SOAP-based)
            $response = $this->requestWithRetry('POST', $config['api_url'], [
                'headers' => [
                    'Content-Type: application/soap+xml',
                    'SOAPAction: ' . $config['soap_action']
                ],
                'body' => $this->buildMicrosAuthEnvelope($config)
            ]);
            
            $this->logEvent('micros', 'CONNECTION_SUCCESS', [
                'response' => substr($response['body'], 0, 500)
            ]);
            
            return [
                'success' => true,
                'provider' => 'Micros 3700',
                'connected' => true
            ];
        } catch (Exception $e) {
            $this->logEvent('micros', 'CONNECTION_FAILED', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'provider' => 'Micros 3700',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Connect to Oracle Simphony
     * 
     * @param array $config Simphony configuration
     * @return array Connection result
     */
    public function connectSimphony($config)
    {
        $this->config = array_merge($this->config, $config);
        
        try {
            // Test connection to Simphony API
            $response = $this->requestWithRetry('GET', $config['api_url'] . '/api/v1/properties', [
                'headers' => [
                    'Authorization: Basic ' . base64_encode($config['username'] . ':' . $config['password']),
                    'Content-Type: application/json'
                ]
            ]);
            
            $this->logEvent('simphony', 'CONNECTION_SUCCESS', [
                'properties' => json_decode($response['body'], true)
            ]);
            
            return [
                'success' => true,
                'provider' => 'Oracle Simphony',
                'connected' => true,
                'properties' => json_decode($response['body'], true)
            ];
        } catch (Exception $e) {
            $this->logEvent('simphony', 'CONNECTION_FAILED', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'provider' => 'Oracle Simphony',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Connect to Lightspeed POS
     * 
     * @param array $config Lightspeed configuration
     * @return array Connection result
     */
    public function connectLightspeed($config)
    {
        $this->config = array_merge($this->config, $config);
        
        try {
            // Test connection to Lightspeed API
            $response = $this->requestWithRetry('GET', $config['api_url'] . '/Account.json', [
                'headers' => [
                    'Authorization: Bearer ' . $config['access_token'],
                    'Content-Type: application/json'
                ]
            ]);
            
            $this->logEvent('lightspeed', 'CONNECTION_SUCCESS', [
                'account' => json_decode($response['body'], true)
            ]);
            
            return [
                'success' => true,
                'provider' => 'Lightspeed',
                'connected' => true,
                'account' => json_decode($response['body'], true)
            ];
        } catch (Exception $e) {
            $this->logEvent('lightspeed', 'CONNECTION_FAILED', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'provider' => 'Lightspeed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Pull orders from POS system
     * 
     * @param string $provider POS provider
     * @param array $filters Order filters
     * @return array Orders data
     */
    public function pullOrders($provider, $filters = [])
    {
        switch ($provider) {
            case 'square':
                return $this->pullSquareOrders($filters);
            case 'toast':
                return $this->pullToastOrders($filters);
            case 'clover':
                return $this->pullCloverOrders($filters);
            case 'micros':
                return $this->pullMicrosOrders($filters);
            case 'simphony':
                return $this->pullSimphonyOrders($filters);
            case 'lightspeed':
                return $this->pullLightspeedOrders($filters);
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported provider'
                ];
        }
    }

    /**
     * Pull orders from Square
     */
    private function pullSquareOrders($filters)
    {
        $locationId = $filters['location_id'] ?? $this->config['location_id'];
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        
        try {
            $response = $this->requestWithRetry('GET', 
                "https://connect.squareup.com/v2/locations/{$locationId}/orders?begin_time={$startDate}T00:00:00Z&end_time={$endDate}T23:59:59Z", 
                [
                    'headers' => [
                        'Authorization: Bearer ' . $this->config['access_token'],
                        'Content-Type: application/json'
                    ]
                ]
            );
            
            $orders = json_decode($response['body'], true);
            
            $this->logEvent('square', 'ORDERS_PULLED', [
                'count' => count($orders['orders'] ?? []),
                'date_range' => "{$startDate} to {$endDate}"
            ]);
            
            return [
                'success' => true,
                'provider' => 'Square',
                'orders' => $orders['orders'] ?? [],
                'pulled_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'provider' => 'Square',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Pull orders from Toast
     */
    private function pullToastOrders($filters)
    {
        $restaurantId = $filters['restaurant_id'] ?? $this->config['restaurant_id'];
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        
        try {
            $response = $this->requestWithRetry('GET', 
                $this->config['api_url'] . "/orders?restaurantId={$restaurantId}&startDate={$startDate}&endDate={$endDate}", 
                [
                    'headers' => [
                        'Authorization: Bearer ' . $this->config['api_key'],
                        'Content-Type: application/json'
                    ]
                ]
            );
            
            $orders = json_decode($response['body'], true);
            
            $this->logEvent('toast', 'ORDERS_PULLED', [
                'count' => count($orders['orders'] ?? []),
                'date_range' => "{$startDate} to {$endDate}"
            ]);
            
            return [
                'success' => true,
                'provider' => 'Toast',
                'orders' => $orders['orders'] ?? [],
                'pulled_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'provider' => 'Toast',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Pull orders from Clover
     */
    private function pullCloverOrders($filters)
    {
        $merchantId = $filters['merchant_id'] ?? $this->config['merchant_id'];
        $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        
        try {
            $response = $this->requestWithRetry('GET', 
                $this->config['api_url'] . "/v3/merchants/{$merchantId}/orders?filter=createdTime>={$startDate}T00:00:00Z&createdTime<={$endDate}T23:59:59Z", 
                [
                    'headers' => [
                        'Authorization: Bearer ' . $this->config['api_token'],
                        'Content-Type: application/json'
                    ]
                ]
            );
            
            $orders = json_decode($response['body'], true);
            
            $this->logEvent('clover', 'ORDERS_PULLED', [
                'count' => count($orders['elements'] ?? []),
                'date_range' => "{$startDate} to {$endDate}"
            ]);
            
            return [
                'success' => true,
                'provider' => 'Clover',
                'orders' => $orders['elements'] ?? [],
                'pulled_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'provider' => 'Clover',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Pull orders from Micros
     */
    private function pullMicrosOrders($filters)
    {
        // Micros 3700 uses SOAP/REST APIs - simplified implementation
        try {
            $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
            $endDate = $filters['end_date'] ?? date('Y-m-d');
            
            $response = $this->requestWithRetry('POST', $this->config['api_url'], [
                'headers' => [
                    'Content-Type: application/soap+xml',
                    'SOAPAction: GetOrders'
                ],
                'body' => $this->buildMicrosOrderEnvelope($startDate, $endDate)
            ]);
            
            $this->logEvent('micros', 'ORDERS_PULLED', [
                'date_range' => "{$startDate} to {$endDate}"
            ]);
            
            return [
                'success' => true,
                'provider' => 'Micros 3700',
                'orders' => $this->parseMicrosResponse($response['body']),
                'pulled_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'provider' => 'Micros 3700',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Pull orders from Simphony
     */
    private function pullSimphonyOrders($filters)
    {
        try {
            $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
            $endDate = $filters['end_date'] ?? date('Y-m-d');
            
            $response = $this->requestWithRetry('GET', 
                $this->config['api_url'] . "/api/v1/orders?startDate={$startDate}&endDate={$endDate}", 
                [
                    'headers' => [
                        'Authorization: Basic ' . base64_encode($this->config['username'] . ':' . $this->config['password']),
                        'Content-Type: application/json'
                    ]
                ]
            );
            
            $orders = json_decode($response['body'], true);
            
            $this->logEvent('simphony', 'ORDERS_PULLED', [
                'count' => count($orders['orders'] ?? []),
                'date_range' => "{$startDate} to {$endDate}"
            ]);
            
            return [
                'success' => true,
                'provider' => 'Oracle Simphony',
                'orders' => $orders['orders'] ?? [],
                'pulled_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'provider' => 'Oracle Simphony',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Pull orders from Lightspeed
     */
    private function pullLightspeedOrders($filters)
    {
        try {
            $startDate = $filters['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
            $endDate = $filters['end_date'] ?? date('Y-m-d');
            
            $response = $this->requestWithRetry('GET', 
                $this->config['api_url'] . "/Order.json?timeStamp=>{$startDate}&timeStamp=<={$endDate}", 
                [
                    'headers' => [
                        'Authorization: Bearer ' . $this->config['access_token'],
                        'Content-Type: application/json'
                    ]
                ]
            );
            
            $orders = json_decode($response['body'], true);
            
            $this->logEvent('lightspeed', 'ORDERS_PULLED', [
                'count' => count($orders['Order'] ?? []),
                'date_range' => "{$startDate} to {$endDate}"
            ]);
            
            return [
                'success' => true,
                'provider' => 'Lightspeed',
                'orders' => $orders['Order'] ?? [],
                'pulled_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'provider' => 'Lightspeed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build Micros authentication envelope
     */
    private function buildMicrosAuthEnvelope($config)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    <soapenv:Header>
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken>
                <wsse:Username>' . $config['username'] . '</wsse:Username>
                <wsse:Password>' . $config['password'] . '</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <Authenticate/>
    </soapenv:Body>
</soapenv:Envelope>';
    }

    /**
     * Build Micros order request envelope
     */
    private function buildMicrosOrderEnvelope($startDate, $endDate)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    <soapenv:Body>
        <GetOrders>
            <StartDate>' . $startDate . '</StartDate>
            <EndDate>' . $endDate . '</EndDate>
        </GetOrders>
    </soapenv:Body>
</soapenv:Envelope>';
    }

    /**
     * Parse Micros SOAP response
     */
    private function parseMicrosResponse($response)
    {
        // Simplified SOAP parsing - in production use proper XML parser
        $orders = [];
        
        // Extract order data from SOAP response
        if (strpos($response, '<Order>') !== false) {
            preg_match_all('/<Order>(.*?)<\/Order>/s', $response, $matches);
            foreach ($matches[1] as $orderXml) {
                $orders[] = [
                    'raw_xml' => $orderXml,
                    'parsed' => false
                ];
            }
        }
        
        return $orders;
    }
}
