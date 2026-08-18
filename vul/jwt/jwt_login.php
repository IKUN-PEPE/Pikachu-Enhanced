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
$tamper_msg = '';
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
    header('location:jwt_login.php');
    exit();
}

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
            $login_msg = "<div class='alert alert-success'><i class='fa fa-check'></i> 登录成功！服务端已下发当前用户 JWT 凭证至 Cookie。</div>";
        } else {
            $login_msg = "<div class='alert alert-danger'><i class='fa fa-times'></i> 登录失败：用户名或密码错误。</div>";
        }
    }
}

// Handle Tampered Token Direct Submit
if (isset($_POST['tamper_submit'])) {
    $custom_token = trim($_POST['custom_jwt'] ?? '');
    if ($custom_token !== '') {
        setcookie('jwt_token', $custom_token, time() + 3600, '/');
        $_COOKIE['jwt_token'] = $custom_token;
        $tamper_msg = "<div class='alert alert-info'><i class='fa fa-refresh'></i> 已将自定义篡改 Token 写入 Cookie 状态！</div>";
    }
}

// Parse Current Session Token (Vulnerable logic: weakly checks or decodes claim)
$current_token = $_COOKIE['jwt_token'] ?? '';
$session_user = '未登录 (访客)';
$session_role = 'guest';
$session_level = 0;
$is_admin = false;

