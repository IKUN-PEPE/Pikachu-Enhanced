<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 5 - DES 规律 Key 加密
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/encrypt_helper.inc.php';

// 处理 POST 解密请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    if (isset($data['username'], $data['password'])) {
        $username = $data['username'];
        $encryptedHex = $data['password'];

        // 依据客户端规则动态还原 Key 与 IV:
        // Key: 用户名前8位，不足8位补'6'
        // IV : '9999' + 用户名前4位，不足4位补'9'
        $key = str_pad(substr($username, 0, 8), 8, '6');
        $iv  = '9999' . str_pad(substr($username, 0, 4), 4, '9');

        $ciphertext = @hex2bin($encryptedHex);
        if ($ciphertext === false) {
            echo json_encode(['success' => false, 'error' => '密文 Hex 解码失败'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $decryptedPassword = openssl_decrypt($ciphertext, 'DES-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($decryptedPassword === false || $decryptedPassword === null) {
            echo json_encode(['success' => false, 'error' => 'DES 密码解密失败，派生 Key/IV 不匹配'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $authRes = pika_encrypt_verify_user($username, $decryptedPassword);
        if ($authRes['success']) {
            echo json_encode([
                'success' => true,
                'message' => '🎉 DES 规律逆向认证成功 (DES Login Succeeded)',
                'username' => $authRes['username'],
                'decrypted_debug' => [
                    'derived_key' => $key,
                    'derived_iv'  => $iv,
                    'decrypted_password' => $decryptedPassword
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $authRes['error'],
                'decrypted_debug' => [
                    'derived_key' => $key,
                    'derived_iv'  => $iv
                ]
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => '缺少 username 或 password 参数'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "des.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[346] = 'active';
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
    color: #c2410c;
    border-bottom-color: #c2410c;
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
                <li class="active">Level 5 - DES 规律 Key 加密</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#ea580c; font-size:12px; padding:4px 10px;">Level 5</span>
                    <span class="badge" style="background:#b45309; font-size:12px; padding:4px 10px;">DES-CBC / Hex</span>
                    <span class="badge" style="background:#4338ca; font-size:12px; padding:4px 10px;">Algorithmic Key Derivation</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    DES 规律派生 Key 与 IV 加密 (Predictable Key Derivation)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    在某些系统中，开发者为了避免硬编码固定密钥被检测扫描，设计了一套“看似动态”的派生算法：密钥并非从服务端拉取，而是根据提交的账号参数按特定规律推算（如字符截断、固定数字填充）。本关考验对前端算法细节的逆向审计与推导还原能力。
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-calculator" style="color:#c2410c;"></i> DES 规律派生加密挑战
                        </h4>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleDesSubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;" oninput="updateDerivedKeys()">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#c2410c; border-color:#c2410c;">
                                <i class="ph-bold ph-lightning"></i> 动态推导 Key/IV -> DES 加密密码 -> 提交
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> DES 规律推导管道实时监视器
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-key"></i> 1. 依据当前账号实时推导的 DES Key 与 IV</div>
                        <div class="inspector-panel" id="inspDerived" style="margin-bottom:12px;">Key: admin666 | IV: 9999admi</div>

                        <div class="inspector-title"><i class="ph-bold ph-file-text"></i> 2. 原始密码明文</div>
                        <div class="inspector-panel" id="inspPlain" style="margin-bottom:12px;">123456</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 3. 抓包载荷 (JSON with Hex-encoded Password)</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-cpu"></i> 4. 服务端逆推解密验证</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与对抗指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> 算法逆向解析</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-terminal"></i> Python 复现脚本</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-wrench"></i> Burp 插件支持</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全防御建议</button>
                        </div>

                        <!-- Tab 1: 算法逆向解析 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 关键派生公式审查与逆向定位</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                在前端 JS 提交函数中，可以清晰审计到如下算法实现：
                            </p>
                            <pre style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; font-size:12.5px; color:#0f172a; line-height:1.6;"><code>// Key 计算：取用户名前 8 位，不足 8 位向右填充字符 '6' 补齐
const key = CryptoJS.enc.Utf8.parse(username.slice(0, 8).padEnd(8, '6'));

// IV 计算：固定前缀 '9999' + 取用户名前 4 位，不足 4 位向右填充 '9' 补齐
const iv  = CryptoJS.enc.Utf8.parse('9999' + username.slice(0, 4).padEnd(4, '9'));

// 密文输出：采用 Hex 十六进制字符串而非常见 Base64
const encryptedHex = CryptoJS.DES.encrypt(password, key, {iv: iv}).ciphertext.toString(CryptoJS.enc.Hex);</code></pre>
                            <p style="font-size:13px; color:#64748b; line-height:1.6; margin-top:10px;">
                                <strong>弱点分析：</strong>只要掌握了该简单数学公式，攻击者即可为任何待爆破的目标账号（如 <code>admin</code>, <code>test</code>, <code>guest</code>）瞬时生成专属 Key/IV。
                            </p>
                        </div>

                        <!-- Tab 2: Python 脚本 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 Python 动态规律加密爆破</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import binascii
import requests
from Crypto.Cipher import DES
from Crypto.Util.Padding import pad

TARGET_URL = "http://127.0.0.1:8765/vul/encrypt/des.php"

def derive_des_params(username):
    # Key 规则: 用户名前8位, 补齐8位'6'
    key_str = username[:8].ljust(8, '6')
    # IV 规则: '9999' + 用户名前4位, 补齐4位'9'
    iv_str  = '9999' + username[:4].ljust(4, '9')
    return key_str.encode('utf-8'), iv_str.encode('utf-8')

def des_encrypt_password(username, password):
    key, iv = derive_des_params(username)
    cipher = DES.new(key, DES.MODE_CBC, iv)
    padded = pad(password.encode('utf-8'), DES.block_size)
    encrypted_bytes = cipher.encrypt(padded)
    return binascii.hexlify(encrypted_bytes).decode('utf-8')

# 爆破测试
username = "admin"
passwords = ["123", "admin888", "123456", "password"]
for pwd in passwords:
    hex_pwd = des_encrypt_password(username, pwd)
    res = requests.post(TARGET_URL, json={"username": username, "password": hex_pwd})
    data = res.json()
    if data.get("success"):
        print(f"[+] 成功破解: 用户名 {username} 密码: {pwd}")
        break
    else:
        print(f"[-] 密码 [{pwd}] 失败: {data.get('error')}")
</code></pre>
                        </div>

                        <!-- Tab 3: Burp 插件支持 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛠️ Burp Suite 规律派生参数处理</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                普通静态加解密插件无法感知“随着不同 Payload (username) 动态调整 Key”。针对此类场景，通常有如下两种最佳实践：
                            </p>
                            <ul style="font-size:13px; color:#334155; line-height:1.7;">
                                <li><strong>Burp Extender (Custom Python/Java Extension):</strong> 在 <code>IHttpListener</code> 中提取请求中的 <code>username</code>，实时执行 <code>derive_key(username)</code> 并加密替换。</li>
                                <li><strong>Intruder Custom Payload Processing:</strong> 若固定单一用户名爆破其密码（如固定爆破 <code>admin</code>），则该用户的 Key/IV 实际上恒定为 <code>admin666</code> 与 <code>9999admi</code>，可直接作为固定 DES 规则载入 AutoDecoder！</li>
                            </ul>
                        </div>

                        <!-- Tab 4: 安全防御建议 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 密码学与安全开发教训</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>切勿“自创规律算法”：</strong>柯克霍夫原则 (Kerckhoffs's principle) 指出：系统的安全性不能建立在算法保密上，而必须建立在密钥保密上。规律派生算法一旦被逆向，等同于无加密。<br>
                                2. <strong>淘汰老旧 DES 算法：</strong>DES 密钥长度仅 56 位，早已在现代硬件下被证实存在被暴力穷举碰撞的严重脆弱性，应彻底迁移至 AES-GCM 或非对称 RSA/ECC。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/crypto-js.min.js"></script>

<script>
function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function updateDerivedKeys() {
    const username = document.getElementById('username').value.trim() || 'admin';
    const keyStr = username.slice(0, 8).padEnd(8, '6');
    const ivStr  = '9999' + username.slice(0, 4).padEnd(4, '9');
    document.getElementById('inspDerived').textContent = `Key: ${keyStr} | IV: ${ivStr}`;
}

function handleDesSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> DES 运算中...';

    // 1. 依据规则动态派生 Key 与 IV
    const keyStr = username.slice(0, 8).padEnd(8, '6');
    const ivStr  = '9999' + username.slice(0, 4).padEnd(4, '9');

    const key = CryptoJS.enc.Utf8.parse(keyStr);
    const iv  = CryptoJS.enc.Utf8.parse(ivStr);

    document.getElementById('inspPlain').textContent = password;
    document.getElementById('inspDerived').textContent = `Key: ${keyStr} | IV: ${ivStr}`;

    // 2. DES-CBC 加密并将密文输出为 Hex
    const encryptedPassword = CryptoJS.DES.encrypt(password, key, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    });
    const encryptedHex = encryptedPassword.ciphertext.toString(CryptoJS.enc.Hex);

    const wirePayload = {
        username: username,
        password: encryptedHex
    };

    document.getElementById('inspCipher').textContent = JSON.stringify(wirePayload, null, 2);

    // 3. 提交请求
    fetch('des.php', {
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
            alertBox.innerHTML = '<i class="ph-bold ph-check-circle"></i> <strong>' + data.message + '</strong> [' + data.username + '] 登录成功！';
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
        submitBtn.innerHTML = '<i class="ph-bold ph-lightning"></i> 动态推导 Key/IV -> DES 加密密码 -> 提交';
    });
}

// 初始化监视器显示
updateDerivedKeys();
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
