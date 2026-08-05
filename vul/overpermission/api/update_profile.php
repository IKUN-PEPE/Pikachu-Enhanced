<?php
header('Content-Type: application/json');

// 获取原始 JSON 负载
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (!$input) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

// 模拟数据库里当前用户的信息
$currentUser = [
    "uid" => 1001,
    "email" => "lucy@pikachu.local",
    "phone" => "13800138000",
    "role" => "user"
];

// 【漏洞点】Mass Assignment 批量赋值漏洞！
// 后端没有使用白名单 (如只允许 email 和 phone) 去接收前端的输入
// 而是将前端传入的整个 JSON 对象的所有 Key-Value 直接覆盖合并到用户对象中！
foreach ($input as $key => $value) {
    // 危险：如果前端传入了 "role": "admin"，这里就会把数据库里的 role 字段覆盖为 admin！
    $currentUser[$key] = $value;
}

// 保存入库...（模拟）
// ...

echo json_encode([
    "status" => "success",
    "message" => "Profile updated",
    "user" => [
        "email" => $currentUser['email'],
        "phone" => $currentUser['phone'],
        "role"  => $currentUser['role']
    ]
]);