if ($current_token !== '') {
    $payload_parsed = jwt_decode_insecure($current_token);
    if (is_array($payload_parsed)) {
        $session_user = $payload_parsed['username'] ?? 'unknown';
        $session_role = $payload_parsed['role'] ?? 'user';
        $session_level = intval($payload_parsed['level'] ?? 0);
        if ($session_role === 'admin' || $session_level === 1) {
            $is_admin = true;
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
.stage-title {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 12px;
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
    font-size: 12.5px;
    word-break: break-all;
    color: var(--text-primary);
}
.admin-terminal {
    background: #020617;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 24px;
    color: #38bdf8;
    font-family: monospace;
    margin-top: 15px;
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
               data-content="使用 pikachu/000000 登录后获取 Token，在右侧工具箱中将 role 改为 admin 或 level 改为 1 并重新编码，提交即可伪造管理员身份！">
                <i class="fa fa-lightbulb-o text-warning"></i> 提示
            </a>
        </div>

        <div class="page-content" style="max-width: 1360px; margin: 0 auto; padding: 24px 20px;">
            
            <!-- Stage Hero -->
            <div class="stage-hero">
                <div class="stage-title">
                    <i class="fa fa-unlock-alt" style="color:#818cf8;"></i> Stage 01: JWT 客户端状态修改与越权认证
                    <span class="label label-info" style="border-radius:12px; font-size:11px; padding:3px 10px;">100 PTS</span>
                </div>
                <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.6;">
                    <b>漏洞场景：</b>当服务端将会话状态（如用户角色 <code>role</code>、权限等级 <code>level</code>）完全存放在客户端 JWT Payload 中，且在处理鉴权时<b>未对 Token 进行严谨的签名校验</b>（或前端直读 Base64 自行放行），攻击者只需对 Payload 进行 Base64 解码并篡改字段，即可实现任意身份水平/垂直越权！
                </p>
            </div>

            <?php echo $flag_msg; ?>

            <div class="row">
                <!-- Left: Normal Login & Session Viewer -->
                <div class="col-md-5">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-sign-in" style="color:var(--primary);"></i> 会员身份登录入口
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

                        <div class="alert alert-info" style="border-radius:8px; font-size:12.5px; margin:0;">
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
                    </div>
                </div>

                <!-- Right: Interactive Tampering Lab & Exploit Console -->
                <div class="col-md-7">
                    <div class="stage-card">
                        <h4 style="margin:0 0 16px 0; font-size:16px; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <i class="fa fa-magic" style="color:var(--primary);"></i> 交互式 Token 篡改与越权提交工具箱
                        </h4>

                        <?php echo $tamper_msg; ?>

                        <div class="row">
                            <div class="col-sm-6" style="margin-bottom:12px;">
                                <label style="font-size:12.5px; font-weight:600;">编辑 Payload JSON (Data Claims):</label>
                                <textarea id="payload_json_editor" style="width:100%; height:130px; font-family:monospace; font-size:12px; background:var(--bg-app); border:1px solid var(--border-subtle); border-radius:6px; padding:10px; color:var(--text-primary);">{
  "username": "admin",
  "level": 1,
  "role": "admin",
  "iat": <?php echo time(); ?>
}</textarea>
                            </div>
                            <div class="col-sm-6" style="margin-bottom:12px;">
                                <label style="font-size:12.5px; font-weight:600;">生成并提交的 Custom Token:</label>
                                <form method="POST">
                                    <textarea id="tampered_token_output" name="custom_jwt" style="width:100%; height:85px; font-family:monospace; font-size:11px; background:var(--bg-app); border:1px solid var(--border-subtle); border-radius:6px; padding:8px; color:#a855f7; word-break:break-all;"></textarea>
                                    <div style="margin-top:8px; display:flex; gap:8px;">
                                        <button type="button" class="btn btn-warning btn-xs" onclick="generateTamperedToken()" style="border-radius:4px; font-weight:600;">
                                            <i class="fa fa-wrench"></i> 编码组装 Token
                                        </button>
                                        <button type="submit" name="tamper_submit" class="btn btn-danger btn-xs" style="border-radius:4px; font-weight:600;">
                                            <i class="fa fa-paper-plane"></i> 写入 Cookie 重放请求
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Admin Terminal Output Area -->
                        <?php if ($is_admin) { ?>
                            <div class="admin-terminal">
                                <div style="color:#10b981; font-weight:700; font-size:15px; margin-bottom:12px;">
                                    <i class="fa fa-shield"></i> 🎯 成功进入系统高权管理中枢 (Admin Center Disclosed)
                                </div>
                                <div style="color:#e2e8f0; font-size:13px; line-height:1.7;">
                                    [+] 身份鉴权通过: <b><?php echo htmlspecialchars($session_user); ?></b> (Role: <?php echo htmlspecialchars($session_role); ?>, Level: <?php echo $session_level; ?>)<br>
                                    [+] 会话模式: Client-side Stateless JWT Bypass Verified<br>
                                    [+] 核心机密中枢 Flag: <span style="color:#f59e0b; font-weight:bold; font-size:14px;">flag{JWT_Auth_Bypass_Client_Tamper_Success}</span><br>
                                    [+] 管理系统数据检索完成，获得全库超级只读授权！
                                </div>
                            </div>
                        <?php } else { ?>
                            <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:10px; padding:20px; text-align:center; color:var(--text-muted); font-size:13px; margin-top:15px;">
                                <i class="fa fa-lock" style="font-size:24px; margin-bottom:8px; display:block; color:var(--text-muted);"></i>
                                当前会话权限为普通用户 (Guest / User)，管理中心处于锁定状态。<br>
                                请将 Payload 中的 <code>role</code> 修改为 <code>admin</code> 并提交伪造 Token！
                            </div>
                        <?php } ?>

                        <hr style="border-color:var(--border-subtle);">

                        <!-- Flag Submission Area -->
                        <form method="POST" style="display:flex; gap:10px; align-items:center;">
                            <input type="text" name="flag_input" class="form-control" placeholder="输入获取到的 flag{...}" style="border-radius:6px;" required>
                            <button type="submit" name="check_flag" class="btn btn-success" style="border-radius:6px; font-weight:700; white-space:nowrap;">
                                <i class="fa fa-check"></i> 提交 Flag
                            </button>
                        </form>
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

<script>
function b64url_encode(str) {
    var b64 = window.btoa(unescape(encodeURIComponent(str)));
    return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function generateTamperedToken() {
    var header = { "alg": "HS256", "typ": "JWT" };
    var payloadStr = document.getElementById('payload_json_editor').value.trim();
    try {
        var payloadObj = JSON.parse(payloadStr);
        var hEnc = b64url_encode(JSON.stringify(header));
        var pEnc = b64url_encode(JSON.stringify(payloadObj));
        // Mock signature for client tamper demo
        var fakeSig = "tampered_signature_payload_insecure";
        document.getElementById('tampered_token_output').value = hEnc + '.' + pEnc + '.' + fakeSig;
    } catch(e) {
        alert("Payload 不是合法的 JSON 格式，请检查语法！");
    }
}

// Auto generate on page load
window.addEventListener('DOMContentLoaded', function() {
    generateTamperedToken();
});
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
