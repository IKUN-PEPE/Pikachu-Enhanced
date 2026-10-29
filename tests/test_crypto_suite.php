<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密 8 大关卡全自动 E2E 验证套件
 */
$BASE_URL = 'http://127.0.0.1/vul/encrypt';
$RSA_PUB_KEY = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDRvA7giwinEkaTYllDYCkzujvi
NH+up0XAKXQot8RixKGpB7nr8AdidEvuo+wVCxZwDK3hlcRGrrqt0Gxqwc11btlM
DSj92Mr3xSaJcshZU8kfj325L8DRh9jpruphHBfh955ihvbednGAvOHOrz3Qy3Cb
ocDbsNeCwNpRxwjIdQIDAQAB
-----END PUBLIC KEY-----";
$SIGN_KEY = 'be56e057f20f883e';

$passed = 0;
$failed = 0;

function post_curl($url, $payload, $is_json = true, $cookie_file = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    if ($is_json) {
        $body = is_array($payload) ? json_encode($payload) : $payload;
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    } else {
        $body = is_array($payload) ? http_build_query($payload) : $payload;
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    if ($cookie_file) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($res, true), 'raw' => $res];
}

function get_curl($url, $cookie_file = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($cookie_file) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

echo "============================================================\n";
echo " Pikachu-Enhanced 前端加解密对抗靶场自动化全量测试\n";
echo "============================================================\n\n";

// --- Level 1: AES 固定 Key ---
echo "[Level 1] 测试 AES 固定 Key 加密 (aes.php)...\n";
$key = '1234567890123456';
$iv  = '1234567890123456';
$raw = json_encode(['username' => 'admin', 'password' => '123456']);
$enc = base64_encode(openssl_encrypt($raw, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv));
$res = post_curl("$BASE_URL/aes.php", ['encryptedData' => $enc], false);

if ($res['data']['success'] ?? false) {
    echo "  [PASS] 认证成功: " . $res['data']['message'] . "\n";
    $passed++;
} else {
    echo "  [FAIL] " . ($res['data']['error'] ?? $res['raw']) . "\n";
    $failed++;
}

// --- Level 2: AES 服务端动态 Key ---
echo "\n[Level 2] 测试 AES 服务端动态 Key (aesserver.php)...\n";
$cookie = tempnam(sys_get_temp_dir(), 'pika_cookie_');
$keyInfo = get_curl("$BASE_URL/server_generate_key.php", $cookie);
if (!empty($keyInfo['aes_key']) && !empty($keyInfo['aes_iv'])) {
    $dynKey = base64_decode($keyInfo['aes_key']);
    $dynIv  = base64_decode($keyInfo['aes_iv']);
    $encDyn = base64_encode(openssl_encrypt($raw, 'AES-128-CBC', $dynKey, OPENSSL_RAW_DATA, $dynIv));
    $resDyn = post_curl("$BASE_URL/aesserver.php", ['encryptedData' => $encDyn], true, $cookie);
    if ($resDyn['data']['success'] ?? false) {
        echo "  [PASS] 动态密钥认证成功: " . $resDyn['data']['message'] . "\n";
        $passed++;
    } else {
        echo "  [FAIL] " . ($resDyn['data']['error'] ?? $resDyn['raw']) . "\n";
        $failed++;
    }
} else {
    echo "  [FAIL] 获取动态密钥失败\n";
    $failed++;
}
@unlink($cookie);

// --- Level 3: RSA 非对称加密 ---
echo "\n[Level 3] 测试 RSA 非对称加密 (rsa.php)...\n";
$rsaEncrypted = '';
openssl_public_encrypt($raw, $rsaEncrypted, $RSA_PUB_KEY, OPENSSL_PKCS1_PADDING);
$rsaB64 = base64_encode($rsaEncrypted);
$resRsa = post_curl("$BASE_URL/rsa.php", ['data' => $rsaB64], false);
if ($resRsa['data']['success'] ?? false) {
    echo "  [PASS] RSA 公钥加密认证成功: " . $resRsa['data']['message'] . "\n";
    $passed++;
} else {
    echo "  [FAIL] " . ($resRsa['data']['error'] ?? $resRsa['raw']) . "\n";
    $failed++;
}

// --- Level 4: AES + RSA 混合加密 (数字信封) ---
echo "\n[Level 4] 测试 AES + RSA 混合数字信封 (aesrsa.php)...\n";
$rndAesKey = random_bytes(16);
$rndAesIv  = random_bytes(16);
$rndKeyB64 = base64_encode($rndAesKey);
$rndIvB64  = base64_encode($rndAesIv);

$encBody = base64_encode(openssl_encrypt($raw, 'AES-128-CBC', $rndAesKey, OPENSSL_RAW_DATA, $rndAesIv));
openssl_public_encrypt($rndKeyB64, $encKeyRsa, $RSA_PUB_KEY, OPENSSL_PKCS1_PADDING);
openssl_public_encrypt($rndIvB64,  $encIvRsa,  $RSA_PUB_KEY, OPENSSL_PKCS1_PADDING);

$resHybrid = post_curl("$BASE_URL/aesrsa.php", [
    'encryptedData' => $encBody,
    'encryptedKey'  => base64_encode($encKeyRsa),
    'encryptedIv'   => base64_encode($encIvRsa)
], true);

if ($resHybrid['data']['success'] ?? false) {
    echo "  [PASS] 混合数字信封双层解密成功: " . $resHybrid['data']['message'] . "\n";
    $passed++;
} else {
    echo "  [FAIL] " . ($resHybrid['data']['error'] ?? $resHybrid['raw']) . "\n";
    $failed++;
}

// --- Level 5: DES 规律 Key ---
echo "\n[Level 5] 测试 DES 规律 Key (des.php)...\n";
$username = 'admin';
$password = '123456';
$desKey = str_pad(substr($username, 0, 8), 8, '6');
$desIv  = '9999' . str_pad(substr($username, 0, 4), 4, '9');
$encDes = bin2hex(openssl_encrypt($password, 'DES-CBC', $desKey, OPENSSL_RAW_DATA, $desIv));

$resDes = post_curl("$BASE_URL/des.php", [
    'username' => $username,
    'password' => $encDes
], true);

if ($resDes['data']['success'] ?? false) {
    echo "  [PASS] DES 规律逆向认证成功: " . $resDes['data']['message'] . "\n";
    $passed++;
} else {
    echo "  [FAIL] " . ($resDes['data']['error'] ?? $resDes['raw']) . "\n";
    $failed++;
}

// --- Level 6: 明文数据加签防篡改 ---
echo "\n[Level 6] 测试明文加签防篡改 (signdata.php)...\n";
$nonce = bin2hex(random_bytes(4));
$timestamp = time();
$signData = $username . $password . $nonce . $timestamp;
$sig = hash_hmac('sha256', $signData, $SIGN_KEY);

$resSign = post_curl("$BASE_URL/signdata.php", [
    'username' => $username,
    'password' => $password,
    'nonce'    => $nonce,
    'timestamp'=> $timestamp,
    'signature'=> $sig
], true);

if ($resSign['data']['success'] ?? false) {
    echo "  [PASS] 合法签名请求认证成功!\n";
    $passed++;
} else {
    echo "  [FAIL] " . ($resSign['data']['error'] ?? $resSign['raw']) . "\n";
    $failed++;
}

// 测试篡改拦截
$resTamper = post_curl("$BASE_URL/signdata.php", [
    'username' => $username,
    'password' => 'tampered_password',
    'nonce'    => $nonce,
    'timestamp'=> $timestamp,
    'signature'=> $sig
], true);

if (!($resTamper['data']['success'] ?? true)) {
    echo "  [PASS] 篡改密码后验签阻断成功: " . $resTamper['data']['error'] . "\n";
    $passed++;
} else {
    echo "  [FAIL] 篡改密码未能成功阻断!\n";
    $failed++;
}

// --- Level 7: 服务端动态加签 ---
echo "\n[Level 7] 测试服务端动态加签 (signdataserver.php)...\n";
$ts7 = time();
$sigApiRes = post_curl("$BASE_URL/get-signature.php", [
    'username' => $username,
    'password' => $password,
    'timestamp'=> $ts7
], true);

if (!empty($sigApiRes['data']['signature'])) {
    $dynSig = $sigApiRes['data']['signature'];
    $resServerSig = post_curl("$BASE_URL/signdataserver.php", [
        'username' => $username,
        'password' => $password,
        'timestamp'=> $ts7,
        'signature'=> $dynSig
    ], true);
    if ($resServerSig['data']['success'] ?? false) {
        echo "  [PASS] 服务端动态加签认证成功: " . $resServerSig['data']['message'] . "\n";
        $passed++;
    } else {
        echo "  [FAIL] " . ($resServerSig['data']['error'] ?? $resServerSig['raw']) . "\n";
        $failed++;
    }
} else {
    echo "  [FAIL] 获取服务端动态签名失败\n";
    $failed++;
}

// --- Level 8: 防止重放攻击 ---
echo "\n[Level 8] 测试防止重放测试 (norepeater.php)...\n";
$ts8 = strval(round(microtime(true) * 1000));
openssl_public_encrypt($ts8, $encTs, $RSA_PUB_KEY, OPENSSL_PKCS1_PADDING);
$encTsB64 = base64_encode($encTs);

$replayPayload = [
    'username' => $username,
    'password' => $password,
    'random'   => $encTsB64
];

// 第一次提交 (正常)
$resReplay1 = post_curl("$BASE_URL/norepeater.php", $replayPayload, true);
if ($resReplay1['data']['success'] ?? false) {
    echo "  [PASS] 首次请求防重放校验通过: " . $resReplay1['data']['message'] . "\n";
    $passed++;
} else {
    echo "  [FAIL] 首次请求校验失败: " . ($resReplay1['data']['error'] ?? $resReplay1['raw']) . "\n";
    $failed++;
}

// 第二次提交 (立即重放相同数据包)
$resReplay2 = post_curl("$BASE_URL/norepeater.php", $replayPayload, true);
if (!($resReplay2['data']['success'] ?? true)) {
    echo "  [PASS] 立即重放相同数据包被成功拦截: " . $resReplay2['data']['error'] . "\n";
    $passed++;
} else {
    echo "  [FAIL] 重放攻击未被拦截!\n";
    $failed++;
}

echo "\n============================================================\n";
echo " 测试完成: $passed 项通过, $failed 项失败\n";
echo "============================================================\n";
exit($failed > 0 ? 1 : 0);
