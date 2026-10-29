<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 4 - AES + RSA 混合加密 (数字信封)
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/encrypt_helper.inc.php';

// 处理 POST 解密请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (isset($data['encryptedData'], $data['encryptedKey'], $data['encryptedIv'])) {
        $encryptedData = $data['encryptedData'];
        $encryptedKey  = $data['encryptedKey'];
        $encryptedIv   = $data['encryptedIv'];

        // 1. 利用服务器私钥解密出客户端生成的临时对称密钥与 IV
        $aesKeyB64 = '';
        $aesIvB64  = '';
        $kOk = openssl_private_decrypt(base64_decode($encryptedKey), $aesKeyB64, ENCRYPT_RSA_PRIVATE_KEY);
        $iOk = openssl_private_decrypt(base64_decode($encryptedIv),  $aesIvB64,  ENCRYPT_RSA_PRIVATE_KEY);

        if (!$kOk || !$iOk || empty($aesKeyB64) || empty($aesIvB64)) {
            echo json_encode(['success' => false, 'error' => 'RSA 信封私钥解密失败，对称密钥损坏'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 2. 将 Base64 对称密钥还原为 16 字节原始数据，解密业务主体密文
        $aesKeyRaw = base64_decode($aesKeyB64);
        $aesIvRaw  = base64_decode($aesIvB64);
        $decryptedData = openssl_decrypt(
            base64_decode($encryptedData),
            'AES-128-CBC',
            $aesKeyRaw,
            OPENSSL_RAW_DATA,
            $aesIvRaw
        );

        if ($decryptedData === false) {
            echo json_encode(['success' => false, 'error' => 'AES 业务载荷解密失败'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $formData = json_decode($decryptedData, true);
        if (isset($formData['username'], $formData['password'])) {
            $authRes = pika_encrypt_verify_user($formData['username'], $formData['password']);
            if ($authRes['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => '🎉 混合双层解密认证成功 (Hybrid Envelope Login Succeeded)',
                    'username' => $authRes['username'],
                    'decrypted_debug' => [
                        'recovered_aes_key' => $aesKeyB64,
                        'recovered_aes_iv'  => $aesIvB64,
                        'payload' => $formData
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $authRes['error'],
                    'decrypted_debug' => $formData
                ], JSON_UNESCAPED_UNICODE);
            }
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => '解密数据结构异常，缺少账号密码字段'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => '缺少 encryptedData, encryptedKey 或 encryptedIv 字段'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "aesrsa.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[345] = 'active';
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.crypto-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.inspector-panel {
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    font-family: monospace;
    font-size: 13px;
    line-height: 1.6;
    overflow-x: auto;
}
.inspector-title {
    color: #94a3b8;
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.nav-tabs-custom {
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 20px;
    display: flex;
    gap: 8px;
}
.nav-tabs-custom button {
    background: none;
    border: none;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}
.nav-tabs-custom button.active {
    color: #4f46e5;
    border-bottom-color: #4f46e5;
}
.tab-pane-custom {
    display: none;
}
.tab-pane-custom.active {
    display: block;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="<?php echo $PIKA_ROOT_DIR;?>vul/encrypt/encrypt.php">前端加解密对抗</a></li>
                <li class="active">Level 4 - AES + RSA 混合加密 (数字信封)</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#4f46e5; font-size:12px; padding:4px 10px;">Level 4</span>
                    <span class="badge" style="background:#0284c7; font-size:12px; padding:4px 10px;">Digital Envelope</span>
                    <span class="badge" style="background:#7c3aed; font-size:12px; padding:4px 10px;">AES-128 + RSA-1024</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    AES + RSA 混合双层加密 (Digital Envelope Architecture)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    这是现代银行、证券交易等高端金融级应用的标准加密模型：客户端每次发起请求时，均在内存中动态生成全新的 16 字节随机 AES Key 与 IV，使用 AES 加密庞大的业务数据（利用对称加密的高性能）；随后使用服务端的 RSA 公钥将该 AES Key 与 IV 加密成“数字信封”。服务端先用私钥拆开信封取出 AES 密钥，再解密业务数据。
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-shield-star" style="color:#4f46e5;"></i> 混合数字信封认证挑战
                        </h4>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleHybridSubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#4f46e5; border-color:#4f46e5;">
                                <i class="ph-bold ph-lock-simple-open"></i> 生成随机对称密钥 -> RSA 封装信封 -> 提交
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> 数字信封多维数据监视器
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-shuffle"></i> 1. 本次请求动态生成的临时对称密钥</div>
                        <div class="inspector-panel" id="inspRandomKey" style="margin-bottom:12px;">// 提交时客户端自动生成 16 字节随机密钥...</div>

                        <div class="inspector-title"><i class="ph-bold ph-file-text"></i> 2. 原始明文负载</div>
                        <div class="inspector-panel" id="inspPlain" style="margin-bottom:12px;">{"username":"admin","password":"..."}</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 3. 最终抓包 JSON (encryptedData + encryptedKey + encryptedIv)</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-cpu"></i> 4. 服务端双阶解密 Echo</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与对抗指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> 信封架构剖析</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-code"></i> Python 信封流水线</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-browser"></i> JSRpc 终极武器</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全审计要点</button>
                        </div>

                        <!-- Tab 1: 信封架构剖析 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 混合加密为什么是金融系统的首选？</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:20px;">
                                <li><strong>兼顾速度与安全性：</strong>RSA 运算极慢且对明文长度有严格上限（1024位 RSA 单次最多只能加密 117 字节），无法加密大型 JSON 数据。因此用 AES 保证高效与无限长度，用 RSA 保护仅 16 字节的 AES 密钥。</li>
                                <li><strong>一包一密 (PFS 雏形)：</strong>由于每次请求的 AES Key 均由客户端随机生成，两次发送相同账号密码的密文包截然不同，防御了密文碰撞嗅探。</li>
                                <li><strong>对抗的核心切入点：</strong>由于 RSA 公钥是公开的，攻击者编写脚本时<strong>也可以每次自建随机 AES 密钥并用公钥打包</strong>，服务器私钥解密完全合法！</li>
                            </ol>
                        </div>

                        <!-- Tab 2: Python 信封流水线 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 Python 数字信封打包与爆破脚本</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import os
import json
import base64
import requests
from Crypto.Cipher import AES, PKCS1_v1_5
from Crypto.PublicKey import RSA
from Crypto.Util.Padding import pad

TARGET_URL = "http://127.0.0.1:8765/vul/encrypt/aesrsa.php"

RSA_PUBLIC_PEM = """-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDRvA7giwinEkaTYllDYCkzujvi
NH+up0XAKXQot8RixKGpB7nr8AdidEvuo+wVCxZwDK3hlcRGrrqt0Gxqwc11btlM
DSj92Mr3xSaJcshZU8kfj325L8DRh9jpruphHBfh955ihvbednGAvOHOrz3Qy3Cb
ocDbsNeCwNpRxwjIdQIDAQAB
-----END PUBLIC KEY-----"""

rsa_key = RSA.import_key(RSA_PUBLIC_PEM)
rsa_cipher = PKCS1_v1_5.new(rsa_key)

def build_envelope_payload(username, password):
    # 1. 生成 16 字节随机 AES Key 与 IV
    aes_key_bytes = os.urandom(16)
    aes_iv_bytes  = os.urandom(16)
    aes_key_b64   = base64.b64encode(aes_key_bytes).decode('utf-8')
    aes_iv_b64    = base64.b64encode(aes_iv_bytes).decode('utf-8')

    # 2. AES 加密业务数据
    payload_str = json.dumps({"username": username, "password": password})
    aes_cipher = AES.new(aes_key_bytes, AES.MODE_CBC, aes_iv_bytes)
    enc_data = base64.b64encode(aes_cipher.encrypt(pad(payload_str.encode('utf-8'), AES.block_size))).decode('utf-8')

    # 3. RSA 公钥加密 AES Key 与 IV
    enc_key = base64.b64encode(rsa_cipher.encrypt(aes_key_b64.encode('utf-8'))).decode('utf-8')
    enc_iv  = base64.b64encode(rsa_cipher.encrypt(aes_iv_b64.encode('utf-8'))).decode('utf-8')

    return {
        "encryptedData": enc_data,
        "encryptedKey": enc_key,
        "encryptedIv": enc_iv
    }

# 提交测试
res = requests.post(TARGET_URL, json=build_envelope_payload("admin", "123456"))
print("[*] 服务端响应:", res.json())
</code></pre>
                        </div>

                        <!-- Tab 3: JSRpc 终极武器 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🌐 为什么混合加密下强烈建议 JSRpc？</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                在实际渗透中，很多系统的 AES 填充或 Key 生成并不遵循标准 PKCS7，可能带有各种自定义移位和混淆。此时用 Python 逐个对齐参数极其耗时。<br>
                                借助 <strong>JSRpc (如 Sekiro 或自建 WebSocket 转发器)</strong>，直接在浏览器挂载当前页面的 <code>sendDataAesRsa</code> 函数，Burp 发出明文，浏览器自动执行完所有复杂的 AES+RSA 计算并回传密文包，实现“降维打击”。
                            </p>
                        </div>

                        <!-- Tab 4: 安全审计建议 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 安全评估与审计要点</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>不可忽视签名与防重放：</strong>混合加密仅保证了机密性 (Confidentiality)，并没有提供完整性 (Integrity) 与新鲜度 (Freshness)。攻击者依然可以重放整个合法的数据包。<br>
                                2. <strong>服务端防暴力破解：</strong>即使前端混合加密再严密，若后端未做验证码或速率封禁，攻击者依然可以利用公钥批量打包发起暴力破解。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/crypto-js.min.js"></script>
<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/jsencrypt.min.js"></script>

<script>
const RSA_PUB_KEY = `<?php echo trim(ENCRYPT_RSA_PUBLIC_KEY); ?>`;

function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function handleHybridSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 正在生成信封并加密...';

    // 1. 构建明文 JSON
    const payloadObj = { username: username, password: password };
    const jsonStr = JSON.stringify(payloadObj);
    document.getElementById('inspPlain').textContent = jsonStr;

    // 2. 动态生成 16 字节随机 AES 密钥与 IV
    const key = CryptoJS.lib.WordArray.random(16);
    const iv  = CryptoJS.lib.WordArray.random(16);

    const keyB64 = key.toString(CryptoJS.enc.Base64);
    const ivB64  = iv.toString(CryptoJS.enc.Base64);

    document.getElementById('inspRandomKey').textContent = `AES Key (Base64): ${keyB64}\nAES IV  (Base64): ${ivB64}`;

    // 3. 用生成的随机 AES 密钥加密业务数据
    const encryptedData = CryptoJS.AES.encrypt(jsonStr, key, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    }).toString();

    // 4. 用服务端 RSA 公钥加密 AES 密钥与 IV
    const rsa = new JSEncrypt();
    rsa.setPublicKey(RSA_PUB_KEY);
    const encryptedKey = rsa.encrypt(keyB64);
    const encryptedIv  = rsa.encrypt(ivB64);

    const wirePayload = {
        encryptedData: encryptedData,
        encryptedKey:  encryptedKey,
        encryptedIv:   encryptedIv
    };

    document.getElementById('inspCipher').textContent = JSON.stringify(wirePayload, null, 2);

    // 5. 提交混合加密请求
    fetch('aesrsa.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8'
        },
        body: JSON.stringify(wirePayload)
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('inspResponse').textContent = JSON.stringify(data, null, 2);
        alertBox.style.display = 'block';
        if (data.success) {
            alertBox.className = 'alert alert-success';
            alertBox.innerHTML = '<i class="ph-bold ph-check-circle"></i> <strong>' + data.message + '</strong> [' + data.username + '] 验证通过！';
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="ph-bold ph-x-circle"></i> <strong>验证失败:</strong> ' + (data.error || '用户名或密码错误');
        }
    })
    .catch(err => {
        document.getElementById('inspResponse').textContent = 'Request Error: ' + err.message;
        alertBox.style.display = 'block';
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 请求出现异常: ' + err.message;
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-lock-simple-open"></i> 生成随机对称密钥 -> RSA 封装信封 -> 提交';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
