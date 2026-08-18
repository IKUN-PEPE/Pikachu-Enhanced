<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF (Token) 会员登录鉴权网关
 */

$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[29] = 'active';

$link = connect();

// 如果已经登录，直接进入会员中心
if (check_csrf_login($link)) {
    header("location:token_get.php");
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

            // 初始化 token
            set_token();

            header("location:token_get.php");
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
    border-color: #10b981 !important;
    background: var(--bg-card) !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
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
    border-color: #10b981;
    color: #059669;
    background: rgba(16, 185, 129, 0.08);
    transform: translateY(-1px);
}
[data-theme="dark"] .quick-fill-chip:hover {
    color: #34d399;
    background: rgba(16, 185, 129, 0.15);
}

.btn-cyber-submit-token {
    width: 100%;
    padding: 12px 18px;
    font-size: 14.5px;
    font-weight: 700;
    border-radius: 8px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-cyber-submit-token:hover {
    background: linear-gradient(135deg, #34d399 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
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
    border-color: #10b981;
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
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 11.5px;
}
[data-theme="dark"] .cyber-info-step-desc code {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF Token 鉴权网关</li>
            </ul>
            <a href="#" class="tips-btn" style="float:right;" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="请登录任意会员账号。进入会员中心后，系统会演示不可预测的随机 Anti-CSRF Token 是如何让跨站伪造请求彻底失效的。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                
                <!-- Stage Header Banner -->
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🔐 关卡 3: CSRF Token 防御机制 - 鉴权网关
                        <span class="cyber-badge-chip" style="border-color:#10b981; color:#34d399; background:rgba(16,185,129,0.15);">Token 动态校验 · 同源策略屏障 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        Anti-CSRF Token 是目前业界最通用的 CSRF 防御机制。服务端在用户表单中植入仅在当前会话中唯一的随机字符串。当请求提交时，后端必须严格比对该 Token 是否与用户 Session 中的值一致。由于<b>同源策略（SOP）禁止第三方站点跨域读取响应体中的 Token</b>，攻击者无法伪造出合法 Token，攻击被坚决阻断！
                    </p>
                </div>

                <div class="row">
                    <!-- Left Column: Login Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom: 20px;">
                        <div class="cyber-login-card">
                            <div>
                                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-user-circle" style="color:#10b981;"></i> Token 会员登录鉴权控制台
                                </h4>

                                <?php if (!empty($error_msg)): ?>
                                    <div class="alert alert-danger" style="border-radius:8px; font-weight:600; font-size:13px; padding:10px 14px; margin-bottom:16px;">
                                        <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="token_get_login.php">
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
                                            <i class="fa fa-magic" style="color:#10b981;"></i> 快捷填入靶场内置会员：
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

                                    <button type="submit" name="submit" class="btn-cyber-submit-token">
                                        <i class="fa fa-sign-in"></i> 登录并验证 Token 会员中心
                                    </button>
                                </form>
                            </div>

                            <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-muted);">
                                <span><i class="fa fa-database"></i> 数据来源：<code>pikachu.member</code></span>
                                <span><i class="fa fa-key"></i> 防护机制：<code>$_SESSION['token']</code></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Explanatory Guide -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom: 20px;">
                        <div class="cyber-login-card">
                            <div>
                                <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px;">
                                    <i class="fa fa-shield" style="color:#10b981;"></i> Anti-CSRF Token 防御机制原理
                                </h4>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">1</span>
                                        什么是 Anti-CSRF Token？
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        Token 是由服务端生成的一串<b>高熵随机不可预测的字符串</b>，随表单一起返回给受信任的浏览器客户端，并在用户提交敏感表单时作为参数一并回传校验。
                                    </p>
                                </div>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">2</span>
                                        为什么黑客无法伪造 Token？
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        同源策略（Same-Origin Policy）严格禁止跨域站点通过 AJAX/Fetch 读取目标网站页面中的 DOM 元素或响应内容。因此恶意站点虽然能触发请求（写操作），却无法读取（读操作）Token 的真实值！
                                    </p>
                                </div>

                                <div class="cyber-info-step">
                                    <div class="cyber-info-step-title">
                                        <span style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">3</span>
                                        Token 的潜在绕过场景（XSS + CSRF）
                                    </div>
                                    <p class="cyber-info-step-desc">
                                        如果目标网站同时存在 XSS 漏洞，攻击者可利用 XSS 脚本在受害者浏览器上下文内部执行同源请求获取 Token，进而绕过 CSRF Token 防护。这体现了<b>纵深防御（Defense-in-Depth）</b>的重要性。
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px; margin-top:10px;">
                    <a href="../csrfpost/csrf_post_login.php" class="btn btn-warning" style="border-radius:8px; font-weight:700; color:#fff;">
                        <i class="fa fa-arrow-left"></i> 上一关：CSRF (POST 方式)
                    </a>
                    <a href="../csrf_referer/csrf_referer.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">
                        下一关：CSRF Referer 绕过 <i class="fa fa-arrow-right"></i>
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
