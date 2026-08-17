<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (GET) 会员登录鉴权网关
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[27] = 'active';

$link = connect();

// 如果已经登录，直接进入会员中心
if (check_csrf_login($link)) {
    header("location:csrf_get.php");
    exit();
}

$error_msg = "";
$username_val = "";

if (isset($_GET['submit']) || isset($_POST['submit'])) {
    $username_val = trim($_REQUEST['username'] ?? '');
    $password_val = trim($_REQUEST['password'] ?? '');

    if ($username_val !== '' && $password_val !== '') {
        $username_safe = escape($link, $username_val);
        $password_safe = escape($link, $password_val);

        $query = "select * from member where username='$username_safe' and pw=md5('$password_safe')";
        $result = execute($link, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $data = mysqli_fetch_assoc($result);
            $_SESSION['csrf']['username'] = $username_val;
            $_SESSION['csrf']['password'] = sha1(md5($password_val));

            header("location:csrf_get.php");
            exit();
        } else {
            $error_msg = "登录失败：用户名或密码错误，请检查输入或点击下方账号快速填入！";
        }
    } else {
        $error_msg = "请输入用户名和密码！";
    }
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
.cyber-login-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 26px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.cyber-input-group {
    position: relative;
    margin-bottom: 18px;
}
.cyber-input-group label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
    display: block;
}
.cyber-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.cyber-input-icon {
    position: absolute;
    left: 14px;
    color: var(--text-muted);
    font-size: 14px;
    pointer-events: none;
}
.cyber-input-field {
    width: 100%;
    padding: 11px 14px 11px 40px !important;
    background: var(--bg-secondary) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 8px !important;
    color: var(--text-primary) !important;
    font-size: 13.5px !important;
    transition: all 0.25s ease !important;
    box-shadow: none !important;
}
.cyber-input-field:focus {
    outline: none !important;
    border-color: #06b6d4 !important;
    background: var(--bg-card) !important;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15) !important;
}

.quick-fill-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 16px 0 22px 0;
}
.quick-fill-chip {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 12px;
    color: var(--text-secondary);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    user-select: none;
}
.quick-fill-chip:hover {
    border-color: #06b6d4;
    color: #0891b2;
    background: rgba(6, 182, 212, 0.08);
    transform: translateY(-1px);
}
[data-theme="dark"] .quick-fill-chip:hover {
    color: #38bdf8;
    background: rgba(6, 182, 212, 0.15);
}

