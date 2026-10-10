<?php

declare(strict_types=1);

namespace Pikachu\Tests\Unit;

use Pikachu\Security\Sanitizer;

class SanitizerTest
{
    public function testEscapeHtml(): bool
    {
        $dirty = '<script>alert(1)</script>';
        $clean = Sanitizer::escapeHtml($dirty);
        assert(!str_contains($clean, '<script>'), 'XSS script tag not escaped');
        assert(str_contains($clean, '&lt;script&gt;'), 'HTML entities not properly generated');
        return true;
    }

    public function testSanitizePath(): bool
    {
        $traversal = '../../../../etc/passwd';
        $clean = Sanitizer::sanitizePath($traversal);
        assert(!str_contains($clean, '..'), 'Directory traversal sequence not stripped');
        assert($clean === 'passwd', 'Basename extraction failed');
        return true;
    }
}
