<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub) 实战靶场核心支撑库
 * 仿真真实渗透测试资产: app.corp-internal.com / prod.corp-internal.com / sso.corp-internal.com:8092
 */
if (!defined('PIKA_ROOT_DIR')) {
    $PIKA_ROOT_DIR = "../../";
}
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';

// 初始化 CorpHub 靶场 Session 状态
if (!isset($_SESSION['tscs_current_user'])) {
    $_SESSION['tscs_current_user'] = null; // 默认未登录 (visitor)
}
if (!isset($_SESSION['tscs_tokens'])) {
    $_SESSION['tscs_tokens'] = [];
}
if (!isset($_SESSION['tscs_jobs'])) {
    $_SESSION['tscs_jobs'] = [
        [
            'jobId' => 1,
            'jobName' => '系统状态常规自检',
            'jobGroup' => 'DEFAULT',
            'invokeTarget' => 'ryTask.ryNoParams',
            'cronExpression' => '0/10 * * * * ?',
            'misfirePolicy' => '3',
            'concurrent' => '1',
            'status' => '0',
            'createTime' => '2026-10-19 12:00:00'
        ]
    ];
}
if (!isset($_SESSION['tscs_job_logs'])) {
    $_SESSION['tscs_job_logs'] = [
        [
            'jobLogId' => 101,
            'jobName' => '系统状态常规自检',
            'jobGroup' => 'DEFAULT',
            'invokeTarget' => 'ryTask.ryNoParams',
            'jobMessage' => 'ryTask.ryNoParams 总共耗时：15毫秒',
            'status' => '0',
            'exceptionInfo' => '',
            'createTime' => date('Y-m-d H:i:s', time() - 300)
        ]
    ];
}

// 模拟数据库: 用户列表 (与真实渗透测试发现完全吻合)
function tscs_get_users() {
    return [
        'admin' => [
            'userId' => 1,
            'userName' => 'admin',
            'nickName' => '超级管理员 (数智中心)',
            'deptId' => 103,
            'deptName' => '数字智能化运营中心',
            'phonenumber' => '13800138000',
            'idCard' => '11010119850101****',
            'password_bcrypt' => '$2a$10$wK2Vb8K9vP2.Z/3i7X8oU.8qP9Wq2tJ5bY7uE0xR4yT3kL9mQ1aZi', // 强哈希
            'role' => 'admin',
            'roleName' => '超级管理员 (全业务特权)',
            'permissions' => ['*:*:*'],
            'isDefaultPwd' => false,
            'status' => '0',
            'loginIp' => '10.10.20.10',
            'loginDate' => '2026-10-26 10:05:22'
        ],
        'dispatch_mgr' => [
            'userId' => 1002,
            'userName' => 'dispatch_mgr',
            'nickName' => '运营调度主管 (高权)',
            'deptId' => 201,
            'deptName' => '企业核心业务与资源调度部',
            'phonenumber' => '13800138001',
            'idCard' => '11010119880202****',
            'password_bcrypt' => '$2a$10$7Z8K3vM1oP9.Q2xR5tY8u.3wE1qA9sD6fG4hJ2kL0zX8cV5bN3mQ',
            'role' => 'admin_dispatch',
            'roleName' => '运营调度主管 (高阶管理权限)',
            'permissions' => ['system:user:list', 'system:user:query', 'monitor:job:list', 'monitor:job:add', 'dispatch:*:*'],
            'isDefaultPwd' => true, // 挂初始密码
            'status' => '0',
            'loginIp' => '10.10.20.21',
            'loginDate' => '2026-10-26 09:18:40'
        ],
        'ops_lead' => [
            'userId' => 1003,
            'userName' => 'ops_lead',
            'nickName' => '华东大区业务总负责人',
            'deptId' => 205,
            'deptName' => '华东区核心业务交付一队',
            'phonenumber' => '13800138002',
            'idCard' => '11010119860303****',
            'password_bcrypt' => '$2a$10$9X2C4vB6nM8.L0kP3oI5u.1qW2eR3tY4uI5oP6aS7dF8gH9jK0lZ',
            'role' => 'regional_lead',
            'roleName' => '区域业务负责人 (中阶管理权限)',
            'permissions' => ['system:user:listByRole', 'workTicket:*:*', 'dispatch:order:*'],
            'isDefaultPwd' => true, // 挂初始密码
            'status' => '0',
            'loginIp' => '10.10.20.22',
            'loginDate' => '2026-10-20 16:40:11'
        ],
        'test1' => [
            'userId' => 1004,
            'userName' => 'test1',
            'nickName' => '外部协作测试工号 1',
            'deptId' => 208,
            'deptName' => '外协协作第三方',
            'phonenumber' => '13800138003',
            'idCard' => '11010119920404****',
            'password_bcrypt' => '$2a$10$5V1B3nN5mK7.J9hG2fD4s.9zX8cV7bN6mQ5wE4rT3yU2iO1pA0sD',
            'role' => 'outer_tester',
            'roleName' => '外协测试人员 (基础业务权限)',
            'permissions' => ['workTicket:apply', 'file:upload', 'file:view'],
            'isDefaultPwd' => true, // 挂初始密码
            'status' => '0',
            'loginIp' => '10.10.20.35',
            'loginDate' => '2026-10-21 14:37:05'
        ],
        'low_role' => [
            'userId' => 1005,
            'userName' => 'low_role',
            'nickName' => '外部员工临时账号',
            'deptId' => 301,
            'deptName' => '外部协作支持组',
            'phonenumber' => '13800138004',
            'idCard' => '11010119950505****',
            'password_bcrypt' => '$2a$10$4B3N2mK1jH9.G8fD7sA6p.0oI9uY8tR7eW6qQ5zX4cV3bN2mK1jH',
            'role' => 'EXT_STAFF',
            'roleName' => '外部协作员工 (只读基础权限)',
            'permissions' => ['system:config:list', 'system:user:selectUserByUserName', 'basics:applyRole:list', 'workTicket:view'],
            'isDefaultPwd' => false,
            'status' => '0',
            'loginIp' => '10.10.20.35',
            'loginDate' => '2026-10-19 15:32:00'
        ]
    ];
}

