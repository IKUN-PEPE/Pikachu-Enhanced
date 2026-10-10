<?php

declare(strict_types=1);

namespace Pikachu\Security;

use Pikachu\Core\Config;

/**
 * Enterprise CTF Flag Generator, Validator & Progress Tracker.
 */
class FlagEngine
{
    public function __construct(private Config $config)
    {
    }

    /**
     * Generate a cryptographic dynamic flag for a specific challenge and user.
     */
    public function generateDynamicFlag(string $challengeId, string $userId = 'guest'): string
    {
        $secret = $this->config->getString('app_secret', 'pikachu-default-secret');
        $hash = substr(hash_hmac('sha256', "{$challengeId}:{$userId}", $secret), 0, 16);
        $cleanId = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $challengeId) ?: 'CHALLENGE');

        return "flag{PIKA_{$cleanId}_{$hash}}";
    }

    /**
     * Verify a submitted flag against expected static or dynamic flags.
     */
    public function verify(string $expected, string $submitted): bool
    {
        return hash_equals(trim($expected), trim($submitted));
    }

    /**
     * Record a solved challenge in user session.
     */
    public function markSolved(string $category, string $challengeId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionKey = "{$category}_flags";
        if (!isset($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }

        $_SESSION[$sessionKey][$challengeId] = true;
    }

    /**
     * Check if a challenge is solved in current session.
     */
    public function isSolved(string $category, string $challengeId): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionKey = "{$category}_flags";
        return !empty($_SESSION[$sessionKey][$challengeId]);
    }
}
