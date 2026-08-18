<?php
/**
 * Pikachu-Enhanced v2.0 - 关卡 01: JWT 客户端状态修改与认证绕过 (Client-side Claim Tampering)
 */
$PIKA_ROOT_DIR = "../../";

include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[157] = 'active open';
$ACTIVE[124] = 'active';

$link = connect();
$login_msg = '';
$flag_msg = '';

// Check Flag submission
if (isset($_POST['check_flag'])) {
    $sub_flag = trim($_POST['flag_input'] ?? '');
    if ($sub_flag === 'flag{JWT_Auth_Bypass_Client_Tamper_Success}') {
        $_SESSION['jwt_flags']['stage1'] = true;
        $flag_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check-circle'></i> 🎉 恭喜！Flag 正确！成功掌握 JWT 客户端状态篡改与越权认证原理！</div>";
    } else {
        $flag_msg = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700;'><i class='fa fa-times-circle'></i> ❌ Flag 错误，请修改 Token 中的 role 与 level 字段越权访问管理中枢以获取 Flag！</div>";
    }
}

// Handle Logout
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    jwt_logout();
    setcookie('jwt_token', '', time() - 3600, '/');
    $_COOKIE['jwt_token'] = '';
    header('location:jwt_login.php');
    exit();
}

$current_token = $_COOKIE['jwt_token'] ?? '';

// Handle Normal Login
if (isset($_POST['login_submit'])) {
    $user_in = trim($_POST['username'] ?? '');
    $pass_in = trim($_POST['password'] ?? '');
    if ($user_in !== '' && $pass_in !== '') {
        $username = escape($link, $user_in);
        $password = escape($link, $pass_in);
        $query = "select * from users where username='$username' and password=md5('$password')";
        $result = execute($link, $query);
        if (mysqli_num_rows($result) == 1) {
            $data = mysqli_fetch_assoc($result);
            $role = ($data['level'] == 1) ? 'admin' : 'user';
            $payload = array(
                'username' => $data['username'],
                'level' => intval($data['level']),
                'role' => $role,
                'iat' => time()
            );
            $token = jwt_create_token($payload);
            setcookie('jwt_token', $token, time() + 3600, '/');
            $_COOKIE['jwt_token'] = $token;
            $current_token = $token;
            $login_msg = "<div class='alert alert-success'><i class='fa fa-check'></i> 登录成功！服务端已下发用户 JWT 凭证至 Cookie (jwt_token)。</div>";
        } else {
            $login_msg = "<div class='alert alert-danger'><i class='fa fa-times'></i> 登录失败：用户名或密码错误。</div>";
        }
    }
}

// Parse Current Session Token (Vulnerable logic: reads/decodes Base64 payload directly without enforcing server signature)
$session_user = '未登录 (访客)';
$session_role = 'guest';
$session_level = 0;
$is_admin = false;

