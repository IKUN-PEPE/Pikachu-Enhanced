<?php

declare(strict_types=1);

namespace Pikachu\Tests\Integration;

class SidebarRouteTest
{
    public function testAllSidebarTargetsExist(): bool
    {
        $rootDir = dirname(__DIR__, 2);
        $headerPath = $rootDir . '/header.php';
        assert(file_exists($headerPath), 'header.php must exist');

        $content = (string) file_get_contents($headerPath);

        // Find all href="<?php echo $PIKA_ROOT_DIR;?>..."
        preg_match_all('/href="<\?php echo \$PIKA_ROOT_DIR;\?>(.*?)"/', $content, $matches);

        $urls = array_unique($matches[1]);
        $tested = 0;
        $missing = [];

        foreach ($urls as $relPath) {
            // strip query string or hash
            $clean = explode('?', $relPath)[0];
            $clean = explode('#', $clean)[0];
            $clean = trim($clean);

            if (empty($clean) || $clean === '#') {
                continue;
            }

            $fullPath = $rootDir . '/' . $clean;
            if (!file_exists($fullPath)) {
                $missing[] = $clean;
            }
            $tested++;
        }

        assert($tested >= 200, "Should test at least 200 routes, tested: {$tested}");
        assert(empty($missing), 'Missing routes detected: ' . implode(', ', $missing));

        return true;
    }
}
