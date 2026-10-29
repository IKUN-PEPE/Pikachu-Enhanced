<?php
/**
 * Pikachu-Enhanced v2.0 - 服务端动态生成 AES Key/IV 接口
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// 每次生成或按会话生成 16 字节 (128位) AES 密钥与 IV
$_SESSION['aes_key'] = base64_encode(random_bytes(16));
$_SESSION['aes_iv']  = base64_encode(random_bytes(16));

echo json_encode([
    'success' => true,
    'aes_key' => $_SESSION['aes_key'],
    'aes_iv'  => $_SESSION['aes_iv'],
    'algo'    => 'AES-128-CBC',
    'timestamp' => time()
], JSON_UNESCAPED_UNICODE);