// 模拟数据库: 系统全局配置表 (与真实渗透测试导出的全局核心配置一致)
function tscs_get_configs() {
    return [
        [
            'configId' => 1,
            'configName' => '主框架页-系统皮肤风格',
            'configKey' => 'sys.index.skinName',
            'configValue' => 'skin-blue',
            'configType' => 'Y',
            'remark' => '蓝色 skin-blue、绿色 skin-green'
        ],
        [
            'configId' => 2,
            'configName' => '用户管理-账号初始密码',
            'configKey' => 'sys.user.initPassword',
            'configValue' => 'Corp@2026!', // 【核心泄露点 1: 真实统一初始密码】
            'configType' => 'Y',
            'remark' => '新员工或批量导入时分配的默认初始口令'
        ],
        [
            'configId' => 3,
            'configName' => '登录验证码开关',
            'configKey' => 'sys.account.captchaEnabled',
            'configValue' => 'false',
            'configType' => 'Y',
            'remark' => '生产环境关闭了图片验证码'
        ],
        [
            'configId' => 4,
            'configName' => '登录密码传输 RSA 1024位公钥',
            'configKey' => 'sys.login.rsaPublicKey',
            'configValue' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC0wO6vKzBw52L40/r0w0c4eZg3
Kx2Uv1H+3L4N8M7x1P9q2w3e4r5t6y7u8i9o0p1a2s3d4f5g6h7j8k9l0z1x2c3v
4b5n6m7q8w9e0r1t2y3u4i5o6p7a8s9d0f1g2h3j4k5l6z7x8c9v0b1n2m3q4w==',
            'configType' => 'Y',
            'remark' => '前端提交口令加密使用'
        ],
        [
            'configId' => 5,
            'configName' => '登录密码服务端解密 RSA 私钥 (严重配置失误)',
            'configKey' => 'sys.login.rsaPrivateKey',
            'configValue' => 'MIICXAIBAAKBgQC0wO6vKzBw52L40/r0w0c4eZg3Kx2Uv1H+3L4N8M7x1P9q2w3e
4r5t6y7u8i9o0p1a2s3d4f5g6h7j8k9l0z1x2c3v4b5n6m7q8w9e0r1t2y3u4i5o
6p7a8s9d0f1g2h3j4k5l6z7x8c9v0b1n2m3q4wIDAQABAoGAe1f2g3h4j5k6l7z8
x9c0v1b2n3m4q5w6e7r8t9y0u1i2o3p4a5s6d7f8g9h0j1k2l3z4x5c6v7b8n9m0
q1w2e3r4t5y6u7i8o9p0a1s2d3f4g5h6j7k8l9z0x1c2v3b4n5m6q7w8e9r0t1y2
u3i4o5p6a7s8d9f0g1h2j3k4l5z6x7c8v9b0n1m2q3w4e5r6t7y8u9i0o1p2a3s4
d5f6g7h8j9k0l1z2x3c4v5b6n7m8q9w0e1r2t3y4u5i6o7p8a9s0d1f2g3h4j5k6', // 【核心泄露点 2: 完整私钥】
            'configType' => 'Y',
            'remark' => '后端与开发调试共用私钥，误入公共配置表'
        ],
        [
            'configId' => 6,
            'configName' => 'BPM/SSO 单点登录系统代号 (SystemCode)',
            'configKey' => 'sys.bpmsso.systemCode',
            'configValue' => 'corp', // 【核心泄露点 3: 系统代号】
            'configType' => 'Y',
            'remark' => '企业企业级综合运营管理系统标识'
        ],
        [
            'configId' => 7,
            'configName' => 'BPM/SSO 共享通信密钥 (Secret)',
            'configKey' => 'sys.bpmsso.secret',
            'configValue' => 'corp', // 【核心泄露点 4: 4位弱密钥】
            'configType' => 'Y',
            'remark' => 'BPM 与 CorpHub 互通预共享密钥 (固定4位字符串)'
        ],
        [
            'configId' => 8,
            'configName' => 'BPM/SSO 服务端点 URL',
            'configKey' => 'sys.bpmsso.url',
            'configValue' => 'http://sso.corp-internal.com:8092/Portal/WebServices/SSOService?wsdl',
            'configType' => 'Y',
            'remark' => '企业联邦 BPM WebServices 接口'
        ],
        [
            'configId' => 9,
            'configName' => 'MinIO 对象存储外部访问地址',
            'configKey' => 'sys.minio.endpoint',
            'configValue' => 'http://172.16.10.50:9000',
            'configType' => 'Y',
            'remark' => '内网直连桶端点'
        ]
    ];
}

