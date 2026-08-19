<?php
/**
 * Pikachu-Enhanced v2.0 - 会话固定漏洞 (Session Fixation) 演练靶场
 */
$PIKA_ROOT_DIR = "../../";

// 漏洞核心：支持通过 GET 参数固定会话 ID (模拟攻击者预设或客户端固定，PHP 8 要求 [a-zA-Z0-9,-])
if (isset($_GET['sid']) && preg_match('/^[a-zA-Z0-9-]{4,64}$/', $_GET['sid'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_id($_GET['sid']);
    }
}

include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[128] = 'active open';
$ACTIVE[130] = 'active';

$link = connect();
$login_msg = '';
$flag_msg = '';

// Flag Verification
if (isset($_POST['check_flag'])) {
    $sub_flag = trim($_POST['flag_input'] ?? '');
    if ($sub_flag === 'flag{Session_Fixation_PreAuth_Hijack_Success}') {
        $_SESSION['fixation_flag_solved'] = true;
        $flag_msg = "<div class='alert alert-success' style='border-radius:10px; font-weight:700;'><i class='fa fa-check-circle'></i> 🎉 恭喜！Flag 正确！成功掌握 Session Fixation 会话固定攻击与防御精髓！</div>";
    } else {
        $flag_msg = "<div class='alert alert-danger' style='border-radius:10px; font-weight:700;'><i class='fa fa-times-circle'></i> ❌ Flag 错误，请先固定 SID 诱导登录成功后在控制台获取 Flag！</div>";
    }
}

// Handle Logout
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header('location:fixation_login.php');
    exit();
}

// Handle Login Action (【核心漏洞点】：登录成功后未调用 session_regenerate_id(true) 销毁并更换 Session ID)
if (isset($_POST['submit'])) {
    $user_in = trim($_POST['username'] ?? '');
    $pass_in = trim($_POST['password'] ?? '');
    
    if ($user_in !== '' && $pass_in !== '') {
        $username = escape($link, $user_in);
        $password = escape($link, $pass_in);
        $query = "select * from users where username='$username' and password=md5('$password')";
        $result = execute($link, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $data = mysqli_fetch_assoc($result);
            // 登录状态直接挂载到当前未更新的 Session ID 上
            $_SESSION['sessionfixation'] = array(
                'username' => $data['username'],
                'level' => intval($data['level']),
                'login_time' => date('Y-m-d H:i:s'),
                'sid_at_login' => session_id()
            );
            $login_msg = "<div class='alert alert-success' style='border-radius:8px;'><i class='fa fa-check-circle'></i> <b>登录成功！</b> 当前用户会话已与 Session ID <code>" . htmlspecialchars(session_id()) . "</code> 完成状态绑定。</div>";
        } else {
            $login_msg = "<div class='alert alert-danger' style='border-radius:8px;'><i class='fa fa-times-circle'></i> 登录失败：用户名或密码错误。</div>";
        }
    } else {
        $login_msg = "<div class='alert alert-warning' style='border-radius:8px;'><i class='fa fa-exclamation-triangle'></i> 用户名或密码不能为空。</div>";
    }
}

