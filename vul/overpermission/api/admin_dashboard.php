<?php
header('Content-Type: application/json');
include_once 'jwt_helper.php';

$authHeader = '';
if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}
if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}

if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    echo json_encode(["status" => "error", "message" => "Missing or invalid Authorization header."]);
    exit;
}

$jwt = $matches[1];
$payload = JWTHelper::decode($jwt);

if ($payload === false) {
    echo json_encode(["status" => "error", "message" => "Invalid JWT Signature."]);
    exit;
}

// 提取 Payload 中的角色
$role = isset($payload['role']) ? $payload['role'] : 'user';

if ($role === 'admin') {
    echo json_encode([
        "status" => "success", 
        "message" => "鉴权通过。后端已确认您的 admin 角色！",
        "flag" => "FLAG{JWT_F0RG3RY_M4ST3R}"
    ]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "权限不足。当前角色为: {$role}。访问此 API 需要 admin 角色。"
    ]);
}
