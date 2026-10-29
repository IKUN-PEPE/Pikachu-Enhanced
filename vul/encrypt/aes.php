<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 1 - AES 固定 Key 加密
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/encrypt_helper.inc.php';

// 处理 POST 解密请求 (支持 API 及 AJAX 调用)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $encryptedData = $_POST['encryptedData'] ?? null;
    if (!$encryptedData) {
        $rawJson = json_decode(file_get_contents('php://input'), true);
        $encryptedData = $rawJson['encryptedData'] ?? null;
    }

    if ($encryptedData) {
        $key = "1234567890123456";
        $iv  = "1234567890123456";
        $ciphertext = base64_decode($encryptedData);
        $decrypted = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            echo json_encode(['success' => false, 'error' => 'AES 密文解密失败 (Decryption failed)'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $formData = json_decode($decrypted, true);
        if (isset($formData['username'], $formData['password'])) {
            $authRes = pika_encrypt_verify_user($formData['username'], $formData['password']);
            if ($authRes['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => '🎉 身份认证成功 (Login Success)',
                    'username' => $authRes['username'],
                    'decrypted_debug' => $formData
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
            echo json_encode(['success' => false, 'error' => '解密数据格式错误，缺少 username 或 password'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => '缺少 encryptedData 参数'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "aes.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[342] = 'active';
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
                <li class="active">Level 1 - AES 固定 Key 加密</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#4f46e5; font-size:12px; padding:4px 10px;">Level 1</span>
                    <span class="badge" style="background:#0284c7; font-size:12px; padding:4px 10px;">AES-128-CBC</span>
                    <span class="badge" style="background:#16a34a; font-size:12px; padding:4px 10px;">Pkcs7 Padding</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    AES 固定 Key 与 IV 对称加密 (Static Key AES)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    此场景代表最常见的前端应用层防抓包手段：客户端在发送用户名、密码等敏感数据前，直接在前端 JS 脚本中调用 <code>CryptoJS.AES.encrypt</code> 使用固定硬编码密钥进行加密。攻击者在抓包时仅能观察到 Base64 编码的密文字符串。
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-sign-in" style="color:#4f46e5;"></i> 业务身份登录挑战 (Client Interface)
                        </h4>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleCryptoSubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;" placeholder="请输入测试用户名 (如 admin)">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;" placeholder="请输入测试密码 (如 123456)">
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#4f46e5; border-color:#4f46e5;">
                                <i class="ph-bold ph-paper-plane-tilt"></i> 客户端加密并提交认证
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> 前端加密流水线实时监视器 (Live Packet Inspector)
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-file-text"></i> 1. 待加密原始 JSON 负载 (Plaintext)</div>
                        <div class="inspector-panel" id="inspPlain" style="margin-bottom:12px;">// 提交表单后此处实时更新...</div>

                        <div class="inspector-title"><i class="ph-bold ph-key"></i> 2. 客户端提取的 Key & IV 参数</div>
                        <div class="inspector-panel" id="inspParams" style="margin-bottom:12px;">Key: 1234567890123456 | IV: 1234567890123456</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 3. 最终抓包密文 (Wire Payload sent via POST)</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-cpu"></i> 4. 服务端解密结果与响应 (Server Debug Echo)</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与逆向破解指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> 逆向断点分析</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-wrench"></i> Burp AutoDecoder</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-terminal"></i> Python 爆破脚本</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全防御建议</button>
                        </div>

                        <!-- Tab 1: 逆向分析 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 Chrome DevTools 逆向断点与定位技巧</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:20px;">
                                <li><strong>搜索加密关键词：</strong>按 <kbd>Ctrl+Shift+F</kbd> 全局搜索 <code>CryptoJS.AES</code>、<code>mode.CBC</code> 或 <code>encryptedData</code>。</li>
                                <li><strong>审查 DOM 事件监听器：</strong>在 Elements 面板选中 <code>#cryptoLoginForm</code>，查看 <code>Event Listeners -> submit</code>，直接定位到绑定的事件函数。</li>
                                <li><strong>观察硬编码密钥：</strong>在当前页面脚本中可直接发现：
                                    <pre style="margin-top:6px; background:#f1f5f9; border-radius:6px; padding:10px; font-size:12px; color:#0f172a;">const key = CryptoJS.enc.Utf8.parse("1234567890123456");
const iv = CryptoJS.enc.Utf8.parse("1234567890123456");</pre>
                                </li>
                                <li><strong>Console 实时解密：</strong>在开发者工具控制台直接测试解密：
                                    <pre style="margin-top:6px; background:#f1f5f9; border-radius:6px; padding:10px; font-size:12px; color:#0f172a;">CryptoJS.AES.decrypt("密文字符串", key, {iv: iv}).toString(CryptoJS.enc.Utf8);</pre>
                                </li>
                            </ol>
                        </div>

                        <!-- Tab 2: Burp AutoDecoder -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛠️ Burp Suite 插件自动化解密配置</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                使用 <strong>AutoDecoder</strong> 或 <strong>BurpCrypto</strong> 插件，可将密文在发包时自动解密，输入明文自动加密：
                            </p>
                            <ul style="font-size:13px; color:#334155; line-height:1.7;">
                                <li><strong>算法类型 (Algorithm):</strong> <code>AES</code></li>
                                <li><strong>加密模式 (Mode):</strong> <code>CBC</code></li>
                                <li><strong>填充方式 (Padding):</strong> <code>PKCS7Padding</code></li>
                                <li><strong>Key / IV 编码:</strong> <code>UTF-8 / Plaintext</code></li>
                                <li><strong>Key:</strong> <code>1234567890123456</code></li>
                                <li><strong>IV:</strong> <code>1234567890123456</code></li>
                                <li><strong>密文格式 (Output Format):</strong> <code>Base64</code></li>
                            </ul>
                            <div class="alert alert-info" style="font-size:12.5px; border-radius:8px; margin-top:14px;">
                                <i class="ph-bold ph-lightbulb"></i> 配置后直接在 Intruder 中载入明文字典，插件会在发包前自动将其加密为合规载荷发往服务端！
                            </div>
                        </div>

                        <!-- Tab 3: Python 爆破脚本 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 独立 Python 自动化渗透脚本</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import json
import base64
import requests
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad

TARGET_URL = "http://127.0.0.1:8765/vul/encrypt/aes.php"
KEY = b"1234567890123456"
IV  = b"1234567890123456"

def encrypt_aes(username, password):
    raw_json = json.dumps({"username": username, "password": password}, separators=(',', ':'))
    cipher = AES.new(KEY, AES.MODE_CBC, IV)
    padded_data = pad(raw_json.encode('utf-8'), AES.block_size)
    encrypted_bytes = cipher.encrypt(padded_data)
    return base64.b64encode(encrypted_bytes).decode('utf-8')

# 测试密码字典爆破
passwords = ["123", "admin888", "123456", "password"]
for pwd in passwords:
    enc_data = encrypt_aes("admin", pwd)
    resp = requests.post(TARGET_URL, data={"encryptedData": enc_data})
    res_json = resp.json()
    if res_json.get("success"):
        print(f"[+] 破解成功! 账号: admin 密码: {pwd}")
        break
    else:
        print(f"[-] 尝试密码: {pwd} -> {res_json.get('error')}")
</code></pre>
                        </div>

                        <!-- Tab 4: 安全加固建议 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 研发与架构安全加固方案</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>避免静态对称密钥硬编码：</strong>将对称密钥直接写在前端 JS 中属于典型的“掩耳盗铃”，任何攻击者都可以通过 F12 检索提取。<br>
                                2. <strong>依托底层 TLS/HTTPS 加密传输：</strong>应用层传输安全应建立在合规的 TLS 协议之上，而非自定义的前端对称加解密。<br>
                                3. <strong>如需应用层保护，采用非对称加密 (RSA/SM2)：</strong>前端仅存放公钥，私钥严格保存在服务端私密存储区，确保密文具备单向保密性。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 引入必要加密库 -->
<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/crypto-js.min.js"></script>

<script>
function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function handleCryptoSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 加密传输中...';

    // 1. 构建原始明文数据包
    const payloadObj = { username: username, password: password };
    const jsonStr = JSON.stringify(payloadObj);
    document.getElementById('inspPlain').textContent = jsonStr;

    // 2. 使用固定 Key 和 IV 执行 AES 加密
    const key = CryptoJS.enc.Utf8.parse("1234567890123456");
    const iv  = CryptoJS.enc.Utf8.parse("1234567890123456");

    const encrypted = CryptoJS.AES.encrypt(jsonStr, key, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    }).toString();

    // 3. 更新抓包密文显示
    document.getElementById('inspCipher').textContent = "encryptedData=" + encodeURIComponent(encrypted);

    // 4. 发送 POST 请求
    const formData = new URLSearchParams();
    formData.append('encryptedData', encrypted);

    fetch('aes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
        },
        body: formData.toString()
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('inspResponse').textContent = JSON.stringify(data, null, 2);
        alertBox.style.display = 'block';
        if (data.success) {
            alertBox.className = 'alert alert-success';
            alertBox.innerHTML = '<i class="ph-bold ph-check-circle"></i> <strong>' + data.message + '</strong> 用户 [' + data.username + '] 验证通过！';
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="ph-bold ph-x-circle"></i> <strong>认证失败:</strong> ' + (data.error || '用户名或密码错误');
        }
    })
    .catch(err => {
        document.getElementById('inspResponse').textContent = 'Request Error: ' + err.message;
        alertBox.style.display = 'block';
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 请求发生异常，请检查控制台';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i> 客户端加密并提交认证';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
