<?php

declare(strict_types=1);

namespace Pikachu;

/**
 * PSR-4 Compliant Autoloader for Pikachu-Enhanced.
 * Enables zero-dependency standalone operation while remaining 100% compatible with Composer.
 */
class Autoloader
{
    /**
     * Namespace prefix to base directory mappings.
     *
     * @var array<string, string>
     */
    private static array $prefixes = [];

    /**
     * Whether the autoloader has been registered.
     */
    private static bool $registered = false;

    /**
     * Register the PSR-4 autoloader with SPL.
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::addNamespace('Pikachu\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
        self::addNamespace('Pikachu\\Tests\\', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR);

        spl_autoload_register([self::class, 'loadClass']);
        self::$registered = true;
    }

    /**
     * Add a base directory for a namespace prefix.
     */
    public static function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        self::$prefixes[$prefix] = $baseDir;
    }

    /**
     * Loads the class file for a given class name.
     */
    public static function loadClass(string $class): bool
    {
        foreach (self::$prefixes as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        return false;
    }
}