// 模拟 SSO 签发与验证 Token 存储
function tscs_issue_sso_token($userCode, $systemCode, $secret) {
    if ($systemCode !== 'corp' || $secret !== 'corp') {
        return ['success' => false, 'error' => '认证失败：systemCode 或 secret 密钥错误'];
    }
    // 成功签发与实测一致的 32 字符随机 Token
    $token = bin2hex(random_bytes(16));
    if (!isset($_SESSION['tscs_sso_tickets'])) {
        $_SESSION['tscs_sso_tickets'] = [];
    }
    $_SESSION['tscs_sso_tickets'][$token] = [
        'userCode' => $userCode,
        'systemCode' => $systemCode,
        'clientIp' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'createTime' => time(),
        'expireTime' => time() + 300 // 5分钟有效
    ];
    return [
        'success' => true,
        'token' => $token,
        'userCode' => $userCode,
        'targetUrl' => "https://app.corp-internal.com/ssoCallback?token={$token}&userCode={$userCode}"
    ];
}

// 模拟 SSO 消费票据登录
function tscs_login_by_sso_token($token) {
    if (!isset($_SESSION['tscs_sso_tickets'][$token])) {
        return ['success' => false, 'error' => 'SSO 票据无效或已过期 (Ticket not found)'];
    }
    $ticket = $_SESSION['tscs_sso_tickets'][$token];
    if (time() > $ticket['expireTime']) {
        unset($_SESSION['tscs_sso_tickets'][$token]);
        return ['success' => false, 'error' => 'SSO 票据已过期 (Ticket expired)'];
    }
    $targetUserCode = $ticket['userCode'];
    $users = tscs_get_users();
    $userData = $users[$targetUserCode] ?? null;

    if (!$userData) {
        // 如果用户库中不存在，仍能映射为系统临时接管管理员
        $userData = [
            'userId' => 99999,
            'userName' => $targetUserCode,
            'nickName' => "SSO 映射特权用户 ({$targetUserCode})",
            'deptName' => 'SSO 外部联邦认证组',
            'role' => 'admin',
            'roleName' => 'SSO 委托超级管理员',
            'permissions' => ['*:*:*'],
            'isDefaultPwd' => false
        ];
    }

    // 建立会话
    $_SESSION['tscs_current_user'] = $userData;
    // 销毁单次票据
    unset($_SESSION['tscs_sso_tickets'][$token]);
    
    return [
        'success' => true,
        'message' => '🎉 SSO 单点免密登录成功！',
        'user' => $userData,
        'bearer_token' => 'ey' . base64_encode(json_encode(['user' => $userData['userName'], 'ts' => time()]))
    ];
}

// 获取当前登录用户
function tscs_get_current_user() {
    return $_SESSION['tscs_current_user'] ?? null;
}

// 模拟普通账号登录
function tscs_login($username, $password) {
    $users = tscs_get_users();
    
    // 【复现缺陷: 真实系统的登录错误回显差异】
    if (!isset($users[$username])) {
        // 用户名不存在时回显：密码错误
        return ['success' => false, 'code' => 500, 'msg' => '密码错误 (Password incorrect)'];
    }
    
    // 用户名存在时核对密码
    $user = $users[$username];
    $initPwd = 'Corp@2026!'; // 系统统一初始密码
    
    // 判断是否匹配初始密码或固定测试密码
    $is_valid = false;
    if ($password === $initPwd) {
        $is_valid = true;
    } elseif ($username === 'low_role' && $password === 'Low@Pikachu123') {
        $is_valid = true;
    } elseif ($username === 'admin' && $password === 'SuperAdmin@Corp#992') {
        $is_valid = true;
    }
    
    if (!$is_valid) {
        // 【用户名存在但密码错误时回显】：用户不存在/密码错误 (方向与常规若依相反，导致可探测用户名)
        return ['success' => false, 'code' => 500, 'msg' => '用户不存在/密码错误 (User not exist / password error)'];
    }
    
    $_SESSION['tscs_current_user'] = $user;
    return [
        'success' => true,
        'code' => 200,
        'msg' => '登录成功',
        'token' => 'ey' . base64_encode(json_encode(['uid' => $user['userId'], 'sub' => $user['userName'], 'iat' => time()])),
        'user' => $user
    ];
}
