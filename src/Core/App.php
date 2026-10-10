<?php

declare(strict_types=1);

namespace Pikachu\Core;

use Pikachu\Autoloader;
use Pikachu\Database\DatabaseManager;
use Pikachu\Security\FlagEngine;
use Pikachu\Challenge\ChallengeRegistry;

/**
 * Application Kernel & Service Container.
 */
class App
{
    private static ?App $instance = null;
    private static bool $booted = false;

    private Config $config;
    private ?DatabaseManager $db = null;
    private ?FlagEngine $flagEngine = null;
    private ?ChallengeRegistry $registry = null;

    private function __construct()
    {
        $this->config = Config::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bootstrap the Pikachu application environment.
     */
    public static function boot(?string $rootDir = null): self
    {
        if (self::$booted && self::$instance !== null) {
            return self::$instance;
        }

        // Register autoloader
        Autoloader::register();

        $root = $rootDir ?? dirname(__DIR__, 2);

        // Load .env if present
        $envFile = $root . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envFile)) {
            Dotenv::load($envFile);
        }

        // Initialize session safely
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.lazy_write', '1');
            session_start();
        }

        date_default_timezone_set('Asia/Shanghai');

        $app = self::getInstance();
        self::$booted = true;

        return $app;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDb(): DatabaseManager
    {
        if ($this->db === null) {
            $this->db = new DatabaseManager($this->config);
        }
        return $this->db;
    }

    public function getFlagEngine(): FlagEngine
    {
        if ($this->flagEngine === null) {
            $this->flagEngine = new FlagEngine($this->config);
        }
        return $this->flagEngine;
    }

    public function getChallengeRegistry(): ChallengeRegistry
    {
        if ($this->registry === null) {
            $this->registry = new ChallengeRegistry();
        }
        return $this->registry;
    }

    public static function isBooted(): bool
    {
        return self::$booted;
    }
}
