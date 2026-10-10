<?php

declare(strict_types=1);

namespace Pikachu\Http;

/**
 * Lightweight Route Dispatcher for Modern Pikachu API & Challenge Endpoints.
 */
class Router
{
    /**
     * @var array<string, array<string, callable>>
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        // Exact match
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            $result = $handler($request);
            return $result instanceof Response ? $result : Response::html((string) $result);
        }

        // Return 404 response
        return Response::json([
            'status' => 'error',
            'code' => 404,
            'message' => "Route not found: [{$method}] {$path}",
        ], 404);
    }
}