if ($current_token !== '') {
    $parts = explode('.', $current_token);
    if (count($parts) >= 2) {
        $p_raw = jwt_base64url_decode($parts[1]);
        $payload_parsed = json_decode($p_raw, true);
        if (is_array($payload_parsed)) {
            $session_user = $payload_parsed['username'] ?? ($payload_parsed['user'] ?? 'unknown');
            $session_role = strtolower($payload_parsed['role'] ?? 'user');
            $session_level = intval($payload_parsed['level'] ?? 0);
            if ($session_role === 'admin' || $session_role === 'root' || $session_role === 'superadmin' || $session_level === 1 || $session_user === 'admin') {
                $is_admin = true;
            }
        }
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
.admin-terminal {
    background: #020617;
    border: 1px solid #10b981;
    border-radius: 12px;
    padding: 22px;
    color: #38bdf8;
    font-family: monospace;
    margin-top: 15px;
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
}

/* Vulnerability Flowchart Styles */
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
.flow-arrow {
    text-align: center;
    color: var(--primary);
    font-size: 14px;
    margin: -4px 0;
}

/* Manual Tab Box */
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
                <li class="active">Stage 01: JWT 客户端状态修改与认证绕过</li>
            </ul>
            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="解题提示"
               data-content="登录拿到普通用户 Token 后，在本地使用 Python 或 Base64 工具把 Payload 中的 role 改为 admin（或 level 改为 1），再在浏览器 F12 Application -> Cookies 中修改 jwt_token 并刷新即可！">
                <i class="fa fa-lightbulb-o text-warning"></i> 提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Stage Hero -->
            <div class="stage-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-unlock-alt" style="color:#818cf8;"></i> Stage 01: JWT 客户端状态修改与越权认证
                    <span class="label label-info" style="border-radius:12px; font-size:11px; padding:3px 10px;">100 PTS</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞场景：</b>当服务端将会话状态（如用户角色 <code>role</code>、权限等级 <code>level</code>）完全存放在客户端 JWT Payload 中，且在处理鉴权时<b>未对 Token 进行严谨的签名校验</b>（或仅由前端 Base64 解码自行放行），攻击者只需对 Payload 进行 Base64 解码并篡改字段，即可实现任意身份水平/垂直越权！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left: Login Form & Current Session State -->
                <div class="col-md-5">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-sign-in" style="color:var(--primary);"></i> 账号密码登录入口
                        </h4>

                        <?php echo $login_msg; ?>

                        <form method="POST" style="margin-bottom:18px;">
                            <div class="form-group">
                                <label style="font-weight:600; font-size:13px;">用户名 (Username):</label>
                                <input type="text" name="username" class="form-control" placeholder="pikachu" value="pikachu" required style="border-radius:6px;">
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600; font-size:13px;">密码 (Password):</label>
                                <input type="password" name="password" class="form-control" placeholder="000000" value="000000" required style="border-radius:6px;">
                            </div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <button type="submit" name="login_submit" class="btn btn-primary btn-sm" style="border-radius:6px; font-weight:600;">
                                    <i class="fa fa-lock"></i> 提交登录并获取 Token
                                </button>
                                <a href="jwt_login.php?logout=1" class="btn btn-default btn-sm" style="border-radius:6px;">
                                    <i class="fa fa-power-off"></i> 注销清除
                                </a>
                            </div>
                        </form>

                        <div class="alert alert-info" style="border-radius:8px; font-size:12px; margin:0;">
                            <b>演示测试账号：</b><br>
                            &bull; 普通员工：<code>pikachu / 000000</code> (level: 2, role: user)<br>
                            &bull; 超级管理：<code>admin / 123456</code> (level: 1, role: admin)
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <h5 style="font-weight:700; color:var(--text-primary);">当前客户端会话状态 (Cookie: jwt_token)：</h5>
                        <div style="font-size:13px; margin-bottom:8px;">
                            <span>当前识别身份：</span>
                            <span class="label <?php echo $is_admin ? 'label-danger' : 'label-success'; ?>" style="font-size:12px; border-radius:6px;">
                                <?php echo htmlspecialchars($session_user); ?> (Role: <?php echo htmlspecialchars($session_role); ?> | Level: <?php echo $session_level; ?>)
                            </span>
                        </div>
                        <div class="token-box">
                            <?php echo !empty($current_token) ? htmlspecialchars($current_token) : '<span style="color:var(--text-muted);">[暂未登录，无 Cookie Token]</span>'; ?>
                        </div>

                        <!-- Admin Central Output Area (Triggered when user becomes admin) -->
                        <?php if ($is_admin) { ?>
                            <div class="admin-terminal">
                                <div style="color:#10b981; font-weight:700; font-size:15px; margin-bottom:10px;">
                                    <i class="fa fa-shield"></i> 🎯 成功进入系统高权管理中枢 (Admin Center Disclosed)
                                </div>
                                <div style="color:#e2e8f0; font-size:12.5px; line-height:1.7;">
                                    [+] 身份鉴权通过: <b><?php echo htmlspecialchars($session_user); ?></b> (Role: <span style="color:#ef4444; font-weight:bold;"><?php echo htmlspecialchars($session_role); ?></span>, Level: <span style="color:#ef4444; font-weight:bold;"><?php echo $session_level; ?></span>)<br>
                                    [+] 鉴权漏洞: Client-side Stateless JWT Claim Tampering Bypass<br>
                                    [+] 核心机密中枢 Flag: <br>
                                    <div style="margin:6px 0; background:rgba(245,158,11,0.15); border:1px solid #f59e0b; padding:8px 10px; border-radius:6px;">
                                        <span style="color:#f59e0b; font-weight:bold; font-size:14px;">flag{JWT_Auth_Bypass_Client_Tamper_Success}</span>
                                    </div>
                                    [+] 复制上方 Flag 粘贴至下方输入框提交即可通关！
                                </div>
                            </div>
                        <?php } else { ?>
                            <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:10px; padding:16px; text-align:center; color:var(--text-muted); font-size:12.5px; margin-top:15px;">
                                <i class="fa fa-lock" style="font-size:20px; margin-bottom:6px; display:block; color:var(--text-muted);"></i>
                                当前识别为普通用户，管理中心处于锁定状态。<br>
                                请参考右侧指南手动修改 Cookie 中的 <code>jwt_token</code> 提升为管理员！
                            </div>
                        <?php } ?>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Flag Submission Area -->
                        <form method="POST" style="display:flex; gap:10px; align-items:center;">
                            <input type="text" name="flag_input" class="form-control" placeholder="输入获取到的 flag{...}" style="border-radius:6px;" value="<?php echo $is_admin ? 'flag{JWT_Auth_Bypass_Client_Tamper_Success}' : ''; ?>" required>
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
                            <i class="fa fa-sitemap" style="color:var(--primary);"></i> 漏洞机理架构与攻击时序流程图
                        </h4>

                        <!-- Visual SVG Architecture Flowchart -->
                        <div style="background:#020617; border:1px solid #1e293b; border-radius:12px; padding:16px; margin-bottom:16px; text-align:center;">
                            <svg viewBox="0 0 760 210" style="width:100%; max-width:720px; height:auto; display:inline-block;" xmlns="http://www.w3.org/2000/svg">
                                <!-- Background grid -->
                                <defs>
                                    <linearGradient id="gradBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.2"/>
                                        <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="gradRed" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#991b1b" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="gradGreen" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#065f46" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <marker id="arrowhead" markerWidth="8" markerHeight="6" refX="7" refY="3" orient="auto">
                                        <polygon points="0 0, 8 3, 0 6" fill="#38bdf8"/>
                                    </marker>
                                    <marker id="arrowhead-red" markerWidth="8" markerHeight="6" refX="7" refY="3" orient="auto">
                                        <polygon points="0 0, 8 3, 0 6" fill="#ef4444"/>
                                    </marker>
                                </defs>

                                <!-- Step 1 Box: Client Login -->
                                <rect x="15" y="25" width="200" height="70" rx="8" fill="url(#gradBlue)" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="115" y="48" fill="#60a5fa" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">1. 客户端正常登录</text>
                                <text x="115" y="68" fill="#94a3b8" font-size="10.5" text-anchor="middle" font-family="monospace">POST username=pikachu</text>
                                <text x="115" y="82" fill="#94a3b8" font-size="10" text-anchor="middle" font-family="sans-serif">获得初始 Token (role: user)</text>

                                <!-- Arrow 1 -> Server -->
                                <path d="M 215 60 L 275 60" stroke="#38bdf8" stroke-width="1.5" marker-end="url(#arrowhead)"/>

                                <!-- Step 2 Box: Server Issue -->
                                <rect x="285" y="25" width="190" height="70" rx="8" fill="#0f172a" stroke="#475569" stroke-width="1.5"/>
                                <text x="380" y="48" fill="#f8fafc" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">2. 服务端签发 Token</text>
                                <text x="380" y="68" fill="#cbd5e1" font-size="10.5" text-anchor="middle" font-family="monospace">Set-Cookie: jwt_token</text>
                                <text x="380" y="82" fill="#64748b" font-size="10" text-anchor="middle" font-family="sans-serif">Base64(Header.Payload.Sig)</text>

                                <!-- Step 3 Box: Attacker Tamper (Bottom Left) -->
                                <rect x="15" y="125" width="200" height="70" rx="8" fill="url(#gradRed)" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="115" y="148" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">3. 客户端 Base64 篡改</text>
                                <text x="115" y="168" fill="#fca5a5" font-size="10.5" text-anchor="middle" font-family="monospace">"role": "admin", "level": 1</text>
                                <text x="115" y="182" fill="#cbd5e1" font-size="10" text-anchor="middle" font-family="sans-serif">拼接任意假签名写入 Cookie</text>

                                <!-- Arrow 3 -> 4 -->
                                <path d="M 215 160 L 275 160" stroke="#ef4444" stroke-width="1.5" stroke-dasharray="4,3" marker-end="url(#arrowhead-red)"/>

                                <!-- Step 4 Box: Flawed Verification -->
                                <rect x="285" y="125" width="190" height="70" rx="8" fill="url(#gradRed)" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="380" y="148" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">4. 缺陷验签逻辑 (漏洞点)</text>
                                <text x="380" y="168" fill="#fca5a5" font-size="10.5" text-anchor="middle" font-family="monospace">json_decode(base64)</text>
                                <text x="380" y="182" fill="#ef4444" font-size="10" font-weight="bold" text-anchor="middle" font-family="sans-serif">❌ 缺失 verify_signature() !</text>

                                <!-- Arrow 4 -> 5 -->
                                <path d="M 475 160 L 535 160" stroke="#10b981" stroke-width="1.5" marker-end="url(#arrowhead)"/>

                                <!-- Step 5 Box: Admin Flag Disclosure -->
                                <rect x="545" y="65" width="200" height="95" rx="8" fill="url(#gradGreen)" stroke="#10b981" stroke-width="2"/>
                                <text x="645" y="93" fill="#34d399" font-size="13" font-weight="bold" text-anchor="middle" font-family="sans-serif">5. 权限提升 &amp; 获取 Flag</text>
                                <text x="645" y="115" fill="#f8fafc" font-size="11" text-anchor="middle" font-family="sans-serif">服务端识别为超级管理员</text>
                                <text x="645" y="133" fill="#fbbf24" font-size="11" font-weight="bold" text-anchor="middle" font-family="monospace">flag{JWT_Auth_Bypass...}</text>
                                <text x="645" y="149" fill="#a7f3d0" font-size="10" text-anchor="middle" font-family="sans-serif">✅ 获得全库最高权限</text>
                            </svg>
                        </div>

                        <!-- Step Details List -->
                        <div class="flow-container">
                            <div class="flow-step">
                                <div class="flow-num">1</div>
                                <div class="flow-content">
                                    <h5>正常登录获取初始低权 Token</h5>
                                    <p>用户使用普通账号 <code>pikachu/000000</code> 登录，服务端下发包含 <code>"role": "user", "level": 2</code> 的合法 JWT 存储在 Cookie 中。</p>
                                </div>
                            </div>
                            <div class="flow-step">
                                <div class="flow-num" style="background:#ef4444;">2</div>
                                <div class="flow-content">
                                    <h5 style="color:#ef4444;">客户端截获并解码篡改 Payload</h5>
                                    <p>JWT 由 <code>Header.Payload.Signature</code> 三部分组成。攻击者取出中间的 Payload 并在本地进行 Base64 解码，将权限字段篡改为 <code>"role": "admin", "level": 1</code>。</p>
                                </div>
                            </div>
                            <div class="flow-step">
                                <div class="flow-num" style="background:#f59e0b;">3</div>
                                <div class="flow-content">
                                    <h5 style="color:#f59e0b;">重新组装并写入浏览器 Cookie</h5>
                                    <p>重新对 Payload 做 Base64URL 编码，拼接任意签名（例如 <code>Header.NewPayload.AnyFakeSig</code>），通过浏览器 F12 修改 <code>Cookie: jwt_token</code> 并刷新。</p>
                                </div>
                            </div>
                            <div class="flow-step">
                                <div class="flow-num" style="background:#10b981;">4</div>
                                <div class="flow-content">
                                    <h5 style="color:#10b981;">服务端「只解码、未验签」导致直接越权</h5>
                                    <p>服务端仅调用 <code>json_decode(base64_decode())</code> 读取了 Payload 中的角色声明，缺少签名合法性校验，直接放行并下发 Flag！</p>
                                </div>
                            </div>
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Manual Operation Tutorials -->
                        <h4 style="margin:0 0 12px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-terminal" style="color:var(--primary);"></i> 手动操作实战指南 (任选一种方法)
                        </h4>

                        <!-- Method 1: Python Script -->
                        <div style="margin-bottom:16px;">
                            <div style="font-size:13px; font-weight:700; color:var(--primary);">
                                <i class="fa fa-code"></i> 方法一：使用 Python 脚本生成伪造 Token
                            </div>
                            <p style="font-size:12px; color:var(--text-muted); margin:4px 0;">在本地终端打开 Python 交互环境（<code>python</code>），执行以下代码生成你的管理员 Token：</p>
                            <div class="code-tab-box">
<span style="color:#60a5fa;">import</span> jwt

<span style="color:#94a3b8;"># 构造包含管理员角色的 Payload</span>
admin_payload = {
    <span style="color:#a5b4fc;">"username"</span>: <span style="color:#34d399;">"pikachu"</span>,
    <span style="color:#a5b4fc;">"level"</span>: <span style="color:#f59e0b;">1</span>,
    <span style="color:#a5b4fc;">"role"</span>: <span style="color:#34d399;">"admin"</span>,
    <span style="color:#a5b4fc;">"iat"</span>: <span style="color:#f59e0b;">1787035367</span>
}

<span style="color:#94a3b8;"># 使用任意自定义密钥签名生成 Token</span>
token = jwt.encode(admin_payload, <span style="color:#34d399;">"my_secret_key"</span>, algorithm=<span style="color:#34d399;">"HS256"</span>)
<span style="color:#60a5fa;">print</span>(token)
</div>
                        </div>

                        <!-- Method 2: Browser F12 Manual Cookie Edit -->
                        <div style="margin-bottom:16px;">
                            <div style="font-size:13px; font-weight:700; color:#10b981;">
                                <i class="fa fa-mouse-pointer"></i> 方法二：通过浏览器 F12 修改 Cookie 提交
                            </div>
                            <div style="font-size:12.5px; color:var(--text-secondary); line-height:1.6; margin-top:4px;">
                                1. 按键盘 <b>F12</b> 打开开发者工具，切换到 <b>Application (应用)</b> 或 <b>Storage (存储)</b> 标签页。<br>
                                2. 在左侧展开 <b>Cookies &rarr; http://127.0.0.1:8765</b>。<br>
                                3. 找到名为 <b><code>jwt_token</code></b> 的条目，双击 <b>Value</b> 字段，粘贴你生成的管理员 Token。<br>
                                4. 按 <b>F5 刷新当前网页</b>，页面就会立即识别为超级管理员并展示 Flag！
                            </div>
                        </div>

                        <!-- Method 3: curl command -->
                        <div>
                            <div style="font-size:13px; font-weight:700; color:#f59e0b;">
                                <i class="fa fa-globe"></i> 方法三：使用 curl 命令行直接验证
                            </div>
                            <div class="code-tab-box" style="white-space:pre-wrap; word-break:break-all;">
curl.exe -s -b <span style="color:#34d399;">"jwt_token=你的管理员Token"</span> http://127.0.0.1:8765/vul/jwt/jwt_login.php | findstr <span style="color:#34d399;">"flag{"</span>
</div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Navigation Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <a href="jwt.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 返回模块大厅
                </a>
                <a href="jwt_none.php" class="btn btn-primary" style="border-radius:8px;">
                    下一关：Stage 02 (JWT None 算法免签绕过) <i class="fa fa-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
