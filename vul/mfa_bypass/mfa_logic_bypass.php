<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$ACTIVE = array_fill(0, 250, '');
$ACTIVE[220] = 'active open';
$ACTIVE[221] = 'active';
$ACTIVE[220] = 'active open';
$ACTIVE[221] = 'active';

$PIKA_ROOT_DIR = "../../";
include_once $PIKA_ROOT_DIR . 'header.php';

if (!isset($_SESSION['mfa_user'])) {
    $_SESSION['mfa_user'] = '';
    $_SESSION['mfa_step1_ok'] = false;
    $_SESSION['mfa_verified'] = false;
}

$mfa_result = "";
$current_stage = "step1";
if ($_SESSION['mfa_step1_ok']) $current_stage = "step2";
if ($_SESSION['mfa_verified']) $current_stage = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['mfa_action'] ?? '';
    
    if ($action === 'login_step1') {
        $u = trim($_POST['username'] ?? '');
        $p = trim($_POST['password'] ?? '');
        if ($u === 'admin' && $p === 'admin123') {
            $_SESSION['mfa_user'] = 'admin';
            $_SESSION['mfa_step1_ok'] = true;
            $_SESSION['mfa_verified'] = false;
            $current_stage = "step2";
            $mfa_result = "<div class='alert alert-info'><b>第一步认证成功！</b>系统已向管理员手机/验证器发送二次动态验证码。请在下方输入 6 位 TOTP 口令进行第二步安全检查。</div>";
        } else {
            $mfa_result = "<div class='alert alert-danger'><b>账号或密码错误：</b>默认管理员口令为 <code>admin</code> / <code>admin123</code>。</div>";
        }
    } else if ($action === 'verify_otp') {
        $otp = trim($_POST['otp_code'] ?? '');
        if ($otp === '888888') { // 假设有效密码为 888888
            $_SESSION['mfa_verified'] = true;
            $current_stage = "success";
            $mfa_result = "<div class='alert alert-success'><h4><i class='fa fa-check'></i> 验证成功！</h4><p>您已完整通过二次认证程序。</p></div>";
        } else {
            $mfa_result = "<div class='alert alert-warning'><b>TOTP 动态码校验失败：</b>您输入的动态验证码不正确。如果您没有管理员的硬件验证令牌或手机，将无法通过常规途径登录！</div>";
        }
    } else if ($action === 'force_dashboard') {
        // 模拟强制 URL 访问后台管理路由 /admin/dashboard.php
        // 后台业务代码只检查了 $_SESSION['mfa_step1_ok']，漏掉了 $_SESSION['mfa_verified'] 的判断！
        if ($_SESSION['mfa_step1_ok'] && $_SESSION['mfa_user'] === 'admin') {
            $current_stage = "success";
            $mfa_result = "<div class='alert alert-success'>
                <h4><i class='fa fa-bolt'></i> 🚀 突破成功！利用逻辑顺序缺陷直接进入企业核心控制台！</h4>
                <p><b>漏洞机理：</b>通过直接抓包修改或在浏览器地址栏强制跳转到后台管理主页 URL <code>/admin/dashboard.php</code>。系统底层的全局鉴权中间件仅仅检查了 <code>\$_SESSION['mfa_step1_ok'] === true</code>，而没有强制验证 <code>\$_SESSION['mfa_verified'] === true</code>！二次认证功能形同虚设！</p>
                <hr/>
                <p><b>高权管理主控室安全旗帜：</b> <code>FLAG{MFA_LOGIC_STEP_SKIPPING_BYPASS_CHAMPION}</code></p>
            </div>";
        } else {
            $mfa_result = "<div class='alert alert-danger'><b>越权阻止：</b>请先完成第一步基本账号密码登录，建立基础认证会话。</div>";
        }
    } else if ($action === 'reset') {
        $_SESSION['mfa_user'] = '';
        $_SESSION['mfa_step1_ok'] = false;
        $_SESSION['mfa_verified'] = false;
        $current_stage = "step1";
        $mfa_result = "<div class='alert alert-info'>系统认证会话已重置。</div>";
    }
}
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="mfa_bypass.php">多因素认证 MFA 绕过</a></li>
                <li class="active">MFA 验证逻辑绕过</li>
            </ul>
        </div>
        <div class="page-content">
            <div class="vul info">
                <h2>📱 MFA 多因素认证流程绕过与强制路由访问 (MFA Step-Skipping)</h2>
                <p>在传统的单点登录与防钓鱼体系中，企业要求对所有内部后台登录强制启用二次多因素身份认证（2FA / MFA）。攻击者可以通过社工或撞库等手段先拿到普通用户的账号密码，但在登录过程中会被阻挡在必须输入动态验证码（TOTP/SMS）的拦截窗口中。</p>
                <p>如果业务后台在设计鉴权逻辑时，缺乏统一的<b>会话多状态状态机校验（State Machine Verification）</b>，攻击者在完成第一步账号密码校验后，无需在 MFA 界面进行任何猜测，<b>直接修改浏览器地址栏或使用 Burp 发送 HTTP 请求，直接强制请求深层业务页面（Forced Browsing / Deep Linking）</b>，即可瞬间瓦解昂贵的多因素安全防线！</p>
                
                <hr/>
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading"><b><i class="fa fa-lock"></i> 企业统一安全认证中心 (SSO Portal)</b></div>
                            <div class="panel-body">
                                <form method="POST">
                                    <?php if ($current_stage === 'step1') { ?>
                                        <div class="form-group">
                                            <label>管理员工号 / 账号：</label>
                                            <input type="text" class="form-control" name="username" value="admin"/>
                                        </div>
                                        <div class="form-group">
                                            <label>管理口令：</label>
                                            <input type="password" class="form-control" name="password" value="admin123"/>
                                        </div>
                                        <button type="submit" name="mfa_action" value="login_step1" class="btn btn-primary btn-block"><i class="fa fa-arrow-right"></i> 登录 (进入 MFA 验证阶段)</button>
                                    <?php } else if ($current_stage === 'step2') { ?>
                                        <div class="alert alert-warning" style="margin-bottom:15px;">
                                            <i class="fa fa-mobile-phone fa-lg"></i> <b>二次安全认证拦截中：</b> 请打开您手机上的 Google Authenticator 或向管理员要取 6 位验证码。
                                        </div>
                                        <div class="form-group">
                                            <label>动态 TOTP 一次性口令 (6 位数字)：</label>
                                            <input type="text" class="form-control input-lg text-center" name="otp_code" placeholder="------" maxlength="6" style="font-size:24px; letter-spacing:5px; font-weight:bold;"/>
                                        </div>
                                        <button type="submit" name="mfa_action" value="verify_otp" class="btn btn-success btn-block"><i class="fa fa-check-square-o"></i> 提交验证码认证</button>
                                        <hr/>
                                        <div class="well" style="background:#fff3cd; border-color:#ffeeba; margin-bottom:0;">
                                            <h5><b>💡 攻击演练核心技巧：</b></h5>
                                            <p style="font-size:12px; color:#856404;">我们没有管理员的手机验证器。请不要在上面胡乱输入密码！利用后台漏检验签状态逻辑缺陷，直接点击下方的<b>【强行请求管理控制台首页 URL】</b>跳过此阶段！</p>
                                            <button type="submit" name="mfa_action" value="force_dashboard" class="btn btn-danger btn-block"><i class="fa fa-bolt"></i> 强行跳转访问 /admin/dashboard.php</button>
                                        </div>
                                    <?php } else { ?>
                                        <div class="text-center" style="padding: 20px 0;">
                                            <h3 style="color:#28a745;"><i class="fa fa-shield fa-4x"></i><br/>已授权进入系统控制台</h3>
                                            <p class="text-muted">当前授权账号：<b><?php echo htmlspecialchars($_SESSION['mfa_user'] ?? 'admin'); ?></b></p>
                                        </div>
                                    <?php } ?>
                                    <hr/>
                                    <button type="submit" name="mfa_action" value="reset" class="btn btn-default btn-sm btn-block"><i class="fa fa-refresh"></i> 重置模拟会话状态</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fa fa-desktop"></i> 后台服务器鉴权状态与调试输出</h4>
                        <div style="margin-top: 10px;">
                            <?php if (!empty($mfa_result)) { echo $mfa_result; } else { ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 在左侧体验标准的双因素认证流程。按默认密码登录后，尝试通过直接 URL 跳转攻击绕过第二步安全门禁！
                                </div>
                            <?php } ?>
                        </div>
                        <div class="panel panel-default" style="margin-top:15px;">
                            <div class="panel-heading"><b>会话变量监视器 (PHP $_SESSION Inspector)</b></div>
                            <div class="panel-body" style="font-family:monospace; font-size:12px; background:#222; color:#0f0;">
                                <?php
                                echo "['mfa_user']     =&gt; '" . htmlspecialchars($_SESSION['mfa_user'] ?? '') . "'\n";
                                echo "['mfa_step1_ok'] =&gt; " . (!empty($_SESSION['mfa_step1_ok']) ? "true (第一步已过)" : "false") . "\n";
                                echo "['mfa_verified'] =&gt; " . (!empty($_SESSION['mfa_verified']) ? "true (MFA二次验过)" : "false (MFA未完成)") . "\n";
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include_once $PIKA_ROOT_DIR . 'footer.php';
?>


