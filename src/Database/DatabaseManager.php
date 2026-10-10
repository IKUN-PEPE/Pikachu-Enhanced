<?php

declare(strict_types=1);

namespace Pikachu\Database;

use Pikachu\Core\Config;
use PDO;
use PDOException;
use mysqli;

/**
 * Modern Database Connection Manager with Dual Engine (PDO + mysqli Bridge).
 */
class DatabaseManager
{
    private ?PDO $pdo = null;
    private ?mysqli $mysqli = null;
    private bool $isConnected = false;
    private string $lastError = '';

    public function __construct(private Config $config)
    {
    }

    /**
     * Get or create PDO connection.
     */
    public function getPdo(): ?PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $host = $this->config->getString('db_host', '127.0.0.1');
        $port = $this->config->getInt('db_port', 3306);
        $dbname = $this->config->getString('db_name', 'pikachu');
        $user = $this->config->getString('db_user', 'root');
        $pass = $this->config->getString('db_pass', 'root');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $this->isConnected = true;
            return $this->pdo;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Get or create mysqli connection for legacy Pikachu challenges.
     */
    public function getMysqli(): ?mysqli
    {
        if ($this->mysqli !== null && @mysqli_ping($this->mysqli)) {
            return $this->mysqli;
        }

        $host = $this->config->getString('db_host', '127.0.0.1');
        $port = $this->config->getInt('db_port', 3306);
        $dbname = $this->config->getString('db_name', 'pikachu');
        $user = $this->config->getString('db_user', 'root');
        $pass = $this->config->getString('db_pass', 'root');

        $link = @mysqli_connect($host, $user, $pass, $dbname, $port);
        if ($link && !mysqli_connect_errno()) {
            mysqli_set_charset($link, 'utf8mb4');
            $this->mysqli = $link;
            $this->isConnected = true;
            return $this->mysqli;
        }

        $this->lastError = mysqli_connect_error() ?: 'Unable to connect to MySQL';
        return null;
    }

    /**
     * Perform safe prepared query using PDO.
     *
     * @param array<int|string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        $pdo = $this->getPdo();
        if ($pdo === null) {
            return [];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Check if database connection is alive.
     */
    public function ping(): bool
    {
        return $this->getPdo() !== null || $this->getMysqli() !== null;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
}
