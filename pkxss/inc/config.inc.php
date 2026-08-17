<?php
//全局session_start
session_start(); 
//全局居设置时区
date_default_timezone_set('Asia/Shanghai');
//全局设置默认字符
header('Content-type:text/html;charset=utf-8');
//定义数据库连接参数
$db_host = getenv('PIKACHU_DB_HOST') ? getenv('PIKACHU_DB_HOST') : (defined('DBHOST') ? DBHOST : 'db');
$db_port = getenv('PIKACHU_DB_PORT') ? getenv('PIKACHU_DB_PORT') : '3306';
$db_user = getenv('PIKACHU_DB_USER') ? getenv('PIKACHU_DB_USER') : 'root';
$db_pass = getenv('PIKACHU_DB_PASSWORD') ? getenv('PIKACHU_DB_PASSWORD') : 'root';

define('DBHOST', $db_host);
define('DBUSER', $db_user);
define('DBPW', $db_pass);
define('DBNAME', 'pkxss');
define('DBPORT', $db_port);

?>
