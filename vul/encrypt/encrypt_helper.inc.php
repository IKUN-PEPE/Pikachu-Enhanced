<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗通用支撑库
 * 提供 RSA 密钥对、HMAC 签名密钥、数据库连接及凭据校验统一逻辑
 */
if (!defined('PIKA_ROOT_DIR')) {
    $PIKA_ROOT_DIR = "../../";
}
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';

// RSA 1024-bit 密钥对（与 encrypt-labs 公私钥标准一致，完美兼容 JSEncrypt）
define('ENCRYPT_RSA_PUBLIC_KEY', "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDRvA7giwinEkaTYllDYCkzujvi
NH+up0XAKXQot8RixKGpB7nr8AdidEvuo+wVCxZwDK3hlcRGrrqt0Gxqwc11btlM
DSj92Mr3xSaJcshZU8kfj325L8DRh9jpruphHBfh955ihvbednGAvOHOrz3Qy3Cb
ocDbsNeCwNpRxwjIdQIDAQAB
-----END PUBLIC KEY-----");

define('ENCRYPT_RSA_PRIVATE_KEY', "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDRvA7giwinEkaTYllDYCkzujviNH+up0XAKXQot8RixKGpB7nr
8AdidEvuo+wVCxZwDK3hlcRGrrqt0Gxqwc11btlMDSj92Mr3xSaJcshZU8kfj325
L8DRh9jpruphHBfh955ihvbednGAvOHOrz3Qy3CbocDbsNeCwNpRxwjIdQIDAQAB
AoGAMek68RylFn025mQFMg90PqcXESHFMN8FrlEvH3F7/rUkc4EvMYKRf1CFsWi5
Cdj1ofyidIibiOaT7kEnS9CK//SmY+1628/eyngOvOR9ADsHN/JRlJ3dHathcBrr
1GENlCB9EmN+Fzhh7vEC2RUPrkkHCYGU2j+9rkzHUCXxLpECQQD5jgm9K7bvsOzM
82v6avdNFAV/9ILdple1xlCfcEuWgnRztxTS6fbVguDCkB95yQq/WT2XzuohUMSG
0uGGemlbAkEA1ya+aG8bRNlEC4yGiROSWZOiFBtiUhMyDGQ4E/FUifNdZSft5jSE
gqUZZYJNchyKSXWtFKyclvJjcnflKxBubwJAT7eexs4bDvA+hK3RtVnMC9Q0eY5a
64ECja9++598leSwXHKEdWeFkOjQ8XXmiBm/lCZmtYLEacYKMWNV5YZe9wJAMYM/
CnWXRu7hE+9Q/ra8VVT+VbY/mDfGqsddiGlfVSfmdGMOAo5PeGlaQNwNypb61BD6
telLWAmMDUm+OXzcjQJBAJGn+vI0JV7OI0m4QpSucn/rJ9pAYJG4HE/MOQcgHog0
AeussmDIlr+wqWr+iJxYfJHc8ikTRSeTgqavruZs2Hg=
-----END RSA PRIVATE KEY-----");

// 签名预共享密钥
define('ENCRYPT_SIGN_SECRET_KEY', "be56e057f20f883e");

/**
 * 校验用户凭据（利用 Pikachu 现有 users 表与 md5 密码校验）
 */
function pika_encrypt_verify_user($username, $password) {
    $link = connect();
    $sql = "SELECT id, username, password FROM users WHERE username = ? LIMIT 1";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'error' => 'Database error: ' . $link->error];
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if ($user['password'] === md5($password)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return ['success' => true, 'username' => $user['username']];
        } else {
            return ['success' => false, 'error' => '密码错误 (Invalid password)'];
        }
    } else {
        return ['success' => false, 'error' => '用户不存在 (User not found)'];
    }
}
