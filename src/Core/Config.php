<?php

declare(strict_types=1);

namespace Pikachu\Core;

/**
 * Modern Type-Safe Configuration Repository.
 */
class Config
{
    private static ?Config $instance = null;

    /**
     * @var array<string, mixed>
     */
    private array $settings = [];

    private function __construct()
    {
        $this->loadDefaults();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize defaults from environment variables and fallback constants.
     */
    private function loadDefaults(): void
    {
        // Database settings
        $this->settings['db_host'] = getenv('PIKACHU_DB_HOST') ?: (defined('DBHOST') ? DBHOST : '127.0.0.1');
        $this->settings['db_port'] = (int) (getenv('PIKACHU_DB_PORT') ?: (defined('DBPORT') ? DBPORT : 3306));
        $this->settings['db_name'] = getenv('PIKACHU_DB_NAME') ?: (defined('DBNAME') ? DBNAME : 'pikachu');
        $this->settings['db_user'] = getenv('PIKACHU_DB_USER') ?: (defined('DBUSER') ? DBUSER : 'root');
        $this->settings['db_pass'] = getenv('PIKACHU_DB_PASSWORD') ?: (defined('DBPW') ? DBPW : 'root');

        // Application settings
        $this->settings['app_name'] = getenv('APP_NAME') ?: 'Pikachu-Enhanced';
        $this->settings['app_env'] = getenv('APP_ENV') ?: 'production';
        $this->settings['app_debug'] = filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);
        $this->settings['app_secret'] = getenv('APP_SECRET') ?: 'pikachu-super-secret-salt-key-2026';
        $this->settings['session_lazy_write'] = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function getString(string $key, string $default = ''): string
    {
        return (string) ($this->settings[$key] ?? $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) ($this->settings[$key] ?? $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        return (bool) ($this->settings[$key] ?? $default);
    }

    public function set(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->settings;
    }
}
