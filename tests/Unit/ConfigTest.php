<?php

declare(strict_types=1);

namespace Pikachu\Tests\Unit;

use Pikachu\Core\Config;

class ConfigTest
{
    public function testConfigDefaults(): bool
    {
        $config = Config::getInstance();
        assert($config->getString('app_name') === 'Pikachu-Enhanced', 'App name default mismatch');
        assert(is_int($config->getInt('db_port')), 'DB port should be integer');
        assert(is_bool($config->getBool('app_debug')), 'App debug should be boolean');
        return true;
    }

    public function testSetAndGet(): bool
    {
        $config = Config::getInstance();
        $config->set('custom_key', 'test_val');
        assert($config->get('custom_key') === 'test_val', 'Custom key get failed');
        assert($config->getString('custom_key') === 'test_val', 'Custom key string get failed');
        assert($config->get('non_existent', 'default') === 'default', 'Fallback default failed');
        return true;
    }
}
