<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.lazy_write', '1');
    session_start();
}
date_default_timezone_set('Asia/Shanghai');
header('Content-type:text/html;charset=utf-8');
// Load Modern PSR-4 Core & Application Kernel
require_once __DIR__ . '/bootstrap.php';

