<?php
/**
 * Pikachu-Enhanced v3.0 - 水平越权 (Horizontal Privilege Escalation) 登录入口
 */
ob_start();
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';

$link = @connect();

// 内置兜底账号库（防止数据库未初始化时无法体验靶场）
$FALLBACK_MEMBERS = [
    'lucy' => ['username' => 'lucy', 'pw' => md5('123456'), 'sex' => 'girl', 'phonenum' => '13666666666', 'address' => 'USA', 'email' => 'lucy@pikachu.com'],
    'lili' => ['username' => 'lili', 'pw' => md5('123456'), 'sex' => 'girl', 'phonenum' => '13888888888', 'address' => 'China', 'email' => 'lili@pikachu.com'],
    'kobe' => ['username' => 'kobe', 'pw' => md5('123456'), 'sex' => 'boy',  'phonenum' => '13999999999', 'address' => 'LA',    'email' => 'kobe@pikachu.com'],
];

function op1_is_logged_in($link, $fallback_members) {
    if (!empty($_SESSION['op']['username']) && !empty($_SESSION['op']['password'])) {
        $u = $_SESSION['op']['username'];
        if ($link) {
            $res = @execute($link, "SELECT * FROM member WHERE username='" . escape($link, $u) . "' AND sha1(pw)='{$_SESSION['op']['password']}'");
            if ($res && mysqli_num_rows($res) === 1) {
                return true;
            }
        }
        if (isset($fallback_members[$u]) && sha1($fallback_members[$u]['pw']) === $_SESSION['op']['password']) {
            return true;
        }
    }
    return false;
}

// 支持强制重新登录参数 ?switch=1
if (isset($_GET['switch']) && $_GET['switch'] == '1') {
    unset($_SESSION['op']);
}

// 若已登录且未要求切换账号，直接进入会员中心
if (op1_is_logged_in($link, $FALLBACK_MEMBERS)) {
    header("Location: op1_mem.php");
    exit;
}

$login_error = '';
$post_user = trim($_POST['username'] ?? '');

if (isset($_POST['submit'])) {
    $password = $_POST['password'] ?? '';
    if ($post_user === '' || $password === '') {
        $login_error = "请输入完整的用户名与登录密码（可点击下方测试账号胶囊一键填入）。";
    } else {
        $authed = false;
        if ($link) {
            $u_esc = escape($link, $post_user);
            $p_esc = escape($link, $password);
            $query = "SELECT * FROM member WHERE username='$u_esc' AND pw=md5('$p_esc')";
            $result = @execute($link, $query);
            if ($result && mysqli_num_rows($result) === 1) {
                $authed = true;
            }
        }
        if (!$authed && isset($FALLBACK_MEMBERS[$post_user]) && $FALLBACK_MEMBERS[$post_user]['pw'] === md5($password)) {
            $authed = true;
        }

        if ($authed) {
            $_SESSION['op']['username'] = $post_user;
            $_SESSION['op']['password'] = sha1(md5($password));
            header("Location: op1_mem.php");
            exit;
        } else {
            $login_error = "登录失败：用户名或密码错误，请使用下方提供的测试账号重试。";
        }
    }
}

