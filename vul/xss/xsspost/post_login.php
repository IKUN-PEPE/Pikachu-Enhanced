<?php
/**
 * Pikachu-Enhanced v2.0 - 反射型 XSS (POST) 会员登录网关
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/function.php';

$ACTIVE = array_fill(0, 100, '');
$ACTIVE[8] = 'active open';
$ACTIVE[10] = 'active';

$link = connect();

// 如果已经登录，直接跳转到演练页面
if (check_xss_login($link)) {
    header("location:xss_reflected_post.php");
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

        $query = "select * from users where username='$username_safe' and password=md5('$password_safe')";
        $result = execute($link, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $data = mysqli_fetch_assoc($result);

            // 登录成功生成 cookie，设置路径为根目录 '/'
            setcookie('ant[uname]', $username_val, time() + 3600, '/');
            setcookie('ant[pw]', sha1(md5($password_val)), time() + 3600, '/');

            header("location:xss_reflected_post.php");
            exit();
        } else {
            $error_msg = "用户名或密码错误，请检查输入或点击下方账号快速填入！";
        }
    } else {
        $error_msg = "请输入用户名和密码！";
    }
}

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<style>
/* Modern Cyber Authentication Styling */
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
    transition: color 0.2s ease;
}
.cyber-input-field {
    width: 100%;
    padding: 11px 14px 11px 40px !important;
    background: var(--bg-secondary) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 8px !important;
    color: var(--text-primary) !important;
    font-size: 13.5px !important;
    font-family: inherit !important;
    transition: all 0.25s ease !important;
    box-shadow: none !important;
}
.cyber-input-field:focus {
    outline: none !important;
    border-color: #06b6d4 !important;
    background: var(--bg-card) !important;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.15) !important;
}
.cyber-input-field:focus + .cyber-input-icon {
    color: #06b6d4;
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

/* Theory Info Steps */
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
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../xss.php">Cross-Site Scripting</a></li>
                <li class="active">反射型 XSS (POST) - 会员登录鉴权网关</li>
            </ul>
            <a href="#" class="tips-btn" style="float:right;" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="为了模拟真实企业系统中受登录保护的 POST 业务操作，请先登录有效账号（例如 admin / 123456）。登录后生成的会话凭据（Cookie）将成为 XSS 攻击的核心目标。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Stage Header Banner -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🔐 反射型 XSS (POST) - 会员登录鉴权网关
                        <span class="cyber-badge-chip">前置登录鉴权 · 模拟企业受限业务 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在真实的企业级 Web 应用中，大多数具有业务价值的 POST 接口（如转账、修改敏感资料、查询会员信息）都受用户认证体系保护。本网关用于模拟已登录会话环境。登录成功后，系统将在浏览器中写入 <code>ant[uname]</code> 与 <code>ant[pw]</code> 会话凭证，供后续演练<b>跨站脚本 Cookie 窃取</b>与<b>结合 CSRF 的表单自动提交利用</b>！
                    </p>
                </div>

                <div class="row">
                    <!-- Left Column: Login Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom: 20px;">
                        <div class="cyber-login-card">
                            <div>
                                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-user-circle" style="color:#06b6d4;"></i> 登录鉴权控制台
                                </h4>

                                <?php if (!empty($error_msg)): ?>
                                    <div class="alert alert-danger" style="border-radius:8px; font-weight:600; font-size:13px; padding:10px 14px; margin-bottom:16px;">
                                        <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="post_login.php" id="loginForm">
                                    <div class="cyber-input-group">
                                        <label for="username_input">用户名 (Username)：</label>
                                        <div class="cyber-input-wrapper">
                                            <input type="text" id="username_input" name="username" class="cyber-input-field" placeholder="请输入用户名" value="<?php echo htmlspecialchars($username_val); ?>" required autocomplete="username" />
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
                                            <i class="fa fa-magic" style="color:#f59e0b;"></i> 快捷填入靶场内置账号：
                                        </span>
                                        <div class="quick-fill-container">
                                            <span class="quick-fill-chip" onclick="quickFill('admin', '123456')">
                                                <i class="fa fa-shield" style="color:#10b981;"></i> <b>admin</b> / 123456 (管理员)
                                            </span>
                                            <span class="quick-fill-chip" onclick="quickFill('pikachu', '000000')">
                                                <i class="fa fa-bolt" style="color:#f59e0b;"></i> <b>pikachu</b> / 000000 (普通用户)
                                            </span>
                                            <span class="quick-fill-chip" onclick="quickFill('test', 'abc123')">
                                                <i class="fa fa-flask" style="color:#8b5cf6;"></i> <b>test</b> / abc123 (测试账号)
                                            </span>
                                        </div>
                                    </div>

                                    <button type="submit" name="submit" class="btn-cyber-submit">
                                        <i class="fa fa-sign-in"></i> 立即验证并进入 POST XSS 演练
                                    </button>
                                </form>
                            </div>

                            <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-muted);">
                                <span><i class="fa fa-database"></i> 数据来源：<code>pikachu.users</code></span>
                                <span><i class="fa fa-clock-o"></i> 会话有效期：1 小时</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Educational Background & Exploitation Mechanism -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom: 20px;">
                        <div class="cyber-login-card">
                            <div>
                                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-book" style="color:#06b6d4;"></i> 深度攻防背景与利用机制
                                </h4>

                                <!-- Step 1 -->
                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #06b6d4, #2563eb); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">1</span>
                                        为什么 POST XSS 需要前置登录？
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        GET 型 XSS 可通过直接把 Payload 拼接在 URL 中（如 <code>?message=&lt;script&gt;...</code>）发给受害者诱导点击；但 POST 型参数位于 HTTP 请求 Body 中。在真实业务里，POST 接口通常处于鉴权保护之下，攻击者必须针对<b>已登录用户</b>设计利用场景。
                                    </p>
                                </div>

                                <!-- Step 2 -->
                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #06b6d4, #2563eb); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">2</span>
                                        登录后生成的 Cookie 凭证
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        登录成功后，服务端会向客户端植入 <code>ant[uname]</code> 与 <code>ant[pw]</code>（密码哈希）。一旦进入后续关卡触发 XSS，注入的 JavaScript 脚本即可通过 <code>document.cookie</code> 直接窃取这些高价值凭据并回传至攻击者控制的钓鱼平台。
                                    </p>
                                </div>

                                <!-- Step 3 -->
                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #06b6d4, #2563eb); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">3</span>
                                        结合 CSRF 的自动提交利用链
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        由于攻击者无法直接发送包含 POST 数据的链接，常规做法是构建第三方钓鱼页面，内嵌隐藏表单并结合 JavaScript <code>document.forms[0].submit()</code>，受害者访问钓鱼网页时会自动向目标网站提交恶意 POST 请求并触发反射 XSS。
                                    </p>
                                </div>

                                <!-- Defense -->
                                <div style="background:rgba(16,185,129,0.06); border-left:4px solid #10b981; padding:12px 14px; border-radius:6px; margin-top:14px;">
                                    <h5 style="color:#10b981; margin:0 0 6px 0; font-weight:700; font-size:13px;"><i class="fa fa-shield"></i> 最佳防御建议</h5>
                                    <ul style="margin:0; padding-left:18px; font-size:12px; color:var(--text-secondary); line-height:1.6;">
                                        <li>开启 Cookie <code>HttpOnly</code> 标志，防止脚本直接窃取会话。</li>
                                        <li>对所有 POST 请求引入 <b>Anti-CSRF Token</b> 机制。</li>
                                        <li>对所有输出到 HTML 页面中的用户输入实施严格的上下文实体编码。</li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="../xss_reflected_get.php" class="btn btn-default" style="border-radius:8px;">
                        <i class="fa fa-arrow-left"></i> 上一关：反射型 XSS (GET)
                    </a>
                    <a href="../xss.php" class="btn btn-primary" style="border-radius:8px; background:linear-gradient(135deg, #0891b2, #0e7490); border:none; font-weight:700;">
                        返回 XSS 漏洞大厅 <i class="fa fa-th-large"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function quickFill(user, pass) {
    const userInput = document.getElementById('username_input');
    const passInput = document.getElementById('password_input');
    if (userInput && passInput) {
        userInput.value = user;
        passInput.value = pass;
        passInput.focus();
    }
}
</script>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>
