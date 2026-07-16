<?php

use PHPUnit\Framework\TestCase;

class HttpSmokeTest extends TestCase
{
    private static string $baseUrl;

    public static function setUpBeforeClass(): void
    {
        $url = $_ENV['API_BASE_URL'] ?? getenv('API_BASE_URL') ?: 'http://localhost:8080';
        self::$baseUrl = rtrim($url, '/');
    }

    private function request(string $method, string $path, array $body = null): array
    {
        $url = self::$baseUrl . $path;
        $options = [
            'http' => [
                'method' => $method,
                'header' => 'Content-Type: application/json',
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ];

        if ($body !== null) {
            $options['http']['content'] = json_encode($body);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RuntimeException("Failed to connect to {$url}");
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Invalid JSON response from {$url}: {$response}");
        }

        return $decoded;
    }

    public function testPublicMenuCategories(): void
    {
        $response = $this->request('GET', '/api/v1/public/menu/categories');

        $this->assertTrue($response['success'], 'Public categories endpoint should succeed');
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data'], 'Categories list should not be empty');
    }

    public function testPublicMenuProducts(): void
    {
        $response = $this->request('GET', '/api/v1/public/menu/products');

        $this->assertTrue($response['success'], 'Public products endpoint should succeed');
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data'], 'Products list should not be empty');
    }

    public function testLoginReturnsToken(): void
    {
        $response = $this->request('POST', '/api/v1/auth/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $this->assertTrue($response['success'], 'Login should succeed with valid credentials');
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('access_token', $response['data'], 'Response should contain access_token');
        $this->assertNotEmpty($response['data']['access_token'], 'access_token should not be empty');
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        $response = $this->request('POST', '/api/v1/auth/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);

        $this->assertFalse($response['success'], 'Login should fail with invalid credentials');
    }
}