$ACTIVE = array_fill(0, 300, '');
$ACTIVE[73] = 'active open';
$ACTIVE[75] = 'active';
include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../op.php">越权访问控制</a></li>
                <li class="active">水平越权 (Horizontal BAC) · 登录入口</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="测试账号：lucy/123456、lili/123456、kobe/123456。登录任一账号后，在查询个人信息时篡改 URL 中的 username 参数即可越权读取其他用户隐私。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <!-- 头部卡片 -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        👥 1. 水平越权漏洞攻防演练 (Horizontal Privilege Escalation)
                        <span class="cyber-badge-chip">访问控制缺陷 · OWASP A01 · 150 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        水平越权（又称基于用户标识的直接对象引用 <b>IDOR</b>）是指相同权限级别的不同用户之间，因服务端<b>仅校验了“是否已登录”，而未校验“请求查询的目标数据是否归属于当前 Session 用户”</b>，导致攻击者只需修改请求中的用户名或 ID 参数，即可横向查阅、修改甚至删除其他平级用户的敏感档案。
                    </p>
                </div>

                <div class="row">
                    <!-- 左侧：会员中心登录控制台 -->
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title">
                                <i class="fa fa-id-badge" style="color:var(--primary);"></i> 步骤一：登录会员个人中心
                            </h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:16px;">
                                请先使用任意一个普通会员账号登录系统，获取合法的初始会话凭证（<code>$_SESSION['op']</code>）：
                            </p>

                            <?php if (!empty($login_error)): ?>
                                <div class="alert alert-danger" style="border-radius:var(--radius-md); margin-bottom:16px; font-size:13px;">
                                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($login_error); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="op1_login.php" id="op1LoginForm">
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label for="op1_username" style="font-weight:600; color:var(--text-primary); font-size:13px;">
                                        <i class="fa fa-user" style="color:var(--primary); width:16px;"></i> 会员账号 (Username)：
                                    </label>
                                    <input class="form-control" type="text" id="op1_username" name="username" value="<?php echo htmlspecialchars($post_user ?: 'lucy'); ?>" placeholder="请输入用户名，如 lucy" style="font-family:monospace;" autocomplete="username" />
                                </div>

                                <div class="form-group" style="margin-bottom:16px;">
                                    <label for="op1_password" style="font-weight:600; color:var(--text-primary); font-size:13px;">
                                        <i class="fa fa-lock" style="color:var(--primary); width:16px;"></i> 登录密码 (Password)：
                                    </label>
                                    <input class="form-control" type="password" id="op1_password" name="password" value="123456" placeholder="请输入密码 (默认 123456)" style="font-family:monospace;" autocomplete="current-password" />
                                </div>

                                <div style="margin-bottom:16px;">
                                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px; font-weight:600;">⚡ 一键填入内置测试账号（密码均为 <code>123456</code>）：</div>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <button type="button" class="payload-chip-btn" onclick="fillAccount('lucy', '123456')">
                                            <i class="fa fa-user" style="color:var(--primary);"></i> 账号 A：lucy / 123456
                                        </button>
                                        <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillAccount('lili', '123456')">
                                            <i class="fa fa-user" style="color:var(--warning);"></i> 账号 B：lili / 123456
                                        </button>
                                        <button type="button" class="payload-chip-btn payload-chip-danger" onclick="fillAccount('kobe', '123456')">
                                            <i class="fa fa-dribbble" style="color:var(--danger);"></i> 账号 C：kobe / 123456
                                        </button>
                                    </div>
                                </div>

                                <button class="btn btn-primary btn-block" type="submit" name="submit" value="Login" style="padding:10px 16px; font-weight:600; border-radius:var(--radius-md);">
                                    <i class="fa fa-sign-in"></i> 登录并进入会员中心 (op1_mem.php)
                                </button>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:20px 0;" />

                            <!-- 数据库预置会员一览表 -->
                            <h4 class="cyber-step-title" style="margin-bottom:10px;">
                                <i class="fa fa-database" style="color:var(--accent);"></i> 靶场预置平行会员账号表 (member)
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" style="margin-bottom:0; font-size:12.5px;">
                                    <thead>
                                        <tr style="background:var(--bg-secondary);">
                                            <th>用户名</th>
                                            <th>密码</th>
                                            <th>权限角色</th>
                                            <th>演练定位</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>lucy</code></td>
                                            <td><code>123456</code></td>
                                            <td><span class="label label-info">普通会员</span></td>
                                            <td>当前登录攻击者账号 (Attacker)</td>
                                        </tr>
                                        <tr>
                                            <td><code>lili</code></td>
                                            <td><code>123456</code></td>
                                            <td><span class="label label-info">普通会员</span></td>
                                            <td>平级受害者账号 1 (Target A)</td>
                                        </tr>
                                        <tr>
                                            <td><code>kobe</code></td>
                                            <td><code>123456</code></td>
                                            <td><span class="label label-info">普通会员</span></td>
                                            <td>平级受害者账号 2 (Target B)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 右侧：教学模式与攻防指南 -->
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <!-- 步骤 1 -->
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 水平越权成因与数据流分析</h4>
                                <div class="cyber-principle-box">
                                    <p><b>漏洞数据流：</b> <span class="flow-step-tag">Session 验证通过</span> ➔ <span class="flow-step-tag">读取 $_GET['username']</span> ➔ <span class="flow-step-tag">未对比 Session 归属</span> ➔ <span class="flow-step-tag">直接查询数据库回显</span></p>
                                    <p style="margin-bottom:0;">
                                        在 <code>op1_mem.php</code> 中，开发者虽然在页面顶部调用了 <code>check_op_login($link)</code> 验证用户已登录，但在执行 SQL 查询 <code>SELECT * FROM member WHERE username='$username'</code> 时，直接使用了客户端可控的 <code>$_GET['username']</code>，而没有验证 <code>$_GET['username'] === $_SESSION['op']['username']</code>。
                                    </p>
                                </div>
                            </div>

                            <!-- 步骤 2 -->
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> 三步完成水平越权实战</h4>
                                <ol style="margin:0; padding-left:18px; font-size:13px; color:var(--text-secondary); line-height:1.8;">
                                    <li>在左侧使用 <code>lucy / 123456</code> 登录，进入会员中心 <code>op1_mem.php</code>；</li>
                                    <li>点击 <b>“查询个人信息”</b> 按钮，观察浏览器地址栏 URL 变为：<br/>
                                        <code>op1_mem.php?username=lucy&amp;submit=点击查看个人信息</code></li>
                                    <li>将 URL 参数中的 <code>username=lucy</code> 篡改为 <code>username=kobe</code> 或 <code>username=lili</code> 并回车，即可横向读取其他会员的手机、住址与邮箱隐私并获取 Flag！</li>
                                </ol>
                            </div>

                            <!-- 步骤 3 -->
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全修复方案</h4>

                                <details class="cyber-details-box">
                                    <summary class="cyber-details-summary">查看漏洞代码 (Vulnerable Code - op1_mem.php)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ❌ 缺陷：信任前端传入的 hidden 表单字段 $_GET['username']
if (isset($_GET['submit']) &amp;&amp; $_GET['username'] != null) {
    $username = escape($link, $_GET['username']);
    $query = "SELECT * FROM member WHERE username='$username'";
    $result = execute($link, $query);
}
</pre>
                                    </div>
                                </details>

                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">查看安全修复代码 (Remediated Code)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 修复方案 1：直接从服务端受信任的 Session 中获取当前用户身份
$username = escape($link, $_SESSION['op']['username']);
$query = "SELECT * FROM member WHERE username='$username'";

// ✅ 修复方案 2：若业务必须传参，强制实施对象级归属校验 (BOLA Check)
if ($_GET['username'] !== $_SESSION['op']['username']) {
    http_response_code(403);
    die("403 Forbidden: 禁止水平越权访问他人档案！");
}
</pre>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function fillAccount(user, pass) {
    document.getElementById('op1_username').value = user;
    document.getElementById('op1_password').value = pass;
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
