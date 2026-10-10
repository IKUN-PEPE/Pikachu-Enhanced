<?php

declare(strict_types=1);

namespace Pikachu\Http;

/**
 * Modern HTTP Request Encapsulation.
 */
class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     */
    public function __construct(
        private array $query = [],
        private array $post = [],
        private array $server = [],
        private array $cookies = []
    ) {
    }

    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_COOKIE);
    }

    public function getMethod(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isAjax(): bool
    {
        $requestedWith = $this->server['HTTP_X_REQUESTED_WITH'] ?? '';
        return strtolower((string) $requestedWith) === 'xmlhttprequest';
    }

    public function getPath(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $pos = strpos($uri, '?');
        return $pos !== false ? substr($uri, 0, $pos) : $uri;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? ($this->query[$key] ?? $default);
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->query[$key]);
    }

    public function getClientIp(): string
    {
        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string) $this->server['HTTP_X_FORWARDED_FOR']);
            return trim($parts[0]);
        }
        return (string) ($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
    }
}
