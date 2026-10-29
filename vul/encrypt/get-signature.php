<?php
/**
 * Pikachu-Enhanced v2.0 - 服务端动态加签接口 (HMAC-SHA256)
 */
require_once __DIR__ . '/encrypt_helper.inc.php';
header('Content-Type: application/json; charset=utf-8');

$secretKey = ENCRYPT_SIGN_SECRET_KEY;

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['username'], $data['password'], $data['timestamp'])) {
    $username = $data['username'];
    $password = $data['password'];
    $timestamp = intval($data['timestamp']);

    // 检查时间戳有效性 (5分钟窗口)
    $now = time();
    if (abs($now - $timestamp) > 300) {
        echo json_encode(['success' => false, 'error' => '请求时间戳已过期 (Timestamp expired)'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 计算 HMAC-SHA256 签名: username + password + timestamp
    $dataToSign = $username . $password . $timestamp;
    $signature = hash_hmac('sha256', $dataToSign, $secretKey);

    echo json_encode([
        'success' => true,
        'signature' => $signature,
        'timestamp' => $timestamp
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'error' => '缺少必需参数 (Missing parameters)'], JSON_UNESCAPED_UNICODE);
}
