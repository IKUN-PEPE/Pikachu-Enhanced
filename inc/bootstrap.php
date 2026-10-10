<?php

declare(strict_types=1);

/**
 * Pikachu-Enhanced Unified Bootstrap Loader.
 * Bridges modern PSR-4 Core services with all legacy challenge scripts.
 */

// 1. Register PSR-4 Autoloader
require_once dirname(__DIR__) . '/src/Autoloader.php';
\Pikachu\Autoloader::register();

// 2. Boot Application Kernel
$app = \Pikachu\Core\App::boot(dirname(__DIR__));

// 3. Define Backward Compatibility Constants if not already defined
if (!defined('DBHOST')) {
    define('DBHOST', $app->getConfig()->getString('db_host', '127.0.0.1'));
}
if (!defined('DBUSER')) {
    define('DBUSER', $app->getConfig()->getString('db_user', 'root'));
}
if (!defined('DBPW')) {
    define('DBPW', $app->getConfig()->getString('db_pass', 'root'));
}
if (!defined('DBNAME')) {
    define('DBNAME', $app->getConfig()->getString('db_name', 'pikachu'));
}
if (!defined('DBPORT')) {
    define('DBPORT', $app->getConfig()->getInt('db_port', 3306));
}

/**
 * Global Helper to get App instance.
 */
function pikachu(): \Pikachu\Core\App {
    return \Pikachu\Core\App::getInstance();
}

/**
 * Global Helper to get Config.
 */
function pika_config(string $key, mixed $default = null): mixed {
    return \Pikachu\Core\App::getInstance()->getConfig()->get($key, $default);
}
