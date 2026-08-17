<?php
/**
 * Pikachu-Enhanced v2.0 - 基于表单的暴力破解教学演练
 */
$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'inc/config.inc.php';
include_once $PIKA_ROOT_DIR . 'inc/mysql.inc.php';
include_once $PIKA_ROOT_DIR . 'header.php';

$link = connect();
$html = '';
$login_success = false;
$user_name = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (isset($_POST['submit']) && !empty($user_name) && !empty($password)) {
    $sql = "select * from users where username=? and password=md5(?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('ss', $user_name, $password);
    
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $login_success = true;
            $html = "<div class='alert alert-success' style='margin:0;'>
                <h4 style='margin-top:0;'><i class='fa fa-check-circle'></i> 🚀 登录成功！身份验证通过</h4>
                <p style='margin-bottom:0;'>欢迎超级用户 <b>" . htmlspecialchars($user_name) . "</b> 进入控制台！</p>
            </div>";
        } else {
            $html = "<div class='alert alert-danger' style='margin:0;'>
                <i class='fa fa-times-circle'></i> 登录失败：用户名或密码错误，请重新尝试。
            </div>";
        }
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="burteforce.php">Brute Force</a></li>
                <li class="active">基于表单的暴力破解教学演练</li>
            </ul>
            <a href="#" class="tips-btn" data-container="body" data-toggle="popover" data-placement="bottom" title="💡 攻防提示" data-content="系统未设置验证码与登录失败次数限制。可使用 Burp Suite Intruder 或字典爆破常用弱口令。">
                <i class="fa fa-lightbulb-o"></i> 点我查看攻防提示
            </a>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        ⚡ 1. 基于表单的暴力破解 (Brute Force) 攻防教学
                        <span class="cyber-badge-chip">暴力猜解 · 字典爆破 · 100 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        暴力破解（Brute Force Attack）是指攻击者通过自动化工具，在没有有效防护策略（如图形验证码、动态令牌、登录错误阈值锁定、IP 频控）的认证接口上，<b>批量枚举猜解用户名与密码字典</b>。只要密码强度不足或存在常见弱口令（如 <code>123456</code>、<code>admin</code>），就会在短时间内被自动化脚本成功猜解！
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-step-card">
                            <h4 class="cyber-step-title"><i class="fa fa-lock" style="color:var(--primary);"></i> 用户登录认证控制台</h4>
                            <p style="color:var(--text-secondary); font-size:13px; margin-bottom:14px;">请输入凭据或使用 Burp Suite Intruder 进行字典爆破：</p>
                            
                            <form method="POST" action="">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label for="user_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">用户名 (Username)：</label>
                                    <input class="form-control" type="text" id="user_input" name="username" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="输入用户名，如 admin" style="font-family:monospace;" />
                                </div>
                                <div class="form-group" style="margin-bottom:14px;">
                                    <label for="pass_input" style="font-weight:600; color:var(--text-primary); font-size:13px;">密码 (Password)：</label>
                                    <input class="form-control" type="password" id="pass_input" name="password" placeholder="输入密码或弱口令" style="font-family:monospace;" />
                                </div>
                                <button class="btn btn-primary" type="submit" name="submit" value="submit" style="margin-bottom:14px;">
                                    <i class="fa fa-sign-in"></i> 登录认证
                                </button>
                                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                                    <button type="button" class="payload-chip-btn" onclick="fillCreds('admin', '123456')"><i class="fa fa-key" style="color:var(--warning);"></i> 填入管理员凭据: admin / 123456</button>
                                    <button type="button" class="payload-chip-btn payload-chip-warning" onclick="fillCreds('kobe', '123456')"><i class="fa fa-user"></i> 填入普通用户: kobe / 123456</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--border-subtle); margin:16px 0;" />

                            <h4 class="cyber-step-title"><i class="fa fa-desktop" style="color:var(--accent);"></i> 认证服务响应回显</h4>
                            <div id="render_output" style="margin-top:10px;">
                                <?php if (!empty($html)): echo $html; else: ?>
                                    <div style="background:var(--bg-secondary); border:1px dashed var(--border-subtle); border-radius:var(--radius-md); padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                                        <i class="fa fa-info-circle"></i> 请在上方输入凭据提交验证
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($login_success): ?>
                            <div class="cyber-flag-card" style="margin-top:16px;">
                                <h4 style="color:#10b981; margin:0 0 6px 0; font-size:15px; font-weight:700;"><i class="fa fa-trophy"></i> 🎉 成功爆破进入账户！</h4>
                                <p style="color:var(--text-secondary); margin:0 0 8px 0; font-size:13px;">你成功命中弱口令凭证。通关凭证：</p>
                                <div>
                                    <span class="label label-success" style="font-size:13px; padding:5px 12px; font-family:monospace; border-radius:var(--radius-sm);">FLAG{BRUTE_FORCE_FORM_CRACKED_SUCCESS}</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="cyber-guide-section">
                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-primary" style="background:var(--primary);">1</span> 暴力破解核心检测点</h4>
                                <div class="cyber-principle-box">
                                    <p>1. <b>无验证码防护：</b> 请求可被脚本无限次并发提交；</p>
                                    <p>2. <b>无错误次数锁定：</b> 连续输错 100 次也不会封禁 IP 或冻结账号；</p>
                                    <p style="margin-bottom:0;">3. <b>回显区分明显：</b> 登录成功与失败的 HTTP 响应特征差异大。</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-warning" style="background:var(--warning);">2</span> Burp Suite 爆破方法</h4>
                                <div class="cyber-principle-box">
                                    <p>1. 抓取 POST 登录请求包，发送至 <b>Intruder</b>；</p>
                                    <p>2. 攻击模式选择 <b>Cluster Bomb</b> 或 <b>Sniper</b>；</p>
                                    <p style="margin-bottom:0;">3. 加载 Top 100 常见弱口令字典，按响应 Length 降序排序。</p>
                                </div>
                            </div>

                            <div class="cyber-step-card">
                                <h4 class="cyber-step-title"><span class="badge badge-success" style="background:var(--success);">3</span> 源码审计与安全防御</h4>
                                <details class="cyber-details-box" open>
                                    <summary class="cyber-details-summary">安全防御方案 (多因子与频控)</summary>
                                    <div class="cyber-details-body">
<pre style="background:#0f172a; color:#f8fafc; border-radius:6px; padding:10px; font-size:12px; margin:0;">
// ✅ 强制引入一次性人机验证码 (CAPTCHA)
// ✅ 实施账户级与 IP 级的请求速率限制 (Rate Limiting)
// ✅ 输错 5 次后增加动态延迟与滑块验证
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
function fillCreds(u, p) {
    document.getElementById('user_input').value = u;
    document.getElementById('pass_input').value = p;
}
</script>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
