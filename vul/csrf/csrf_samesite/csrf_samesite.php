<?php
/**
 * Pikachu-Enhanced v2.0 - CSRF 进阶关卡 5: SameSite Lax 机制限制与请求方法覆盖 (Method Override) 绕过
 */
$PIKA_ROOT_DIR = "../../../";
include_once $PIKA_ROOT_DIR . "inc/config.inc.php";
include_once $PIKA_ROOT_DIR . "inc/function.php";
include_once $PIKA_ROOT_DIR . "inc/mysql.inc.php";

$ACTIVE = array_fill(0, 250, '');
$ACTIVE[25] = 'active open';
$ACTIVE[34] = 'active';

$link = connect();

if (!isset($_SESSION['csrf']['username'])) {
    $_SESSION['csrf']['username'] = 'vince';
}

$alert_msg = "";
$alert_type = "";

// 模拟支持请求方式覆盖的后端框架 (如 Laravel / Spring / Rails 中的 _method 或 X-HTTP-Method-Override)
$effective_method = $_SERVER['REQUEST_METHOD'];
if (isset($_REQUEST['_method'])) {
    $effective_method = strtoupper($_REQUEST['_method']);
} elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $effective_method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}

// 检查是否触发了针对 POST/PUT 的修改逻辑
if (($effective_method === 'POST' || $effective_method === 'PUT') && (isset($_REQUEST['submit']) || isset($_REQUEST['email']))) {
    if (!empty($_REQUEST['email']) && !empty($_REQUEST['phonenum'])) {
        $email = escape($link, $_REQUEST['email']);
        $phone = escape($link, $_REQUEST['phonenum']);
        $user = escape($link, $_SESSION['csrf']['username']);
        
        $query = "UPDATE member SET email='$email', phonenum='$phone' WHERE username='$user'";
        execute($link, $query);
        
        $is_get_navigation = ($_SERVER['REQUEST_METHOD'] === 'GET');
        $alert_type = $is_get_navigation ? "danger" : "success";
        $alert_msg = $is_get_navigation 
            ? "💥 <b>SameSite=Lax 绕过成功！</b> 攻击者利用 <code>_method=POST</code> 参数通过浏览器顶层 GET 导航携带了 Lax Cookie，并在服务端成功触发了 POST 敏感修改操作！" 
            : "✅ <b>更新成功！</b> 通过常规 POST 请求完成更新。";
    }
}

$user_safe = escape($link, $_SESSION['csrf']['username']);
$res = execute($link, "SELECT * FROM member WHERE username='$user_safe'");
$member = mysqli_fetch_assoc($res);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8765';
$override_url = "{$protocol}{$host}/vul/csrf/csrf_samesite/csrf_samesite.php?_method=POST&email=pwned_lax_bypass@evil.com&phonenum=13877776666&submit=submit";

include_once $PIKA_ROOT_DIR . 'header.php';
?>

<div class="main-content">
    <div class="main-content-inner">
        <div class="breadcrumbs ace-save-state" id="breadcrumbs">
            <ul class="breadcrumb">
                <li><i class="ace-icon fa fa-home home-icon"></i><a href="../csrf.php">CSRF</a></li>
                <li class="active">CSRF 进阶 5: SameSite Lax 绕过</li>
            </ul>
        </div>

        <div class="page-content">
            <div class="cyber-stage-container">
                <div class="cyber-header-card">
                    <h1 class="cyber-header-title">
                        🎯 关卡 8: SameSite=Lax 限制与请求方法覆盖 (Method Override) 绕过
                        <span class="cyber-badge-chip">现代浏览器防护 · 顶层导航绕过 · 300 PTS</span>
                    </h1>
                    <p class="cyber-desc-text">
                        在现代浏览器中，Cookie 默认施加 <code>SameSite=Lax</code> 策略，跨站（Cross-Site）发起的异步请求与子资源加载（如图片、隐藏 POST 表单）将<b>拒绝携带 Cookie</b>。然而，Lax 策略对于<b>顶层导航（Top-Level Navigation 如直接链接跳转）依然允许携带 Cookie</b>！若后端框架支持 <code>_method=POST</code> 请求方式覆盖，攻击者只需诱导受害者点击一个常规链接，即可利用顶层 GET 导航携带 Cookie 并在服务端执行 POST 业务修改！
                    </p>
                </div>

                <?php if (!empty($alert_msg)): ?>
                    <div class="alert alert-<?php echo $alert_type; ?>" style="border-radius:8px; font-size:13.5px; font-weight:600; padding:14px 18px; margin-bottom:20px;">
                        <?php echo $alert_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Left: Profile & Form -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 16px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-lock" style="color:#06b6d4;"></i> 当前会员资料 (当前用户: <?php echo htmlspecialchars($member['username'] ?? 'vince'); ?>)
                            </h4>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:14px; margin-bottom:18px; font-size:13px;">
                                <p style="margin:0 0 6px 0;"><b>会员邮箱：</b> <?php echo htmlspecialchars($member['email'] ?? ''); ?></p>
                                <p style="margin:0;"><b>会员手机：</b> <?php echo htmlspecialchars($member['phonenum'] ?? ''); ?></p>
                            </div>

                            <form method="POST" action="csrf_samesite.php">
                                <div class="form-group" style="margin-bottom:12px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">修改电子邮箱：</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" required />
                                </div>
                                <div class="form-group" style="margin-bottom:18px;">
                                    <label style="font-weight:700; color:var(--text-primary); font-size:13px;">修改手机号码：</label>
                                    <input type="text" name="phonenum" class="form-control" value="<?php echo htmlspecialchars($member['phonenum'] ?? ''); ?>" required />
                                </div>
                                <button type="submit" name="submit" value="submit" class="btn btn-info btn-block" style="border-radius:8px; font-weight:700; background:linear-gradient(135deg, #06b6d4, #0891b2); border:none; padding:10px;">
                                    <i class="fa fa-save"></i> 常规 POST 保存
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Method Override Top-Level Navigation Exploit -->
                    <div class="col-lg-6 col-md-12" style="margin-bottom:20px;">
                        <div class="cyber-login-card">
                            <h4 style="margin:0 0 14px 0; color:var(--text-primary); font-weight:800; font-size:16px;">
                                <i class="fa fa-external-link" style="color:#ef4444;"></i> Method Override 顶层导航攻击载荷
                            </h4>

                            <p style="color:var(--text-secondary); font-size:12.5px; line-height:1.6; margin-bottom:10px;">
                                攻击者将 POST 请求转化为附带 <code>_method=POST</code> 的顶层 GET 导航链接，避开 Lax 对跨站 POST 的 Cookie 拦截：
                            </p>

                            <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; padding:12px; font-family:'Fira Code', monospace; font-size:11.5px; word-break:break-all; color:#ef4444; margin-bottom:14px;">
                                <?php echo htmlspecialchars($override_url); ?>
                            </div>

                            <a href="<?php echo htmlspecialchars($override_url); ?>" class="btn btn-danger btn-block" style="border-radius:8px; font-weight:700; padding:10px;">
                                <i class="fa fa-bolt"></i> 模拟外部诱导受害者点击此顶层导航链接
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-color); padding-top:18px;">
                    <a href="../csrf_double_cookie/csrf_double_cookie.php" class="btn btn-default" style="border-radius:8px;"><i class="fa fa-arrow-left"></i> 上一关：双重 Cookie 校验绕过</a>
                    <a href="../csrf.php" class="btn btn-primary" style="border-radius:8px; font-weight:700;">返回 CSRF 演练大厅 <i class="fa fa-th-large"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once $PIKA_ROOT_DIR . 'footer.php'; ?>
