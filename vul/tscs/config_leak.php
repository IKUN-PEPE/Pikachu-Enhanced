<?php
/**
 * Pikachu-Enhanced v2.0 - 企业级综合运营管理系统 (CorpHub)
 * Level 1: 系统参数配置全量泄露与 RSA 私钥暴露
 */
$PIKA_ROOT_DIR = "../../";
require_once __DIR__ . '/tscs_helper.inc.php';

// 处理 API 请求 (供前端 AJAX 或 Burp Suite 直接探测)
if (isset($_GET['api']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false && !isset($_GET['view']))) {
    header('Content-Type: application/json; charset=utf-8');
    
    // 检查是否带鉴权 (真实系统中低权限账号如 low_role 可直接拉取全量)
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $simulatedRole = $_GET['role'] ?? ($_SESSION['tscs_current_user']['role'] ?? 'anonymous');
    
    // 真实漏洞核心: 未校验是否为管理员，只要是登录用户 (甚至部分配置被匿名放行) 即全量返回敏感配置与私钥
    $configs = tscs_get_configs();
    
    echo json_encode([
        'total' => count($configs),
        'rows' => $configs,
        'code' => 200,
        'msg' => '查询成功'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$ACTIVE = array_fill(0, 360, '');
$ACTIVE[350] = 'active open';
$ACTIVE[352] = 'active';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.tscs-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-subtle, #e2e8f0);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}
.json-display {
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    font-family: monospace;
    font-size: 12.5px;
    line-height: 1.6;
    max-height: 420px;
    overflow-y: auto;
}
.key-highlight {
    background: #450a0a;
    border: 1px solid #ef4444;
    border-radius: 8px;
    padding: 14px 18px;
    color: #fecaca;
    margin-top: 16px;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="<?php echo $PIKA_ROOT_DIR;?>index.php">首页</a></li>
                <li><a href="index.php">企业级综合业务系统 (CorpHub) 靶场</a></li>
                <li class="active">Level 1: 系统配置与私钥泄露</li>
            </ul>
        </div>

        <div class="page-content">
            
            <!-- 关卡说明 Header -->
            <div style="margin-bottom: 22px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="badge" style="background:#0284c7; padding:4px 10px; font-size:12px;">Level 1</span>
                    <span class="badge" style="background:#dc2626; padding:4px 10px; font-size:12px;">CVSS 8.2 高危</span>
                    <span class="badge" style="background:#4f46e5; padding:4px 10px; font-size:12px;">CWE-200 信息泄露</span>
                </div>
                <h2 style="font-weight: 800; font-size: 22px; margin: 0 0 8px 0; color: #0f172a;">
                    系统参数配置全量泄露与 RSA 私钥暴露
                </h2>
                <p style="font-size: 14px; color: #64748b; margin: 0; line-height: 1.6;">
                    <strong>真实场景还原：</strong>在对目标主系统 <code>/prod-api</code> 渗透过程中，测试人员发现系统参数接口 <code>GET /prod-api/system/config/list</code> 缺乏严格的角色过滤。攻击者利用已授权的低权限账号（甚至匿名调用某些参数端点）可直接拉取全量参数。服务端不仅吐出了参数名，连同敏感的 <code>configValue</code> 一并原样返回，导致整个系统的密码学防御与初始身份防护瞬间崩塌！
                </p>
            </div>

            <!-- 演练目标提示 -->
            <div class="alert alert-info" style="border-radius:10px; font-size:13px; line-height:1.6;">
                <strong>🎯 本关演练目标：</strong><br>
                1. 模拟调用 <code>GET /prod-api/system/config/list</code> 接口，观察完整的 JSON 响应数据。<br>
                2. 从响应中定位并提取三大核心敏感资产：<strong>① 统一初始密码 (sys.user.initPassword)</strong>、<strong>② 4位 SSO 密钥 (sys.bpmsso.secret)</strong>、<strong>③ 全平台通用的 1024 位 RSA 私钥 (sys.login.rsaPrivateKey)</strong>。<br>
                3. 将截获的密码密文粘贴至右侧解密器，验证能否用泄露的私钥一键还原明文密码！
            </div>

            <div class="row">
                
                <!-- 左侧: 交互式接口发包与响应观察 -->
                <div class="col-md-7">
                    <div class="tscs-card">
                        <h4 style="margin:0 0 16px 0; font-weight:700; font-size:16px; display:flex; align-items:center; justify-content:space-between;">
                            <span><i class="ph-bold ph-paper-plane-tilt" style="color:#0284c7;"></i> 接口发包演练 (API Terminal)</span>
                            <span style="font-size:12px; font-family:monospace; color:#64748b;">GET /prod-api/system/config/list</span>
                        </h4>

                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:14px; font-size:13px;">
                            <div style="margin-bottom:6px;"><strong>请求方法：</strong> <code>GET</code></div>
                            <div style="margin-bottom:6px;"><strong>请求 URL：</strong> <code>http://127.0.0.1:8765/vul/tscs/config_leak.php?api=1</code></div>
                            <div><strong>请求头：</strong> <code>Authorization: Bearer eyJhbGci... (低权限账号 low_role)</code></div>
                        </div>

                        <button id="sendBtn" class="btn btn-primary" onclick="sendConfigRequest()" style="border-radius:8px; font-weight:700; padding:10px 20px; background:#0284c7; border-color:#0284c7;">
                            <i class="ph-bold ph-broadcast"></i> 发送请求获取配置数据
                        </button>
                        <button class="btn btn-default" onclick="highlightSensitiveData()" style="border-radius:8px; font-weight:600; padding:10px 16px; margin-left:8px;">
                            <i class="ph-bold ph-crosshair"></i> 高亮提取三大核心凭据
                        </button>

                        <div style="margin-top:16px;">
                            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px; text-transform:uppercase;">
                                服务端原生 JSON 响应：
                            </div>
                            <pre class="json-display" id="responseBox">// 点击上方按钮发送请求...</pre>
                        </div>
                    </div>
                </div>

                <!-- 右侧: 泄露凭据解析与 RSA 在线解密验证 -->
                <div class="col-md-5">
                    
                    <!-- 敏感凭据提取结果 -->
                    <div class="tscs-card">
                        <h4 style="margin:0 0 14px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:6px;">
                            <i class="ph-bold ph-key" style="color:#dc2626;"></i> 提取出的核心敏感资产看板
                        </h4>

                        <div class="key-highlight" id="keyPanel" style="display:none;">
                            <div style="font-weight:700; font-size:13px; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                                <i class="ph-bold ph-warning"></i> 警告：已截获系统级敏感私钥与凭据！
                            </div>
                            <div style="font-size:12.5px; line-height:1.7;">
                                <div><strong>① 初始密码：</strong> <code style="background:#7f1d1d; color:#fef2f2; padding:2px 6px; border-radius:4px;" id="initPwVal">Corp@2026!</code></div>
                                <div style="margin-top:4px;"><strong>② SSO 密钥：</strong> <code style="background:#7f1d1d; color:#fef2f2; padding:2px 6px; border-radius:4px;" id="ssoSecVal">corp</code> (4位弱密钥)</div>
                                <div style="margin-top:4px;"><strong>③ RSA 私钥：</strong> 长度 848 字符 (1024-bit PKCS#1)</div>
                            </div>
                        </div>
                        <div id="noKeyTip" style="font-size:13px; color:#64748b; padding:16px 0;">
                            尚未提取数据，请先在左侧点击“发送请求”。
                        </div>
                    </div>

                    <!-- 密码学验证: RSA 离线解密工具 -->
                    <div class="tscs-card">
                        <h4 style="margin:0 0 12px 0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:6px;">
                            <i class="ph-bold ph-shield-warning" style="color:#f59e0b;"></i> 前端登录密码离线解密验证
                        </h4>
                        <p style="font-size:12.5px; color:#64748b; margin-bottom:12px; line-height:1.5;">
                            真实系统中，前端新建用户或登录时使用公钥加密口令。现在你拥有了泄露的私钥，可直接还原截获的密码密文：
                        </p>

                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-size:12px; font-weight:600; color:#334155;">截获的前端密码密文 (Base64)：</label>
                            <textarea id="sampleCipher" class="form-control" rows="3" style="font-family:monospace; font-size:11.5px; border-radius:8px;">qU0x9KzL1wE4rT7yU0iO3pA6sD9fG2hJ5kL8zX1cV4bN7mQ0wE3rT6yU9iO2pA5sD8fG1hJ4kL7zX0cV3bN6mQ9wE2rT5yU8iO1pA4sD7fG0hJ3kL6zX9cV2bN5mQ8wE1rT4yU7iO0pA3sD6fG9hJ2kL5zX8cV1bN4mQ7wE0rT3yU6iO9pA2sD5fG8hJ1kL4zX7cV0bN3mQ6==</textarea>
                        </div>

                        <button class="btn btn-warning btn-block" onclick="decryptSamplePassword()" style="border-radius:8px; font-weight:700; padding:10px;">
                            <i class="ph-bold ph-lock-simple-open"></i> 使用泄露私钥解密密码
                        </button>

                        <div id="decryptResult" style="display:none; margin-top:14px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:12px; font-size:13px; color:#065f46;">
                            <strong>🎉 解密成功！还原明文密码：</strong>
                            <code style="font-size:14px; font-weight:700; color:#047857;" id="plainPwd">Corp@2026!</code>
                        </div>
                    </div>

                    <!-- 下一步行动引导 -->
                    <div class="tscs-card" style="background:#f0f9ff; border-color:#bae6fd;">
                        <h5 style="margin:0 0 8px 0; font-weight:700; color:#0369a1;">
                            <i class="ph-bold ph-arrow-right"></i> 下一步渗透闭环行动：
                        </h5>
                        <p style="font-size:12.5px; color:#0284c7; margin-bottom:12px; line-height:1.6;">
                            在获得初始密码 <code>Corp@2026!</code> 后，立即前往 <strong>Level 2 初始密码批量接管</strong>，对枚举出的核心管理工号执行无阻碍批量登录！
                        </p>
                        <a href="weak_password.php" class="btn btn-sm btn-primary" style="border-radius:6px; font-weight:700;">
                            前往 Level 2 批量接管 ➔
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
let globalConfigData = null;

function sendConfigRequest() {
    const btn = document.getElementById('sendBtn');
    const respBox = document.getElementById('responseBox');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> 正在请求接口...';

    fetch('config_leak.php?api=1')
        .then(r => r.json())
        .then(data => {
            globalConfigData = data;
            respBox.textContent = JSON.stringify(data, null, 2);
            document.getElementById('keyPanel').style.display = 'block';
            document.getElementById('noKeyTip').style.display = 'none';
        })
        .catch(err => {
            respBox.textContent = '请求异常: ' + err.message;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-bold ph-broadcast"></i> 发送请求获取配置数据';
        });
}

function highlightSensitiveData() {
    if (!globalConfigData) {
        alert('请先点击“发送请求获取配置数据”！');
        return;
    }
    document.getElementById('keyPanel').style.display = 'block';
    document.getElementById('noKeyTip').style.display = 'none';
    alert('已成功定位关键配置项：\n- sys.user.initPassword = Corp@2026!\n- sys.bpmsso.secret = corp\n- sys.login.rsaPrivateKey = 848 字符完整私钥！');
}

function decryptSamplePassword() {
    const res = document.getElementById('decryptResult');
    res.style.display = 'block';
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
