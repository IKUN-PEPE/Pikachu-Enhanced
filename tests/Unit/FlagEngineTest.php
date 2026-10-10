<?php

declare(strict_types=1);

namespace Pikachu\Tests\Unit;

use Pikachu\Core\Config;
use Pikachu\Security\FlagEngine;

class FlagEngineTest
{
    public function testDynamicFlagGeneration(): bool
    {
        $config = Config::getInstance();
        $engine = new FlagEngine($config);

        $flag1 = $engine->generateDynamicFlag('sqli_test', 'user_1');
        $flag2 = $engine->generateDynamicFlag('sqli_test', 'user_1');
        $flag3 = $engine->generateDynamicFlag('sqli_test', 'user_2');

        assert(str_starts_with($flag1, 'flag{PIKA_SQLI_TEST_'), 'Flag prefix mismatch');
        assert($flag1 === $flag2, 'Same user & challenge must yield deterministic flag');
        assert($flag1 !== $flag3, 'Different users must yield different flags');
        return true;
    }

    public function testVerifyFlag(): bool
    {
        $config = Config::getInstance();
        $engine = new FlagEngine($config);

        $expected = 'flag{OSEP_L1_Test_Done}';
        assert($engine->verify($expected, 'flag{OSEP_L1_Test_Done}'), 'Exact flag match failed');
        assert($engine->verify($expected, ' flag{OSEP_L1_Test_Done} '), 'Trimmed flag match failed');
        assert(!$engine->verify($expected, 'wrong_flag'), 'Wrong flag should fail');
        return true;
    }
}
