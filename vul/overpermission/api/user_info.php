<?php
header('Content-Type: application/json');

// 模拟数据库数据
$mock_db = [
    1001 => [
        "uid" => 1001,
        "username" => "lucy",
        "email" => "lucy@pikachu.local",
        "balance" => 50.00,
        "api_key" => "sk-live-lucy-998877"
    ],
    1002 => [
        "uid" => 1002,
        "username" => "lili",
        "email" => "lili@pikachu.local",
        "balance" => 120.50,
        "api_key" => "sk-live-lili-112233"
    ],
    8888 => [
        "uid" => 8888,
        "username" => "admin",
        "email" => "admin@pikachu.local",
        "balance" => 9999999.99,
        "api_key" => "FLAG{B0LA_ID0R_M4ST3R}"
    ]
];

if (isset($_GET['uid'])) {
    $uid = intval($_GET['uid']);
    
    // 【漏洞点】这里只判断了 uid 存不存在，而没有校验当前 Session 是否属于该 uid！
    // 典型的 BOLA (API IDOR) 漏洞
    if (array_key_exists($uid, $mock_db)) {
        echo json_encode([
            "status" => "success",
            "data" => $mock_db[$uid]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "User not found."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Missing uid parameter."
    ]);
}