$current_sid = session_id();
$is_logged_in = isset($_SESSION['sessionfixation']['username']);
$session_user = $is_logged_in ? $_SESSION['sessionfixation']['username'] : '';
$session_level = $is_logged_in ? $_SESSION['sessionfixation']['level'] : 0;
$login_time = $is_logged_in ? ($_SESSION['sessionfixation']['login_time'] ?? '') : '';

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.fix-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}
.fix-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.code-box {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 8px;
    padding: 12px 14px;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.6;
    color: #38bdf8;
    word-break: break-all;
    margin-top: 8px;
}
.victim-terminal {
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
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="sessionfixation.php">Session Fixation</a></li>
                <li class="active">漏洞登录页与会话劫持演练</li>
            </ul>
            <a href="#" style="float:right" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 解题与利用提示"
               data-content="1. 构造带有 ?sid=pika123456 的固定 URL；2. 在受害者会话中完成登录；3. 攻击者直接携带 PHPSESSID=pika123456 即可免密劫持该用户会话！">
                <i class="fa fa-lightbulb-o text-warning"></i> 攻防提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1400px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Hero Banner -->
            <div class="fix-hero">
                <div style="font-size:22px; font-weight:800; margin:0 0 10px 0; display:flex; align-items:center; gap:12px;">
                    <i class="fa fa-anchor" style="color:#818cf8;"></i> Session Fixation 会话固定漏洞实战演练
                    <span class="label label-warning" style="border-radius:12px; font-size:11px; padding:3px 10px;">中危 · 凭证劫持</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞机理：</b>服务端在用户完成鉴权登录（Authentication）后，<b>未调用 <code>session_regenerate_id(true)</code> 重新生成全新的会话 ID</b>，而是直接沿用了登录前客户端指定或预置的旧 Session ID。攻击者可通过诱导受害者使用预设的固定 SID 登录，进而直接通过该 SID 劫持受害者的已登录会话！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left Column: Victim Login & Session Status Monitor -->
                <div class="col-md-5">
                    <div class="fix-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-user-circle" style="color:var(--primary);"></i> 受害者会话登录门户 (Victim Portal)
                        </h4>

                        <?php echo $login_msg; ?>

                        <!-- Current Session ID Monitor -->
                        <div style="background:var(--bg-secondary); border:1px solid var(--border-subtle); border-radius:8px; padding:14px; margin-bottom:18px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <span style="font-size:12.5px; font-weight:600; color:var(--text-secondary);">当前生效的 Session ID：</span>
                                <span class="label <?php echo (isset($_GET['sid']) ? 'label-danger' : 'label-info'); ?>" style="border-radius:4px; font-size:11px;">
                                    <?php echo (isset($_GET['sid']) ? '固定注入 SID' : '标准随机 SID'); ?>
                                </span>
                            </div>
                            <div class="code-box" style="margin-top:0;">
                                <?php echo htmlspecialchars($current_sid); ?>
                            </div>
                        </div>

                        <!-- Login Form -->
                        <form method="POST" style="margin-bottom:16px;">
                            <div class="form-group">
                                <label style="font-weight:600; font-size:13px;">账号 (Username):</label>
                                <input type="text" name="username" class="form-control" placeholder="admin / pikachu" value="<?php echo $is_logged_in ? htmlspecialchars($session_user) : 'admin'; ?>" required style="border-radius:6px;">
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600; font-size:13px;">密码 (Password):</label>
                                <input type="password" name="password" class="form-control" placeholder="123456 / 000000" value="<?php echo $is_logged_in ? '' : '123456'; ?>" required style="border-radius:6px;">
                            </div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <button type="submit" name="submit" class="btn btn-primary btn-sm" style="border-radius:6px; font-weight:600;">
                                    <i class="fa fa-sign-in"></i> 提交登录 (保留当前 SID)
                                </button>
                                <a href="fixation_login.php?logout=1" class="btn btn-default btn-sm" style="border-radius:6px;">
                                    <i class="fa fa-power-off"></i> 注销清除会话
                                </a>
                            </div>
                        </form>

                        <div class="alert alert-info" style="border-radius:8px; font-size:12px; margin:0;">
                            <b>演示测试账号：</b><br>
                            &bull; 超级管理员：<code>admin / 123456</code> (level: 1)<br>
                            &bull; 普通会员：<code>pikachu / 000000</code> (level: 2)
                        </div>

                        <!-- Logged-in State Card -->
                        <?php if ($is_logged_in) { ?>
                            <div class="victim-terminal">
                                <div style="color:#10b981; font-weight:700; font-size:15px; margin-bottom:10px;">
                                    <i class="fa fa-unlock"></i> 🎉 用户已成功登录 (Session State Bound)
                                </div>
                                <div style="font-size:12.5px; color:#e2e8f0; line-height:1.8;">
                                    [+] 已登录用户: <b style="color:#38bdf8;"><?php echo htmlspecialchars($session_user); ?></b> (权限等级: <?php echo $session_level; ?>)<br>
                                    [+] 登录时间: <?php echo htmlspecialchars($login_time); ?><br>
                                    [+] 绑定会话 SID: <span style="color:#f59e0b; font-weight:bold; font-family:monospace;"><?php echo htmlspecialchars($current_sid); ?></span><br>
                                    [+] 漏洞验证凭条 Flag: <br>
                                    <div style="margin:8px 0; background:rgba(245,158,11,0.15); border:1px solid #f59e0b; padding:8px 10px; border-radius:6px;">
                                        <span style="color:#f59e0b; font-weight:bold; font-size:14px;">flag{Session_Fixation_PreAuth_Hijack_Success}</span>
                                    </div>
                                    [+] <a href="fixation_profile.php" class="btn btn-success btn-xs" style="border-radius:4px; font-weight:600; margin-top:4px;">
                                        <i class="fa fa-external-link"></i> 前往登录后敏感信息中心 (Profile)
                                    </a>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:10px; padding:16px; text-align:center; color:var(--text-muted); font-size:12.5px; margin-top:15px;">
                                <i class="fa fa-lock" style="font-size:20px; margin-bottom:6px; display:block; color:var(--text-muted);"></i>
                                当前会话尚未登录，请在上方输入账号密码完成登录以触发会话绑定！
                            </div>
                        <?php } ?>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Flag Submission Area -->
                        <form method="POST" style="display:flex; gap:10px; align-items:center;">
                            <input type="text" name="flag_input" class="form-control" placeholder="输入获取到的 flag{...}" style="border-radius:6px;" value="<?php echo $is_logged_in ? 'flag{Session_Fixation_PreAuth_Hijack_Success}' : ''; ?>" required>
                            <button type="submit" name="check_flag" class="btn btn-success" style="border-radius:6px; font-weight:700; white-space:nowrap;">
                                <i class="fa fa-check"></i> 提交 Flag
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Vulnerability Architecture Flowchart & Attacker Exploit Tools -->
                <div class="col-md-7">
                    <div class="fix-card">
                        <h4 style="margin:0 0 14px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-sitemap" style="color:var(--primary);"></i> 会话固定漏洞架构与攻击时序流程图
                        </h4>

                        <!-- Visual SVG Flowchart -->
                        <div style="background:#020617; border:1px solid #1e293b; border-radius:12px; padding:16px; margin-bottom:16px; text-align:center;">
                            <svg viewBox="0 0 760 210" style="width:100%; max-width:720px; height:auto; display:inline-block;" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="fixGradBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#1d4ed8" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="fixGradRed" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#991b1b" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <linearGradient id="fixGradGreen" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#065f46" stop-opacity="0.05"/>
                                    </linearGradient>
                                    <marker id="fix-arr" markerWidth="8" markerHeight="6" refX="7" refY="3" orient="auto">
                                        <polygon points="0 0, 8 3, 0 6" fill="#38bdf8"/>
                                    </marker>
                                    <marker id="fix-arr-red" markerWidth="8" markerHeight="6" refX="7" refY="3" orient="auto">
                                        <polygon points="0 0, 8 3, 0 6" fill="#ef4444"/>
                                    </marker>
                                </defs>

                                <!-- Step 1: Attacker generates fixed SID -->
                                <rect x="15" y="25" width="200" height="70" rx="8" fill="url(#fixGradRed)" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="115" y="48" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">1. 攻击者预设固定 SID</text>
                                <text x="115" y="68" fill="#fca5a5" font-size="10.5" text-anchor="middle" font-family="monospace">?sid=pika123456</text>
                                <text x="115" y="82" fill="#cbd5e1" font-size="10" text-anchor="middle" font-family="sans-serif">构造钓鱼链接发送给受害者</text>

                                <!-- Arrow 1 -> 2 -->
                                <path d="M 215 60 L 275 60" stroke="#ef4444" stroke-width="1.5" marker-end="url(#fix-arr-red)"/>

                                <!-- Step 2: Victim Logs in -->
                                <rect x="285" y="25" width="190" height="70" rx="8" fill="url(#fixGradBlue)" stroke="#3b82f6" stroke-width="1.5"/>
                                <text x="380" y="48" fill="#60a5fa" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">2. 受害者点击链接并登录</text>
                                <text x="380" y="68" fill="#94a3b8" font-size="10.5" text-anchor="middle" font-family="monospace">POST username=admin</text>
                                <text x="380" y="82" fill="#cbd5e1" font-size="10" text-anchor="middle" font-family="sans-serif">浏览器携带固定 SID</text>

                                <!-- Arrow 2 -> 3 -->
                                <path d="M 475 60 L 535 60" stroke="#38bdf8" stroke-width="1.5" marker-end="url(#fix-arr)"/>

                                <!-- Step 3: Vulnerable Server (Right) -->
                                <rect x="545" y="25" width="200" height="70" rx="8" fill="#0f172a" stroke="#ef4444" stroke-width="1.5"/>
                                <text x="645" y="48" fill="#f87171" font-size="12" font-weight="bold" text-anchor="middle" font-family="sans-serif">3. 缺陷服务端直接放行</text>
                                <text x="645" y="68" fill="#cbd5e1" font-size="10.5" text-anchor="middle" font-family="monospace">未执行 regenerate_id</text>
                                <text x="645" y="82" fill="#ef4444" font-size="10" font-weight="bold" text-anchor="middle" font-family="sans-serif">❌ 沿用旧 SID 绑定特权</text>

                                <!-- Arrow 3 -> 4 -->
                                <path d="M 645 95 L 645 125" stroke="#10b981" stroke-width="1.5" marker-end="url(#fix-arr)"/>

                                <!-- Step 4: Attacker Hijacks Session (Bottom) -->
                                <rect x="285" y="125" width="460" height="65" rx="8" fill="url(#fixGradGreen)" stroke="#10b981" stroke-width="2"/>
                                <text x="515" y="148" fill="#34d399" font-size="13" font-weight="bold" text-anchor="middle" font-family="sans-serif">4. 攻击者无需密码直接劫持会话 (Session Hijacking)</text>
                                <text x="515" y="166" fill="#fbbf24" font-size="11" font-weight="bold" text-anchor="middle" font-family="monospace">Cookie: PHPSESSID=pika123456 &rarr; 直接获得 Admin 权限</text>
                                <text x="515" y="180" fill="#a7f3d0" font-size="10" text-anchor="middle" font-family="sans-serif">✅ 获取 Flag: flag{Session_Fixation_PreAuth_Hijack_Success}</text>
                            </svg>
                        </div>

                        <!-- Detailed Step List -->
                        <div class="flow-container">
                            <div class="flow-step">
                                <div class="flow-num" style="background:#ef4444;">1</div>
                                <div class="flow-content">
                                    <h5 style="color:#ef4444;">构造指定 Session ID 的钓鱼链接</h5>
                                    <p>攻击者预先生成一个自定义的会话标识符（如 <code>pika123456</code>），构造链接：<code>http://127.0.0.1:8765/vul/sessionfixation/fixation_login.php?sid=pika123456</code> 发送给目标用户。</p>
                                </div>
                            </div>
                            <div class="flow-step">
                                <div class="flow-num" style="background:#3b82f6;">2</div>
                                <div class="flow-content">
                                    <h5 style="color:#3b82f6;">诱导受害者点击并在固定 SID 下完成认证</h5>
                                    <p>受害者在浏览器中打开该链接，输入自己的管理员账号与密码（如 <code>admin / 123456</code>）完成登录。</p>
                                </div>
                            </div>
                            <div class="flow-step">
                                <div class="flow-num" style="background:#10b981;">3</div>
                                <div class="flow-content">
                                    <h5 style="color:#10b981;">攻击者在另外一台电脑/无痕浏览器直接复用该 SID</h5>
                                    <p>由于服务端登录前后 Session ID 保持不变，攻击者只需在自己电脑的浏览器中将 Cookie <code>PHPSESSID</code> 设置为 <code>pika123456</code>，即可完全免密进入受害者的管理中心！</p>
                                </div>
                            </div>
                        </div>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Attacker Exploit Helper -->
                        <h4 style="margin:0 0 12px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-terminal" style="color:var(--primary);"></i> 攻击验证与钓鱼链接生成器
                        </h4>

                        <div style="margin-bottom:14px;">
                            <label style="font-size:12.5px; font-weight:600;">1. 快速生成预设 SID 钓鱼测试链接：</label>
                            <div style="display:flex; gap:8px;">
                                <input type="text" id="fixed_sid_input" class="form-control input-sm" value="pika-attacker-fixed-sid-888" style="font-family:monospace; border-radius:4px;">
                                <button type="button" class="btn btn-warning btn-sm" onclick="generatePhishingLink()" style="border-radius:4px; font-weight:600; white-space:nowrap;">
                                    <i class="fa fa-link"></i> 组装链接
                                </button>
                            </div>
                            <div class="code-box" id="phishing_link_box">
                                <a id="phishing_link_anchor" href="fixation_login.php?sid=pika-attacker-fixed-sid-888" style="color:#38bdf8; text-decoration:underline;">
                                    fixation_login.php?sid=pika-attacker-fixed-sid-888
                                </a>
                            </div>
                        </div>

                        <div>
                            <label style="font-size:12.5px; font-weight:600;">2. 攻击者命令行 (curl) 携带固定 SID 越权验证：</label>
                            <div class="code-box" style="white-space:pre-wrap;">
curl.exe -s -b <span style="color:#34d399;">"PHPSESSID=pika-attacker-fixed-sid-888"</span> http://127.0.0.1:8765/vul/sessionfixation/fixation_profile.php | findstr <span style="color:#34d399;">"Current user"</span>
</div>
                        </div>

                        <div style="margin-top:14px; background:rgba(16,185,129,0.1); border-left:3px solid #10b981; border-radius:6px; padding:10px 14px; font-size:12px; color:var(--text-secondary);">
                            <b style="color:#10b981;"><i class="fa fa-shield"></i> 官方修复方案：</b><br>
                            在用户每次登录成功时，服务端<b>必须立即调用 <code>session_regenerate_id(true)</code></b>，强制注销并销毁旧会话 ID，签发新的随机会话凭据，切断会话固定攻击链。
                        </div>

                    </div>
                </div>
            </div>

            <!-- Navigation Footer -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <a href="sessionfixation.php" class="btn btn-default" style="border-radius:8px;">
                    <i class="fa fa-arrow-left"></i> 返回模块概述
                </a>
                <a href="fixation_profile.php" class="btn btn-primary" style="border-radius:8px;">
                    查看会话敏感信息页 (Profile) <i class="fa fa-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function generatePhishingLink() {
    var sid = document.getElementById('fixed_sid_input').value.trim();
    if (!sid) sid = "pika_attacker_fixed_sid_888";
    var link = "fixation_login.php?sid=" + encodeURIComponent(sid);
    var anchor = document.getElementById('phishing_link_anchor');
    anchor.href = link;
    anchor.innerText = link;
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
