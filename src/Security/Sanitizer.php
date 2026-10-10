<?php

declare(strict_types=1);

namespace Pikachu\Security;

/**
 * Educational Defense Sanitizer showcasing modern remediation patterns.
 */
class Sanitizer
{
    /**
     * Defense against Cross-Site Scripting (XSS).
     */
    public static function escapeHtml(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Defense against Command Injection.
     */
    public static function escapeCommandArg(string $arg): string
    {
        return escapeshellarg($arg);
    }

    /**
     * Defense against Path Traversal.
     */
    public static function sanitizePath(string $filename): string
    {
        $filename = basename($filename);
        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename) ?: 'invalid_file';
    }
}