.btn-cyber-submit {
    width: 100%;
    padding: 12px 18px;
    font-size: 14.5px;
    font-weight: 700;
    border-radius: 8px;
    background: linear-gradient(135deg, #0891b2 0%, #0284c7 100%);
    color: #ffffff;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(8, 145, 178, 0.3);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-cyber-submit:hover {
    background: linear-gradient(135deg, #06b6d4 0%, #0369a1 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 145, 178, 0.4);
    color: #ffffff;
}

.cyber-info-step {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}
.cyber-info-step:hover {
    border-color: #06b6d4;
}
.cyber-info-step-title {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cyber-info-step-desc {
    font-size: 12.5px;
    color: var(--text-secondary);
    line-height: 1.65;
    margin: 0;
}
.cyber-info-step-desc code {
    background: rgba(6, 182, 212, 0.1);
    color: #0284c7;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 11.5px;
}
[data-theme="dark"] .cyber-info-step-desc code {
    background: rgba(6, 182, 212, 0.15);
    color: #38bdf8;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF (GET) 会员登录网关</li>
            </ul>
            <a href="#" class="tips-btn" style="float:right;" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="请先登录靶场内置会员账号（如 vince / 123456 或 kobe / 123456），进入个人会员中心后将演示如何通过伪造的 GET 请求修改会员信息。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Stage Header Banner -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🔐 关卡 1: CSRF (GET 方式) - 会员鉴权网关
                        <span class="cyber-badge-chip">GET 传参 · 无状态防护 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在本演练中，受害者（会员用户）登录后将获得持久有效的会话 Session。当受害者访问含有恶意 GET 构造链接的外部网页时，浏览器将自动携带其会员会话 Cookie 提交敏感修改请求，导致会员的<b>电话、住址与邮箱被零交互篡改</b>！请先登录任意账号进入会员中心。
                    </p>
                </div>

                <div class="row">
                    <!-- Left Column: Login Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom: 20px;">
                        <div class="cyber-login-card">
                            <div>
                                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-user-circle" style="color:#06b6d4;"></i> 会员登录鉴权控制台
                                </h4>

                                <?php if (!empty($error_msg)): ?>
                                    <div class="alert alert-danger" style="border-radius:8px; font-weight:600; font-size:13px; padding:10px 14px; margin-bottom:16px;">
                                        <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="csrf_get_login.php">
                                    <div class="cyber-input-group">
                                        <label for="username_input">会员用户名 (Username)：</label>
                                        <div class="cyber-input-wrapper">
                                            <input type="text" id="username_input" name="username" class="cyber-input-field" placeholder="请输入会员用户名" value="<?php echo htmlspecialchars($username_val); ?>" required autocomplete="username" />
                                            <i class="fa fa-user cyber-input-icon"></i>
                                        </div>
                                    </div>

                                    <div class="cyber-input-group">
                                        <label for="password_input">登录密码 (Password)：</label>
                                        <div class="cyber-input-wrapper">
                                            <input type="password" id="password_input" name="password" class="cyber-input-field" placeholder="请输入密码" required autocomplete="current-password" />
                                            <i class="fa fa-lock cyber-input-icon"></i>
                                        </div>
                                    </div>

                                    <div>
                                        <span style="font-size:12px; font-weight:700; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                                            <i class="fa fa-magic" style="color:#f59e0b;"></i> 快捷填入靶场内置会员：
                                        </span>
                                        <div class="quick-fill-container">
                                            <span class="quick-fill-chip" onclick="quickFill('vince', '123456')">
                                                <i class="fa fa-user" style="color:#06b6d4;"></i> <b>vince</b> / 123456
                                            </span>
                                            <span class="quick-fill-chip" onclick="quickFill('allen', '123456')">
                                                <i class="fa fa-user" style="color:#10b981;"></i> <b>allen</b> / 123456
                                            </span>
                                            <span class="quick-fill-chip" onclick="quickFill('kobe', '123456')">
                                                <i class="fa fa-star" style="color:#f59e0b;"></i> <b>kobe</b> / 123456
                                            </span>
                                            <span class="quick-fill-chip" onclick="quickFill('lucy', '123456')">
                                                <i class="fa fa-heart" style="color:#ec4899;"></i> <b>lucy</b> / 123456
                                            </span>
                                        </div>
                                    </div>

                                    <button type="submit" name="submit" class="btn-cyber-submit">
                                        <i class="fa fa-sign-in"></i> 登录并进入个人会员中心
                                    </button>
                                </form>
                            </div>

                            <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-muted);">
                                <span><i class="fa fa-database"></i> 数据来源：<code>pikachu.member</code></span>
                                <span><i class="fa fa-shield"></i> 鉴权机制：<code>$_SESSION['csrf']</code></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Explanatory Guide -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom: 20px;">
                        <div class="cyber-login-card">
                            <div>
                                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-book" style="color:#06b6d4;"></i> GET 型 CSRF 漏洞利用深度分析
                                </h4>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #06b6d4, #2563eb); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">1</span>
                                        为什么 GET 请求最容易被 CSRF 攻击？
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        HTML 中几乎所有可以引用外部资源的标签（如 <code>&lt;img src="..."&gt;</code>、<code>&lt;iframe src="..."&gt;</code>、<code>&lt;link href="..."&gt;</code>）都会在页面加载时自动向目标发起 GET 请求，无需受害者点击任何按钮即可无感执行。
                                    </p>
                                </div>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #06b6d4, #2563eb); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">2</span>
                                        典型攻击载荷结构展示
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        攻击者只需将恶意修改 URL 封装到图片标签中并在论坛/邮件中发布：<br/>
                                        <code>&lt;img src="http://target/csrf_get_edit.php?sex=boy&phonenum=13800000000&add=Hacked&email=evil@hacker.com&submit=submit" width="0" height="0" /&gt;</code>
                                    </p>
                                </div>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #06b6d4, #2563eb); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">3</span>
                                        HTTP RFC 规范与幂等性要求
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        根据 RFC 规范，HTTP GET 应当是<b>只读且幂等（Idempotent）</b>的操作，严禁使用 GET 处理增、删、改等状态变更请求。
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="../csrf.php" class="btn btn-default" style="border-radius:8px;">
                        <i class="fa fa-arrow-left"></i> 返回 CSRF 演练大厅
                    </a>
                    <a href="../csrfpost/csrf_post_login.php" class="btn btn-warning" style="border-radius:8px; font-weight:700; color:#fff; background:linear-gradient(135deg, #f59e0b, #d97706); border:none;">
                        下一关：CSRF (POST 方式) <i class="fa fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function quickFill(user, pass) {
    const u = document.getElementById('username_input');
    const p = document.getElementById('password_input');
    if (u && p) {
        u.value = user;
        p.value = pass;
        p.focus();
    }
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
