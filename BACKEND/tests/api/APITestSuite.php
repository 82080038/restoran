<?php

/**
 * APITestSuite - Comprehensive API Testing Suite
 * 
 * This suite provides automated testing for all RESTAURANT_ERP API endpoints.
 * It includes authentication, request validation, response verification,
 * and reporting capabilities.
 * 
 * @package EBP\Tests\API
 * @version 1.0.0
 */

class APITestSuite
{
    private $baseUrl;
    private $authToken;
    private $testResults = [];
    private $tenantId;
    private $branchId;
    private $verbose = true;

    public function __construct($config = [])
    {
        $this->baseUrl = $config['base_url'] ?? 'http://localhost/restoran/BACKEND/public/api/v1';
        $this->tenantId = $config['tenant_id'] ?? 1;
        $this->branchId = $config['branch_id'] ?? 1;
        $this->verbose = $config['verbose'] ?? true;
    }

    /**
     * Run all tests
     * 
     * @return array Test results
     */
    public function runAllTests()
    {
        $this->log("Starting API Test Suite...");
        $this->log("Base URL: {$this->baseUrl}");
        $this->log("Tenant ID: {$this->tenantId}, Branch ID: {$this->branchId}");
        $this->log(str_repeat('=', 60));

        // Authentication tests
        $this->runAuthenticationTests();

        // Restaurant tests
        $this->runRestaurantTests();

        // Menu tests
        $this->runMenuTests();

        // Order tests
        $this->runOrderTests();

        // Reservation tests
        $this->runReservationTests();

        // Customer tests
        $this->runCustomerTests();

        // Inventory tests
        $this->runInventoryTests();

        // Loyalty tests
        $this->runLoyaltyTests();

        // Employee tests
        $this->runEmployeeTests();

        // Kitchen tests
        $this->runKitchenTests();

        // Report generation
        $this->generateReport();

        return $this->testResults;
    }

    /**
     * Run authentication tests
     */
    private function runAuthenticationTests()
    {
        $this->log("\n--- Authentication Tests ---");

        // Test login
        $this->testLogin();

        // Test register
        $this->testRegister();

        // Test logout
        $this->testLogout();

        // Test token validation
        $this->testTokenValidation();
    }

    /**
     * Test login endpoint
     */
    private function testLogin()
    {
        $testName = 'POST /auth/login';
        $this->log("Testing: {$testName}");

        $data = [
            'email' => 'admin@ebp.com',
            'password' => 'admin123'
        ];

        $response = $this->makeRequest('POST', '/auth/login', $data);
        $this->assertResponse($testName, $response, 200);

        if ($response['status_code'] == 200 && isset($response['data']['token'])) {
            $this->authToken = $response['data']['token'];
            $this->log("✓ Auth token acquired");
        }
    }

    /**
     * Test register endpoint
     */
    private function testRegister()
    {
        $testName = 'POST /auth/register';
        $this->log("Testing: {$testName}");

        $data = [
            'name' => 'Test User',
            'email' => 'testuser' . time() . '@test.com',
            'password' => 'test123',
            'phone' => '1234567890'
        ];

        $response = $this->makeRequest('POST', '/auth/register', $data);
        $this->assertResponse($testName, $response, 201);
    }

