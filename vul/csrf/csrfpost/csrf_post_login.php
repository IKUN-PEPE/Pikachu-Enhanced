<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (POST) 会员登录鉴权网关
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[28] = 'active';

$link = connect();

// 如果已经登录，直接进入会员中心
if (check_csrf_login($link)) {
    header("location:csrf_post.php");
    exit();
}

$error_msg = "";
$username_val = "";

if (isset($_POST['submit'])) {
    $username_val = trim($_POST['username'] ?? '');
    $password_val = trim($_POST['password'] ?? '');

    if ($username_val !== '' && $password_val !== '') {
        $username_safe = escape($link, $username_val);
        $password_safe = escape($link, $password_val);

        $query = "select * from member where username='$username_safe' and pw=md5('$password_safe')";
        $result = execute($link, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $data = mysqli_fetch_assoc($result);
            $_SESSION['csrf']['username'] = $username_val;
            $_SESSION['csrf']['password'] = sha1(md5($password_val));

            header("location:csrf_post.php");
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
    border-color: #f59e0b !important;
    background: var(--bg-card) !important;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
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
    border-color: #f59e0b;
    color: #d97706;
    background: rgba(245, 158, 11, 0.08);
    transform: translateY(-1px);
}
[data-theme="dark"] .quick-fill-chip:hover {
    color: #fbbf24;
    background: rgba(245, 158, 11, 0.15);
}

.btn-cyber-submit-post {
    width: 100%;
    padding: 12px 18px;
    font-size: 14.5px;
    font-weight: 700;
    border-radius: 8px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-cyber-submit-post:hover {
    background: linear-gradient(135deg, #fbbf24 0%, #b45309 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
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
    border-color: #f59e0b;
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
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 11.5px;
}
[data-theme="dark"] .cyber-info-step-desc code {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF (POST) 会员登录网关</li>
            </ul>
            <a href="#" class="tips-btn" style="float:right;" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="登录任意会员账号（如 vince / 123456）进入 POST 会员中心，观察即便业务采用 POST 提交，攻击者如何通过第三方表单自动提交利用链进行跨站伪造。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Stage Header Banner -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🔐 关卡 2: CSRF (POST 方式) - 会员鉴权网关
                        <span class="cyber-badge-chip" style="border-color:#f59e0b; color:#fbbf24; background:rgba(245,158,11,0.15);">POST 传参 · 伪造表单 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        很多开发者误以为只要将 HTTP 请求从 GET 改为 POST 就能杜绝 CSRF。实际上，攻击者完全可以通过在第三方恶意站点中构造一个<b>隐藏的 HTML 表单并注入自动提交脚本</b>，在受害者不知情的情况下向目标 POST 接口发起跨站伪造请求！
                    </p>
                </div>

                <div class="row">
                    <!-- Left Column: Login Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom: 20px;">
                        <div class="cyber-login-card">
                            <div>
                                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-user-circle" style="color:#f59e0b;"></i> POST 会员登录鉴权控制台
                                </h4>

                                <?php if (!empty($error_msg)): ?>
                                    <div class="alert alert-danger" style="border-radius:8px; font-weight:600; font-size:13px; padding:10px 14px; margin-bottom:16px;">
                                        <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="csrf_post_login.php">
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

                                    <button type="submit" name="submit" class="btn-cyber-submit-post">
                                        <i class="fa fa-sign-in"></i> POST 登录并进入个人中心
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
                                    <i class="fa fa-book" style="color:#f59e0b;"></i> POST 型 CSRF 利用技术剖析
                                </h4>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #f59e0b, #d97706); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">1</span>
                                        POST 请求能否有效防御 CSRF？
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        不能。虽然 POST 无法像 GET 那样通过单纯的 URL 发送，但网页支持通过 JavaScript 创建或提交表单。浏览器在提交跨域表单时，依然会自动附加目标网站的认证 Cookie。
                                    </p>
                                </div>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #f59e0b, #d97706); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">2</span>
                                        自动提交 PoC 核心原理
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        攻击者构建 HTML 文件：<br/>
                                        <code>&lt;form action="http://target/edit.php" method="POST"&gt;...&lt;/form&gt;</code><br/>
                                        配合 <code>&lt;script&gt;document.forms[0].submit();&lt;/script&gt;</code>，受害者只要加载网页，脚本就会瞬间完成 POST 提交。
                                    </p>
                                </div>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #f59e0b, #d97706); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">3</span>
                                        从本质理解 CSRF 防护核心
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        CSRF 防护的关键在于<b>“请求来源与不可预测凭据的验证”</b>（如 Anti-CSRF Token、SameSite 属性），而非请求方法（GET / POST）的切换。
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="../csrfget/csrf_get_login.php" class="btn btn-info" style="border-radius:8px; font-weight:700;">
                        <i class="fa fa-arrow-left"></i> 上一关：CSRF (GET 方式)
                    </a>
                    <a href="../csrftoken/token_get_login.php" class="btn btn-success" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #10b981, #059669); border:none;">
                        下一关：CSRF Token 防御机制 <i class="fa fa-arrow-right"></i>
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
