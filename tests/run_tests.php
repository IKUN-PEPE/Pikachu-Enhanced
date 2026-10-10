<?php

declare(strict_types=1);

/**
 * Zero-Dependency Standalone Test Runner for Pikachu-Enhanced.
 * Can be executed inside Docker (`php tests/run_tests.php`) or on any PHP CLI.
 */

require_once dirname(__DIR__) . '/inc/bootstrap.php';

$testClasses = [
    \Pikachu\Tests\Unit\ConfigTest::class,
    \Pikachu\Tests\Unit\FlagEngineTest::class,
    \Pikachu\Tests\Unit\ChallengeRegistryTest::class,
    \Pikachu\Tests\Unit\RouterTest::class,
    \Pikachu\Tests\Unit\SanitizerTest::class,
    \Pikachu\Tests\Integration\SidebarRouteTest::class,
];

echo "============================================================\n";
echo " Pikachu-Enhanced Automated Test Suite (PHP)\n";
echo "============================================================\n\n";

$passed = 0;
$failed = 0;

foreach ($testClasses as $className) {
    echo "[RUN] {$className}...\n";
    $instance = new $className();
    $methods = get_class_methods($instance);

    foreach ($methods as $method) {
        if (!str_starts_with($method, 'test')) {
            continue;
        }

        try {
            $result = $instance->$method();
            echo "  ✓ {$method} [PASS]\n";
            $passed++;
        } catch (\Throwable $e) {
            echo "  ✗ {$method} [FAIL]: " . $e->getMessage() . "\n";
            $failed++;
        }
    }
    echo "\n";
}

echo "============================================================\n";
echo " Results: {$passed} Passed, {$failed} Failed\n";
echo "============================================================\n";

exit($failed > 0 ? 1 : 0);