    /**
     * Test logout endpoint
     */
    private function testLogout()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping logout test - no auth token");
            return;
        }

        $testName = 'POST /auth/logout';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('POST', '/auth/logout', [], $this->authToken);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test token validation
     */
    private function testTokenValidation()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping token validation - no auth token");
            return;
        }

        $testName = 'GET /auth/validate';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/auth/validate', [], $this->authToken);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run restaurant tests
     */
    private function runRestaurantTests()
    {
        $this->log("\n--- Restaurant Tests ---");

        $this->testGetRestaurants();
        $this->testGetRestaurantById();
        $this->testSearchRestaurants();
    }

    /**
     * Test get restaurants endpoint
     */
    private function testGetRestaurants()
    {
        $testName = 'GET /restaurants';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId];
        $response = $this->makeRequest('GET', '/restaurants', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get restaurant by ID endpoint
     */
    private function testGetRestaurantById()
    {
        $testName = 'GET /restaurants/{id}';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/restaurants/1');
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test search restaurants endpoint
     */
    private function testSearchRestaurants()
    {
        $testName = 'GET /restaurants/search';
        $this->log("Testing: {$testName}");

        $params = ['query' => 'test'];
        $response = $this->makeRequest('GET', '/restaurants/search', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run menu tests
     */
    private function runMenuTests()
    {
        $this->log("\n--- Menu Tests ---");

        $this->testGetMenuItems();
        $this->testGetMenuItemById();
        $this->testGetMenuCategories();
    }

    /**
     * Test get menu items endpoint
     */
    private function testGetMenuItems()
    {
        $testName = 'GET /menu/items';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'branch_id' => $this->branchId];
        $response = $this->makeRequest('GET', '/menu/items', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get menu item by ID endpoint
     */
    private function testGetMenuItemById()
    {
        $testName = 'GET /menu/items/{id}';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/menu/items/1');
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get menu categories endpoint
     */
    private function testGetMenuCategories()
    {
        $testName = 'GET /menu/categories';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId];
        $response = $this->makeRequest('GET', '/menu/categories', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run order tests
     */
    private function runOrderTests()
    {
        $this->log("\n--- Order Tests ---");

        $this->testCreateOrder();
        $this->testGetOrders();
        $this->testGetOrderById();
        $this->testUpdateOrder();
    }

    /**
     * Test create order endpoint
     */
    private function testCreateOrder()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping create order test - no auth token");
            return;
        }

        $testName = 'POST /orders';
        $this->log("Testing: {$testName}");

        $data = [
            'tenant_id' => $this->tenantId,
            'branch_id' => $this->branchId,
            'customer_id' => 1,
            'order_type' => 'DINE_IN',
            'items' => [
                ['menu_item_id' => 1, 'quantity' => 2]
            ]
        ];

        $response = $this->makeRequest('POST', '/orders', $data, $this->authToken);
        $this->assertResponse($testName, $response, 201);
    }

    /**
     * Test get orders endpoint
     */
    private function testGetOrders()
    {
        $testName = 'GET /orders';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'branch_id' => $this->branchId];
        $response = $this->makeRequest('GET', '/orders', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get order by ID endpoint
     */
    private function testGetOrderById()
    {
        $testName = 'GET /orders/{id}';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/orders/1');
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test update order endpoint
     */
    private function testUpdateOrder()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping update order test - no auth token");
            return;
        }

        $testName = 'PUT /orders/{id}';
        $this->log("Testing: {$testName}");

        $data = ['status' => 'PREPARING'];
        $response = $this->makeRequest('PUT', '/orders/1', $data, $this->authToken);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run reservation tests
     */
    private function runReservationTests()
    {
        $this->log("\n--- Reservation Tests ---");

        $this->testCreateReservation();
        $this->testGetReservations();
        $this->testGetReservationById();
        $this->testCheckAvailability();
    }

    /**
     * Test create reservation endpoint
     */
    private function testCreateReservation()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping create reservation test - no auth token");
            return;
        }

        $testName = 'POST /reservations';
        $this->log("Testing: {$testName}");

        $data = [
            'tenant_id' => $this->tenantId,
            'branch_id' => $this->branchId,
            'customer_id' => 1,
            'reservation_date' => date('Y-m-d', strtotime('+1 day')),
            'reservation_time' => '19:00',
            'party_size' => 4
        ];

        $response = $this->makeRequest('POST', '/reservations', $data, $this->authToken);
        $this->assertResponse($testName, $response, 201);
    }

    /**
     * Test get reservations endpoint
     */
    private function testGetReservations()
    {
        $testName = 'GET /reservations';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'branch_id' => $this->branchId];
        $response = $this->makeRequest('GET', '/reservations', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get reservation by ID endpoint
     */
    private function testGetReservationById()
    {
        $testName = 'GET /reservations/{id}';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/reservations/1');
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test check availability endpoint
     */
    private function testCheckAvailability()
    {
        $testName = 'GET /reservations/availability';
        $this->log("Testing: {$testName}");

        $params = [
            'tenant_id' => $this->tenantId,
            'branch_id' => $this->branchId,
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '19:00',
            'party_size' => 4
        ];
        $response = $this->makeRequest('GET', '/reservations/availability', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run customer tests
     */
    private function runCustomerTests()
    {
        $this->log("\n--- Customer Tests ---");

        $this->testGetCustomers();
        $this->testGetCustomerById();
        $this->testUpdateCustomer();
    }

    /**
     * Test get customers endpoint
     */
    private function testGetCustomers()
    {
        $testName = 'GET /customers';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId];
        $response = $this->makeRequest('GET', '/customers', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get customer by ID endpoint
     */
    private function testGetCustomerById()
    {
        $testName = 'GET /customers/{id}';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/customers/1');
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test update customer endpoint
     */
    private function testUpdateCustomer()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping update customer test - no auth token");
            return;
        }

        $testName = 'PUT /customers/{id}';
        $this->log("Testing: {$testName}");

        $data = ['name' => 'Updated Name'];
        $response = $this->makeRequest('PUT', '/customers/1', $data, $this->authToken);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run inventory tests
     */
    private function runInventoryTests()
    {
        $this->log("\n--- Inventory Tests ---");

        $this->testGetInventoryItems();
        $this->testGetInventoryItemById();
        $this->testGetStockBalances();
    }

    /**
     * Test get inventory items endpoint
     */
    private function testGetInventoryItems()
    {
        $testName = 'GET /inventory/items';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId];
        $response = $this->makeRequest('GET', '/inventory/items', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get inventory item by ID endpoint
     */
    private function testGetInventoryItemById()
    {
        $testName = 'GET /inventory/items/{id}';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/inventory/items/1');
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get stock balances endpoint
     */
    private function testGetStockBalances()
    {
        $testName = 'GET /inventory/stock-balances';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'branch_id' => $this->branchId];
        $response = $this->makeRequest('GET', '/inventory/stock-balances', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run loyalty tests
     */
    private function runLoyaltyTests()
    {
        $this->log("\n--- Loyalty Tests ---");

        $this->testGetLoyaltyPoints();
        $this->testGetLoyaltyRewards();
        $this->testRedeemReward();
    }

    /**
     * Test get loyalty points endpoint
     */
    private function testGetLoyaltyPoints()
    {
        $testName = 'GET /loyalty/points';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'customer_id' => 1];
        $response = $this->makeRequest('GET', '/loyalty/points', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get loyalty rewards endpoint
     */
    private function testGetLoyaltyRewards()
    {
        $testName = 'GET /loyalty/rewards';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId];
        $response = $this->makeRequest('GET', '/loyalty/rewards', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test redeem reward endpoint
     */
    private function testRedeemReward()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping redeem reward test - no auth token");
            return;
        }

        $testName = 'POST /loyalty/rewards/{id}/redeem';
        $this->log("Testing: {$testName}");

        $data = ['customer_id' => 1];
        $response = $this->makeRequest('POST', '/loyalty/rewards/1/redeem', $data, $this->authToken);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run employee tests
     */
    private function runEmployeeTests()
    {
        $this->log("\n--- Employee Tests ---");

        $this->testGetEmployees();
        $this->testGetEmployeeById();
        $this->testGetEmployeeSchedule();
    }

    /**
     * Test get employees endpoint
     */
    private function testGetEmployees()
    {
        $testName = 'GET /employees';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'branch_id' => $this->branchId];
        $response = $this->makeRequest('GET', '/employees', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get employee by ID endpoint
     */
    private function testGetEmployeeById()
    {
        $testName = 'GET /employees/{id}';
        $this->log("Testing: {$testName}");

        $response = $this->makeRequest('GET', '/employees/1');
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get employee schedule endpoint
     */
    private function testGetEmployeeSchedule()
    {
        $testName = 'GET /employees/{id}/schedule';
        $this->log("Testing: {$testName}");

        $params = ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+7 days'))];
        $response = $this->makeRequest('GET', '/employees/1/schedule', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Run kitchen tests
     */
    private function runKitchenTests()
    {
        $this->log("\n--- Kitchen Tests ---");

        $this->testGetKitchenOrders();
        $this->testUpdateKitchenOrderStatus();
        $this->testGetKitchenQueue();
    }

    /**
     * Test get kitchen orders endpoint
     */
    private function testGetKitchenOrders()
    {
        $testName = 'GET /kitchen/orders';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'branch_id' => $this->branchId];
        $response = $this->makeRequest('GET', '/kitchen/orders', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test update kitchen order status endpoint
     */
    private function testUpdateKitchenOrderStatus()
    {
        if (!$this->authToken) {
            $this->log("⊘ Skipping update kitchen order status - no auth token");
            return;
        }

        $testName = 'PUT /kitchen/orders/{id}/status';
        $this->log("Testing: {$testName}");

        $data = ['status' => 'COOKING'];
        $response = $this->makeRequest('PUT', '/kitchen/orders/1/status', $data, $this->authToken);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Test get kitchen queue endpoint
     */
    private function testGetKitchenQueue()
    {
        $testName = 'GET /kitchen/queue';
        $this->log("Testing: {$testName}");

        $params = ['tenant_id' => $this->tenantId, 'branch_id' => $this->branchId];
        $response = $this->makeRequest('GET', '/kitchen/queue', $params);
        $this->assertResponse($testName, $response, 200);
    }

    /**
     * Make HTTP request
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param string|null $token Auth token
     * @return array Response
     */
    private function makeRequest($method, $endpoint, $data = [], $token = null)
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($token) {
            $headers[] = "Authorization: Bearer {$token}";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if (!empty($data) && $method === 'GET') {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'status_code' => $statusCode,
            'data' => json_decode($response, true),
            'error' => $error
        ];
    }

    /**
     * Assert response
     * 
     * @param string $testName Test name
     * @param array $response Response
     * @param int $expectedStatusCode Expected status code
     */
    private function assertResponse($testName, $response, $expectedStatusCode)
    {
        $passed = $response['status_code'] == $expectedStatusCode;

        $this->testResults[] = [
            'test' => $testName,
            'expected' => $expectedStatusCode,
            'actual' => $response['status_code'],
            'passed' => $passed,
            'error' => $response['error']
        ];

        if ($passed) {
            $this->log("✓ PASSED: {$testName}");
        } else {
            $this->log("✗ FAILED: {$testName} (Expected: {$expectedStatusCode}, Got: {$response['status_code']})");
            if ($response['error']) {
                $this->log("  Error: {$response['error']}");
            }
        }
    }

    /**
     * Log message
     * 
     * @param string $message Message to log
     */
    private function log($message)
    {
        if ($this->verbose) {
            echo $message . "\n";
        }
    }

    /**
     * Generate test report
     * 
     * @return array Report data
     */
    private function generateReport()
    {
        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, fn($r) => $r['passed']));
        $failedTests = $totalTests - $passedTests;

        $report = [
            'summary' => [
                'total_tests' => $totalTests,
                'passed' => $passedTests,
                'failed' => $failedTests,
                'success_rate' => $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0
            ],
            'tests' => $this->testResults,
            'generated_at' => date('Y-m-d H:i:s')
        ];

        $this->log("\n" . str_repeat('=', 60));
        $this->log("Test Summary:");
        $this->log("Total Tests: {$totalTests}");
        $this->log("Passed: {$passedTests}");
        $this->log("Failed: {$failedTests}");
        $this->log("Success Rate: {$report['summary']['success_rate']}%");
        $this->log(str_repeat('=', 60));

        // Save report to file
        $reportFile = __DIR__ . '/reports/api_test_report_' . date('YmdHis') . '.json';
        $reportDir = dirname($reportFile);
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        $this->log("Report saved to: {$reportFile}");

        return $report;
    }

    /**
     * Run specific test module
     * 
     * @param string $module Module name
     * @return array Test results
     */
    public function runModule($module)
    {
        $method = 'run' . ucfirst($module) . 'Tests';
        
        if (method_exists($this, $method)) {
            $this->$method();
            return $this->testResults;
        }

        throw new Exception("Unknown module: {$module}");
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['argv'][0])) {
    $config = [
        'base_url' => $argv[1] ?? 'http://localhost/restoran/BACKEND/public/api/v1',
        'tenant_id' => $argv[2] ?? 1,
        'branch_id' => $argv[3] ?? 1,
        'verbose' => true
    ];

    $suite = new APITestSuite($config);
    $results = $suite->runAllTests();

    exit($results['summary']['failed'] > 0 ? 1 : 0);
}
