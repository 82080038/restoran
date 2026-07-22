<?php

declare(strict_types=1);

namespace App\Core;

class Router
{

    /** @var array<array{method: string, path: string, handler: callable}> */
    private array $routes = [];
    private array $groupStack = [];

    public function addRoute(string $method, string $path, callable $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->currentPrefix() . $path,
            'handler' => $handler,
            'middleware' => array_merge($this->currentMiddleware(), $middleware)
        ];
    }

    public function group(string $prefix, array $middleware, callable $routes): void
    {
        $this->groupStack[] = ['prefix' => rtrim($prefix, '/'), 'middleware' => $middleware];
        $routes($this);
        array_pop($this->groupStack);
    }

    private function currentPrefix(): string
    {
        return implode('', array_column($this->groupStack, 'prefix'));
    }

    private function currentMiddleware(): array
    {
        return array_merge([], ...array_column($this->groupStack, 'middleware'));
    }

    private function applyMiddleware(array $request, array $middleware): array
    {
        foreach ($middleware as $handler) {
            $request = $handler($request);
        }

        return $request;
    }

    public function add(string $method, string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute($method, $path, $handler, $middleware);
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                // Convert route pattern to regex
                $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route['path']);
                $pattern = str_replace('/', '\/', $pattern);
                
                if (preg_match('/^' . $pattern . '$/', $uri, $matches)) {
                    // Extract path parameters
                    array_shift($matches); // Remove full match
                    
                    // Build request array
                    $rawBody = file_get_contents('php://input');
                    $request = [
                        'method' => $method,
                        'uri' => $uri,
                        'raw_body' => $rawBody,
                        'body' => json_decode($rawBody, true) ?? [],
                        'query' => $_GET,
                        'headers' => function_exists('getallheaders') ? getallheaders() : []
                    ];
                    
                    // Extract parameter names from path
                    preg_match_all('/\{([a-zA-Z_]+)\}/', $route['path'], $paramNames);
                    $paramNames = $paramNames[1] ?? [];
                    
                    // Add parameters to request
                    foreach ($paramNames as $index => $name) {
                        $request[$name] = $matches[$index] ?? null;
                    }
                    
                    $request = $this->applyMiddleware($request, $route['middleware'] ?? []);
                    call_user_func($route['handler'], $request);
                    exit;
                }
            }
        }

        Response::error("Route not found", 404);
    }
}
